<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
		'empresa_id', 'plano_id', 'valor', 'transacao_id', 'status', 'forma_pagamento', 
		'link_boleto', 'status_detalhe', 'descricao', 'qr_code_base64', 'qr_code',
		'external_reference', 'notification_url', 'raw_response', 'paid_at', 'mp_status_last_sync_at'
	];


	protected $casts = [
		'raw_response' => 'array',
		'paid_at' => 'datetime',
		'mp_status_last_sync_at' => 'datetime',
	];

	public function plano(){
		return $this->belongsTo(PlanoEmpresa::class, 'plano_id');
	}

	public function empresa(){
		return $this->belongsTo(Empresa::class, 'empresa_id');
	}

	/*
	0 - novo
	1 - Aprovado
	2 - Pendente
	3 - 
	*/
}
