<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  json_helper.php — leitura dos ficheiros JSON (textos, projetos, formação)
 * ============================================================================
 */

/**
 * Lê um ficheiro JSON e devolve o conteúdo como array.
 *
 * Em caso de erro (ficheiro ilegível ou JSON mal formado) devolve um array vazio
 * e regista o problema no log do PHP, para o site continuar a funcionar em vez
 * de mostrar um erro ao visitante.
 */
function parseJsonFile(string $path): array
{
    $content = @file_get_contents($path);
    if ($content === false) {
        error_log("Não foi possível ler o ficheiro: $path");
        return [];
    }

    try {
        // JSON_THROW_ON_ERROR: em vez de devolver null em silêncio, lança uma exceção
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $ex) {
        error_log("JSON inválido em $path: " . $ex->getMessage());
        return [];
    }

    return is_array($data) ? $data : [];
}
