<?php
/**
 * ============================================================================
 *  header.php — barra de navegação no topo (igual em todas as páginas)
 * ============================================================================
 *  - Logótipo (link para o início na língua atual)
 *  - Seletor de idioma: cada opção leva à MESMA página noutra língua (urlCurrentIn)
 *  - Menu: em ecrãs pequenos fica escondido atrás do botão "hambúrguer"
 *    (componente "collapse" do Bootstrap)
 *  - navHref(): na página inicial os links são só "#secção" (scroll);
 *    noutras páginas levam ao início e depois à secção
 * ============================================================================
 */
/** @var string $lang          Língua atual */
/** @var array  $languageNames ['en' => 'English', 'pt' => 'Português'] */
/** @var array  $projects      Projetos (o link "Projetos" só aparece se houver) */
/** @var array  $educations    Percurso (o link só aparece se houver) */
?>
<header class="container-fluid py-3 fixed-top">
    <div class="container">
        <nav class="navbar navbar-expand-lg w-100">
            <div class="d-flex justify-content-between align-items-center w-100 position-relative flex-wrap">

                <a class="navbar-brand m-0" href="<?php echo e(urlHome()); ?>">
                    <img src="/assets/img/logo.svg" alt="Dilan Pereira" width="129" height="28">
                </a>

                <div class="d-flex align-items-center order-lg-3">
                    <div class="dropdown">
                        <button class="btn btn-sm dropdown-toggle rounded-pill px-3 custom-dropdown-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?php echo e(__('language', 'Language')); ?>">
                            <span class="lang"><?php echo e(strtoupper($lang)); ?></span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 dropdown-menu-custom">
                            <?php foreach ($languageNames as $code => $name): ?>
                                <li>
                                    <a class="dropdown-item <?php echo ($lang === $code) ? 'active' : ''; ?>"
                                       href="<?php echo e(urlCurrentIn($code)); ?>"
                                       lang="<?php echo e($code); ?>"><?php echo e($name); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <a href="<?php echo e(navHref('contacto')); ?>" class="btn btn-outline-dark btn-sm fs-6 rounded-pill px-3 d-none d-lg-inline ms-3">
                        <?php echo __('headerContact', 'Contact me'); ?>
                    </a>

                    <button class="navbar-toggler border-0 shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <div class="collapse navbar-collapse justify-content-center" id="menu">
                    <ul class="navbar-nav gap-lg-4 text-center">
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(navHref('inicio')); ?>"><?php echo __('headerHome', 'Home'); ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(navHref('sobre')); ?>"><?php echo __('headerAbout', 'About'); ?></a></li>
                        <?php if (!empty($educations)): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo e(navHref('percurso')); ?>"><?php echo __('headerEducation', 'Education & Experience'); ?></a></li>
                        <?php endif; ?>
                        <?php if (!empty($projects)): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo e(navHref('projetos')); ?>"><?php echo __('headerProjects', 'Projects'); ?></a></li>
                        <?php endif; ?>
                        <li class="nav-item d-lg-none">
                            <a class="nav-link fw-bold" href="<?php echo e(navHref('contacto')); ?>"><?php echo __('headerContact', 'Contact me'); ?></a>
                        </li>
                    </ul>
                </div>

            </div>
        </nav>
    </div>
</header>