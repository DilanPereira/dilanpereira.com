<?php
/**
 * ============================================================================
 *  project.php — página de um projeto (/project/<slug> e /pt/projeto/<slug>)
 * ============================================================================
 *  Carregada pelo router.php, que já encontrou o projeto pelo slug do endereço
 *  e o guardou em $projetoAtual (com os textos na língua atual).
 *
 *  Aqui só se definem o título e as descrições desta página e se inclui o
 *  <head> comum (head.php); o resto é o HTML da página.
 * ============================================================================
 */
/** @var string $lang */
/** @var array $projetoAtual */

$pageTitle = $projetoAtual['titulo'] . ' | Dilan Pereira';
$pageDesc  = __('projMetaDesc', 'Dilan Pereira - Project details') . ': ' . $projetoAtual['titulo'];
$ogTitle   = $pageTitle;
$ogDesc    = $projetoAtual['descricao'];
$ogType    = 'article';
require __DIR__ . '/../php/head.php';
?>
<body id="inicio">
    <?php require_once __DIR__ . '/../php/header.php'; ?>

    <main class="container py-5 mt-5">
        <div class="row reveal">
            <!-- Imagens: carrossel (se houver várias), imagem única, ou retângulo cinzento se não houver -->
            <div class="col-lg-7 mb-4">
                <?php if (!empty($projetoAtual['carrossel'])): ?>
                    <div id="projectCarousel" class="carousel slide shadow-lg rounded-4 overflow-hidden" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#projectCarousel" data-bs-slide-to="0" class="active"></button>
                            <?php for ($i = 1; $i <= count($projetoAtual['carrossel']); $i++): ?>
                                <button type="button" data-bs-target="#projectCarousel" data-bs-slide-to="<?php echo $i; ?>"></button>
                            <?php endfor; ?>
                        </div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="<?php echo e(asset($projetoAtual['imagem'])); ?>" class="d-block w-100" style="height: 450px; object-fit: cover;" alt="<?php echo e($projetoAtual['titulo']); ?>">
                            </div>
                            <?php foreach ($projetoAtual['carrossel'] as $imgExtra): ?>
                                <div class="carousel-item">
                                    <img src="<?php echo e(asset($imgExtra)); ?>" class="d-block w-100" style="height: 450px; object-fit: cover;" loading="lazy" alt="<?php echo e($projetoAtual['titulo']); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#projectCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#projectCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                <?php else: ?>
                    <?php if (hasImage($projetoAtual['imagem'] ?? null)): ?>
                        <img src="<?php echo e(asset($projetoAtual['imagem'])); ?>" class="img-fluid rounded-4 shadow-lg w-100" style="height: 450px; object-fit: cover;" alt="<?php echo e($projetoAtual['titulo']); ?>">
                    <?php else: ?>
                        <div class="rounded-4 shadow-sm w-100 bg-light border" style="height: 450px;" aria-hidden="true"></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Título, tecnologias, descrição curta e botões -->
            <div class="col-lg-5 ps-lg-5">
                <h1 class="display-5 fw-bold mb-3"><?php echo e($projetoAtual['titulo']); ?></h1>

                <div class="mb-4">
                    <?php foreach ($projetoAtual['tecnologias'] as $tech): ?>
                        <span class="badge rounded-pill bg-light text-dark border me-1">
                                <?php echo e($tech); ?>
                            </span>
                    <?php endforeach; ?>
                </div>

                <p class="lead text-muted mb-4">
                    <?php echo e($projetoAtual['descricao']); ?>
                </p>

                <div class="d-flex gap-3">
                    <?php if (!empty($projetoAtual['link']) && $projetoAtual['link'] !== 'index.php' && $projetoAtual['link'] !== 'placeholder'): ?>
                        <a href="<?php echo e($projetoAtual['link']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-dark rounded-pill px-4">
                            <?php echo __('projCheck', 'Check it out'); ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo e(urlHome()); ?>#projetos" class="btn btn-outline-dark rounded-pill px-4">
                        <?php echo __('back', 'Back'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Descrição detalhada (só aparece se existir no projects.json) -->
        <?php if (!empty($projetoAtual['descDetalhada'])): ?>
            <div class="row mt-5 reveal">
                <div class="col-12">
                    <hr class="soft-divider">
                    <h2 class="fw-bold mb-4 h3"><?php echo __('projDetails', 'Project Details'); ?></h2>
                    <div class="project-content lead" style="line-height: 1.8; text-align: justify;">
                        <?php echo nl2br(e($projetoAtual['descDetalhada'])); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../php/footer.php'; ?>

    <!-- "defer": os scripts descarregam em paralelo e só correm depois de o HTML estar pronto -->
    <script src="<?php echo e(versioned('assets/js/bootstrap.bundle.min.js')); ?>" defer></script>
    <script src="<?php echo e(versioned('assets/js/script.js')); ?>" defer></script>
</body>
</html>