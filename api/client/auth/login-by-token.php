<?php
/**
 * -------------------------------------------------
 * API Endpoint: Login By Token
 * -------------------------------------------------
 * Dokoncuje prihlaseni klienta pres login token
 * a heslo. Vytvari databazovou session
 * a uklada server-side auth kontext.
 */
session_start();

require_once __DIR__ . '/../../../core/helper.php';
require_once __DIR__ . '/../../../core/db.php';

if (!is_post()) {
    json_response([
        'success' => false,
        'message' => t('login_method_not_allowed') !== 'login_method_not_allowed' ? t('login_method_not_allowed') : 'Method not allowed.'
    ], 405);
}

require_csrf();

$token = trim((string) post('token', ''));
$password = (string) post('password', '');

if ($token === '' || $password === '') {
    json_response([
        'success' => false,
        'message' => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.'
    ], 422);
}

try {
    $tokenHash = hash_token($token);
    $stmt = $pdo->prepare(
        'SELECT u.*, p.jmeno, p.prijmeni, p.jazyk
         FROM alpha_pracovnici_uzivatele u
         LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id
         WHERE u.login_qr_token IN (?, ?)
         LIMIT 1'
    );
    $stmt->execute([$tokenHash, $token]);
    $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dbUser) {
        json_response([
            'success' => false,
            'message' => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.'
        ], 401);
    }

    if ((int) ($dbUser['is_active'] ?? 0) !== 1) {
        json_response([
            'success' => false,
            'message' => t('login_account_inactive') !== 'login_account_inactive' ? t('login_account_inactive') : 'This account is not active.'
        ], 403);
    }

    if ((int) ($dbUser['login_qr_enabled'] ?? 0) !== 1) {
        json_response([
            'success' => false,
            'message' => t('login_qr_not_enabled') !== 'login_qr_not_enabled' ? t('login_qr_not_enabled') : 'QR login is not enabled for this account.'
        ], 403);
    }

    if (!empty($dbUser['locked_until']) && strtotime((string) $dbUser['locked_until']) > time()) {
        json_response([
            'success' => false,
            'message' => t('login_account_locked') !== 'login_account_locked' ? t('login_account_locked') : 'Account is locked.'
        ], 423);
    }

    if (!password_verify($password, (string) ($dbUser['password_hash'] ?? ''))) {
        $attempts = (int) ($dbUser['failed_login_attempts'] ?? 0) + 1;

        if ($attempts >= 5) {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ?, locked_until = NOW() + INTERVAL 15 MINUTE WHERE id = ?');
        } else {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ? WHERE id = ?');
        }

        $updateStmt->execute([$attempts, $dbUser['id']]);

        json_response([
            'success' => false,
            'message' => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.'
        ], 401);
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $sessionSecret = generate_secure_token();
    $sessionHash = hash_token($sessionSecret);

    $updateStmt = $pdo->prepare(
        'UPDATE alpha_pracovnici_uzivatele
         SET failed_login_attempts = 0,
             locked_until = NULL,
             last_login_at = NOW(),
             last_login_ip = ?
         WHERE id = ?'
    );
    $updateStmt->execute([$ip, $dbUser['id']]);

    $sessionStmt = $pdo->prepare(
        'INSERT INTO alpha_pracovnici_uzivatele_sessions (user_id, refresh_token_hash, user_agent, ip_address, expires_at)
         VALUES (?, ?, ?, ?, NOW() + INTERVAL 30 DAY)'
    );
    $sessionStmt->execute([$dbUser['id'], $sessionHash, $userAgent, $ip]);

    store_login_session($dbUser, (string) ($dbUser['login_qr_token'] ?? ''), $sessionHash);

    $_SESSION['user_name'] = trim((string) ($dbUser['jmeno'] ?? '') . ' ' . (string) ($dbUser['prijmeni'] ?? ''))
        ?: (t('default_user_name') !== 'default_user_name' ? t('default_user_name') : 'Uživatel');
    $_SESSION['user_id'] = (int) $dbUser['id'];
    $_SESSION['lang_id'] = (int) ($dbUser['jazyk'] ?? 1);

    json_response(['success' => true]);
} catch (Throwable $e) {
    app_log('Token login failed: ' . $e->getMessage());
    json_response([
        'success' => false,
        'message' => t('login_server_error') !== 'login_server_error' ? t('login_server_error') : 'Internal server error.'
    ], 500);
}
