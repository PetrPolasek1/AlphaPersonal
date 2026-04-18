<?php
/**
 * -------------------------------------------------
 * API Endpoint: Logout
 * -------------------------------------------------
 * Bezpecne odhlasi uzivatele, revokuje DB session
 * a rotuje login token pro dalsi prihlaseni.
 */
session_start();

require_once __DIR__ . '/../../../core/helper.php';
require_once __DIR__ . '/../../../core/db.php';

if (!is_post()) {
    redirect('../../../profile.php');
}

require_csrf();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$refreshTokenHash = trim((string) ($_SESSION['refresh_token_hash'] ?? ''));
$nextLoginToken = '';

if ($refreshTokenHash !== '') {
    try {
        $stmt = $pdo->prepare(
            'UPDATE alpha_pracovnici_uzivatele_sessions
             SET revoked_at = NOW()
             WHERE refresh_token_hash = ? AND revoked_at IS NULL'
        );
        $stmt->execute([$refreshTokenHash]);
    } catch (Throwable $e) {
        app_log('Logout revoke failed: ' . $e->getMessage());
    }
}

if ($userId > 0) {
    try {
        $nextLoginToken = generate_secure_token();
        $nextLoginTokenHash = hash_token($nextLoginToken);

        $stmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET login_qr_token = ? WHERE id = ?');
        $stmt->execute([$nextLoginTokenHash, $userId]);
    } catch (Throwable $e) {
        app_log('Logout token rotation failed: ' . $e->getMessage());
        $nextLoginToken = '';
    }
}

clear_auth_session();

$redirectUrl = 'http://localhost/portal/client/login';

if ($nextLoginToken !== '') {
    $redirectUrl = 'http://localhost/portal/client/login/u/' . rawurlencode($nextLoginToken);
}

redirect($redirectUrl);
