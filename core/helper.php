<?php


// -------------------------------------------------
// Escapování (XSS ochrana)
// -------------------------------------------------
function e($value) {
    // 1. Ošetření null hodnoty (časté u dat z DB)
    if (!isset($value) || $value === null) {
        return; 
    }

    // 2. Pokud je to pole (třeba zapomenutý výpis celého jazykového pole)
    // tak ho nevypsat, aby to nezpůsobilo Fatal Error "Array to string conversion"
    if (is_array($value)) {
        // Volitelně pro debug: echo "[Array]";
        return;
    }

    // 3. Samotný výpis s ošetřením uvozovek (ENT_QUOTES) a UTF-8
    echo htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


// -------------------------------------------------
// Debug print
// -------------------------------------------------
function dump($value): void
{
    echo '<pre style="background:#111;color:#0f0;padding:15px;">';
    print_r($value);
    echo '</pre>';
}


// -------------------------------------------------
// Dump & die
// -------------------------------------------------
function dd($value): void
{
    dump($value);
    exit;
}


// -------------------------------------------------
// JSON response (API ready)
// -------------------------------------------------
function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}


// -------------------------------------------------
// Bezpečný redirect
// -------------------------------------------------
function redirect(string $url): void
{
    header("Location: " . $url);
    exit;
}


// -------------------------------------------------
// Přihlášení / odhlášení
// -------------------------------------------------
function store_login_session(array $dbUser, string $loginToken, string $refreshTokenHash): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $jmeno = trim((string)($dbUser['jmeno'] ?? ''));
    $prijmeni = trim((string)($dbUser['prijmeni'] ?? ''));
    $displayName = trim($jmeno . ' ' . $prijmeni);

    $_SESSION['user_id'] = (int)($dbUser['id'] ?? 0);
    $_SESSION['user_name'] = $displayName !== '' ? $displayName : 'Uživatel';
    $_SESSION['lang_id'] = (int)($dbUser['jazyk'] ?? 1);
    $_SESSION['login_token'] = $loginToken;
    $_SESSION['refresh_token_hash'] = $refreshTokenHash;
    $_SESSION['just_logged_in'] = true;
}

function generate_secure_token(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

function hash_token(string $token): string
{
    return hash('sha256', $token);
}

function normalize_email(?string $email): string
{
    $email = trim((string) $email);

    if ($email === '') {
        return '';
    }

    return strtolower($email);
}

function is_valid_email(?string $email): bool
{
    $normalizedEmail = normalize_email($email);

    return $normalizedEmail !== '' && filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL) !== false;
}

function is_local_request(): bool
{
    $remoteAddress = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    return in_array($remoteAddress, ['127.0.0.1', '::1'], true);
}

function build_password_reset_url(string $token): string
{
    return 'reset-password.php?token=' . rawurlencode($token);
}

function get_login_redirect_url(): string
{
    $token = trim((string)($_SESSION['login_token'] ?? ''));

    if ($token !== '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . '/portal/client/login/u/' . rawurlencode($token);
    }

    return 'login.php';
}

function get_current_auth_session(): ?array
{
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $refreshTokenHash = trim((string) ($_SESSION['refresh_token_hash'] ?? ''));

    if ($userId <= 0 || $refreshTokenHash === '') {
        return null;
    }

    try {
        global $pdo;

        if (!isset($pdo) || !($pdo instanceof PDO)) {
            require __DIR__ . '/db.php';
        }

        $stmt = $pdo->prepare(
            'SELECT s.user_id, s.expires_at, s.revoked_at, u.is_active
             FROM alpha_pracovnici_uzivatele_sessions s
             JOIN alpha_pracovnici_uzivatele u ON u.id = s.user_id
             WHERE s.user_id = ?
               AND s.refresh_token_hash = ?
               AND s.revoked_at IS NULL
               AND s.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([$userId, $refreshTokenHash]);
        $sessionRow = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        app_log('Session validation failed: ' . $e->getMessage());
        return null;
    }

    if (!$sessionRow || (int) ($sessionRow['is_active'] ?? 0) !== 1) {
        return null;
    }

    return $sessionRow;
}

function is_authenticated(): bool
{
    return get_current_auth_session() !== null;
}

function require_auth(): void
{
    if (!is_authenticated()) {
        clear_auth_session();
        redirect(get_login_redirect_url());
    }
}

function clear_auth_session(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}


// -------------------------------------------------
// CSRF hidden field helper
// -------------------------------------------------
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf'];
}

function is_valid_csrf(?string $token): bool
{
    $sessionToken = (string) ($_SESSION['_csrf'] ?? '');
    $submittedToken = trim((string) $token);

    return $sessionToken !== '' && $submittedToken !== '' && hash_equals($sessionToken, $submittedToken);
}

function require_csrf(): void
{
    if (!is_valid_csrf(post('_csrf'))) {
        if (isAjax()) {
            json_response([
                'success' => false,
                'message' => 'Neplatný požadavek.'
            ], 419);
        }

        http_response_code(419);
        exit('Neplatný požadavek.');
    }
}


// -------------------------------------------------
// Bezpečné získání GET parametru
// -------------------------------------------------
function get(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}


// -------------------------------------------------
// Bezpečné získání POST parametru
// -------------------------------------------------
function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}


// -------------------------------------------------
// Kontrola request metody
// -------------------------------------------------
function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}


// -------------------------------------------------
// Základní logování (mimo DB)
// -------------------------------------------------
function app_log(string $message): void
{
    $basePath = dirname(__DIR__, 2);
    $file = $basePath . '/storage/logs/app.log';
    $directory = dirname($file);

    $date = date('Y-m-d H:i:s');

    if (!is_dir($directory)) {
        @mkdir($directory, 0750, true);
    }

    @file_put_contents($file, "[$date] $message\n", FILE_APPEND | LOCK_EX);
}


/**
 * Ochrana: Detekce, zda požadavek přišel přes AJAX (jQuery)
 * @return bool
 */
function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}


function vypocitejVekCZ(string $datumNarozeni): ?int
{
    $narozeni = DateTime::createFromFormat('d.m.Y', $datumNarozeni);

    if (!$narozeni) {
        return null; 
    }

    $dnes = new DateTime();

    return $dnes->diff($narozeni)->y;
}
