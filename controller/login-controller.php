<?php
/**
 * -------------------------------------------------
 * Controller: Login
 * -------------------------------------------------
 * Ověřuje login token z URL.
 * Připravuje jazyk, kontext uživatele
 * a přihlašovací view.
 */

class LoginController
{
    private $model;
    private $pdo;

    public function __construct($model, $pdo)
    {
        $this->model = $model;
        $this->pdo = $pdo;
    }

    public function handleRequest()
    {
        $token = get('t', '');

        if (empty($token)) {
            $this->showError('Neplatný nebo chybějící odkaz.');
            return;
        }

        $dbUser = $this->model->getUserByToken($token);

        if (!$dbUser || $dbUser['is_active'] != 1 || $dbUser['login_qr_enabled'] != 1) {
            $this->showError('Tento odkaz není platný, vypršel, nebo je účet zablokován.');
            return;
        }

        $_SESSION['login_return_token'] = $token;
        $_SESSION['lang_id'] = $dbUser['jazyk'] ?? 1;
        loadTranslations($this->pdo, $_SESSION['lang_id'], 'front');

        $email = $dbUser['login_email'];
        $jmeno = $dbUser['jmeno'] ?? '';
        $prijmeni = $dbUser['prijmeni'] ?? '';
        $display_name = trim($jmeno . ' ' . $prijmeni) ?: $email;

        require_once __DIR__ . '/../view/login-view.php';
    }

    private function showError($message)
    {
        require_once __DIR__ . '/../view/error-view.php';
        exit;
    }
}
?>
