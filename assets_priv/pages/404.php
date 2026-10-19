<?php
/**
 * ============================================================================
 *  404.php — "Página não encontrada"
 * ============================================================================
 *  Mostrada pelo router.php quando o endereço não corresponde a nenhuma página
 *  (ex.: /qualquer-coisa ou um projeto que não existe). O router já enviou o
 *  código HTTP 404, para o Google saber que a página não existe.
 *  Reaproveita o visual da página de manutenção (classes wip-* do style.css),
 *  incluindo os botões de idioma (lang_switch.php).
 * ============================================================================
 */
$pageTitle = __('notFoundTitle', 'Page not found') . ' | Dilan Pereira';
$noIndex = true;   // não faz sentido o Google indexar uma página de erro
require __DIR__ . '/../php/head.php';
?>
<body>
<main class="wip-page">
    <div class="wip-card">
        <a href="<?php echo e(urlHome()); ?>"><img src="/assets/img/logo.svg" alt="Dilan Pereira" width="166" height="36" class="wip-logo"></a>
        <p class="wip-status">404</p>
        <h1 class="wip-title"><?php echo e(__('notFoundTitle', 'Page not found')); ?></h1>
        <p class="wip-text"><?php echo e(__('notFoundText', "The page you're looking for doesn't exist or has moved.")); ?></p>
        <a href="<?php echo e(urlHome()); ?>" class="btn btn-dark rounded-pill px-4"><?php echo e(__('notFoundBack', 'Back to the homepage')); ?></a>
    </div>

    <!-- Mudar de idioma: mostra esta mesma 404 na língua escolhida -->
    <footer class="wip-footer">
        <?php require __DIR__ . '/../php/lang_switch.php'; ?>
        <p class="copyright mb-0">&copy; <?php echo date('Y'); ?> <strong>Dilan Pereira</strong></p>
    </footer>
</main>
</body>
</html>
