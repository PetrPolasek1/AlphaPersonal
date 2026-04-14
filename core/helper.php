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
// CSRF hidden field helper
// -------------------------------------------------
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
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

    $date = date('Y-m-d H:i:s');
    file_put_contents($file, "[$date] $message\n", FILE_APPEND);
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