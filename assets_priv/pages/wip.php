<?php
/**
 * ============================================================================
 *  wip.php — página de manutenção
 * ============================================================================
 *  Incluída pela home.php quando $isWIP = true (no config.php).
 *  Nessa situação a home.php já enviou o código HTTP 503 ("temporariamente
 *  indisponível") e o head.php pôs "noindex", para o Google não guardar esta página.
 *  Os textos estão em assets_priv/lang/wip.json.
 * ============================================================================
 */

/** @var string $lang      Língua atual (lang_init.php) */

$wip = loadLanguageData('wip', $lang);
$t = fn(string $key, string $default) => e($wip[$key] ?? $default);
$email = __('contEmail', 'contacto@dilanpereira.com');
?>
<main class="wip-page">
    <div class="wip-card">
        <img src="/assets/img/logo.svg" alt="Dilan Pereira" width="166" height="36" class="wip-logo">

        <p class="wip-status">
            <span class="wip-status-dot" aria-hidden="true"></span>
            <?php echo $t('wipStatus', 'Under maintenance'); ?>
        </p>

        <h1 class="wip-title"><?php echo $t('wipMainTitle', "I'm making a few improvements"); ?></h1>

        <p class="wip-text"><?php echo $t('wipMainMessage', 'The site will be back shortly. In the meantime, you can reach me by email or on LinkedIn.'); ?></p>

        <div class="wip-actions">
            <a href="mailto:<?php echo e($email); ?>" class="btn btn-dark rounded-pill px-4 wip-email">
                <?php echo e($email); ?>
            </a>
            <a href="https://www.linkedin.com/in/dilanpereira/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark rounded-pill px-4">LinkedIn</a>
            <a href="https://github.com/DilanPereira" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark rounded-pill px-4">GitHub</a>
        </div>
    </div>

    <footer class="wip-footer">
        <?php require __DIR__ . '/../php/lang_switch.php'; ?>
        <p class="copyright mb-0">&copy; <?php echo date('Y'); ?> <strong>Dilan Pereira</strong></p>
    </footer>
</main>
