<?php
\Carbon\Carbon::setLocale('pt_BR');
$variables['data_hoje_extenso'] = now()
    ->locale('pt_BR')
    ->translatedFormat('d \d\e F \d\e Y');
