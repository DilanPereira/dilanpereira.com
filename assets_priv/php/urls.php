<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  urls.php — funções que constroem os endereços do site
 * ============================================================================
 *
 *  Em vez de escrever endereços "à mão" nos templates (ex.: href="/pt/projeto/x"),
 *  usa-se sempre uma destas funções. Assim, se a estrutura dos endereços mudar,
 *  só se muda aqui.
 *
 *  Estrutura dos endereços:
 *      /                       início, na língua principal (inglês)
 *      /pt/                    início em português
 *      /project/<slug>         projeto, na língua principal
 *      /pt/projeto/<slug>      projeto em português
 *
 *  Quando o parâmetro $lang não é passado (null), usa-se a língua da página
 *  atual (App::lang()).
 * ============================================================================
 */

/** Endereço público do site, usado em links absolutos (Google, redes sociais, sitemap). */
const SITE_URL = 'https://dilanpereira.com';


/**
 * Prefixo da língua no início do endereço.
 *   langPrefix('en') -> ''      (a língua principal não tem prefixo)
 *   langPrefix('pt') -> '/pt'
 */
function langPrefix(?string $lang = null): string
{
    $lang ??= App::lang();
    return $lang === App::defaultLang() ? '' : '/' . $lang;
}


/**
 * Endereço da página inicial.
 *   urlHome('en') -> '/'
 *   urlHome('pt') -> '/pt/'
 */
function urlHome(?string $lang = null): string
{
    return langPrefix($lang) . '/';
}


/**
 * "Slug" de um projeto: a parte final do endereço que o identifica (ex.: "portfolio").
 *
 * Usa o campo "slug" do projects.json. Se um projeto não tiver esse campo,
 * cria um automaticamente a partir do título em inglês:
 *   "Personal Portfolio Website" -> "personal-portfolio-website"
 * (minúsculas, sem acentos, espaços e símbolos trocados por "-").
 * Em último caso usa o id do projeto.
 */
function projectSlug(array $project): string
{
    if (!empty($project['slug'])) {
        return (string)$project['slug'];
    }

    // O título pode vir já traduzido (texto) ou com todas as línguas (['pt' => ..., 'en' => ...])
    $title = is_array($project['titulo'] ?? null)
        ? (string)($project['titulo']['en'] ?? reset($project['titulo']))
        : (string)($project['titulo'] ?? '');

    // iconv com ASCII//TRANSLIT tira os acentos (ç -> c, ã -> a)
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $title) ?: $title;
    $slug  = strtolower(trim((string)preg_replace('/[^A-Za-z0-9]+/', '-', $ascii), '-'));

    return $slug !== '' ? $slug : (string)($project['id'] ?? '');
}


/**
 * Endereço de um projeto.
 *   urlProject($p, 'en') -> '/project/portfolio'
 *   urlProject($p, 'pt') -> '/pt/projeto/portfolio'
 * A palavra "project"/"projeto" vem do languages.php ('project' => ...).
 */
function urlProject(array $project, ?string $lang = null): string
{
    $lang ??= App::lang();
    $word = App::languages()[$lang]['project'];
    return langPrefix($lang) . '/' . $word . '/' . rawurlencode(projectSlug($project));
}


/**
 * Endereço da página ATUAL noutra língua.
 * Usado no seletor de idioma: se estás em /pt/projeto/portfolio e carregas em
 * "English", vais para /project/portfolio (e não para a página inicial).
 * Na 404, vais para o mesmo endereço errado noutra língua (mostra a 404 nessa língua).
 */
function urlCurrentIn(string $lang): string
{
    $route = App::route();

    if ($route['page'] === 'project' && !empty($route['project'])) {
        return urlProject($route['project'], $lang);
    }

    // Página 404: o mesmo endereço noutra língua, para mostrar a 404 nessa língua.
    //   /pt/abc            -> /abc
    //   /pt/projeto/xyz    -> /project/xyz   (a palavra "projeto" também é traduzida)
    if ($route['page'] === '404' && !empty($route['segments'])) {
        $segments = $route['segments'];

        // Se o resto do endereço começar por um código de língua (ex.: /pt/en/abc),
        // o endereço noutra língua seria interpretado de outra forma: vai para o início
        if (App::isLanguage($segments[0])) {
            return urlHome($lang);
        }
        if (in_array($segments[0], array_column(App::languages(), 'project'), true)) {
            $segments[0] = App::languages()[$lang]['project'];
        }
        return langPrefix($lang) . '/' . implode('/', array_map('rawurlencode', $segments));
    }

    return urlHome($lang);
}


/**
 * Link para uma secção da página inicial (#sobre, #projetos, #contacto...).
 *   - Se já estamos na página inicial: só "#sobre" (faz scroll, sem recarregar)
 *   - Se estamos noutra página (ex.: um projeto): "/pt/#sobre" (vai ao início e depois à secção)
 */
function navHref(string $anchor): string
{
    $onHome = App::route()['page'] === 'home';
    return ($onHome ? '' : urlHome()) . '#' . $anchor;
}


/**
 * Transforma um caminho de ficheiro público em caminho absoluto.
 *   asset('assets/img/foto.png') -> '/assets/img/foto.png'
 *
 * Porquê: em /pt/projeto/portfolio, um caminho relativo "assets/img/foto.png"
 * seria procurado em /pt/projeto/assets/img/foto.png (que não existe).
 * Começando por "/", é sempre procurado a partir da raiz do site.
 *
 * Endereços externos (https://...) e caminhos que já começam por "/" ficam iguais.
 */
function asset(string $path): string
{
    if ($path === '' || preg_match('#^(https?:)?//#', $path) || $path[0] === '/') {
        return $path;
    }
    return '/' . $path;
}


/**
 * Igual a asset(), mas acrescenta "?v=<data de alteração do ficheiro>".
 *   versioned('assets/css/style.css') -> '/assets/css/style.css?v=1760812345'
 *
 * Usado no CSS e no JS. O nginx diz ao browser para guardar estes ficheiros
 * em cache durante muito tempo (o site fica mais rápido nas visitas seguintes).
 * Quando o ficheiro muda, o número muda, e o browser descarrega a versão nova
 * em vez de usar a antiga da cache.
 */
function versioned(string $path): string
{
    $url  = asset($path);
    $file = PUBLIC_DIR . $url;
    return is_file($file) ? $url . '?v=' . filemtime($file) : $url;
}


/**
 * Tags para o Google, colocadas no <head> de cada página:
 *
 *   <link rel="canonical" ...>                       "o endereço oficial desta página é este"
 *   <link rel="alternate" hreflang="en" ...>         "a versão em inglês está aqui"
 *   <link rel="alternate" hreflang="pt" ...>         "a versão em português está aqui"
 *   <link rel="alternate" hreflang="x-default" ...>  "para outras línguas, usa esta"
 *
 * Assim o Google sabe que / e /pt/ são a mesma página em línguas diferentes
 * (e não conteúdo duplicado), e mostra a versão certa a cada pessoa.
 */
function hreflangTags(): string
{
    $out = '<link rel="canonical" href="' . e(SITE_URL . urlCurrentIn(App::lang())) . '">' . PHP_EOL;

    foreach (array_keys(App::languages()) as $code) {
        $out .= '    <link rel="alternate" hreflang="' . e($code) . '" href="' . e(SITE_URL . urlCurrentIn($code)) . '">' . PHP_EOL;
    }
    $out .= '    <link rel="alternate" hreflang="x-default" href="' . e(SITE_URL . urlCurrentIn(App::defaultLang())) . '">' . PHP_EOL;

    return $out;
}


/**
 * Indica se um projeto tem imagem real.
 * Projetos ainda sem imagem têm "imagem": "placeholder" (ou vazio) no projects.json;
 * nesses casos o template mostra um retângulo cinzento em vez de uma imagem partida.
 */
function hasImage(?string $path): bool
{
    return $path !== null && $path !== '' && $path !== 'placeholder';
}
