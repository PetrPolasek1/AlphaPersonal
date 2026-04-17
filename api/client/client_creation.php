<?php
// vytvor_klienta.php

$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
$isCli = PHP_SAPI === 'cli';
$isLocalRequest = in_array($remoteAddress, ['127.0.0.1', '::1'], true);

if (!$isCli && !$isLocalRequest) {
    http_response_code(403);
    exit('Pristup odepren.');
}

// 1. Nastavení připojení k databázi
$host = 'localhost';
$dbname = 'alphapersonal';
$user = 'root';
$heslo_db = '';

try {
    // Vytvoření PDO připojení s nastavením znakové sady
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $heslo_db);
    
    // Zapnutí vyhazování výjimek při chybách v SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Příprava dat pro nového uživatele
    $id_pracovnika = 3;
    $login_email = 'petr@gmail.com';
    $heslo_v_textu = 'heslo123';

    // 3. Bezpečné zahashování hesla
    $password_hash = password_hash($heslo_v_textu, PASSWORD_DEFAULT);

    // 4. Vygenerování bezpečného náhodného stringu pro login_qr_token (32 znaků)
    $login_qr_token = bin2hex(random_bytes(16));

    // 5. Příprava SQL dotazu
    $sql = "INSERT INTO alpha_pracovnici_uzivatele 
                (id_pracovnika, login_email, password_hash, login_qr_token) 
            VALUES 
                (:id_pracovnika, :login_email, :password_hash, :login_qr_token)";

    // Vytvoření prepared statementu
    $stmt = $pdo->prepare($sql);

    // 6. Bezpečné spuštění dotazu s navázanými parametry
    $stmt->execute([
        ':id_pracovnika' => $id_pracovnika,
        ':login_email'   => $login_email,
        ':password_hash' => $password_hash,
        ':login_qr_token'=> $login_qr_token
    ]);

    // 7. Výpis úspěchu a generování QR odkazu
    // Sestavení základní URL webu
    $baseUrl = 'http://localhost/portal';
    // Sestavení finální URL pro přihlášení přesně podle zadání
    $loginUrl = $baseUrl . '/client/login/u/' . $login_qr_token;
    // Sestavení URL pro API na generování QR kódu
    $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($loginUrl);

    echo "<h3>Účet byl úspěšně vytvořen!</h3>";
    echo "<p>Níže naleznete unikátní přihlašovací odkaz a QR kód pro tohoto uživatele.</p>";
    echo "<h4>Přihlašovací údaje:</h4>";
    echo "<ul>";
    echo "<li><strong>Přihlašovací e-mail:</strong> " . htmlspecialchars($login_email) . "</li>";
    echo "<li><strong>Heslo (čistý text):</strong> " . htmlspecialchars($heslo_v_textu) . "</li>";
    echo "</ul>";
    echo "<h4>Bezpečnostní přihlašovací odkaz:</h4>";
    echo '<p><a href="' . htmlspecialchars($loginUrl) . '" target="_blank">' . htmlspecialchars($loginUrl) . '</a></p>';
    echo "<h4>QR kód pro přihlášení:</h4>";
    echo '<p>Naskenujte kód mobilním telefonem pro rychlé přihlášení:</p>';
    echo '<img src="' . htmlspecialchars($qrApiUrl) . '" alt="QR kód pro přihlášení">';

} catch (PDOException $e) {
    // Odchycení a výpis chyby databáze
    echo "<h3>Chyba při práci s databází:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    // Odchycení ostatních chyb (např. pokud selže random_bytes)
    echo "<h3>Obecná chyba:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
