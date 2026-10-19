<?php
/**
 * ============================================================================
 *  footer.php — rodapé (igual em todas as páginas)
 * ============================================================================
 *  - Copyright com o ano atual e links para o LinkedIn e GitHub
 *  - Botão "voltar ao topo": aparece depois de descer a página
 *    (o comportamento está no script.js, secção "Voltar ao topo")
 * ============================================================================
 */
?>
<footer class="py-5 mt-5">
    <div class="container text-center">
        <hr class="soft-divider">

        <div class="footer-content">
            <p class="copyright mb-4">
                &copy; <?php echo date('Y'); ?> Dilan Pereira
            </p>

            <div class="footer-links d-flex justify-content-center gap-4">
                <a href="https://www.linkedin.com/in/dilanpereira/" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                <a href="https://github.com/DilanPereira" target="_blank" rel="noopener noreferrer">GitHub</a>
            </div>
        </div>
    </div>
    <button type="button" id="backToTop" class="btn btn-accent shadow-lg" title="<?php echo e(__('backToTop', 'Back to top')); ?>" aria-label="<?php echo e(__('backToTop', 'Back to top')); ?>">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-up-short" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5z"/>
        </svg>
    </button>
</footer>