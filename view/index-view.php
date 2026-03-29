<!DOCTYPE html>
<html lang="en">

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
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }
        .dashboard-fullscreen-grid .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center; 
        }
        .is-pointer { cursor: pointer; }
        .is-pointer:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
    </style>
</head>

<body class="nk-body ">
    <div class="nk-app-root " data-sidebar-collapse="lg">
        <div class="nk-main">
            <div class="nk-sidebar nk-sidebar-fixed" id="sidebar">
                <div class="nk-compact-toggle">
                    <button class="btn btn-xs btn-outline-light btn-icon compact-toggle text-light bg-white rounded-3">
                        <em class="icon off ni ni-chevron-left"></em>
                        <em class="icon on ni ni-chevron-right"></em>
                    </button>
                </div>
                <div class="nk-sidebar-element nk-sidebar-head">
                    <div class="nk-sidebar-brand">
                        <a href="index.php" class="logo-link">
                            <div class="logo-wrap">
                                <img class="logo-img logo-light" src="images/logo.png" srcset="images/logo2x.png 2x" alt="">
                                <img class="logo-img logo-dark" src="images/logo-dark.png" srcset="images/logo-dark2x.png 2x" alt="">
                                <img class="logo-img logo-icon" src="images/logo-icon.png" srcset="images/logo-icon2x.png 2x" alt="">
                            </div>
                        </a>
                    </div>
                </div>
                <div class="nk-sidebar-element nk-sidebar-body">
                    <div class="nk-sidebar-content h-100" data-simplebar>
                        <div class="nk-sidebar-menu">
                            <ul class="nk-menu">
                                <li class="nk-menu-item">
                                    <a href="index.php" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-dashboard-fill"></em></span>
                                        <span class="nk-menu-text"><?php e(t('dashboard') !== 'dashboard' ? t('dashboard') : 'Dashboard'); ?></span>
                                    </a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="message.php" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-chat-fill"></em></span>
                                        <span class="nk-menu-text"><?php e(t('messages_menu') !== 'messages_menu' ? t('messages_menu') : 'Zprávy'); ?></span>
                                    </a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="pozadavky.html" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span>
                                        <span class="nk-menu-text"><?php e(t('requests_menu') !== 'requests_menu' ? t('requests_menu') : 'Požadavky zaměstnanců'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="nk-sidebar-element nk-sidebar-footer">
                    <div class="nk-sidebar-footer-extended pt-3">
                        <div class="border border-light rounded-3">
                            <a class="d-flex px-3 py-2 bg-primary bg-opacity-10 rounded-bottom-3" href="#">
                                <div class="media-group">
                                    <div class="media media-sm media-middle media-circle text-bg-primary">
                                        <img src="images/avatar/a.png" />
                                    </div>
                                    <div class="media-text">
                                        <h6 class="fs-6 mb-0"><?php e($fullName); ?></h6>
                                    </div>
                                    <em class="icon ni ni-chevron-right ms-auto ps-1"></em>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="nk-wrap">
                <div class="nk-header nk-header-fixed">
                    <div class="container-fluid">
                        <div class="nk-header-wrap">
                            <div class="nk-header-logo ms-n1">
                                <div class="nk-sidebar-toggle me-1">
                                    <button class="btn btn-sm btn-zoom btn-icon sidebar-toggle d-sm-none"><em class="icon ni ni-menu"> </em></button>
                                    <button class="btn btn-md btn-zoom btn-icon sidebar-toggle d-none d-sm-inline-flex"><em class="icon ni ni-menu"> </em></button>
                                </div>
                            </div>
                            <div class="nk-header-tools">
                                <ul class="nk-quick-nav ms-2">
                                    <li class="dropdown d-inline-flex">
                                        <a data-bs-toggle="dropdown" class="d-inline-flex" href="#">
                                            <div class="media media-md media-circle media-middle text-bg-primary">
                                                <img src="images/avatar/a.png" />
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-md rounded-3">
                                            <div class="dropdown-content py-3">
                                                <div class="border border-light rounded-3">
                                                    <a class="d-flex px-3 py-2 bg-primary bg-opacity-10 rounded-bottom-3" href="#">
                                                        <div class="media-group">
                                                            <div class="media media-sm media-middle media-circle text-bg-primary"><img src="images/avatar/a.png" /></div>
                                                            <div class="media-text"><h6 class="fs-6 mb-0"><?php e($fullName); ?></h6></div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="nk-content">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                <div class="nk-block-head nk-page-head">
                                    <div class="nk-block-head-between">
                                        <div class="nk-block-head-content">
                                            <h2 class="display-6"><?php e(t('welcome_user') !== 'welcome_user' ? t('welcome_user') : 'Welcome'); ?> <?php e($firstName); ?>!</h2>
                                            <p><?php e(t('select_module_desc') !== 'select_module_desc' ? t('select_module_desc') : 'Vyberte modul, se kterým chcete pracovat.'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div id="dashboard-main-view">
                                    <div class="dashboard-fullscreen-grid" style="--grid-cols: <?php e($gridCols); ?>; --grid-rows: <?php e($gridRows); ?>;">
                                        <?php foreach ($modules as $row): ?>
                                            <div class="card card-full <?php e($row['color']); ?> bg-opacity-10 border-0 is-pointer" onclick="handleCardClick('<?php e($row['form_id']); ?>')">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                                        <div class="fs-6 text-light mb-0"><?php e(t('module_label') !== 'module_label' ? t('module_label') : 'Modul'); ?></div>
                                                        <em class="icon ni <?php e($row['icon']); ?> fs-2 text-dark bg-white rounded-circle p-3 shadow-sm"></em>
                                                    </div>
                                                    <h5 class="fs-2 mb-0"><?php 
                                                        $modKey = 'mod_' . $row['form_id']; 
                                                        e(t($modKey) !== $modKey ? t($modKey) : $row['title']); 
                                                    ?></h5>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
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
                </div>
                
                <div class="nk-footer">
                    <div class="container-xl">
                        <div class="d-flex align-items-center flex-wrap justify-content-between mx-n3">
                            <div class="nk-footer-links px-3">
                                <ul class="nav nav-sm">
                                    <li class="nav-item">
                                        <a class="nav-link" href="/#"><?php e(t('home_admin') !== 'home_admin' ? t('home_admin') : 'Home'); ?></a>
                                    </li>
                                    <li class="nav-item"><a class="nav-link" href="/#">Pricing</a></li>
                                    <li class="nav-item"><a class="nav-link" href="/#">Privacy Policy</a></li>
                                    <li class="nav-item"><a class="nav-link" href="/#">FAQ</a></li>
                                    <li class="nav-item"><a class="nav-link" href="/#">Contact</a></li>
                                </ul>
                            </div>
                            <div class="nk-footer-copyright fs-6 px-3"> &copy; 2023 All Rights Reserved to <a href="#">Copygen</a>. </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel"><?php e(t('form_title') !== 'form_title' ? t('form_title') : 'Formulář'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavřít"></button>
                </div>
                <div class="modal-body p-4" id="modal-form-content">
                    </div>
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
                formModalEl.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('modal-form-content').innerHTML = '';
                });
            }
        });

        function handleCardClick(id) {
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
                document.getElementById('mobile-form-title').innerText = '<?php e(t('form_title') !== 'form_title' ? t('form_title') : 'Formulář'); ?>';
                dashboardMain.classList.add('d-none');
                mobileFormView.classList.remove('d-none');
            } else {
                document.getElementById('formModalLabel').innerText = '<?php e(t('form_title') !== 'form_title' ? t('form_title') : 'Formulář'); ?>';
                if (formModal) formModal.show();
            }

            fetch('get_form.php?id=' + encodeURIComponent(id))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Nepodařilo se načíst obsah. Status: ' + response.status);
                    }
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