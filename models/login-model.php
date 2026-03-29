<?php
// models/login-model.php

class LoginModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserByToken($token) {
        $stmt = $this->pdo->prepare('
            SELECT u.*, p.jmeno, p.prijmeni, p.jazyk 
            FROM alpha_pracovnici_uzivatele u 
            LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id 
            WHERE u.login_qr_token = ?
        ');
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
}
?>