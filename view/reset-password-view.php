<?php
/**
 * -------------------------------------------------
 * View: Reset Password
 * -------------------------------------------------
 * Renderuje formular pro nastaveni noveho hesla
 * po overeni reset tokenu.
 */
?>
<!DOCTYPE html>
<html lang="<?= (($_SESSION['lang_id'] ?? 1) == 3) ? 'en' : 'cs' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">
    <title><?php e(t('reset_password_title') !== 'reset_password_title' ? t('reset_password_title') : 'Nastavení nového hesla'); ?> - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
    <style>
        .app-footer-row {
            gap: 0.5rem 1rem;
        }

        .app-footer-nav {
            justify-content: center;
            gap: 0.125rem 0.25rem;
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
    <div class="nk-app-root ">
        <div class="nk-main">
            <div class="nk-wrap has-shape flex-column">
                <div class="nk-shape bg-shape-blur-a start-0 top-0"></div>
                <div class="nk-shape bg-shape-blur-b end-0 bottom-0"></div>

                <div class="text-center pt-5">
                    <a href="login.php" class="logo-link">
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
                                        <h1 class="nk-block-title mb-1"><?php e(t('reset_password_heading') !== 'reset_password_heading' ? t('reset_password_heading') : 'Zvolte nové heslo'); ?></h1>
                                        <p class="small"><?php e(t('reset_password_description') !== 'reset_password_description' ? t('reset_password_description') : 'Zadejte nové heslo, které chcete pro svůj účet používat.'); ?></p>
                                    </div>
                                </div>

                                <?php if (!empty($errorMsg)): ?>
                                    <div class="alert alert-danger mb-3"><?php e($errorMsg); ?></div>
                                <?php endif; ?>

                                <?php if ($resetRequest): ?>
                                    <form action="reset-password.php" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="token" value="<?php e($token); ?>">

                                        <div class="row gy-3">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="form-label" for="password"><?php e(t('new_password') !== 'new_password' ? t('new_password') : 'Nové heslo'); ?></label>
                                                    <div class="form-control-wrap">
                                                        <input class="form-control" type="password" name="password" id="password" required minlength="6">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="form-label" for="confirm_password"><?php e(t('confirm_password') !== 'confirm_password' ? t('confirm_password') : 'Potvrdit nové heslo'); ?></label>
                                                    <div class="form-control-wrap">
                                                        <input class="form-control" type="password" name="confirm_password" id="confirm_password" required minlength="6">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button class="btn btn-primary" type="submit"><?php e(t('save_password') !== 'save_password' ? t('save_password') : 'Uložit nové heslo'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="text-center">
                                        <a class="btn btn-outline-primary" href="forgot-password.php"><?php e(t('forgot_password_send_link') !== 'forgot_password_send_link' ? t('forgot_password_send_link') : 'Poslat nový odkaz'); ?></a>
                                    </div>
                                <?php endif; ?>

                                <div class="text-center mt-3">
                                    <a href="login.php"><?php e(t('return_to_login') !== 'return_to_login' ? t('return_to_login') : 'Return to Login'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="nk-footer">
                    <div class="container-xl">
                        <div class="d-flex align-items-center flex-wrap justify-content-between mx-n3 app-footer-row">
                            <div class="nk-footer-links px-3">
                                <ul class="nav nav-sm app-footer-nav">
                                    <li class="nav-item"><a class="nav-link" href="#" onclick="return false;"><?php e(t('footer_privacy_policy') !== 'footer_privacy_policy' ? t('footer_privacy_policy') : 'Privacy Policy'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#" onclick="return false;"><?php e(t('footer_faq') !== 'footer_faq' ? t('footer_faq') : 'FAQ'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#" onclick="return false;"><?php e(t('footer_contact') !== 'footer_contact' ? t('footer_contact') : 'Contact'); ?></a></li>
                                </ul>
                            </div>
                            <div class="nk-footer-copyright fs-6 px-3"><?php e(t('footer_copyright_short') !== 'footer_copyright_short' ? t('footer_copyright_short') : '© 2023 Copygen.'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/bundle.js?v1.1.0"></script>
    <script src="assets/js/scripts.js?v1.1.0"></script>
</body>
</html>
