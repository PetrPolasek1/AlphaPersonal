<?php
// zpravy.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
