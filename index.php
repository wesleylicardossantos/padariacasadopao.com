<?php

/** Compatibilidade Hostgator/cPanel quando a raiz pública aponta para a raiz do projeto. */

$publicIndex = __DIR__ . '/public/index.php';

if (! is_file($publicIndex)) {
    http_response_code(500);
    echo 'Arquivo public/index.php não encontrado.';
    exit;
}

$_SERVER['SCRIPT_FILENAME'] = $publicIndex;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require $publicIndex;
