<?php
/**
 * -------------------------------------------------
 * API Endpoint: Login By Credentials
 * -------------------------------------------------
 * Alternativni prihlaseni e-mailem a heslem.
 * Vytvari databazovou session a nastavuje
 * aplikační session kontext uzivatele.
 */
session_start();

require_once __DIR__ . '/../../../core/db.php';
require_once __DIR__ . '/../../../core/helper.php';

if (!is_post()) {
    json_response([
        'success' => false,
        'message' => t('login_method_not_allowed') !== 'login_method_not_allowed' ? t('login_method_not_allowed') : 'Method not allowed.'
    ], 405);
}

require_csrf();

$email = normalize_email(post('email', ''));
$password = (string) post('password', '');

if (!is_valid_email($email) || $password === '') {
    json_response([
        'success' => false,
        'message' => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.'
    ], 422);
}

try {
    $stmt = $pdo->prepare(
        'SELECT u.*, p.jmeno, p.prijmeni, p.jazyk
         FROM alpha_pracovnici_uzivatele u
         LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id
         WHERE u.login_email = ?
         LIMIT 1'
    );
    $stmt->execute([$email]);
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

    if (!empty($dbUser['locked_until']) && strtotime((string) $dbUser['locked_until']) > time()) {
        json_response([
            'success' => false,
            'message' => t('login_account_locked') !== 'login_account_locked' ? t('login_account_locked') : 'Account is locked.'
        ], 423);
    }

    if (!password_verify($password, (string) ($dbUser['password_hash'] ?? ''))) {
        $attempts = (int) ($dbUser['failed_login_attempts'] ?? 0) + 1;

        if ($attempts >= 5) {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ?, locked_until = NOW() + INTERVAL 15 MINUTE WHERE login_email = ?');
        } else {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ? WHERE login_email = ?');
        }

        $updateStmt->execute([$attempts, $email]);

        json_response([
            'success' => false,
            'message' => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.'
        ], 401);
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $newLoginToken = generate_secure_token();
    $newLoginTokenHash = hash_token($newLoginToken);
    $sessionSecret = generate_secure_token();
    $sessionHash = hash_token($sessionSecret);

    $updateStmt = $pdo->prepare(
        'UPDATE alpha_pracovnici_uzivatele
         SET failed_login_attempts = 0,
             locked_until = NULL,
             last_login_at = NOW(),
             last_login_ip = ?,
             login_qr_token = ?
         WHERE id = ?'
    );
    $updateStmt->execute([$ip, $newLoginTokenHash, $dbUser['id']]);

    $sessionStmt = $pdo->prepare(
        'INSERT INTO alpha_pracovnici_uzivatele_sessions (user_id, refresh_token_hash, user_agent, ip_address, expires_at)
         VALUES (?, ?, ?, ?, NOW() + INTERVAL 30 DAY)'
    );
    $sessionStmt->execute([$dbUser['id'], $sessionHash, $userAgent, $ip]);

    $dbUser['login_qr_token'] = $newLoginToken;
    store_login_session($dbUser, $newLoginToken, $sessionHash);

    $_SESSION['user_name'] = trim((string) ($dbUser['jmeno'] ?? '') . ' ' . (string) ($dbUser['prijmeni'] ?? ''))
        ?: (t('default_user_name') !== 'default_user_name' ? t('default_user_name') : 'Uživatel');

    json_response(['success' => true]);
} catch (Throwable $e) {
    app_log('Credential login failed: ' . $e->getMessage());
    json_response([
        'success' => false,
        'message' => t('login_server_error') !== 'login_server_error' ? t('login_server_error') : 'Internal server error.'
    ], 500);
}
