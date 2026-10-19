<?php
/**
 * ============================================================================
 *  home.php — página inicial (/ e /pt/)
 * ============================================================================
 *  Carregada pelo router.php. Quando chega aqui, já existem:
 *    $lang, $projects, $educations, __()  (lang_init.php)
 *    $isWIP                               (config.php)
 *    urlHome(), urlProject(), asset()...  (urls.php)
 *
 *  Se $isWIP estiver ligado, mostra a página de manutenção (wip.php) em vez
 *  do conteúdo normal.
 * ============================================================================
 */
require_once __DIR__ . '/../php/contact_helper.php';

/** @var string $lang */
/** @var array $projects */
/** @var array $educations */
/** @var bool $isWIP Site em manutenção (config.php) */

// Em manutenção: 503 diz aos motores de busca que é temporário (não indexam a página)
if ($isWIP) {
    http_response_code(503);
    header('Retry-After: 3600');
}

// Formulário de contacto: token, momento em que foi mostrado e resultado do último envio
$csrfToken = contactCsrfToken();
contactMarkFormShown();
$contactFlash = $_SESSION['contact_flash'] ?? null;
unset($_SESSION['contact_flash']);
$contactOld = $contactFlash['old'] ?? [];
$contactErrors = $contactFlash['errors'] ?? [];
$old = static fn(string $field): string => e($contactOld[$field] ?? '');
$invalid = static fn(string $field): string => in_array($field, $contactErrors, true) ? ' is-invalid' : '';
?>

<?php
$extraHead = <<<'HTML'
    <!-- Dados estruturados (Schema.org): dizem ao Google e às IAs quem é a pessoa deste site -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Person",
      "name": "Dilan Pereira",
      "jobTitle": "Software Engineer",
      "url": "https://dilanpereira.com",
      "sameAs": [
        "https://github.com/DilanPereira",
        "https://www.linkedin.com/in/dilanpereira/"
      ],
      "knowsAbout": [
        "Software Engineering",
        "Full-Stack Development",
        "Java",
        "Python",
        "PHP",
        "SQL",
        "Computer Networks",
        "CCNA ITN",
        "CCNA SRWE"
      ],
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Castelo Branco",
        "addressCountry": "PT"
      }
    }
    </script>

HTML;
require __DIR__ . '/../php/head.php';
?>
<body id="inicio">

<?php if ($isWIP): ?>
    <?php include __DIR__ . '/wip.php'; ?>
<?php else: ?>

<?php require_once __DIR__ . '/../php/header.php'; ?>

    <main>
        <!-- ===== Início: nome, função e botões ===== -->
        <section class="container-fluid min-vh-100 d-flex align-items-center text-center reveal">
            <div class="container">
                <h1 class="display-4 fw-bold">
                    <?php echo __('heroMainStudy', 'Computer Engineering Student') ?><br>
                    <span class="text-accent"><?php echo __('heroWorkArea', 'Software Development and Networks') ?></span>
                </h1>

                <p class="lead text-muted mt-3">
                    <?php
                    echo __('heroDesc','I explore the full spectrum of Computer Engineering, with a special interest in intelligent systems, network architecture, security, and software development.');
                    echo __('heroExp',' Networking background (CCNA) with skills in Java, Python, PHP, and SQL databases.');
                    ?>
                </p>

                <a href="#sobre" class="btn btn-dark rounded-pill px-4 py-2 mt-4">
                    <?php echo __('heroMore', 'Find out more about me'); ?>
                </a>

            </div>
        </section>

        <!-- ===== Sobre mim: foto e apresentação ===== -->
        <hr class="soft-divider" id="sobre">
        <section class="container py-5 mt-5 reveal">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-5 mb-4 mb-md-0 text-center text-md-start">
                    <img src="/assets/img/imgteste.png"
                         alt="Dilan Pereira"
                         width="1080" height="1350"
                         class="img-fluid rounded-4 shadow-lg"
                         loading="lazy"
                         style="border: 4px solid var(--accent);
                                    padding: 0;
                                    width: 100%;
                                    display: block;
                                    max-width: 400px;
                                    max-height:500px;
                                    height: auto;">
                </div>

                <div class="col-lg-8 col-md-7 ps-md-5">
                    <p class="text-uppercase fw-bold text-muted mb-2 small" style="letter-spacing: 2px;"><?php echo __('aboutSubTitle', 'About me') ?></p>
                    <h2 class="display-5 fw-bold mb-4">
                        <?php echo __('aboutTitle', 'Building <span class="text-accent">software</span> with a strong <span class="text-accent">infrastructure</span> perspective.') ?>
                    </h2>

                    <div class="lead mb-4">
                        <p>
                            <?php
                            echo __("aboutText1", "Hello! I'm <strong>Dilan Pereira</strong>, a Computer Engineering student focused on exploring the full spectrum of the field. With a special interest in programming, networking (with CCNA ITN & SRWE modules), IoT, and cybersecurity, I aim to bridge software development with secure and robust infrastructure.");
                            ?>
                        </p>
                        <p>
                            <?php
                            echo __("aboutText2", "Throughout my academic and practical journey, I have worked with various programming languages and systems, consolidating knowledge in development methodologies and engineering best practices.");
                            ?>
                        </p>
                    </div>
                    <!-- Competências: 3 lado a lado; entre 768 e 991px (coluna estreita) 2 + 1 -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-sm-4 col-md-6 col-lg-4">
                            <div class="skill-card p-3 border rounded-3 bg-light">
                                <h3 class="h5 mb-1 fw-bold">Backend</h3>
                                <small class="text-muted">Java, Python, PHP</small>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-6 col-lg-4">
                            <div class="skill-card p-3 border rounded-3 bg-light">
                                <h3 class="h5 mb-1 fw-bold">Mobile/<wbr>Web</h3>
                                <small class="text-muted">Kotlin, HTML, CSS, PHP, JS</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4 col-md-12 col-lg-4">
                            <div class="skill-card p-3 border rounded-3 bg-light">
                                <h3 class="h5 mb-1 fw-bold">Data</h3>
                                <small class="text-muted">SQL, JSONs, JWT</small>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($educations)): ?>
                        <a href="#percurso" class="btn btn-dark rounded-pill px-4 py-2">
                            <?php echo __('aboutBackground', 'View my background') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($educations)): ?>
            <hr class="soft-divider" id="percurso">
            <!-- ===== Percurso: formação e experiência (itens "extra" escondidos até carregar no botão) ===== -->
            <section class="timeline-container py-5 reveal">
                <div class="container" >
                    <div class="row">
                        <div class="col-12 col-md-8 col-lg-6 mx-auto">
                            <div class="d-flex justify-content-between align-items-center mb-5">
                                <h2 class="section-title mb-0">
                                    <?php echo __('eduTitle', 'Education & Experience') ?>
                                </h2>
                                <div>
                                    <button id="toggle-percurso-btn" 
                                            class="btn btn-link text-muted text-decoration-none p-0 fw-semibold toggle-percurso-btn" 
                                            type="button" 
                                            aria-expanded="false"
                                            data-text-show="<?php echo e(__('btnShowFull', 'Ver percurso completo 📜')); ?>"
                                            data-text-hide="<?php echo e(__('btnHideFull', 'Ocultar anteriores 👁️‍🗨️')); ?>">
                                        <?php echo __('btnShowFull', 'Ver percurso completo 📜'); ?>
                                    </button>
                                </div>
                            </div>

                            <?php foreach($educations as $item): 
                                // loadLanguageData() já devolve os textos no idioma ativo
                                $anoText = $item['ano'] ?? '';
                                $tituloText = $item['titulo'] ?? '';
                                $instText = $item['instituicao'] ?? '';
                                $descText = $item['descricao'] ?? '';

                                $isRelevante = isset($item['relevante']) ? $item['relevante'] : true;
                                $itemClass = $isRelevante ? 'timeline-item ps-4 pb-5' : 'timeline-item ps-4 pb-5 extra-exp-item';
                            ?>
                                <div class="<?php echo $itemClass; ?>">
                                    <div class="timeline-dot"></div>
                                    
                                    <div class="timeline-collapse-wrapper <?php echo (!$isRelevante) ? 'd-none' : ''; ?>">
                                        <div class="timeline-date text-uppercase">
                                            <?php echo e($anoText); ?>
                                        </div>
                                        <h3 class="timeline-title h5 mb-1 d-flex align-items-center gap-2 flex-wrap">
                                            <?php if (empty($item['link'])): ?>
                                                <?php echo e($tituloText); ?>
                                            <?php else: ?>
                                                <a href="<?php echo e($item['link']); ?>" target="_blank" rel="noopener noreferrer" class="timeline-link">
                                                    <?php echo e($tituloText); ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-up-right ms-1 mb-1" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M14 2.5a.5.5 0 00-.5-.5h-6a.5.5 0 0 0 0 1h4.793L2.146 13.146a.5.5 0 00.708.708L13 3.707V8.5a.5.5 0 0 0 1 0v-6z"/>
                                                    </svg>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!$isRelevante): ?>
                                                <span class="badge bg-light text-muted border fw-normal" style="font-size: 0.7rem;">
                                                    <?php echo __('eduComplementary', 'Complementary Experience'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </h3>
                                        <div class="timeline-inst">
                                            <?php if (empty($item['linkIns'])): ?>
                                                <?php echo e($instText); ?>
                                            <?php else: ?>
                                                <a href="<?php echo e($item['linkIns']); ?>" target="_blank" rel="noopener noreferrer" class="timeline-link">
                                                    <?php echo e($instText); ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-up-right ms-1 mb-1" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M14 2.5a.5.5 0 00-.5-.5h-6a.5.5 0 0 0 0 1h4.793L2.146 13.146a.5.5 0 00.708.708L13 3.707V8.5a.5.5 0 0 0 1 0v-6z"/>
                                                    </svg>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <p class="timeline-desc mb-0">
                                            <?php echo e($descText); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($projects)): ?>
            <hr class="soft-divider" id="projetos">
            <!-- ===== Projetos: um cartão por projeto, com link para a página de detalhe ===== -->
            <section class="py-5 reveal">
                <div class="container">
                    <h2 class="section-title mb-5"><?php echo __('projTitle', 'Featured Projects') ?></h2>

                    <div class="row g-4">
                        <?php foreach($projects as $proj): ?>
                            <div class="col-12 col-md-6 col-lg-4 reveal">
                                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                                    <div style="height: 200px; overflow: hidden; background: #eee;">
                                        <?php if (hasImage($proj['imagem'] ?? null)): ?>
                                            <img src="<?php echo e(asset($proj['imagem'])); ?>" loading="lazy" class="card-img-top w-100 h-100" style="object-fit: cover;" alt="<?php echo e($proj['titulo']); ?>">
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="mb-2" aria-label="<?php echo e(__('techUsed', 'Technologies used')); ?>">
                                            <?php foreach($proj['tecnologias'] as $tech): ?>
                                                <span class="badge rounded-pill bg-light text-dark border me-1" style="font-size: 0.75rem;">
                                                        <?php echo e($tech); ?>
                                                    </span>
                                            <?php endforeach; ?>
                                        </div>

                                        <h3 class="h5 card-title fw-bold"><?php echo e($proj['titulo']); ?></h3>
                                        <p class="card-text text-muted" style="font-size: 0.9rem;">
                                            <?php echo e($proj['descricao']); ?>
                                        </p>
                                    </div>

                                    <div class="card-footer bg-white border-0 p-4 pt-0">
                                        <a href="<?php echo e(urlProject($proj)); ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                                            <?php echo __('projCheck', 'Check it out') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <hr class="soft-divider" id="contacto">
        <!-- ===== Contacto: links diretos e formulário (enviado para /contacto.php) ===== -->
        <section class="py-5 reveal">
            <div class="container">
                <div class="row g-4 g-lg-5">
                    <div class="col-lg-5">
                        <h2 class="section-title mb-4"><?php echo __('contTitle', 'Get in touch'); ?></h2>
                        <p class="text-muted mb-5">
                            <?php echo __('contDesc', "I'm always open to new projects, collaborations, or just to chat about technology. Choose whichever way suits you best!"); ?>
                        </p>

                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-light p-3 rounded-circle me-3 text-center" style="width: 60px; height: 60px;" aria-hidden="true">
                                <span style="font-size: 1.5rem;">✉️</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="h6 mb-0 fw-bold">E-mail</h3>
                                <a href="mailto:<?php echo __('contEmail', 'contact@dilanpereira.com') ?>" class="contact-link">
                                    <?php echo __('contEmail', 'contact@dilanpereira.com') ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-up-right ms-1 mb-1" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M14 2.5a.5.5 0 0 0-.5-.5h-6a.5.5 0 0 0 0 1h4.793L2.146 13.146a.5.5 0 0 0 .708.708L13 3.707V8.5a.5.5 0 0 0 1 0v-6z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-light p-3 rounded-circle me-3 text-center" style="width: 60px; height: 60px;" aria-hidden="true">
                                <span style="font-size: 1.5rem;">📍</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="h6 mb-0 fw-bold"><?php echo __('contLoc', 'Location') ?></h3>
                                <a href="https://maps.app.goo.gl/1r3L2CF1DMxTAmfv7" target="_blank" rel="noopener noreferrer" class="contact-link">
                                    Castelo Branco, Portugal
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-up-right ms-1 mb-1" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M14 2.5a.5.5 0 0 0-.5-.5h-6a.5.5 0 0 0 0 1h4.793L2.146 13.146a.5.5 0 0 0 .708.708L13 3.707V8.5a.5.5 0 0 0 1 0v-6z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <?php if ($contactFlash):
                            $status = $contactFlash['status'];
                            $alertMap = [
                                'ok'      => ['success', __('formSuccess', 'Message sent successfully!')],
                                'invalid' => ['warning', __('formInvalid', 'Please check the highlighted fields.')],
                                'rate'    => ['warning', __('formRateLimit', 'You have sent several messages recently. Please try again later.')],
                                'expired' => ['warning', __('formExpired', 'The form expired. Please try again.')],
                                'error'   => ['danger',  __('formError', 'Something went wrong. Please email me directly.')],
                            ];
                            [$alertType, $alertText] = $alertMap[$status] ?? $alertMap['error'];
                        ?>
                            <div class="alert alert-<?php echo $alertType; ?> rounded-4 border-0 shadow-sm mb-4" role="<?php echo $status === 'ok' ? 'status' : 'alert'; ?>">
                                <?php echo e($alertText); ?>
                            </div>
                        <?php endif; ?>
                        <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                            <form action="/contacto.php" method="POST">
                                <input type="hidden" name="csrf" value="<?php echo e($csrfToken); ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="form-nome" class="form-label fw-semibold"><?php echo __("contFormName", 'Name') ?></label>
                                        <input type="text" id="form-nome" name="nome" maxlength="100" autocomplete="name" value="<?php echo $old('nome'); ?>" class="form-control bg-light border-0 py-2<?php echo $invalid('nome'); ?>" placeholder="<?php echo e(__("contFormNameEx", 'Your name')); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="form-email" class="form-label fw-semibold">E-mail</label>
                                        <input type="email" id="form-email" name="email" maxlength="254" autocomplete="email" value="<?php echo $old('email'); ?>" class="form-control bg-light border-0 py-2<?php echo $invalid('email'); ?>" placeholder="<?php echo e(__("contFormEmailEx", 'example@email.com')); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="form-assunto" class="form-label fw-semibold"><?php echo __("contFormSubject", 'Subject') ?></label>
                                        <input type="text" id="form-assunto" name="assunto" maxlength="150" value="<?php echo $old('assunto'); ?>" class="form-control bg-light border-0 py-2<?php echo $invalid('assunto'); ?>" placeholder="<?php echo e(__("contFormSubjectEx", 'How can I help?')); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="form-msg" class="form-label fw-semibold"><?php echo __("contFormMsg", 'Message'); ?></label>
                                        <textarea name="mensagem" id="form-msg" minlength="10" maxlength="5000" class="form-control bg-light border-0 py-2<?php echo $invalid('mensagem'); ?>" rows="4" placeholder="<?php echo e(__("contFormMsgEx", "Write your message here...")); ?>" required><?php echo $old('mensagem'); ?></textarea>
                                    </div>
                                    <div class="visually-hidden-pot" aria-hidden="true">
                                        <label for="website">URL:</label>
                                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                                    </div>
                                    <div class="col-12 mt-4">
                                        <p class="small text-muted mb-3"><?php echo __('formPrivacy', 'Your details are only used to reply to your message.'); ?></p>
                                        <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill fw-bold" style="background-color: var(--text-main);">
                                            <?php echo __("contFormSend", 'Send Message') ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
    <!-- "defer": os scripts descarregam em paralelo e só correm depois de o HTML estar pronto -->
    <script src="<?php echo e(versioned('assets/js/bootstrap.bundle.min.js')); ?>" defer></script>
    <script src="<?php echo e(versioned('assets/js/script.js')); ?>" defer></script>

<?php endif; // fim do "if ($isWIP) ... else" ?>
</body>
</html>