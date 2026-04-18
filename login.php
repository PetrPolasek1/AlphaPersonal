<?php
/**
 * -------------------------------------------------
 * Root Entry: Login
 * -------------------------------------------------
 * Vstupni bod prihlaseni klienta.
 * Nacita login model/controller a pripravuje
 * zobrazeni prihlasovaciho formulare.
 */
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
