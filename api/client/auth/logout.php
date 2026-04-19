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
$redirectUrl = get_login_redirect_url();

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

clear_auth_session();

redirect($redirectUrl);
