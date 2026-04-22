<?php
/**
 * -------------------------------------------------
 * View: Dashboard
 * -------------------------------------------------
 * Renderuje hlavni dashboard klienta,
 * karty formularu a modal/mobilni kontejner
 * pro dynamicke formularove podani.
 */
?>
<!DOCTYPE html>
<html lang="<?= (($_SESSION['lang_id'] ?? 1) == 3) ? 'en' : 'cs' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">

    <title><?php e(t('dashboard') !== 'dashboard' ? t('dashboard') : 'Dashboard'); ?> - CopyGen</title>

    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
    <style>
        .dashboard-fullscreen-grid {
            display: grid;
            gap: 1.5rem;
            min-height: calc(100vh - 200px);
            padding-bottom: 2rem;
            grid-template-columns: 1fr;
            grid-auto-rows: minmax(150px, 1fr);
        }
        @media (min-width: 768px) {
            .dashboard-fullscreen-grid {
                grid-template-columns: repeat(var(--grid-cols, 2), 1fr);
                grid-template-rows: repeat(var(--grid-rows, 1), minmax(150px, 1fr));
            }
        }

        .dashboard-fullscreen-grid .card {
            width: 100%;
            height: 100%;
            margin: 0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 12px;
        }
        .dashboard-fullscreen-grid .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .is-pointer { cursor: pointer; }
        .is-pointer:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; }

        .dynamic-title { font-size: 1.2rem; margin-bottom: 0.25rem; }
        .dynamic-desc { font-size: 0.85rem; opacity: 0.75; }

        @media (min-width: 768px) {
            .dynamic-title { font-size: 1.5rem; margin-bottom: 0.5rem; }
            .dynamic-desc { font-size: 0.8rem; }
        }

        .dashboard-faq-block {
            margin-top: 2rem;
            padding-bottom: 2rem;
        }

        .dashboard-faq-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            overflow: hidden;
        }

        .dashboard-faq-card .accordion-button {
            font-weight: 600;
        }

        .dashboard-faq-card .accordion-button:not(.collapsed) {
            box-shadow: none;
        }

        .dashboard-faq-card .accordion-body {
            color: #526484;
            line-height: 1.7;
        }

        .app-footer-row {
            justify-content: center;
            gap: 0.5rem 1rem;
            text-align: center;
        }

        .app-footer-nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.125rem 0.25rem;
        }

        .nk-footer-links,
        .nk-footer-copyright {
            width: 100%;
        }

        .nk-footer-copyright {
            text-align: center;
        }

        .app-footer-nav .nav-item {
            min-width: 0;
        }

        .app-footer-nav .nav-link {
            font-size: 0.72rem;
            line-height: 1.2;
            padding: 0.15rem 0.35rem;
            text-align: center;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        @media (max-width: 767.98px) {
            .app-footer-row {
                flex-direction: column;
                align-items: center !important;
                justify-content: center !important;
                text-align: center;
            }

            .app-footer-nav {
                display: grid;
                grid-template-columns: minmax(0, 1.8fr) repeat(2, minmax(0, 0.7fr));
                width: min(100%, 21rem);
                gap: 0.1rem 0.2rem;
                margin-inline: auto;
            }

            .nk-footer-links {
                width: 100%;
            }

            .app-footer-nav .nav-link {
                font-size: 0.625rem;
                padding: 0.1rem 0.15rem;
            }

            .nk-footer-copyright {
                width: 100%;
                text-align: center;
                font-size: 0.6875rem !important;
            }
        }
    </style>
</head>

<body class="nk-body ">
    <div class="nk-app-root " data-sidebar-collapse="lg">
        <div class="nk-main">

            <?php include __DIR__ . '/../core/sidebar.php'; ?>

            <div class="nk-wrap">
                <?php include __DIR__ . '/../core/header.php'; ?>

                <div class="nk-content">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">

                                <div class="nk-block-head nk-page-head pb-4">
                                    <div class="nk-block-head-between">
                                        <div class="nk-block-head-content">
                                            <h2 class="display-6"><?php e(t('welcome_user') !== 'welcome_user' ? t('welcome_user') : 'Welcome'); ?> <?php e($firstName ?? (t('default_user_name') !== 'default_user_name' ? t('default_user_name') : 'Uživatel')); ?>!</h2>
                                            <p><?php e(t('select_module_desc') !== 'select_module_desc' ? t('select_module_desc') : 'Vyberte formulář, který chcete vyplnit.'); ?></p>

                                            <?php if (isset($_SESSION['flash_success'])): ?>
                                                <div class="alert alert-success alert-icon mt-3">
                                                    <em class="icon ni ni-check-circle"></em> <?php e($_SESSION['flash_success']); ?>
                                                </div>
                                                <?php unset($_SESSION['flash_success']); ?>
                                            <?php endif; ?>

                                            <?php if (isset($_SESSION['flash_error'])): ?>
                                                <div class="alert alert-danger alert-icon mt-3">
                                                    <em class="icon ni ni-alert-circle"></em> <?php e($_SESSION['flash_error']); ?>
                                                </div>
                                                <?php unset($_SESSION['flash_error']); ?>
                                            <?php endif; ?>

                                            <?php if (!empty($notificationMessage)): ?>
                                                <div class="alert alert-info alert-icon mt-3">
                                                    <em class="icon ni ni-bell"></em> <?php e($notificationMessage); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div id="dashboard-main-view">
                                    <div class="dashboard-fullscreen-grid" style="--grid-cols: <?php e($gridCols ?? 2); ?>; --grid-rows: <?php e($gridRows ?? 1); ?>;">
                                        <?php if (!empty($forms)): ?>
                                            <?php foreach ($forms as $form): ?>
                                                <div class="card card-full is-pointer <?php e($form['color'] ?? 'bg-primary'); ?> bg-opacity-10" onclick="handleCardClick(<?php echo $form['id']; ?>, '<?php e(addslashes(t($form['title_localized_key']))); ?>')">
                                                    <div class="card-body">
                                                        <div class="media media-rg media-middle media-circle bg-white shadow-sm text-dark mb-3">
                                                            <em class="icon ni ni-file-docs"></em>
                                                        </div>
                                                        <h5 class="dynamic-title fw-medium text-dark">
                                                            <?php e(t($form['title_localized_key'])); ?>
                                                        </h5>
                                                        <p class="dynamic-desc text-dark mb-0">
                                                            <?php e(t($form['description_localized_key'])); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="dashboard-faq-block">
                                        <div class="nk-block-head nk-block-head-sm pt-2">
                                            <div class="nk-block-head-content">
                                                <h3 class="nk-block-title">Rychlé otázky</h3>
                                                <p class="text-soft mb-0">Krátké odpovědi na nejběžnější dotazy.</p>
                                            </div>
                                        </div>

                                        <div class="card shadow-none dashboard-faq-card">
                                            <div class="card-body p-0">
                                                <div class="accordion" id="dashboardQuickFaq">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardFaqOne" aria-expanded="true" aria-controls="dashboardFaqOne">
                                                                Jak se máš?
                                                            </button>
                                                        </h2>
                                                        <div id="dashboardFaqOne" class="accordion-collapse collapse show" data-bs-parent="#dashboardQuickFaq">
                                                            <div class="accordion-body">
                                                                Mám se dobře a jsem připravený pomoct s dalším krokem v aplikaci.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardFaqTwo" aria-expanded="false" aria-controls="dashboardFaqTwo">
                                                                Kde začít?
                                                            </button>
                                                        </h2>
                                                        <div id="dashboardFaqTwo" class="accordion-collapse collapse" data-bs-parent="#dashboardQuickFaq">
                                                            <div class="accordion-body">
                                                                Začni výběrem jedné karty nahoře. Po kliknutí se otevře formulář, ve kterém můžeš rovnou pokračovat.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardFaqThree" aria-expanded="false" aria-controls="dashboardFaqThree">
                                                                Uloží se moje data?
                                                            </button>
                                                        </h2>
                                                        <div id="dashboardFaqThree" class="accordion-collapse collapse" data-bs-parent="#dashboardQuickFaq">
                                                            <div class="accordion-body">
                                                                Ano, po odeslání formuláře se údaje uloží a uvidíš je také v přehledu požadavků.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardFaqFour" aria-expanded="false" aria-controls="dashboardFaqFour">
                                                                Funguje to i na mobilu?
                                                            </button>
                                                        </h2>
                                                        <div id="dashboardFaqFour" class="accordion-collapse collapse" data-bs-parent="#dashboardQuickFaq">
                                                            <div class="accordion-body">
                                                                Ano, dashboard i tento akordeon jsou připravené tak, aby byly přehledné na telefonu i na počítači.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardFaqFive" aria-expanded="false" aria-controls="dashboardFaqFive">
                                                                Co když si nevím rady?
                                                            </button>
                                                        </h2>
                                                        <div id="dashboardFaqFive" class="accordion-collapse collapse" data-bs-parent="#dashboardQuickFaq">
                                                            <div class="accordion-body">
                                                                Když si nebudeš jistý, otevři požadovaný formulář a pokračuj krok po kroku. Rozhraní je připravené tak, aby bylo co nejjednodušší.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="mobile-form-view" class="d-none">
                                    <div class="nk-block-head">
                                        <div class="nk-block-head-between">
                                            <div class="nk-block-head-content">
                                                <button onclick="closeMobileForm()" class="btn btn-outline-light bg-white d-inline-flex align-items-center mb-3">
                                                    <em class="icon ni ni-arrow-left"></em>
                                                    <span><?php e(t('back_btn') !== 'back_btn' ? t('back_btn') : 'Zpět na přehled'); ?></span>
                                                </button>
                                                <h2 class="display-6" id="mobile-form-title"><?php e(t('loading') !== 'loading' ? t('loading') : 'Načítání...'); ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="nk-block">
                                        <div class="card card-bordered">
                                            <div class="card-inner" id="mobile-form-content"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div> <div class="nk-footer">
                    <div class="container-xl">
                        <div class="d-flex align-items-center flex-wrap justify-content-center mx-n3 app-footer-row">
                            <div class="nk-footer-links px-3">
                                <ul class="nav nav-sm app-footer-nav">
                                    <li class="nav-item"><a class="nav-link" href="#" onclick="return false;"><?php e(t('footer_privacy_policy') !== 'footer_privacy_policy' ? t('footer_privacy_policy') : 'Privacy Policy'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#" onclick="return false;"><?php e(t('footer_faq') !== 'footer_faq' ? t('footer_faq') : 'FAQ'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#" onclick="return false;"><?php e(t('footer_contact') !== 'footer_contact' ? t('footer_contact') : 'Contact'); ?></a></li>
                                </ul>
                            </div>
                            <div class="nk-footer-copyright fs-6 px-3"><?php e(t('footer_copyright') !== 'footer_copyright' ? t('footer_copyright') : '© 2023 All Rights Reserved to Copygen.'); ?></div>
                        </div>
                    </div>
                </div> </div> </div> </div> <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel"><?php e(t('form_title') !== 'form_title' ? t('form_title') : 'Formulář'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavřít"></button>
                </div>
                <div class="modal-body p-4" id="modal-form-content"></div>
            </div>
        </div>
    </div>

    <script src="assets/js/bundle.js?v1.1.0"></script>
    <script src="assets/js/scripts.js?v1.1.0"></script>
    <script>
        let formModal;

        document.addEventListener('DOMContentLoaded', function() {
            const formModalEl = document.getElementById('formModal');
            if (typeof bootstrap !== 'undefined' && formModalEl) {
                formModal = new bootstrap.Modal(formModalEl);
                formModalEl.addEventListener('hidden.bs.modal', function() {
                    document.getElementById('modal-form-content').innerHTML = '';
                });
            }
        });

        function syncResponsiveDashboardState() {
            const mobileFormView = document.getElementById('mobile-form-view');
            const formModalEl = document.getElementById('formModal');
            const isMobile = window.innerWidth < 768;

            if (!isMobile && mobileFormView && !mobileFormView.classList.contains('d-none')) {
                closeMobileForm();
            }

            if (isMobile && formModal && formModalEl && formModalEl.classList.contains('show')) {
                formModal.hide();
            }
        }

        window.addEventListener('resize', syncResponsiveDashboardState);

        document.addEventListener('DOMContentLoaded', function() {
            syncResponsiveDashboardState();
        });

        function handleCardClick(id, formTitle) {
            const isMobile = window.innerWidth < 768;
            const dashboardMain = document.getElementById('dashboard-main-view');
            const mobileFormView = document.getElementById('mobile-form-view');
            const targetContainer = isMobile ? document.getElementById('mobile-form-content') : document.getElementById('modal-form-content');

            targetContainer.innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php e(t('loading') !== 'loading' ? t('loading') : 'Načítání...'); ?></span>
                    </div>
                </div>`;

            if (isMobile) {
                document.getElementById('mobile-form-title').innerText = formTitle;
                dashboardMain.classList.add('d-none');
                mobileFormView.classList.remove('d-none');
            } else {
                document.getElementById('formModalLabel').innerText = formTitle;
                if (formModal) formModal.show();
            }

            fetch('get_form.php?id=' + encodeURIComponent(id))
                .then(response => {
                    if (!response.ok) throw new Error('Nepodařilo se načíst obsah. Status: ' + response.status);
                    return response.text();
                })
                .then(html => {
                    targetContainer.innerHTML = html;
                })
                .catch(error => {
                    targetContainer.innerHTML = `
                        <div class="alert alert-danger m-0">
                            <em class="icon ni ni-alert-circle"></em> ${error.message}
                        </div>`;
                });
        }

        function closeMobileForm() {
            document.getElementById('mobile-form-view').classList.add('d-none');
            document.getElementById('dashboard-main-view').classList.remove('d-none');
            document.getElementById('mobile-form-content').innerHTML = '';
        }
    </script>
</body>
</html>
