<?php
session_start();

require_once __DIR__ . '/../../../core/helper.php';
require_once __DIR__ . '/../../../core/db.php';

if (!is_post()) {
    redirect('../../../profile.php');
}

require_csrf();

$loginToken = trim((string) ($_SESSION['login_token'] ?? ''));
$refreshTokenHash = trim((string) ($_SESSION['refresh_token_hash'] ?? ''));

if ($refreshTokenHash !== '') {
    try {
        $stmt = $pdo->prepare(
            'UPDATE alpha_pracovnici_uzivatele_sessions
             SET revoked_at = NOW()
             WHERE refresh_token_hash = ? AND revoked_at IS NULL'
        );
        $stmt->execute([$refreshTokenHash]);
    } catch (Exception $e) {
        app_log('Logout revoke failed: ' . $e->getMessage());
    }
}

clear_auth_session();

$redirectUrl = 'http://localhost/portal/client/login/u/';

if ($loginToken !== '') {
    $redirectUrl .= urlencode($loginToken);
}

redirect($redirectUrl);
?>
