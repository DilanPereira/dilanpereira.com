<?php
declare(strict_types=1);

/**
 * Mostra as mensagens do formulário de contacto no terminal.
 *
 *   php assets_priv/tools/mensagens.php          -> últimas 10
 *   php assets_priv/tools/mensagens.php 50       -> últimas 50
 *   php assets_priv/tools/mensagens.php todas    -> todas
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('Europe/Lisbon');

$file = dirname(__DIR__) . '/data/mensagens.jsonl';
if (!file_exists($file)) {
    echo "Ainda não há mensagens.\n";
    exit;
}

$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$arg = $argv[1] ?? '10';
if ($arg !== 'todas') {
    $lines = array_slice($lines, -max(1, (int)$arg));
}

printf("%d mensagem(ns)\n", count($lines));
foreach ($lines as $line) {
    $m = json_decode($line, true);
    if (!$m) {
        continue;
    }
    echo str_repeat('─', 60) . "\n";
    printf("%s  ·  %s <%s>  ·  %s\n", date('d/m/Y H:i', (int)strtotime((string)($m['data'] ?? ''))), $m['nome'], $m['email'], strtoupper($m['idioma'] ?? ''));
    printf("Assunto: %s\n\n%s\n", $m['assunto'], $m['mensagem']);
}
echo str_repeat('─', 60) . "\n";
