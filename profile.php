<?php
/**
 * -------------------------------------------------
 * Root Entry: Profile
 * -------------------------------------------------
 * Vstupni bod profilove sekce.
 * Ověřuje session a předává řízení controlleru
 * pro profil a změnu hesla.
 */
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
