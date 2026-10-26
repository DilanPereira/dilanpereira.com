<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  config.php — configuração carregada no início de cada pedido
 * ============================================================================
 *  1. Interruptor de manutenção ($isWIP)
 *  2. Cabeçalhos HTTP de segurança
 *  3. Sessão (cookie seguro)
 *  4. Funções auxiliares, textos e língua (lang_init.php)
 * ============================================================================
 */


/* ----------------------------------------------------------------------------
 * 1. Manutenção
 * ----------------------------------------------------------------------------
 *  true  = o site mostra a página "em manutenção" (HTTP 503)
 *  false = o site funciona normalmente
 * Para testar localmente com o site bloqueado no servidor, basta mudar aqui.
 */
$isWIP = false;


/* ----------------------------------------------------------------------------
 * 2. Cabeçalhos de segurança
 * ----------------------------------------------------------------------------
 * O pedido chega por HTTPS? (diretamente, ou através do Nginx Proxy Manager /
 * Cloudflare Tunnel, que avisam com cabeçalhos próprios)
 */
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || str_contains($_SERVER['HTTP_CF_VISITOR'] ?? '', '"https"');

if (!headers_sent()) {
    // Não anunciar a versão do PHP (dá pistas a quem procura falhas conhecidas)
    header_remove('X-Powered-By');

    // O browser não deve "adivinhar" o tipo de um ficheiro (ex.: tratar um .txt como script)
    header('X-Content-Type-Options: nosniff');

    // Proíbe outros sites de mostrarem este dentro de um <iframe> (proteção contra clickjacking)
    header('X-Frame-Options: SAMEORIGIN');

    // Ao clicar num link para fora, só envia o domínio de origem, não o endereço completo
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Desliga funcionalidades do browser que o site não usa
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');

    // Isola a janela do site de janelas abertas por outros sites
    header('Cross-Origin-Opener-Policy: same-origin');

    /*
     * Content-Security-Policy (CSP): lista do que a página pode carregar.
     * Se alguém conseguir injetar um <script> na página, o browser recusa-o,
     * porque só são permitidos scripts vindos de ficheiros do próprio site.
     *   default-src 'self'      por omissão, só recursos do próprio site
     *   script-src 'self'       JavaScript só de ficheiros .js do site (nada inline)
     *   style-src ... inline    CSS do site + atributos style="" (usados nos templates)
     *   img-src 'self' data:    imagens do site + ícones embutidos do Bootstrap
     *   form-action 'self'      formulários só podem enviar para o próprio site
     *   frame-ancestors 'self'  igual ao X-Frame-Options, na versão moderna
     *   base-uri / object-src   fecham truques antigos de injeção
     */
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; "
        . "img-src 'self' data:; font-src 'self'; connect-src 'self'; form-action 'self'; "
        . "frame-ancestors 'self'; base-uri 'self'; object-src 'none'");

    // Com HTTPS: o browser passa a usar sempre HTTPS neste site durante 1 ano
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}


/* ----------------------------------------------------------------------------
 * 3. Sessão
 * ----------------------------------------------------------------------------
 * A sessão guarda: a língua da última página vista, o token do formulário de
 * contacto e o resultado do último envio.
 */
if (session_status() === PHP_SESSION_NONE) {
    // Só aceita IDs de sessão criados pelo próprio servidor (impede "session fixation")
    ini_set('session.use_strict_mode', '1');
    // Nome próprio para o cookie (o padrão "PHPSESSID" denuncia que o site é PHP)
    session_name('dp_session');

    session_set_cookie_params([
        'lifetime' => 0,          // dura até o browser fechar
        'path'     => '/',
        'secure'   => $isHttps,   // só enviado por HTTPS (desligado em http://localhost)
        'httponly' => true,       // inacessível ao JavaScript
        'samesite' => 'Lax',      // não é enviado em pedidos vindos de outros sites (ajuda contra CSRF)
    ]);
    session_start();
}

date_default_timezone_set('Europe/Lisbon');


/* ----------------------------------------------------------------------------
 * 4. Funções auxiliares, textos e língua
 * ----------------------------------------------------------------------------
 */
require_once __DIR__ . '/App.php';          // estado do pedido (língua, página)
require_once __DIR__ . '/helpers.php';      // e() para escapar HTML
require_once __DIR__ . '/json_helper.php';  // leitura dos JSON
require_once __DIR__ . '/log_manager.php';  // logCustomError()
require_once __DIR__ . '/lang_init.php';    // línguas, $lang, textos, __()
