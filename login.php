<?php
// login.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1. Načtení helperu, databáze a překladů
require_once 'core/helper.php'; 
require_once 'core/db.php'; 

// 2. Načtení MVC tříd
require_once 'models/login-model.php';
require_once 'controller/login-controller.php';

// 3. Inicializace a spuštění
$model = new LoginModel($pdo);
$controller = new LoginController($model, $pdo);

// 4. Předání kontroly Controlleru
$controller->handleRequest(); 
?>