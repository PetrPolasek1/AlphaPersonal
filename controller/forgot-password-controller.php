<?php
// controller/ForgotPasswordController.php

class ForgotPasswordController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest() {
        // Pokud byl formulář odeslán
        if (is_post()) {
            $email = post('email');

            // Validace e-mailu
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $token = $this->model->createPasswordResetToken($email);

                if ($token) {
                    // Tady by proběhlo reálné odeslání e-mailu
                    $resetLink = "https://tvoje-domena.cz/reset-password.php?token=" . $token;
                    $subject = "Obnova hesla - CopyGen";
                    $message = "Pro obnovu hesla klikněte zde: \n" . $resetLink;
                    $headers = "From: no-reply@tvoje-domena.cz\r\n";
                    
                    mail($email, $subject, $message, $headers);
                    app_log("Odeslán e-mail pro obnovu hesla na: $email");
                }
            }

            // Uložíme e-mail do session, abychom ho mohli vypsat na další stránce
            $_SESSION['reset_email'] = $email;
            
            // Z bezpečnostních důvodů vždy říkáme, že se to povedlo (aby útočník nehádal e-maily)
            $_SESSION['flash_success'] = "Pokud je e-mail zaregistrován, odeslali jsme instrukce.";
            redirect('check-email.php');
            return;
        }

        // Pokud to není POST, jen zobrazíme stránku (View)
        require_once __DIR__ . '/../view/forgot-password-view.php';
    }
}
?>