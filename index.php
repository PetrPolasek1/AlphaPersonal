<?php
/**
 * -------------------------------------------------
 * Root Entry: Dashboard
 * -------------------------------------------------
 * Vstupni bod dashboardu klienta.
 * Ověřuje aktivní session a předává řízení
 * dashboard controlleru.
 */
session_start();

// 1. Načtení helperu, překladů a databáze
require_once 'core/helper.php';
require_auth();
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
