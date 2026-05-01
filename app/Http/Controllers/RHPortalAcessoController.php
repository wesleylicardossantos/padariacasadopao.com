<?php

namespace App\Http\Controllers;

use App\Http\Requests\RH\PortalAdminConfigRequest;
use App\Http\Requests\RH\PortalInviteRequest;
use App\Http\Requests\RH\PortalLoginRequest;
use App\Http\Requests\RH\PortalPasswordResetRequest;
use App\Http\Requests\RH\PortalRecoveryRequest;
use App\Models\Funcionario;
use App\Models\RHPortalFuncionario;
use App\Modules\RH\Application\Actions\AuthenticatePortalUserAction;
use App\Modules\RH\Application\Actions\BuildPortalInviteAction;
use App\Modules\RH\Application\Actions\BuildPortalRecoveryAction;
use App\Modules\RH\Application\Actions\ConfigurePortalAccessAction;
use App\Modules\RH\Application\DTOs\AuthenticatePortalUserData;
use App\Modules\RH\Application\DTOs\ConfigurePortalAccessData;
use App\Modules\RH\Application\DTOs\PortalInviteData;
use App\Modules\RH\Application\DTOs\PortalRecoveryData;
use App\Services\RHPortalAcessoService;
use App\Services\RH\RHAdminAuditService;
use App\Services\RHWhatsAppService;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RHPortalAcessoController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct(
        private RHPortalAcessoService $portalAcessoService,
        private RHWhatsAppService $whatsAppService,
        private AuthenticatePortalUserAction $authenticatePortalUser,
        private BuildPortalRecoveryAction $buildPortalRecovery,
        private BuildPortalInviteAction $buildPortalInvite,
        private ConfigurePortalAccessAction $configurePortalAccess,
        private RHAdminAuditService $audit,
    ) {
        $this->middleware('tenant.context');
    }

    public function loginForm()
    {
        if (session('funcionario_portal.funcionario_id')) {
            return redirect()->route('rh.portal_externo.dashboard');
        }

        return view('rh.portal_acesso.login');
    }

    public function login(PortalLoginRequest $request)
    {
        $result = $this->authenticatePortalUser->execute(new AuthenticatePortalUserData(
            login: (string) $request->input('login'),
            senha: (string) $request->input('senha'),
            ip: $request->ip(),
        ));

        if (!($result['ok'] ?? false)) {
            return back()->withInput()->with('flash_erro', $result['message'] ?? 'Não foi possível autenticar o acesso ao portal.');
        }

        /** @var Funcionario $funcionario */
        $funcionario = $result['funcionario'];

        $request->session()->regenerate();

        session([
            'funcionario_portal' => [
                'funcionario_id' => (int) $funcionario->id,
                'empresa_id' => (int) $funcionario->empresa_id,
                'nome' => (string) $funcionario->nome,
                'email' => (string) $funcionario->email,
                'cpf' => (string) $funcionario->cpf,
            ],
            'tenant.empresa_id' => (int) $funcionario->empresa_id,
        ]);

        app()->instance('tenant.empresa_id', (int) $funcionario->empresa_id);

        return redirect()->route('rh.portal_externo.dashboard');
    }

    public function logout()
    {
        session()->forget('funcionario_portal');

        return redirect('/portal')->with('flash_sucesso', 'Sessão encerrada com sucesso.');
    }

    public function primeiroAcesso(string $token)
    {
        $acesso = RHPortalFuncionario::where('token_primeiro_acesso', $token)->first();
        abort_unless($this->portalAcessoService->tokenValido($acesso, 'token_primeiro_acesso'), 404);

        return view('rh.portal_acesso.definir_senha', [
            'token' => $token,
            'tipo' => 'primeiro_acesso',
            'funcionario' => $acesso->funcionario,
        ]);
    }

    public function salvarPrimeiroAcesso(PortalPasswordResetRequest $request, string $token)
    {
        $acesso = RHPortalFuncionario::where('token_primeiro_acesso', $token)->first();
        abort_unless($this->portalAcessoService->tokenValido($acesso, 'token_primeiro_acesso'), 404);

        $this->portalAcessoService->definirSenha($acesso, (string) $request->input('senha'));

        return redirect('/portal')->with('flash_sucesso', 'Senha criada com sucesso. Agora você já pode entrar com CPF ou e-mail.');
    }

    public function esqueciSenhaForm()
    {
        return view('rh.portal_acesso.recuperar');
    }

    public function enviarRecuperacao(PortalRecoveryRequest $request)
    {
        $result = $this->buildPortalRecovery->execute(new PortalRecoveryData(
            login: (string) $request->input('login'),
            canal: (string) ($request->input('canal') ?: 'email'),
        ));

        if (!($result['ok'] ?? false)) {
            return back()->withInput()->with('flash_erro', $result['message'] ?? 'Não foi possível preparar a recuperação de acesso.');
        }

        /** @var Funcionario $funcionario */
        $funcionario = $result['funcionario'];
        $mensagem = $this->mensagemRecuperacao($funcionario, (string) $result['link']);
        $canal = (string) $result['canal'];

        if ($canal === 'whatsapp') {
            $resultado = $this->whatsAppService->enviar($funcionario->celular ?: $funcionario->telefone, $mensagem);
            if (!empty($resultado['link'])) {
                return redirect($resultado['link']);
            }

            return back()->with('flash_sucesso', $resultado['mensagem'] ?? 'Link de recuperação preparado para WhatsApp.');
        }

        if (!$funcionario->email || $funcionario->email === 'null') {
            return back()->withInput()->with('flash_erro', 'Este funcionário não possui e-mail cadastrado. Use a opção WhatsApp.');
        }

        Mail::raw($mensagem, function ($mail) use ($funcionario) {
            $mail->to($funcionario->email, $funcionario->nome)
                ->subject('Recuperação de senha - Portal do Funcionário');
        });

        return back()->with('flash_sucesso', 'Link de recuperação enviado com sucesso.');
    }

    public function redefinirSenha(string $token)
    {
        $acesso = RHPortalFuncionario::where('token_recuperacao', $token)->first();
        abort_unless($this->portalAcessoService->tokenValido($acesso, 'token_recuperacao'), 404);

        return view('rh.portal_acesso.definir_senha', [
            'token' => $token,
            'tipo' => 'recuperacao',
            'funcionario' => $acesso->funcionario,
        ]);
    }

    public function salvarNovaSenha(PortalPasswordResetRequest $request, string $token)
    {
        $acesso = RHPortalFuncionario::where('token_recuperacao', $token)->first();
        abort_unless($this->portalAcessoService->tokenValido($acesso, 'token_recuperacao'), 404);

        $this->portalAcessoService->definirSenha($acesso, (string) $request->input('senha'));

        return redirect('/portal')->with('flash_sucesso', 'Senha redefinida com sucesso.');
    }

    public function enviarAcessoAdmin(PortalInviteRequest $request, int $funcionarioId)
    {
        $funcionario = Funcionario::query()
            ->where('empresa_id', $this->tenantEmpresaId($request))
            ->findOrFail($funcionarioId);

        $this->authorize('sendInvite', RHPortalFuncionario::class);

        $result = $this->buildPortalInvite->execute(new PortalInviteData(
            funcionario: $funcionario,
            canal: (string) $request->input('canal', 'whatsapp'),
        ));

        $mensagem = $this->mensagemPrimeiroAcesso($funcionario, (string) $result['link']);
        $canal = (string) $result['canal'];

        if ($canal === 'email') {
            if (!$funcionario->email || $funcionario->email === 'null') {
                return back()->with('flash_erro', 'Funcionário sem e-mail cadastrado para envio do acesso.');
            }

            Mail::raw($mensagem, function ($mail) use ($funcionario) {
                $mail->to($funcionario->email, $funcionario->nome)
                    ->subject('Primeiro acesso - Portal do Funcionário');
            });

            $this->audit->log('portal.invite.email', 'rh-portal', ['funcionario_id' => (int) $funcionario->id, 'canal' => 'email'], 'funcionario', (int) $funcionario->id, (int) $funcionario->empresa_id);
            return back()->with('flash_sucesso', 'Acesso enviado por e-mail com sucesso.');
        }

        $resultado = $this->whatsAppService->enviar($funcionario->celular ?: $funcionario->telefone, $mensagem);
        if (!empty($resultado['link'])) {
            return redirect($resultado['link']);
        }

        $this->audit->log('portal.invite.whatsapp', 'rh-portal', ['funcionario_id' => (int) $funcionario->id, 'canal' => 'whatsapp'], 'funcionario', (int) $funcionario->id, (int) $funcionario->empresa_id);
        return back()->with('flash_sucesso', $resultado['mensagem'] ?? 'Acesso preparado para WhatsApp.');
    }

    public function salvarConfiguracaoAdmin(PortalAdminConfigRequest $request, int $funcionarioId)
    {
        $funcionario = Funcionario::query()
            ->where('empresa_id', $this->tenantEmpresaId($request))
            ->findOrFail($funcionarioId);

        $this->authorize('configure', RHPortalFuncionario::class);

        $this->configurePortalAccess->execute(new ConfigurePortalAccessData(
            funcionario: $funcionario,
            ativo: $request->boolean('ativo'),
            perfilId: $request->filled('perfil_id') ? (int) $request->input('perfil_id') : null,
            podeVerRelatorioProdutos: $request->boolean('pode_ver_relatorio_produtos'),
            podeVerRelatorioProdutosExtra: $request->boolean('pode_ver_relatorio_produtos_extra'),
        ));

        $this->audit->log('portal.configure_access', 'rh-portal', [
            'funcionario_id' => (int) $funcionario->id,
            'ativo' => $request->boolean('ativo'),
            'perfil_id' => $request->filled('perfil_id') ? (int) $request->input('perfil_id') : null,
            'pode_ver_relatorio_produtos' => $request->boolean('pode_ver_relatorio_produtos'),
            'pode_ver_relatorio_produtos_extra' => $request->boolean('pode_ver_relatorio_produtos_extra'),
        ], 'funcionario', (int) $funcionario->id, (int) $funcionario->empresa_id);

        return back()->with('flash_sucesso', 'Configurações do portal do funcionário atualizadas com sucesso.');
    }

    public function dashboard(Request $request)
    {
        return app(RHPortalFuncionarioController::class)->externo($request);
    }

    public function pdf(Request $request, int $apuracaoId)
    {
        return app(RHPortalFuncionarioController::class)->pdfExterno($request, $apuracaoId);
    }

    private function mensagemPrimeiroAcesso(Funcionario $funcionario, string $link): string
    {
        return "Olá, {$funcionario->nome}!\nSeu acesso ao Portal do Funcionário foi liberado.\nCrie sua senha no link: {$link}\nDepois você poderá entrar com CPF ou e-mail.";
    }

    private function mensagemRecuperacao(Funcionario $funcionario, string $link): string
    {
        return "Olá, {$funcionario->nome}!\nRecebemos uma solicitação para redefinir sua senha do Portal do Funcionário.\nUse este link: {$link}\nSe você não pediu essa alteração, ignore esta mensagem.";
    }
}
