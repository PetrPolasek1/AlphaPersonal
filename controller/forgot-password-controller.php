<?php
/**
 * -------------------------------------------------
 * Controller: Forgot Password
 * -------------------------------------------------
 * Zpracovava zadosti o reset hesla.
 * Validuje vstup a spousti vytvoreni
 * jednorazoveho reset tokenu.
 */

class ForgotPasswordController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest() {
        if (is_post()) {
            require_csrf();

            $email = normalize_email(post('email'));
            $token = null;

            if (is_valid_email($email)) {
                $lastRequestAt = (int) ($_SESSION['password_reset_last_request_at'] ?? 0);

                if ($lastRequestAt === 0 || (time() - $lastRequestAt) >= 60) {
                    $token = $this->model->createPasswordResetToken($email);
                    $_SESSION['password_reset_last_request_at'] = time();
                }
            }

            $_SESSION['reset_email'] = $email;
            unset($_SESSION['password_reset_preview_url']);

            if ($token && is_local_request()) {
                $_SESSION['password_reset_preview_url'] = build_password_reset_url($token);
            }

            $_SESSION['flash_success'] = t('password_reset_link_sent') !== 'password_reset_link_sent'
                ? t('password_reset_link_sent')
                : 'Pokud pro tento e-mail existuje účet, odeslali jsme odkaz pro obnovu hesla.';

            redirect('check-email.php');
            return;
        }

        unset($_SESSION['auth_error']);
        require_once __DIR__ . '/../view/forgot-password-view.php';
    }
}
