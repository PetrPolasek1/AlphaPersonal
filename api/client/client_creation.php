<?php
/**
 * -------------------------------------------------
 * API Utility: Client Creation
 * -------------------------------------------------
 * Lokalni pomocny skript pro vytvoreni klienta.
 * Slouzi pro administrativni nebo lokalni setup
 * testovaciho uctu a login odkazu.
 */

require_once __DIR__ . '/../../core/helper.php';
require_once __DIR__ . '/../../core/db.php';

$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
$isCli = PHP_SAPI === 'cli';
$isLocalRequest = in_array($remoteAddress, ['127.0.0.1', '::1'], true);

if (!$isCli && !$isLocalRequest) {
    http_response_code(403);
    exit('Pristup odepren.');
}

$cliArgs = [];

if ($isCli && isset($argv) && is_array($argv)) {
    foreach (array_slice($argv, 1) as $argument) {
        if (strpos($argument, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $argument, 2);
        $cliArgs[$key] = $value;
    }
}

$idPracovnika = (int) ($cliArgs['id_pracovnika'] ?? ($_REQUEST['id_pracovnika'] ?? 0));
$loginEmail = normalize_email($cliArgs['email'] ?? ($_REQUEST['email'] ?? ''));
$plainPassword = (string) ($cliArgs['password'] ?? ($_REQUEST['password'] ?? ''));

if ($idPracovnika <= 0 || !is_valid_email($loginEmail) || strlen($plainPassword) < 6) {
    http_response_code(422);
    echo "<h3>Chybi vstupni data.</h3>";
    echo "<p>Pouziti:</p>";
    echo "<pre>php api/client/client_creation.php id_pracovnika=3 email=uzivatel@example.cz password=Tajne123</pre>";
    exit;
}

try {
    $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $loginQrToken = generate_secure_token();
    $loginQrTokenHash = hash_token($loginQrToken);

    $sql = "INSERT INTO alpha_pracovnici_uzivatele
                (id_pracovnika, login_email, password_hash, login_qr_token)
            VALUES
                (:id_pracovnika, :login_email, :password_hash, :login_qr_token)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_pracovnika' => $idPracovnika,
        ':login_email' => $loginEmail,
        ':password_hash' => $passwordHash,
        ':login_qr_token' => $loginQrTokenHash,
    ]);

    $baseUrl = 'http://localhost/portal';
    $loginUrl = $baseUrl . '/client/login/u/' . $loginQrToken;
    $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($loginUrl);

    echo "<h3>Ucet byl uspesne vytvoren.</h3>";
    echo "<p>Prihlasovaci udaje:</p>";
    echo "<ul>";
    echo "<li><strong>E-mail:</strong> " . htmlspecialchars($loginEmail, ENT_QUOTES, 'UTF-8') . "</li>";
    echo "<li><strong>Heslo:</strong> " . htmlspecialchars($plainPassword, ENT_QUOTES, 'UTF-8') . "</li>";
    echo "</ul>";
    echo "<p><strong>Prihlasovaci odkaz:</strong> <a href=\"" . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . "\" target=\"_blank\" rel=\"noopener noreferrer\">" . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . "</a></p>";
    echo '<p><img src="' . htmlspecialchars($qrApiUrl, ENT_QUOTES, 'UTF-8') . '" alt="QR kod pro prihlaseni"></p>';
} catch (Throwable $e) {
    http_response_code(500);
    echo "<h3>Chyba pri vytvareni uctu.</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
}
