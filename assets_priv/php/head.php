<?php
/**
 * ============================================================================
 *  head.php — início do HTML (<!DOCTYPE> até </head>), igual em todas as páginas
 * ============================================================================
 *
 *  Antes, cada página repetia este bloco todo. Agora as páginas só definem
 *  as variáveis que mudam e fazem "require" deste ficheiro.
 *
 *  Variáveis que a página pode definir ANTES de incluir este ficheiro
 *  (se não definir, usa-se o valor por omissão entre parênteses):
 *
 *    $pageTitle  título no separador do browser    ("Dilan Pereira")
 *    $pageDesc   descrição que o Google mostra     (texto "metaDesc" do texts.json)
 *    $ogTitle    título na pré-visualização de links (WhatsApp, LinkedIn...)
 *    $ogDesc     descrição na pré-visualização de links
 *    $ogType     "website" para o início, "article" para projetos
 *    $noIndex    true = pedir ao Google para NÃO indexar (manutenção, 404)
 *    $extraHead  HTML extra a meter no <head> (ex.: o Schema.org da página inicial)
 *
 *  Escapar texto: usar sempre e() (helpers.php) em tudo o que vem de dados.
 *
 *  O operador "??=" significa: "se a variável ainda não existe, dá-lhe este valor".
 * ============================================================================
 */

/** @var string $lang  Língua atual (lang_init.php) */
/** @var bool $isWIP   Site em manutenção (config.php) */

$pageTitle ??= 'Dilan Pereira';
$pageDesc  ??= __('metaDesc', 'Dilan Pereira - Computer Engineering Student.');
$ogTitle   ??= 'Dilan Pereira | Software Engineer';
$ogDesc    ??= __('ogDesc', 'Explore my journey and projects in software development and infrastructure.');
$ogType    ??= 'website';
$noIndex   ??= $isWIP;   // em manutenção, nunca deixar o Google indexar

// Imagem de pré-visualização na língua atual (og-image-pt.png, og-image-en.png...).
// Se ainda não existir para esta língua, usa a inglesa.
$ogImage = file_exists(PUBLIC_DIR . "/assets/img/og-image-$lang.png") ? "og-image-$lang.png" : 'og-image-en.png';

// Código de região usado pelo Facebook/LinkedIn (pt -> pt_PT). Línguas novas sem
// entrada aqui usam só o código ("fr"), o que também é aceite.
$ogLocale = ['pt' => 'pt_PT', 'en' => 'en_US'][$lang] ?? $lang;
?>
<!DOCTYPE html>
<html lang="<?php echo e($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>

    <!-- Texto mostrado pelo Google por baixo do título nos resultados -->
    <meta name="description" content="<?php echo e($pageDesc); ?>">
    <meta name="author" content="Dilan Pereira">

    <?php if ($noIndex): ?>
    <!-- Não indexar esta página (manutenção ou 404) -->
    <meta name="robots" content="noindex">
    <?php else: ?>
    <!-- Endereço oficial desta página e as suas versões noutras línguas (ver urls.php) -->
    <?php echo hreflangTags(); ?>
    <?php endif; ?>

    <!-- Open Graph: pré-visualização quando o link é partilhado (WhatsApp, LinkedIn, Facebook...) -->
    <meta property="og:type" content="<?php echo e($ogType); ?>">
    <meta property="og:url" content="<?php echo e(SITE_URL . urlCurrentIn($lang)); ?>">
    <meta property="og:site_name" content="Dilan Pereira">
    <meta property="og:title" content="<?php echo e($ogTitle); ?>">
    <meta property="og:description" content="<?php echo e($ogDesc); ?>">
    <meta property="og:locale" content="<?php echo e($ogLocale); ?>">
    <meta property="og:image" content="<?php echo e(SITE_URL . '/assets/img/' . $ogImage); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <!-- Twitter/X: mostrar a imagem grande -->
    <meta name="twitter:card" content="summary_large_image">

    <!-- Estilos. versioned() acrescenta "?v=<data do ficheiro>": o browser guarda-os em cache
         durante muito tempo, e quando o ficheiro muda o endereço muda e descarrega o novo -->
    <link href="<?php echo e(versioned('assets/css/bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(versioned('assets/css/style.css')); ?>" rel="stylesheet">
    <!-- Começa a descarregar a fonte logo no início, para o texto não "saltar" -->
    <link rel="preload" href="/assets/fonts/inter-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Ícones: separador do browser (.ico e .svg) e ecrã inicial do iPhone -->
    <link rel="icon" href="/assets/img/favicon.ico" sizes="48x48">
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
    <link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
<?php echo $extraHead ?? ''; ?>
</head>
