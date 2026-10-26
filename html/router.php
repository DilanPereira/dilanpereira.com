<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  router.php — "porta de entrada" de todas as páginas do site
 * ============================================================================
 *
 *  Todos os pedidos de páginas chegam aqui e é este ficheiro que decide o que
 *  mostrar, com base no endereço (padrão "Front Controller"):
 *
 *      /                        -> página inicial, em inglês
 *      /pt/                     -> página inicial, em português
 *      /project/portfolio       -> projeto "portfolio", em inglês
 *      /pt/projeto/portfolio    -> projeto "portfolio", em português
 *      /sitemap.xml             -> lista de páginas para o Google
 *      (qualquer outra coisa)   -> página 404
 *
 *  Quem manda os pedidos para aqui:
 *    - No servidor (Pi): o nginx, com a regra "try_files ... /router.php"
 *      (ver deploy/nginx.conf).
 *    - No PC (IntelliJ ou "php -S"): a opção "router script" do servidor de testes.
 *
 *  Ficheiros que existem de verdade na pasta html/ (css, js, imagens, robots.txt,
 *  contacto.php) NÃO passam por aqui: são servidos diretamente.
 *
 *  Os templates das páginas estão fora da pasta pública, em assets_priv/pages/,
 *  por isso ninguém os consegue abrir diretamente pelo browser.
 * ============================================================================
 */


/* ----------------------------------------------------------------------------
 * 1. Servidor de testes do PC (IntelliJ / php -S)
 * ----------------------------------------------------------------------------
 * O servidor embutido do PHP manda TODOS os pedidos para o router, até os de
 * imagens e CSS. Se o ficheiro pedido existir (ex.: /assets/css/style.css),
 * "return false" diz ao PHP: "serve tu este ficheiro tal como está".
 * No nginx isto nunca acontece, porque o nginx já serve esses ficheiros sozinho.
 */
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . rawurldecode(strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/');
    $real = realpath($file);
    // Só ficheiros DENTRO da pasta html/ (nunca "../assets_priv/...") e nunca o próprio router
    if ($real !== false && is_file($real) && str_starts_with($real, __DIR__ . DIRECTORY_SEPARATOR) && $real !== __FILE__) {
        return false;
    }
}


/* ----------------------------------------------------------------------------
 * 2. Constantes usadas pelas páginas
 * ----------------------------------------------------------------------------
 * PUBLIC_DIR : pasta html/ (para verificar se um ficheiro público existe, ex.: og-image)
 * PAGES_DIR  : pasta com os templates das páginas (fora da pasta pública)
 */
define('PUBLIC_DIR', __DIR__);
define('PAGES_DIR', dirname(__DIR__) . '/assets_priv/pages');


/* ----------------------------------------------------------------------------
 * 3. Ler e normalizar o endereço pedido
 * ----------------------------------------------------------------------------
 * Exemplo: o browser pede "/pt/projeto/portfolio?x=1"
 *   $path     = "/pt/projeto/portfolio"         (o caminho, sem o "?x=1")
 *   $query    = ['x' => '1']                     (os parâmetros depois do "?")
 *   $segments = ['pt', 'projeto', 'portfolio']   (o caminho partido nas "/")
 *
 * Normalização (segurança): "\" passa a "/" e várias "/" seguidas passam a uma.
 * Sem isto, um endereço como "/en//site-malicioso.com" podia acabar num
 * redirecionamento para "//site-malicioso.com", que o browser trata como
 * outro site ("open redirect").
 */
$requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
[$rawPath, $rawQuery] = array_pad(explode('?', $requestUri, 2), 2, '');

// Nota: não se usa parse_url() no caminho, porque trata "//site.com/x" como um domínio
$path = rawurldecode($rawPath);
$path = '/' . ltrim((string)preg_replace('#/{2,}#', '/', str_replace('\\', '/', $path)), '/');

$query = [];
parse_str($rawQuery, $query);

$segments = array_values(array_filter(explode('/', $path), 'strlen'));


/* ----------------------------------------------------------------------------
 * 4. Descobrir a língua a partir do endereço
 * ----------------------------------------------------------------------------
 * A lista de línguas está em assets_priv/php/languages.php. A primeira da lista
 * (inglês) é a "principal" e não tem prefixo no endereço.
 *
 *   /pt/projeto/portfolio  -> o 1.º segmento é "pt", que é uma língua conhecida:
 *                             língua = pt, e tiramo-lo da lista -> ['projeto', 'portfolio']
 *   /project/portfolio     -> "project" não é uma língua: língua = en (a principal)
 *
 * A língua tem de ser indicada ANTES de carregar o config.php, porque é o
 * lang_init.php (carregado pelo config) que carrega os textos nessa língua.
 */
require_once dirname(__DIR__) . '/assets_priv/php/App.php';

$routeLanguages = array_keys(require dirname(__DIR__) . '/assets_priv/php/languages.php');
$defaultLang    = $routeLanguages[0];

$routeLang = $defaultLang;
if ($segments && in_array($segments[0], $routeLanguages, true)) {
    $routeLang = array_shift($segments);
}
App::requestLang($routeLang);


/* ----------------------------------------------------------------------------
 * 5. Carregar a configuração do site
 * ----------------------------------------------------------------------------
 * config.php : sessão, cabeçalhos de segurança, $isWIP, e carrega o lang_init.php
 *              (que define $lang, $languages, $projects, $educations e a função __())
 * urls.php   : funções que constroem os endereços (urlHome, urlProject, ...)
 */
require_once dirname(__DIR__) . '/assets_priv/php/config.php';
require_once dirname(__DIR__) . '/assets_priv/php/urls.php';

/** @var array  $languages Lista de línguas (de languages.php) */
/** @var array  $projects  Projetos, já com os textos na língua atual */
/** @var bool   $isWIP     Site em manutenção? (config.php) */
/** @var string $lang      Língua atual */


/**
 * Redireciona para outro endereço do próprio site e termina.
 *   301 (por omissão) = mudança permanente: o browser e o Google passam a usar o novo endereço.
 *   302               = mudança temporária (usado durante a manutenção).
 * Por segurança, só aceita caminhos internos que começam por UMA "/".
 */
function redirectTo(string $url, int $status = 301): never
{
    if (!str_starts_with($url, '/') || str_starts_with($url, '//')) {
        $url = '/';
    }
    header('Location: ' . $url, true, $status);
    exit;
}

/**
 * Guarda na sessão a língua da página que está a ser vista.
 * Serve para o contacto.php (que não passa pelo router) saber para que
 * língua voltar depois de enviar o formulário.
 * Só é chamada para páginas reais (início e projetos), não para 404s,
 * para que um pedido a uma imagem inexistente não mude a língua da sessão.
 */
function rememberLanguage(): void
{
    $_SESSION['lang'] = App::lang();
}

/** Mostra uma página: marca a rota atual e carrega o template. */
function render(string $template, array $route): never
{
    App::setRoute($route);
    // Os templates usam as variáveis globais ($lang, $projects, $isWIP...)
    extract($GLOBALS, EXTR_SKIP);
    require PAGES_DIR . '/' . $template;
    exit;
}


/* ----------------------------------------------------------------------------
 * 6. Endereços antigos -> endereços novos
 * ----------------------------------------------------------------------------
 * Links antigos que já tenham sido partilhados (ou que o Google conheça) não
 * podem dar erro. Redirecionamos cada um para o equivalente novo:
 *
 *   /index.php                        -> /
 *   /index.php?lang=pt  ou  /?lang=pt -> /pt/
 *   /projeto.php?id=1                 -> /project/portfolio
 *   /projeto.php?id=1&lang=pt         -> /pt/projeto/portfolio
 *   /en/...                           -> /...   (o inglês não usa prefixo)
 */

// Língua pedida pelo parâmetro antigo "?lang=xx" (só se for uma língua que existe)
$legacyLang = (isset($query['lang']) && is_string($query['lang']) && App::isLanguage($query['lang']))
    ? $query['lang']
    : null;

// Página inicial antiga
if ($path === '/index.php' || ($legacyLang !== null && $path === '/')) {
    redirectTo(urlHome($legacyLang ?? $lang));
}

// Página de projeto antiga: procura o projeto pelo id e manda para o endereço com o slug
if ($path === '/projeto.php') {
    $id = (int)($query['id'] ?? 0);
    foreach ($projects as $p) {
        if ((int)$p['id'] === $id) {
            redirectTo(urlProject($p, $legacyLang ?? $lang));
        }
    }
    redirectTo(urlHome($legacyLang ?? $lang)); // id que não existe: vai para o início
}

// "/en/..." é a mesma coisa que "/...": manda para a versão sem prefixo,
// para não haver duas versões da mesma página (o Google não gosta de duplicados)
if (preg_match('#^/' . preg_quote($defaultLang, '#') . '(/|$)#', $path)) {
    redirectTo(substr($path, strlen($defaultLang) + 1) ?: '/');
}


/* ----------------------------------------------------------------------------
 * 7. Páginas
 * ----------------------------------------------------------------------------
 * Cada bloco verifica se o endereço corresponde a uma página; se sim, mostra-a
 * (render() termina o pedido). Se nenhum corresponder, chega ao fim: 404.
 */

// --- sitemap.xml (lista de todas as páginas, em todas as línguas, para o Google)
if ($path === '/sitemap.xml') {
    render('sitemap.php', ['page' => 'sitemap']);
}

// --- "/pt" sem barra no fim -> "/pt/"
// Cada página deve ter um único endereço; escolhemos a versão com "/".
if (!$segments && $path !== '/' && !str_ends_with($path, '/')) {
    redirectTo($path . '/');
}

// --- Página inicial: "/" ou "/pt/" (depois de tirar a língua, não sobra nada)
if (!$segments) {
    rememberLanguage();
    render('home.php', ['page' => 'home']);
}

// --- Página de projeto: "/project/<slug>" ou "/pt/projeto/<slug>"
// Depois de tirar a língua, sobram exatamente 2 partes: [palavra, slug]
if (count($segments) === 2) {
    [$word, $slug] = $segments;

    // Todas as palavras de "projeto" conhecidas: ['en' => 'project', 'pt' => 'projeto', ...]
    $projectWords = array_column($languages, 'project');

    if (in_array($word, $projectWords, true)) {
        foreach ($projects as $p) {
            if (projectSlug($p) !== $slug) {
                continue; // não é este projeto, passa ao próximo
            }

            // Palavra de outra língua (ex.: "/pt/project/portfolio"):
            // redireciona para a palavra certa ("/pt/projeto/portfolio")
            if ($word !== $languages[$lang]['project']) {
                redirectTo(urlProject($p));
            }

            // Em manutenção, as páginas de projeto não se mostram: volta ao início
            // (302, porque é só enquanto dura a manutenção)
            if ($isWIP) {
                redirectTo(urlHome(), 302);
            }

            rememberLanguage();
            $projetoAtual = $p;   // o template project.php usa esta variável
            render('project.php', ['page' => 'project', 'project' => $p]);
        }
    }
}


/* ----------------------------------------------------------------------------
 * 8. Nada correspondeu: página não encontrada (404)
 * ----------------------------------------------------------------------------
 */
// Guarda o resto do endereço (sem a língua), para o seletor de idioma da 404
// levar à mesma página noutra língua: /pt/abc -> /abc (que também dá 404, mas em inglês)
http_response_code(404);
render('404.php', ['page' => '404', 'segments' => $segments]);
