<?php
// model/AuthModel.php

class AuthModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createPasswordResetToken($email) {
        // KROK 1: Kontrola, zda e-mail v databázi skutečně existuje
        $stmt = $this->pdo->prepare("SELECT id FROM alpha_pracovnici_uzivatele WHERE login_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return false; // Uživatel neexistuje
        }

        // KROK 2: Pokud existuje, vygenerujeme token
        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expiresAt]);

        return $token;
    }
}