<?php
// controller/ForgotPasswordController.php

class ForgotPasswordController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest() {
        if (is_post()) {
            require_csrf();
            $email = post('email');

            $token = $this->model->createPasswordResetToken($email);

            if ($token) {
                app_log("SIMULACE: Odeslan e-mail pro $email s tokenem $token");

                $_SESSION['reset_email'] = $email;
                $_SESSION['flash_success'] = t('password_reset_link_sent') !== 'password_reset_link_sent' ? t('password_reset_link_sent') : 'Odkaz pro obnovu hesla byl úspěšně odeslán na váš e-mail.';
                redirect('check-email.php');
            } else {
                $_SESSION['auth_error'] = t('password_reset_user_not_found') !== 'password_reset_user_not_found' ? t('password_reset_user_not_found') : 'Uživatel s tímto e-mailem nebyl nalezen.';
                require_once __DIR__ . '/../view/forgot-password-view.php';
            }
            return;
        }

        require_once __DIR__ . '/../view/forgot-password-view.php';
    }
}
