<?php
/**
 * -------------------------------------------------
 * Root Endpoint: Protected Document Download
 * -------------------------------------------------
 * Ověřuje oprávnění uživatele ke stažení dokumentu
 * a vrací soubor pouze přes autorizovaný endpoint.
 */
session_start();

require_once 'core/helper.php';
require_auth();
require_once 'core/db.php';
require_once 'models/request-model.php';

$userId = (int) ($_SESSION['user_id'] ?? 0);
$fileName = basename(trim((string) get('file', '')));

if ($fileName === '') {
    http_response_code(400);
    exit(t('file_missing') !== 'file_missing' ? t('file_missing') : 'Chybějící soubor.');
}

$model = new RequestModel($pdo);

if (!$model->userCanAccessFile($userId, $fileName)) {
    http_response_code(403);
    exit(t('access_denied') !== 'access_denied' ? t('access_denied') : 'Přístup odepřen.');
}

$filePath = __DIR__ . '/documents/' . $fileName;

if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    exit(t('file_not_found') !== 'file_not_found' ? t('file_not_found') : 'Soubor nebyl nalezen.');
}

$mimeType = mime_content_type($filePath);
if (!$mimeType) {
    $mimeType = 'application/octet-stream';
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . rawurlencode($fileName) . '"');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
readfile($filePath);
exit;
?>
