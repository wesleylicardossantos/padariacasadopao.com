<?php
namespace App\Http\Controllers;

use App\Services\RHIAExternaService;

class RHIAExternaController extends Controller
{
    public function index()
    {
        return view('rh.ia_externa.index');
    }

    public function enviar()
    {
        $payload = ['msg'=>'Teste IA externa'];
        RHIAExternaService::enviarEmail(env('RH_IA_EMAIL_TO'), $payload);
        RHIAExternaService::enviarWhatsappWebhook(env('RH_IA_WHATSAPP_WEBHOOK'), $payload);
        return back();
    }
}
