<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  helpers.php — pequenas funções usadas em todo o site
 * ============================================================================
 */

/**
 * Escapa texto para o meter em HTML com segurança.
 *
 * Converte caracteres especiais (< > " ' &) nos seus códigos HTML, para que
 * um texto como  <script>alert(1)</script>  apareça escrito na página em vez
 * de ser executado (proteção contra XSS). Deve ser usada SEMPRE que se mostra
 * um valor que não foi escrito à mão no próprio template.
 *
 *   e('Tom & Jerry')  ->  'Tom &amp; Jerry'
 *
 * ENT_QUOTES     escapa aspas simples e duplas (seguro dentro de atributos)
 * ENT_SUBSTITUTE troca bytes inválidos por "�" em vez de devolver texto vazio
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
