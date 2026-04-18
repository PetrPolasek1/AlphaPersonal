<?php
/**
 * -------------------------------------------------
 * Core: Database Bootstrap
 * -------------------------------------------------
 * Inicializuje PDO připojení k databázi.
 * Současně navazuje jazykovou vrstvu,
 * která závisí na aktivním PDO objektu.
 */

$host = getenv('APP_DB_HOST') ?: 'localhost';
$db = getenv('APP_DB_NAME') ?: 'alphapersonal';
$user = getenv('APP_DB_USER') ?: 'root';
$pass = getenv('APP_DB_PASS');
$pass = $pass === false ? '' : $pass;
$charset = getenv('APP_DB_CHARSET') ?: 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Chyba připojení k databázi. Zkuste to prosím později.");
}
require_once __DIR__ . '/language.php';
?>
