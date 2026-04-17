<?php
session_start();

require_once __DIR__ . '/../../../core/helper.php';
require_once __DIR__ . '/../../../core/db.php';

if (!is_post()) {
    json_response(["success" => false, "message" => t('login_method_not_allowed') !== 'login_method_not_allowed' ? t('login_method_not_allowed') : 'Method not allowed.']);
}

$token = post('token', '');
$password = post('password', '');

if (empty($token) || empty($password)) {
    json_response(["success" => false, "message" => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.']);
}

try {
    $stmt = $pdo->prepare('SELECT u.*, p.jmeno, p.prijmeni, p.jazyk FROM alpha_pracovnici_uzivatele u LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id WHERE u.login_qr_token = ?');
    $stmt->execute([$token]);
    $dbUser = $stmt->fetch();

    if (!$dbUser) {
        json_response(["success" => false, "message" => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.']);
    }

    if ($dbUser['is_active'] != 1) {
        json_response(["success" => false, "message" => t('login_account_inactive') !== 'login_account_inactive' ? t('login_account_inactive') : 'This account is not active.']);
    }

    if ($dbUser['login_qr_enabled'] != 1) {
        json_response(["success" => false, "message" => t('login_qr_not_enabled') !== 'login_qr_not_enabled' ? t('login_qr_not_enabled') : 'QR login is not enabled for this account.']);
    }

    if (!empty($dbUser['locked_until']) && strtotime($dbUser['locked_until']) > time()) {
        json_response(["success" => false, "message" => t('login_account_locked') !== 'login_account_locked' ? t('login_account_locked') : 'Account is locked.']);
    }

    if (password_verify($password, $dbUser['password_hash'])) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
        $updateStmt->execute([$ip, $dbUser['id']]);

        $accessToken = bin2hex(random_bytes(32));
        $refreshToken = bin2hex(random_bytes(32));
        $refreshTokenHash = hash('sha256', $refreshToken);

        $sessionStmt = $pdo->prepare('INSERT INTO alpha_pracovnici_uzivatele_sessions (user_id, refresh_token_hash, user_agent, ip_address, expires_at) VALUES (?, ?, ?, ?, NOW() + INTERVAL 30 DAY)');
        $sessionStmt->execute([$dbUser['id'], $refreshTokenHash, $userAgent, $ip]);
        store_login_session($dbUser, (string) $dbUser['login_qr_token'], $refreshTokenHash);

        $jmeno = $dbUser['jmeno'] ?? '';
        $prijmeni = $dbUser['prijmeni'] ?? '';

        $_SESSION['user_name'] = trim($jmeno . ' ' . $prijmeni) ?: (t('default_user_name') !== 'default_user_name' ? t('default_user_name') : 'Uživatel');
        $_SESSION['user_id'] = $dbUser['id'];
        $_SESSION['lang_id'] = $dbUser['jazyk'] ?? 1;

        json_response([
            "success" => true,
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken
        ]);
    } else {
        $attempts = (int) $dbUser['failed_login_attempts'] + 1;

        if ($attempts >= 5) {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ?, locked_until = NOW() + INTERVAL 15 MINUTE WHERE id = ?');
        } else {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ? WHERE id = ?');
        }
        $updateStmt->execute([$attempts, $dbUser['id']]);

        json_response(["success" => false, "message" => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.']);
    }
} catch (Exception $e) {
    json_response(["success" => false, "message" => t('login_server_error') !== 'login_server_error' ? t('login_server_error') : 'Internal server error.']);
}
?>
