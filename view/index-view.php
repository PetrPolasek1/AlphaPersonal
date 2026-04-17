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
    </style>
</head>

<body class="nk-body ">
    <div class="nk-app-root " data-sidebar-collapse="lg">
        <div class="nk-main">

            <?php include __DIR__ . '/../Core/sidebar.php'; ?>

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
                        <div class="d-flex align-items-center flex-wrap justify-content-between mx-n3">
                            <div class="nk-footer-links px-3">
                                <ul class="nav nav-sm">
                                    <li class="nav-item"><a class="nav-link" href="#"><?php e(t('home_admin') !== 'home_admin' ? t('home_admin') : 'Home'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#"><?php e(t('footer_pricing') !== 'footer_pricing' ? t('footer_pricing') : 'Pricing'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#"><?php e(t('footer_privacy_policy') !== 'footer_privacy_policy' ? t('footer_privacy_policy') : 'Privacy Policy'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#"><?php e(t('footer_faq') !== 'footer_faq' ? t('footer_faq') : 'FAQ'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#"><?php e(t('footer_contact') !== 'footer_contact' ? t('footer_contact') : 'Contact'); ?></a></li>
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
