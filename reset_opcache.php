<?php

if (!function_exists('opcache_reset')) {
    die('OPcache não está habilitado neste servidor.');
}

$result = opcache_reset();

if ($result) {
    echo 'OPcache limpo com sucesso.';
} else {
    echo 'Falha ao limpar o OPcache.';
}