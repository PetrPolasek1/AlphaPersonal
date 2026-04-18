<?php
/**
 * -------------------------------------------------
 * Root Entry: Messages
 * -------------------------------------------------
 * Vstupni bod modulu zprav.
 * Ověřuje přihlášení a načítá MVC vrstvu
 * pro inbox, koš a akce se zprávami.
 */
session_start();

require_once 'core/helper.php'; 
require_auth();
require_once 'core/db.php'; 

require_once 'models/message-model.php';
require_once 'controller/message-controller.php';

// Inicializace a spuštění
$model = new MessageModel($pdo);
$controller = new MessageController($model);

$controller->handleRequest(); 
?>
