<?php
/**
 * -------------------------------------------------
 * Model: Authentication / Password Reset
 * -------------------------------------------------
 * Datova vrstva resetu hesla.
 * Vytvari hashovane reset tokeny
 * a meni heslo po jejich overeni.
 */

class AuthModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createPasswordResetToken($email) {
        $email = normalize_email($email);

        if (!is_valid_email($email)) {
            return null;
        }

        $stmt = $this->pdo->prepare("SELECT id FROM alpha_pracovnici_uzivatele WHERE login_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        $token = generate_secure_token();
        $tokenHash = hash_token($token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $tokenHash, $expiresAt]);

        return $token;
    }

    public function findValidPasswordResetByToken(string $token): ?array
    {
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        $tokenHash = hash_token($token);
        $stmt = $this->pdo->prepare(
            "SELECT id, email, expires_at
             FROM password_resets
             WHERE expires_at > NOW()
               AND token IN (?, ?)
             ORDER BY id DESC
             LIMIT 1"
        );
        $stmt->execute([$tokenHash, $token]);
        $resetRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resetRow ?: null;
    }

    public function resetPasswordByToken(string $token, string $passwordHash): bool
    {
        $resetRow = $this->findValidPasswordResetByToken($token);

        if (!$resetRow) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $updateStmt = $this->pdo->prepare(
                "UPDATE alpha_pracovnici_uzivatele
                 SET password_hash = ?, failed_login_attempts = 0, locked_until = NULL
                 WHERE login_email = ?"
            );
            $updateStmt->execute([$passwordHash, $resetRow['email']]);

            if ($updateStmt->rowCount() < 1) {
                $this->pdo->rollBack();
                return false;
            }

            $deleteStmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $deleteStmt->execute([$resetRow['email']]);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            app_log('Password reset failed: ' . $e->getMessage());
            return false;
        }
    }
}
