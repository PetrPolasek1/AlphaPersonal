<?php
// api/client/auth/login-by-token.php

// 1. ZAPNEME SESSION HNED NA ZAČÁTKU (kvůli načtení db.php a language.php)
session_start();

// 2. Načteme helper a připojení k databázi (S VYUŽITÍM __DIR__ PROTI CHYBÁM CESTY)
require_once __DIR__ . '/../../../helper.php';
require_once __DIR__ . '/../../../controller/db.php'; 

// 3. Zamezení jiných metod než POST pomocí helperu
if (!is_post()) {
    json_response(["success" => false, "message" => "Method not allowed"]);
}

// 4. Vstupy a základní kontrola pomocí helperu
$token = post('token', '');
$password = post('password', '');

if (empty($token) || empty($password)) {
    json_response(["success" => false, "message" => "Neplatné přihlašovací údaje."]);
}

try {
    // 5. Dohledání uživatele (Přidal jsem "p.jazyk" do SELECTu, abychom ho mohli uložit do session!)
    $stmt = $pdo->prepare('SELECT u.*, p.jmeno, p.prijmeni, p.jazyk FROM alpha_pracovnici_uzivatele u LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id WHERE u.login_qr_token = ?');
    $stmt->execute([$token]);
    $dbUser = $stmt->fetch();

    if (!$dbUser) {
        json_response(["success" => false, "message" => "Neplatné přihlašovací údaje."]);
    }

    // Kontroly stavu účtu
    if ($dbUser['is_active'] != 1) {
        json_response(["success" => false, "message" => "Tento účet není aktivní."]);
    }

    if ($dbUser['login_qr_enabled'] != 1) {
        json_response(["success" => false, "message" => "Přihlášení pomocí QR není pro tento účet povoleno."]);
    }

    if (!empty($dbUser['locked_until']) && strtotime($dbUser['locked_until']) > time()) {
        json_response(["success" => false, "message" => "Účet je uzamčen"]);
    }

    // 6. Ověření hesla
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

        // 7. Uložení dat do session (bez opakovaného volání session_start)
        $jmeno = $dbUser['jmeno'] ?? '';
        $prijmeni = $dbUser['prijmeni'] ?? '';
        
        $_SESSION['user_name'] = trim($jmeno . ' ' . $prijmeni) ?: 'Uživatel';
        $_SESSION['user_id'] = $dbUser['id'];
        $_SESSION['lang_id'] = $dbUser['jazyk'] ?? 1; // Uložení jazyka

        // Čistá odpověď přes helper
        json_response([
            "success" => true,
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken
        ]);
        
    } else {
        // Heslo je špatně
        $attempts = (int)$dbUser['failed_login_attempts'] + 1;
        
        if ($attempts >= 5) {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ?, locked_until = NOW() + INTERVAL 15 MINUTE WHERE id = ?');
        } else {
            $updateStmt = $pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET failed_login_attempts = ? WHERE id = ?');
        }
        $updateStmt->execute([$attempts, $dbUser['id']]);
        
        json_response(["success" => false, "message" => "Neplatné přihlašovací údaje."]);
    }
} catch (Exception $e) {
    json_response(["success" => false, "message" => "Interní chyba serveru."]);
}
?>