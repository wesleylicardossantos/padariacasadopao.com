<?php
use Illuminate\Support\Facades\DB;

function folhaFechadaGlobal(){
    $mes = date('m');
    $ano = date('Y');

    return DB::table('rh_folha_fechamentos')
        ->where('mes',$mes)
        ->where('ano',$ano)
        ->where('status','fechado')
        ->exists();
}
