<?php
/**
 * -------------------------------------------------
 * View: Forgot Password
 * -------------------------------------------------
 * Renderuje formular pro zadost o reset hesla
 * a navigaci zpet do prihlaseni.
 */
?>
<!DOCTYPE html>
<html lang="<?= (($_SESSION['lang_id'] ?? 1) == 3) ? 'en' : 'cs' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">
    <title><?php e(t('forgot_password_title') !== 'forgot_password_title' ? t('forgot_password_title') : 'Forgot Password'); ?> - CopyGen</title>
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
                    <a href="index.php" class="logo-link">
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
                                        <h1 class="nk-block-title mb-1"><?php e(t('forgot_password_heading') !== 'forgot_password_heading' ? t('forgot_password_heading') : 'Reset Your Password'); ?></h1>
                                        <p class="small"><?php e(t('forgot_password_description') !== 'forgot_password_description' ? t('forgot_password_description') : 'Enter your email address and we will send you instructions to reset your password.'); ?></p>
                                    </div>
                                </div>

                                <?php if (isset($_SESSION['auth_error'])): ?>
                                    <div class="alert alert-danger mb-3">
                                        <?php e($_SESSION['auth_error']); unset($_SESSION['auth_error']); ?>
                                    </div>
                                <?php endif; ?>

                                <form action="forgot-password.php" method="POST">
                                    <?= csrf_field() ?>
                                    <div class="row gy-3">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label" for="email"><?php e(t('forgot_password_email_label') !== 'forgot_password_email_label' ? t('forgot_password_email_label') : 'Email Address'); ?></label>
                                                <div class="form-control-wrap">
                                                    <input class="form-control" type="email" name="email" id="email" placeholder="<?php e(t('forgot_password_email_ph') !== 'forgot_password_email_ph' ? t('forgot_password_email_ph') : 'Enter email address'); ?>" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button class="btn btn-primary" type="submit"><?php e(t('forgot_password_send_link') !== 'forgot_password_send_link' ? t('forgot_password_send_link') : 'Send Link'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <div class="text-center mt-3">
                                    <p class="small">
                                        <?php
                                        $loginReturnToken = trim((string) ($_SESSION['login_return_token'] ?? ''));
                                        $loginReturnUrl = $loginReturnToken !== ''
                                            ? 'login.php?t=' . rawurlencode($loginReturnToken)
                                            : 'login.php';
                                        ?>
                                        <a href="<?php e($loginReturnUrl); ?>"><?php e(t('return_to_login') !== 'return_to_login' ? t('return_to_login') : 'Return to Login'); ?></a>
                                    </p>
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
