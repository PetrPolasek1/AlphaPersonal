<?php
// controller/ForgotPasswordController.php

class ForgotPasswordController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest() {
        if (is_post()) {
            $email = post('email');

            // Zavoláme model pro kontrolu a vytvoření tokenu
            $token = $this->model->createPasswordResetToken($email);

            if ($token) {
                // Tady by v budoucnu bylo odeslání e-mailu přes PHPMailer
                // Zatím jen zalogujeme pro tvou kontrolu
                app_log("SIMULACE: Odeslán e-mail pro $email s tokenem $token");
                
                $_SESSION['reset_email'] = $email;
                $_SESSION['flash_success'] = "Odkaz pro obnovu hesla byl úspěšně odeslán na váš e-mail.";
                redirect('check-email.php');
            } else {
                // Pokud uživatel neexistuje, vypíšeme chybu (nebo obecnou hlášku)
                $_SESSION['auth_error'] = "Uživatel s tímto e-mailem nebyl nalezen.";
                require_once __DIR__ . '/../view/forgot-password-view.php';
            }
            return;
        }

        require_once __DIR__ . '/../view/forgot-password-view.php';
    }
}