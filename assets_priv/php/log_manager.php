<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  log_manager.php — registo de erros do site
 * ============================================================================
 *  Os erros ficam em assets_priv/logs/AAAA-MM-DD.log (um ficheiro por dia),
 *  fora da pasta pública. Esta pasta está no .gitignore.
 *
 *  Para ver no servidor:  sudo cat /mnt/ssd/apps/portfolio/assets_priv/logs/*.log
 * ============================================================================
 */

/**
 * Acrescenta uma linha ao log do dia.
 *
 * A mensagem é "limpa" antes de ser escrita: quebras de linha e caracteres de
 * controlo são trocados por espaços. Isto impede "log injection", ou seja, que
 * alguém meta texto que pareça linhas de log falsas (ex.: através do nome no
 * formulário, se algum dia esse texto for registado).
 */
function logCustomError(string $message): void
{
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0750, true);
    }

    $clean = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $message) ?? '';
    $line  = date('H:i:s') . ' -> ' . mb_substr($clean, 0, 1000) . PHP_EOL;

    // FILE_APPEND: acrescenta no fim; LOCK_EX: dois pedidos ao mesmo tempo não misturam linhas
    @file_put_contents($logDir . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
}
