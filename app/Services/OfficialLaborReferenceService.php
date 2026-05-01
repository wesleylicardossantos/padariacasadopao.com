<?php

namespace App\Services;

use App\Models\RHDepartmentReference;
use App\Models\RHOfficialAdmissionIndicator;
use App\Models\RHOfficialCboOccupation;
use App\Models\RHOfficialContractType;
use App\Models\RHOfficialNatureActivity;
use App\Models\RHOfficialFunction;
use App\Models\RHOfficialWorkerCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OfficialLaborReferenceService
{
    private const CBO_REMOTE_URL = 'https://www.gov.br/trabalho-e-emprego/pt-br/assuntos/cbo/servicos/downloads/cbo2002-ocupacao.csv';
    private const CBO_FALLBACK_PATH = 'database/reference/cbo2002-ocupacao.csv';
    private const ESOCIAL_TABLES_URL = 'https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-v-1.3/tabelas.html';
    private const ESOCIAL_LAYOUT_URL = 'https://www.gov.br/esocial/pt-br/documentacao-tecnica/leiautes-esocial-versao-s-1-3-nt-06-2026-rev-09-04-2026/index.html';
    private const CBO_DOWNLOADS_URL = 'https://www.gov.br/trabalho-e-emprego/pt-br/assuntos/cbo/servicos/downloads/downloads';

    public function ensureSynced(bool $force = false): void
    {
        if (!$this->hasReferenceTables()) {
            return;
        }

        if ($force || RHOfficialWorkerCategory::query()->count() === 0) {
            $this->syncWorkerCategories();
        }

        if ($force || RHOfficialContractType::query()->count() === 0) {
            $this->syncContractTypes();
        }

        if ($force || RHOfficialNatureActivity::query()->count() === 0) {
            $this->syncNatureActivities();
        }

        if ($force || RHDepartmentReference::query()->count() === 0) {
            $this->syncDepartments();
        }

        if ($force || RHOfficialAdmissionIndicator::query()->count() === 0) {
            $this->syncAdmissionIndicators();
        }

        if ($force || RHOfficialCboOccupation::query()->count() === 0) {
            $this->syncCboOccupations($force);
        }

        if ($force || RHOfficialFunction::query()->count() === 0) {
            $this->syncFunctions();
        }

    }

    public function syncAll(bool $force = false): array
    {
        $this->ensureSynced($force);

        return [
            'categorias' => RHOfficialWorkerCategory::query()->count(),
            'tipos_contrato' => RHOfficialContractType::query()->count(),
            'naturezas' => RHOfficialNatureActivity::query()->count(),
            'departamentos' => RHDepartmentReference::query()->count(),
            'indicativos_admissao' => RHOfficialAdmissionIndicator::query()->count(),
            'funcoes' => RHOfficialFunction::query()->count(),
            'cbo' => RHOfficialCboOccupation::query()->count(),
        ];
    }

    public function getWorkerCategories()
    {
        if (!Schema::hasTable('rh_official_worker_categories')) {
            return collect();
        }

        return RHOfficialWorkerCategory::query()
            ->where('ativo', 1)
            ->orderBy('codigo')
            ->get();
    }

    public function getContractTypes()
    {
        if (!Schema::hasTable('rh_official_contract_types')) {
            return collect();
        }

        return RHOfficialContractType::query()
            ->where('ativo', 1)
            ->orderBy('codigo')
            ->get();
    }

    public function getNatureActivities()
    {
        if (!Schema::hasTable('rh_official_nature_activities')) {
            return collect();
        }

        return RHOfficialNatureActivity::query()
            ->where('ativo', 1)
            ->orderBy('codigo')
            ->get();
    }

    public function getDepartments()
    {
        if (!Schema::hasTable('rh_department_references')) {
            return collect();
        }

        return RHDepartmentReference::query()
            ->where('ativo', 1)
            ->orderBy('ordem')
            ->orderBy('descricao')
            ->get();
    }

    public function getAdmissionIndicators()
    {
        if (!Schema::hasTable('rh_official_admission_indicators')) {
            return collect();
        }

        return RHOfficialAdmissionIndicator::query()
            ->where('ativo', 1)
            ->orderBy('codigo')
            ->get();
    }

    public function searchFunctions(?string $term, int $limit = 20)
    {
        if (!Schema::hasTable('rh_official_functions')) {
            return collect();
        }

        $limit = max(1, min($limit, 30));
        $term = trim((string) $term);

        $query = RHOfficialFunction::query()->where('ativo', 1)->orderBy('descricao');

        if ($term !== '') {
            $normalized = $this->normalizeText($term);
            $query->where(function ($q) use ($term, $normalized) {
                $q->where('codigo', 'like', '%' . preg_replace('/\D+/', '', $term) . '%')
                    ->orWhere('descricao', 'like', '%' . $term . '%')
                    ->orWhere('descricao_normalizada', 'like', '%' . $normalized . '%')
                    ->orWhere('cbo_codigo', 'like', '%' . preg_replace('/\D+/', '', $term) . '%');
            });
        }

        return $query->limit($limit)->get();
    }

    public function searchCbo(?string $term, int $limit = 20)
    {
        if (!Schema::hasTable('rh_official_cbo_occupations')) {
            return collect();
        }

        $limit = max(1, min($limit, 50));
        $term = trim((string) $term);

        $query = RHOfficialCboOccupation::query()->orderBy('codigo');

        if ($term !== '') {
            $normalized = $this->normalizeText($term);
            $query->where(function ($q) use ($term, $normalized) {
                $q->where('codigo', 'like', '%' . preg_replace('/\D+/', '', $term) . '%')
                    ->orWhere('titulo', 'like', '%' . $term . '%')
                    ->orWhere('titulo_normalizado', 'like', '%' . $normalized . '%');
            });
        }

        return $query->limit($limit)->get();
    }

    public function suggestDepartment(?string $funcao, ?string $cbo = null): ?string
    {
        $joined = $this->normalizeText(trim((string) $funcao) . ' ' . trim((string) $cbo));

        if ($joined === '') {
            return null;
        }

        $map = [
            'financeiro' => 'FINANCEIRO',
            'contador' => 'FINANCEIRO',
            'fiscal' => 'FINANCEIRO',
            'rh' => 'RH',
            'recursos humanos' => 'RH',
            'dp' => 'RH',
            'administrativo' => 'ADMINISTRATIVO',
            'gerente' => 'ADMINISTRATIVO',
            'diretor' => 'ADMINISTRATIVO',
            'vendas' => 'COMERCIAL',
            'vendedor' => 'COMERCIAL',
            'comercial' => 'COMERCIAL',
            'marketing' => 'COMERCIAL',
            'estoque' => 'LOGISTICA',
            'almoxarifado' => 'LOGISTICA',
            'logistica' => 'LOGISTICA',
            'entrega' => 'LOGISTICA',
            'motorista' => 'LOGISTICA',
            'producao' => 'PRODUCAO',
            'forneiro' => 'OPERACIONAL',
            'cozinha' => 'OPERACIONAL',
            'auxiliar de cozinha' => 'OPERACIONAL',
            'caixa' => 'OPERACIONAL',
            'atendente' => 'OPERACIONAL',
            'operador' => 'OPERACIONAL',
            'operacional' => 'OPERACIONAL',
            'suporte' => 'SUPORTE',
            'ti' => 'TECNOLOGIA',
            'tecnologia' => 'TECNOLOGIA',
            'desenvolvedor' => 'TECNOLOGIA',
            'programador' => 'TECNOLOGIA',
        ];

        foreach ($map as $needle => $department) {
            if (Str::contains($joined, $needle)) {
                return $department;
            }
        }

        return null;
    }

    private function hasReferenceTables(): bool
    {
        return Schema::hasTable('rh_official_worker_categories')
            && Schema::hasTable('rh_official_contract_types')
            && Schema::hasTable('rh_official_nature_activities')
            && Schema::hasTable('rh_official_cbo_occupations')
            && Schema::hasTable('rh_department_references')
            && Schema::hasTable('rh_official_admission_indicators')
            && Schema::hasTable('rh_official_functions');
    }

    private function syncWorkerCategories(): void
    {
        $rows = [
            ['101', 'Empregado - Geral, inclusive o empregado público da administração direta ou indireta contratado pela CLT', 'Empregado e Trabalhador Temporário', '2014-01-01', null, true],
            ['102', 'Empregado - Trabalhador rural por pequeno prazo da Lei 11.718/2008', 'Empregado e Trabalhador Temporário', '2014-01-01', null, true],
            ['103', 'Empregado - Aprendiz', 'Empregado e Trabalhador Temporário', '2014-01-01', null, true],
            ['104', 'Empregado - Doméstico', 'Empregado e Trabalhador Temporário', '2014-01-01', null, true],
            ['105', 'Empregado - Contrato a termo firmado nos termos da Lei 9.601/1998', 'Empregado e Trabalhador Temporário', '2014-01-01', null, true],
            ['106', 'Trabalhador temporário - Contrato nos termos da Lei 6.019/1974', 'Empregado e Trabalhador Temporário', '2014-01-01', null, true],
            ['107', 'Empregado - Contrato de trabalho Verde e Amarelo - sem acordo para antecipação mensal da multa rescisória do FGTS', 'Empregado e Trabalhador Temporário', '2020-01-01', '2022-12-31', false],
            ['108', 'Empregado - Contrato de trabalho Verde e Amarelo - com acordo para antecipação mensal da multa rescisória do FGTS', 'Empregado e Trabalhador Temporário', '2020-01-01', '2022-12-31', false],
            ['111', 'Empregado - Contrato de trabalho intermitente', 'Empregado e Trabalhador Temporário', '2014-01-01', null, true],
            ['201', 'Trabalhador avulso portuário', 'Avulso', '2014-01-01', null, true],
            ['202', 'Trabalhador avulso não portuário', 'Avulso', '2014-01-01', null, true],
            ['301', 'Servidor público titular de cargo efetivo, magistrado, ministro de Tribunal de Contas, conselheiro de Tribunal de Contas e membro do Ministério Público', 'Agente Público', '2014-01-01', null, true],
            ['302', 'Servidor público ocupante de cargo exclusivo em comissão', 'Agente Público', '2014-01-01', null, true],
            ['303', 'Exercente de mandato eletivo', 'Agente Público', '2014-01-01', null, true],
            ['304', 'Servidor público exercente de mandato eletivo, inclusive com exercício de cargo em comissão', 'Agente Público', '2014-01-01', null, true],
            ['305', 'Servidor público indicado para conselho ou órgão deliberativo, na condição de representante do governo, órgão ou entidade da administração pública', 'Agente Público', '2014-01-01', null, true],
            ['306', 'Servidor público contratado por tempo determinado, sujeito a regime administrativo especial definido em lei própria', 'Agente Público', '2014-01-01', null, true],
            ['307', 'Militar dos Estados e Distrito Federal', 'Agente Público', '2014-01-01', null, true],
            ['308', 'Conscrito', 'Agente Público', '2014-01-01', '2023-04-25', false],
            ['309', 'Agente público - Outros', 'Agente Público', '2014-01-01', null, true],
            ['310', 'Servidor público eventual', 'Agente Público', '2014-01-01', null, true],
            ['311', 'Ministros, juízes, procuradores, promotores ou oficiais de justiça à disposição da Justiça Eleitoral', 'Agente Público', '2014-01-01', null, true],
            ['312', 'Auxiliar local', 'Agente Público', '2014-01-01', null, true],
            ['313', 'Servidor público exercente de atividade de instrutoria, curso ou concurso, convocado para pareceres técnicos, depoimentos ou aditância no exterior', 'Agente Público', '2014-01-01', null, true],
            ['314', 'Militar das Forças Armadas', 'Agente Público', '2014-01-01', null, true],
            ['401', 'Dirigente sindical - Informação prestada pelo sindicato', 'Cessão', '2014-01-01', null, true],
            ['410', 'Trabalhador cedido/exercício em outro órgão/juiz auxiliar - Informação prestada pelo cessionário/destino', 'Cessão', '2014-01-01', null, true],
            ['501', 'Dirigente sindical - Segurado especial', 'Segurado Especial', '2014-01-01', null, true],
            ['701', 'Contribuinte individual - Autônomo em geral, exceto se enquadrado em uma das demais categorias de contribuinte individual', 'Contribuinte Individual', '2014-01-01', null, true],
            ['711', 'Contribuinte individual - Transportador autônomo de passageiros', 'Contribuinte Individual', '2014-01-01', null, true],
            ['712', 'Contribuinte individual - Transportador autônomo de carga', 'Contribuinte Individual', '2014-01-01', null, true],
            ['721', 'Contribuinte individual - Diretor não empregado, com FGTS', 'Contribuinte Individual', '2014-01-01', null, true],
            ['722', 'Contribuinte individual - Diretor não empregado, sem FGTS', 'Contribuinte Individual', '2014-01-01', null, true],
            ['723', 'Contribuinte individual - Empresário, sócio e membro de conselho de administração ou fiscal', 'Contribuinte Individual', '2014-01-01', null, true],
            ['731', 'Contribuinte individual - Cooperado que presta serviços por intermédio de cooperativa de trabalho', 'Contribuinte Individual', '2014-01-01', null, true],
            ['734', 'Contribuinte individual - Transportador cooperado que presta serviços por intermédio de cooperativa de trabalho', 'Contribuinte Individual', '2014-01-01', null, true],
            ['738', 'Contribuinte individual - Cooperado filiado a cooperativa de produção', 'Contribuinte Individual', '2014-01-01', null, true],
            ['741', 'Contribuinte individual - Microempreendedor individual', 'Contribuinte Individual', '2014-01-01', null, true],
            ['751', 'Contribuinte individual - Magistrado classista temporário da Justiça do Trabalho ou da Justiça Eleitoral que seja aposentado de qualquer regime previdenciário', 'Contribuinte Individual', '2014-01-01', null, true],
            ['761', 'Contribuinte individual - Associado eleito para direção de cooperativa, associação ou entidade de classe de qualquer natureza ou finalidade, bem como o síndico ou administrador eleito para exercer atividade de direção condominial, desde que recebam remuneração', 'Contribuinte Individual', '2014-01-01', null, true],
            ['771', 'Contribuinte individual - Membro de conselho tutelar, nos termos da Lei 8.069/1990', 'Contribuinte Individual', '2014-01-01', null, true],
            ['781', 'Ministro de confissão religiosa ou membro de vida consagrada, de congregação ou de ordem religiosa', 'Contribuinte Individual', '2014-01-01', null, true],
            ['901', 'Estagiário', 'Bolsista', '2014-01-01', null, true],
            ['902', 'Médico residente, residente em área profissional de saúde ou médico em curso de formação', 'Bolsista', '2014-01-01', null, true],
            ['903', 'Bolsista', 'Bolsista', '2014-01-01', null, true],
            ['904', 'Participante de curso de formação, como etapa de concurso público, sem vínculo de emprego/estatutário', 'Bolsista', '2014-01-01', null, true],
            ['906', 'Beneficiário do Programa Nacional de Prestação de Serviço Civil Voluntário', 'Bolsista', '2022-01-28', null, true],
        ];

        DB::transaction(function () use ($rows) {
            RHOfficialWorkerCategory::query()->delete();
            foreach ($rows as [$codigo, $descricao, $grupo, $inicio, $fim, $ativo]) {
                RHOfficialWorkerCategory::query()->create([
                    'codigo' => $codigo,
                    'descricao' => $descricao,
                    'grupo' => $grupo,
                    'inicio_vigencia' => $inicio,
                    'fim_vigencia' => $fim,
                    'ativo' => $ativo,
                    'fonte' => 'eSocial Tabela 01',
                    'fonte_url' => self::ESOCIAL_TABLES_URL,
                    'fonte_atualizada_em' => now(),
                ]);
            }
        });
    }

    private function syncContractTypes(): void
    {
        $rows = [
            ['1', 'Prazo indeterminado'],
            ['2', 'Prazo determinado, definido em dias'],
            ['3', 'Prazo determinado, vinculado à ocorrência de um fato'],
        ];

        DB::transaction(function () use ($rows) {
            RHOfficialContractType::query()->delete();
            foreach ($rows as [$codigo, $descricao]) {
                RHOfficialContractType::query()->create([
                    'codigo' => $codigo,
                    'descricao' => $descricao,
                    'ativo' => true,
                    'fonte' => 'eSocial leiaute S-1.3',
                    'fonte_url' => self::ESOCIAL_LAYOUT_URL,
                    'fonte_atualizada_em' => now(),
                ]);
            }
        });
    }

    private function syncNatureActivities(): void
    {
        $rows = [
            ['1', 'Trabalho urbano'],
            ['2', 'Trabalho rural'],
        ];

        DB::transaction(function () use ($rows) {
            RHOfficialNatureActivity::query()->delete();
            foreach ($rows as [$codigo, $descricao]) {
                RHOfficialNatureActivity::query()->create([
                    'codigo' => $codigo,
                    'descricao' => $descricao,
                    'ativo' => true,
                    'fonte' => 'eSocial leiaute S-1.3',
                    'fonte_url' => self::ESOCIAL_LAYOUT_URL,
                    'fonte_atualizada_em' => now(),
                ]);
            }
        });
    }

    private function syncAdmissionIndicators(): void
    {
        $rows = [
            ['1', 'Admissão normal', true],
            ['2', 'Decorrente de ação fiscal', true],
            ['3', 'Decorrente de decisão judicial', true],
        ];

        foreach ($rows as [$codigo, $descricao, $ativo]) {
            RHOfficialAdmissionIndicator::query()->updateOrCreate(
                ['codigo' => $codigo],
                [
                    'descricao' => $descricao,
                    'ativo' => $ativo,
                    'fonte' => 'eSocial S-2200/S-2300',
                    'fonte_url' => self::ESOCIAL_LAYOUT_URL,
                    'fonte_atualizada_em' => Carbon::now(),
                ]
            );
        }
    }

    private function syncFunctions(): void
    {
        if (!Schema::hasTable('rh_official_functions') || !Schema::hasTable('rh_official_cbo_occupations')) {
            return;
        }

        $now = now();
        $rows = RHOfficialCboOccupation::query()
            ->orderBy('titulo')
            ->get(['codigo', 'titulo', 'titulo_normalizado'])
            ->map(function ($item) use ($now) {
                return [
                    'codigo' => 'FUNC-' . $item->codigo,
                    'descricao' => $item->titulo,
                    'descricao_normalizada' => $item->titulo_normalizado ?: $this->normalizeText($item->titulo),
                    'cbo_codigo' => $item->codigo,
                    'ativo' => true,
                    'fonte' => 'CBO 2002 / eSocial (descrição do cargo/função)',
                    'fonte_url' => self::CBO_DOWNLOADS_URL,
                    'fonte_atualizada_em' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();

        DB::transaction(function () use ($rows) {
            RHOfficialFunction::query()->delete();
            foreach (array_chunk($rows, 500) as $chunk) {
                RHOfficialFunction::query()->insert($chunk);
            }
        }, 1);
    }

    private function syncDepartments(): void
    {
        $rows = [
            ['ADM', 'ADMINISTRATIVO', 10],
            ['COM', 'COMERCIAL', 20],
            ['FIN', 'FINANCEIRO', 30],
            ['RHU', 'RH', 40],
            ['LOG', 'LOGISTICA', 50],
            ['OPE', 'OPERACIONAL', 60],
            ['PRD', 'PRODUCAO', 70],
            ['SUP', 'SUPORTE', 80],
            ['TEC', 'TECNOLOGIA', 90],
            ['OUT', 'OUTROS', 100],
        ];

        DB::transaction(function () use ($rows) {
            RHDepartmentReference::query()->delete();
            foreach ($rows as [$codigo, $descricao, $ordem]) {
                RHDepartmentReference::query()->create([
                    'codigo' => $codigo,
                    'descricao' => $descricao,
                    'ordem' => $ordem,
                    'ativo' => true,
                ]);
            }
        });
    }

    private function syncCboOccupations(bool $force = false): void
    {
        $csv = $this->downloadRemoteCboCsv();
        if ($csv === null) {
            $fallback = base_path(self::CBO_FALLBACK_PATH);
            if (is_file($fallback)) {
                $csv = file_get_contents($fallback) ?: null;
            }
        }

        if ($csv === null) {
            throw new \RuntimeException('Não foi possível carregar a base oficial de CBO.');
        }

        $rows = $this->parseCboCsv($csv);
        if ($rows === []) {
            throw new \RuntimeException('A base oficial de CBO retornou vazia.');
        }

        DB::transaction(function () use ($rows) {
            RHOfficialCboOccupation::query()->delete();
            foreach (array_chunk($rows, 500) as $chunk) {
                RHOfficialCboOccupation::query()->insert($chunk);
            }
        }, 1);
    }

    private function downloadRemoteCboCsv(): ?string
    {
        try {
            $response = Http::timeout(45)->retry(2, 800)->get(self::CBO_REMOTE_URL);
            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao baixar CBO oficial', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function parseCboCsv(string $csv): array
    {
        $temp = fopen('php://temp', 'r+');
        fwrite($temp, $csv);
        rewind($temp);

        $rows = [];
        $headerRead = false;
        $now = now();

        while (($data = fgetcsv($temp, 0, ';')) !== false) {
            if (!$headerRead) {
                $headerRead = true;
                continue;
            }

            $codigo = trim((string) ($data[0] ?? ''));
            $titulo = trim((string) ($data[1] ?? ''));
            if ($codigo === '' || $titulo === '') {
                continue;
            }

            $rows[] = [
                'codigo' => $codigo,
                'titulo' => $titulo,
                'titulo_normalizado' => $this->normalizeText($titulo),
                'fonte' => 'CBO 2002 - Ocupação',
                'fonte_url' => self::CBO_DOWNLOADS_URL,
                'fonte_atualizada_em' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        fclose($temp);

        return $rows;
    }

    private function normalizeText(string $value): string
    {
        $value = Str::ascii(mb_strtolower($value, 'UTF-8'));
        $value = preg_replace('/[^a-z0-9\s]/', ' ', $value) ?: $value;
        return preg_replace('/\s+/', ' ', trim($value)) ?: '';
    }
}
