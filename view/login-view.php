<?php
/**
 * -------------------------------------------------
 * View: Login
 * -------------------------------------------------
 * Renderuje prihlasovaci obrazovku klienta.
 * Obsahuje formular s heslem a AJAX flow
 * pro dokonceni prihlaseni.
 */
?>
<!DOCTYPE html>
<html lang="<?= (($_SESSION['lang_id'] ?? 1) == 3) ? 'en' : 'cs' ?>">
<head>
    <meta charset="UTF-8">
    <base href="/portal/dist/">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">

    <title><?php e(t('mess_loggin_title')); ?> - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
    <style>
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
            <div class="nk-wrap has-shape flex-column">
                <!-- <div class="nk-shape bg-shape-blur-a start-0 top-0"></div> -->
                <!-- <div class="nk-shape bg-shape-blur-b end-0 bottom-0"></div> -->
                <div class="text-center pt-5">
                    <a href="javascript:void(0)" class="logo-link">
                        <div class="logo-wrap">
                            <img class="logo-img logo-light" src="images/logo.png" srcset="images/logo2x.png 2x" alt="">
                            <img class="logo-img logo-dark" src="images/logo-dark.png" srcset="images/logo-dark2x.png 2x" alt="">
                            <img class="logo-img logo-icon" src="images/logo-icon.png" srcset="images/logo-icon2x.png 2x" alt="">
                        </div>
                    </a>
                </div>
                <div class="container p-2 p-sm-4 mt-auto">
                    <div class="row justify-content-center">
                        <div class="col-md-7 col-lg-5 col-xl-5 col-xxl-4">
                            <div class="nk-block">
                                <div class="nk-block-head text-center mb-4 pb-2">
                                    <div class="nk-block-head-content">
                                        <h1 class="nk-block-title mb-1"><?php e(t('mess_loggin_title')); ?></h1>

                                        <p class="small"><?php e(t('loggin_desc') !== 'loggin_desc' ? t('loggin_desc') : 'Sign in to your account to customize your content generation settings and view your history.'); ?></p>
                                    </div>
                                </div>

                                <div id="loginError" class="alert alert-danger d-none mb-3"></div>

                                <?php if (isset($_SESSION['flash_success'])): ?>
                                    <div class="alert alert-success mb-3">
                                        <?php e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="alert alert-info mb-4 text-center">
                                    <?php e(t('loggin_as') !== 'loggin_as' ? t('loggin_as') : 'Přihlašujete se jako:'); ?><br><strong><?php e($display_name); ?></strong>
                                </div>

                                <form id="loginForm" action="api/client/auth/login-by-token.php" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="token" id="token" value="<?php e($token); ?>">
                                    <input type="hidden" name="email" value="<?php e($email); ?>">

                                    <div class="row gy-3">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label" for="password"><?php e(t('loggin_pass')); ?></label>
                                                <div class="form-control-wrap">
                                                    <a href="password" class="password-toggle form-control-icon end" title="<?php e(t('password_toggle_title') !== 'password_toggle_title' ? t('password_toggle_title') : 'Toggle show/hide password'); ?>">
                                                        <em class="icon ni ni-eye inactive"></em>
                                                        <em class="icon ni ni-eye-off active"></em>
                                                    </a>
                                                    <input class="form-control" type="password" id="password" name="password" placeholder="<?php e(t('loggin_pass')); ?>" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <a class="link small" href="forgot-password.php"><?php e(t('forgot_password') !== 'forgot_password' ? t('forgot_password') : 'Forgot password?'); ?></a>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button class="btn btn-primary" type="submit"><?php e(t('btn_loggin')); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="nk-footer">
                    <div class="container-xl">
                        <div class="d-flex align-items-center flex-wrap justify-content-center mx-n3 app-footer-row">
                            <div class="nk-footer-links px-3">
                                <ul class="nav nav-sm app-footer-nav">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" onclick="return false;"><?php e(t('footer_privacy_policy') !== 'footer_privacy_policy' ? t('footer_privacy_policy') : 'Privacy Policy'); ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" onclick="return false;"><?php e(t('footer_faq') !== 'footer_faq' ? t('footer_faq') : 'FAQ'); ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" onclick="return false;"><?php e(t('footer_contact') !== 'footer_contact' ? t('footer_contact') : 'Contact'); ?></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="nk-footer-copyright fs-6 px-3"><?php e(t('footer_copyright') !== 'footer_copyright' ? t('footer_copyright') : '© 2023 All Rights Reserved to Copygen.'); ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="assets/js/bundle.js?v1.1.0"></script>
    <script src="assets/js/scripts.js?v1.1.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const errorDiv = document.getElementById('loginError');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    errorDiv.classList.add('d-none');

                    fetch(form.action, {
                        method: form.method,
                        body: new FormData(form)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'index.php';
                        } else {
                            errorDiv.textContent = data.message || '<?php e(t("login_server_error") !== "login_server_error" ? t("login_server_error") : "Chyba přihlášení."); ?>';
                            errorDiv.classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        errorDiv.textContent = '<?php e(t("error_server_com") !== "error_server_com" ? t("error_server_com") : "Došlo k chybě při komunikaci se serverem."); ?>';
                        errorDiv.classList.remove('d-none');
                    });
                });
            }
        });
    </script>
</body>
</html>
