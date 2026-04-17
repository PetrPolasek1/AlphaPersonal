<!DOCTYPE html>
<html lang="<?= (($_SESSION['lang_id'] ?? 1) == 3) ? 'en' : 'cs' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">
    <title><?php e(t('forgot_password_title') !== 'forgot_password_title' ? t('forgot_password_title') : 'Forgot Password'); ?> - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
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

                                <?php
                                $urlToken = isset($_GET['token']) ? htmlspecialchars($_GET['token']) : '';
                                $formAction = "forgot-password.php";
                                if (!empty($urlToken)) {
                                    $formAction .= "?token=" . $urlToken;
                                }
                                ?>

                                <form action="<?php echo $formAction; ?>" method="POST">
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
                                        if (!empty($urlToken)) {
                                            $loginUrl = "/portal/client/login/u/" . $urlToken;
                                        } else {
                                            $loginUrl = "/portal/client/login";
                                        }
                                        ?>
                                        <a href="<?php echo $loginUrl; ?>"><?php e(t('return_to_login') !== 'return_to_login' ? t('return_to_login') : 'Return to Login'); ?></a>
                                    </p>
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
