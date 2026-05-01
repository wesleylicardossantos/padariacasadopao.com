
// MULTI EMPRESA
Route::get('/rh/saas-run', function(){
    return \App\Services\RHSaaSMultiTenantService::executarParaTodas();
});

// WHATSAPP REAL
Route::post('/rh/whatsapp-webhook', 'RHWhatsAppWebhookController@receber');
