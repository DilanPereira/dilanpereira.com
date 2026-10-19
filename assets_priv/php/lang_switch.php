<?php
/**
 * ============================================================================
 *  lang_switch.php — botões de idioma (EN | PT) das páginas simples
 * ============================================================================
 *  Usado nas páginas sem cabeçalho completo: manutenção (wip.php) e 404 (404.php).
 *  Cada botão leva à mesma página na outra língua (urlCurrentIn):
 *    - na 404, ao mesmo endereço noutra língua (mostra a 404 nessa língua);
 *    - na manutenção, à página inicial dessa língua.
 *  Línguas novas acrescentadas no languages.php aparecem aqui sozinhas.
 * ============================================================================
 */
/** @var string $lang Língua atual */
?>
<nav class="wip-lang" aria-label="<?php echo e(__('language', 'Language')); ?>">
    <?php foreach (App::languages() as $code => $info): ?>
        <a href="<?php echo e(urlCurrentIn($code)); ?>"
           class="wip-lang-link<?php echo $lang === $code ? ' is-active' : ''; ?>"
           <?php echo $lang === $code ? 'aria-current="true"' : ''; ?>
           lang="<?php echo e($code); ?>" title="<?php echo e($info['name']); ?>">
            <?php echo e(strtoupper($code)); ?>
        </a>
    <?php endforeach; ?>
</nav>
