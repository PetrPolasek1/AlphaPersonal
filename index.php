<?php
// index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1. Načtení helperu, překladů a databáze
require_once 'core/helper.php';
require_once 'core/db.php'; 

// 2. Načtení MVC tříd
require_once 'models/index-model.php';
require_once 'controller/index-controller.php';

// 3. Inicializace a spuštění
$model = new IndexModel($pdo);
$controller = new IndexController($model);

// 4. Předání kontroly Controlleru
$controller->handleRequest(); 
?>