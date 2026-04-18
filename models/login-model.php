<?php
/**
 * -------------------------------------------------
 * Model: Login
 * -------------------------------------------------
 * Datova vrstva prihlaseni.
 * Vyhledava uzivatele podle login tokenu
 * a umi token po prihlaseni rotovat.
 */

class LoginModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserByToken($token) {
        $token = trim((string) $token);

        if ($token === '') {
            return false;
        }

        $tokenHash = hash_token($token);
        $stmt = $this->pdo->prepare('
            SELECT u.*, p.jmeno, p.prijmeni, p.jazyk
            FROM alpha_pracovnici_uzivatele u
            LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id
            WHERE u.login_qr_token IN (?, ?)
            LIMIT 1
        ');
        $stmt->execute([$tokenHash, $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rotateUserLoginToken(int $userId): string
    {
        $newToken = generate_secure_token();
        $newTokenHash = hash_token($newToken);

        $stmt = $this->pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET login_qr_token = ? WHERE id = ?');
        $stmt->execute([$newTokenHash, $userId]);

        return $newToken;
    }
}
