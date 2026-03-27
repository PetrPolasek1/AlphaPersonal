<?php
header('Content-Type: application/json');

// Zamezení jiných metod než POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

// 1. Vstupy a základní kontrola
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($token) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Neplatné přihlašovací údaje."]);
    exit;
}

// Připojení k databázi
$host = 'localhost';
$db   = 'alphapersonal';
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
    // Dohledání uživatele s propojením na tabulku pracovníků pro získání jména
    $stmt = $pdo->prepare('SELECT u.*, p.jmeno, p.prijmeni FROM alpha_pracovnici_uzivatele u LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id WHERE u.login_qr_token = ?');
    $stmt->execute([$token]);
    $dbUser = $stmt->fetch();

    if (!$dbUser) {
        echo json_encode(["success" => false, "message" => "Neplatné přihlašovací údaje."]);
        exit;
    }

    // 2. Kontrola stavu účtu
    if ($dbUser['is_active'] != 1) {
        echo json_encode(["success" => false, "message" => "Tento účet není aktivní."]);
        exit;
    }

    // Kontrola povolení přihlášení pomocí QR
    if ($dbUser['login_qr_enabled'] != 1) {
        echo json_encode(["success" => false, "message" => "Přihlášení pomocí QR není pro tento účet povoleno."]);
        exit;
    }

    // Kontrola uzamčení účtu
    if (!empty($dbUser['locked_until']) && strtotime($dbUser['locked_until']) > time()) {
        echo json_encode(["success" => false, "message" => "Účet je uzamčen"]);
        exit;
    }

    // 3. Ověření hesla
    if (password_verify($password, $dbUser['password_hash'])) {
        // Úspěšné přihlášení
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
        $updateStmt->execute([$ip, $dbUser['id']]);

        // Vygenerování tokenů
        $accessToken = bin2hex(random_bytes(32));
        $refreshToken = bin2hex(random_bytes(32));
        $refreshTokenHash = hash('sha256', $refreshToken);

        // Uložení relace do databáze
        $sessionStmt = $pdo->prepare('INSERT INTO alpha_pracovnici_uzivatele_sessions (user_id, refresh_token_hash, user_agent, ip_address, expires_at) VALUES (?, ?, ?, ?, NOW() + INTERVAL 30 DAY)');
        $sessionStmt->execute([$dbUser['id'], $refreshTokenHash, $userAgent, $ip]);

        // Uložení jména do session pro zobrazení v index.php
        session_start();
        $jmeno = $dbUser['jmeno'] ?? '';
        $prijmeni = $dbUser['prijmeni'] ?? '';
        $_SESSION['user_name'] = trim($jmeno . ' ' . $prijmeni) ?: 'Uživatel';
        $_SESSION['user_id'] = $dbUser['id'];

        echo json_encode([
            "success" => true,
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken
        ]);
        exit;
    } else {
        // Heslo je špatně
        $attempts = (int)$dbUser['failed_login_attempts'] + 1;
        
        if ($attempts >= 5) {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ?, locked_until = NOW() + INTERVAL 15 MINUTE WHERE id = ?');
        } else {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ? WHERE id = ?');
        }
        $updateStmt->execute([$attempts, $dbUser['id']]);
        
        echo json_encode(["success" => false, "message" => "Neplatné přihlašovací údaje."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Interní chyba serveru."]);
}