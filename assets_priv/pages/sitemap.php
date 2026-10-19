<?php
/**
 * ============================================================================
 *  sitemap.php — gera o endereço /sitemap.xml
 * ============================================================================
 *
 *  O sitemap é uma lista de todas as páginas do site, que o Google lê para
 *  as encontrar mais depressa. É submetido no Google Search Console e está
 *  indicado no robots.txt.
 *
 *  É gerado automaticamente: cada página (início + cada projeto) aparece uma vez
 *  por língua, e cada entrada indica as versões nas outras línguas (hreflang).
 *  Ao adicionar um projeto ou uma língua, o sitemap atualiza-se sozinho.
 *
 *  Carregado pelo router.php quando se pede /sitemap.xml.
 * ============================================================================
 */

/** @var array $languages        Lista de línguas (languages.php) */
/** @var bool $isWIP             Em manutenção, o sitemap sai vazio */

// Diz ao browser/Google que isto é XML e não HTML
header('Content-Type: application/xml; charset=utf-8');

// Lê os projetos diretamente do JSON (sem tradução), porque só precisamos do slug,
// que é igual em todas as línguas
$allProjects = parseJsonFile(__DIR__ . '/../lang/projects.json');

// Lista de páginas. Cada página é uma função que, dada uma língua, devolve o endereço:
//   $pages[0]('pt') -> '/pt/'
//   $pages[1]('pt') -> '/pt/projeto/portfolio'
$pages = [static fn(string $l): string => urlHome($l)];
foreach ($allProjects as $p) {
    $pages[] = static fn(string $l): string => urlProject($p, $l);
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

if (!$isWIP) {
    foreach ($pages as $urlFor) {
        // Uma entrada <url> por língua...
        foreach (array_keys($languages) as $l) {
            echo "  <url>\n    <loc>" . SITE_URL . e($urlFor($l)) . "</loc>\n";

            // ...e dentro dela, os endereços da mesma página nas outras línguas
            foreach (array_keys($languages) as $alt) {
                echo '    <xhtml:link rel="alternate" hreflang="' . e($alt) . '" href="' . SITE_URL . e($urlFor($alt)) . '"/>' . "\n";
            }
            echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . SITE_URL . e($urlFor(App::defaultLang())) . '"/>' . "\n";
            echo "  </url>\n";
        }
    }
}

echo '</urlset>' . PHP_EOL;
