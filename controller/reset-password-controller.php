<?php
/**
 * -------------------------------------------------
 * Controller: Reset Password
 * -------------------------------------------------
 * Dokoncovaci vrstva reset flow.
 * Ověřuje reset token a provádí finální
 * změnu hesla uživatele.
 */

class ResetPasswordController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest() {
        $token = trim((string) get('token', ''));
        $errorMsg = '';

        if (is_post()) {
            require_csrf();

            $token = trim((string) post('token', ''));
            $newPassword = (string) post('password', '');
            $confirmPassword = (string) post('confirm_password', '');

            if ($token === '') {
                $errorMsg = t('password_reset_invalid_token') !== 'password_reset_invalid_token'
                    ? t('password_reset_invalid_token')
                    : 'Resetovací odkaz je neplatný nebo už vypršel.';
            } elseif ($newPassword !== $confirmPassword) {
                $errorMsg = t('err_password_match') !== 'err_password_match'
                    ? t('err_password_match')
                    : 'Nová hesla se neshodují.';
            } elseif (strlen($newPassword) < 6) {
                $errorMsg = t('err_password_length') !== 'err_password_length'
                    ? t('err_password_length')
                    : 'Nové heslo musí mít alespoň 6 znaků.';
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

                if ($this->model->resetPasswordByToken($token, $passwordHash)) {
                    $_SESSION['flash_success'] = t('password_reset_success') !== 'password_reset_success'
                        ? t('password_reset_success')
                        : 'Heslo bylo úspěšně změněno. Nyní se můžete přihlásit.';

                    $loginReturnToken = trim((string) ($_SESSION['login_return_token'] ?? ''));
                    $redirectUrl = $loginReturnToken !== ''
                        ? 'login.php?t=' . rawurlencode($loginReturnToken)
                        : 'forgot-password.php';

                    redirect($redirectUrl);
                    return;
                }

                $errorMsg = t('password_reset_invalid_token') !== 'password_reset_invalid_token'
                    ? t('password_reset_invalid_token')
                    : 'Resetovací odkaz je neplatný nebo už vypršel.';
            }
        }

        $resetRequest = $token !== '' ? $this->model->findValidPasswordResetByToken($token) : null;

        if ($token !== '' && !$resetRequest && $errorMsg === '') {
            $errorMsg = t('password_reset_invalid_token') !== 'password_reset_invalid_token'
                ? t('password_reset_invalid_token')
                : 'Resetovací odkaz je neplatný nebo už vypršel.';
        }

        require_once __DIR__ . '/../view/reset-password-view.php';
    }
}
