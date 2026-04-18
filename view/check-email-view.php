<?php
/**
 * -------------------------------------------------
 * View: Check Email
 * -------------------------------------------------
 * Potvrzovaci obrazovka po vytvoreni reset zadosti.
 * Zobrazuje informaci o dalsim kroku
 * a lokalni preview reset odkazu.
 */
?>
<!DOCTYPE html>
<html lang="<?= (($_SESSION['lang_id'] ?? 1) == 3) ? 'en' : 'cs' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">
    <title><?php e(t('check_email_title') !== 'check_email_title' ? t('check_email_title') : 'Check Your Email'); ?> - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
</head>

<body class="nk-body ">
    <div class="nk-app-root ">
        <div class="nk-main">
            <div class="nk-wrap has-shape flex-column">
                <div class="nk-shape bg-shape-blur-a start-0 top-0"></div>
                <div class="nk-shape bg-shape-blur-b end-0 bottom-0"></div>

                <div class="text-center pt-5">
                    <a href="" class="logo-link">
                        <div class="logo-wrap">
                            <img class="logo-img logo-light" src="images/logo.png" srcset="images/logo2x.png 2x" alt="">
                            <img class="logo-img logo-dark" src="images/logo-dark.png" srcset="images/logo-dark2x.png 2x" alt="">
                            <img class="logo-img logo-icon" src="images/logo-icon.png" srcset="images/logo-icon2x.png 2x" alt="">
                        </div>
                    </a>
                </div>

                <div class="container p-2 p-sm-4 mt-auto">
                    <div class="row justify-content-center">
                        <div class="col-md-7 col-lg-4">
                            <div class="nk-block">
                                <div class="mb-5 text-center">
                                    <img src="images/illustrations/envelope-send.svg" alt="" />
                                </div>
                                <div class="nk-block-head text-center">
                                    <div class="nk-block-head-content">
                                        <h1 class="nk-block-title mb-1"><?php e(t('check_email_heading') !== 'check_email_heading' ? t('check_email_heading') : 'Check Your Email'); ?></h1>

                                        <p>
                                            <?php e(t('check_email_description_intro') !== 'check_email_description_intro' ? t('check_email_description_intro') : 'Please check the email address'); ?>
                                            <strong class="fw-bold"><?php e($_SESSION['reset_email'] ?? (t('check_email_desc_fallback') !== 'check_email_desc_fallback' ? t('check_email_desc_fallback') : 'your email')); ?></strong>
                                            <?php e(t('check_email_description_outro') !== 'check_email_description_outro' ? t('check_email_description_outro') : 'for instructions to reset your password.'); ?>
                                        </p>

                                        <?php if (isset($_SESSION['flash_success'])): ?>
                                            <div class="text-success mt-2 fw-medium">
                                                <?php e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($_SESSION['password_reset_preview_url'])): ?>
                                            <div class="alert alert-warning mt-3 text-start">
                                                <strong><?php e(t('local_preview_notice') !== 'local_preview_notice' ? t('local_preview_notice') : 'Lokální náhled odkazu:'); ?></strong><br>
                                                <a href="<?php e($_SESSION['password_reset_preview_url']); ?>"><?php e($_SESSION['password_reset_preview_url']); ?></a>
                                            </div>
                                            <?php unset($_SESSION['password_reset_preview_url']); ?>
                                        <?php endif; ?>

                                    </div>
                                </div>
                                <div class="d-grid mt-4 pt-2">
                                    <a class="btn btn-primary" href="forgot-password.php"><?php e(t('check_email_resend') !== 'check_email_resend' ? t('check_email_resend') : 'Resend Email'); ?></a>
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
                                    <li class="nav-item"><a class="nav-link" href="#"><?php e(t('home_admin') !== 'home_admin' ? t('home_admin') : 'Home'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#"><?php e(t('footer_privacy_policy') !== 'footer_privacy_policy' ? t('footer_privacy_policy') : 'Privacy Policy'); ?></a></li>
                                    <li class="nav-item"><a class="nav-link" href="#"><?php e(t('footer_faq') !== 'footer_faq' ? t('footer_faq') : 'FAQ'); ?></a></li>
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
</body>

</html>
