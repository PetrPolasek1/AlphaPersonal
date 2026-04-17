<?php
// profile.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Načtení jádra (uprav cesty, pokud jsi to přesunul do složky 'core/')
require_once 'core/helper.php';
require_auth();
require_once 'core/db.php'; 

// Načtení MVC
require_once 'models/profile-model.php';
require_once 'controller/profile-controller.php';

$model = new ProfileModel($pdo);
$controller = new ProfileController($model);

$controller->handleRequest(); 
?>
