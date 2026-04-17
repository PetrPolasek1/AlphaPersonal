<?php
session_start();
require_once __DIR__ . '/../../../core/helper.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => t('login_method_not_allowed') !== 'login_method_not_allowed' ? t('login_method_not_allowed') : 'Method not allowed.']);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.']);
    exit;
}

$host = 'localhost';
$db = 'alphapersonal';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(["success" => false, "message" => "Chyba připojení k databázi."]);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT u.*, p.jmeno, p.prijmeni, p.jazyk FROM alpha_pracovnici_uzivatele u LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id WHERE u.login_email = ?');
    $stmt->execute([$email]);
    $dbUser = $stmt->fetch();

    if (!$dbUser) {
        echo json_encode(["success" => false, "message" => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.']);
        exit;
    }

    if ($dbUser['is_active'] != 1) {
        echo json_encode(["success" => false, "message" => t('login_account_inactive') !== 'login_account_inactive' ? t('login_account_inactive') : 'This account is not active.']);
        exit;
    }

    if (!empty($dbUser['locked_until']) && strtotime($dbUser['locked_until']) > time()) {
        echo json_encode(["success" => false, "message" => t('login_account_locked') !== 'login_account_locked' ? t('login_account_locked') : 'Account is locked.']);
        exit;
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

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $jmeno = $dbUser['jmeno'] ?? '';
        $prijmeni = $dbUser['prijmeni'] ?? '';
        $_SESSION['user_name'] = trim($jmeno . ' ' . $prijmeni) ?: (t('default_user_name') !== 'default_user_name' ? t('default_user_name') : 'Uživatel');

        echo json_encode([
            "success" => true,
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken
        ]);
        exit;
    } else {
        $attempts = (int) $dbUser['failed_login_attempts'] + 1;

        if ($attempts >= 5) {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ?, locked_until = NOW() + INTERVAL 15 MINUTE WHERE login_email = ?');
        } else {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ? WHERE login_email = ?');
        }
        $updateStmt->execute([$attempts, $email]);

        echo json_encode(["success" => false, "message" => t('login_invalid_credentials') !== 'login_invalid_credentials' ? t('login_invalid_credentials') : 'Invalid login credentials.']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => t('login_server_error') !== 'login_server_error' ? t('login_server_error') : 'Internal server error.']);
    exit;
}
