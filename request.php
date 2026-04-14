<?php
// request.php
session_start();
// Načtení databáze a pomocných souborů
require_once 'core/db.php'; 
require_once 'core/helper.php';

// Načtení MVC
require_once 'models/request-model.php';
require_once 'controller/request-controller.php';

// Inicializace modelu a kontroleru stejně jako u zpráv
$model = new RequestModel($pdo);
$controller = new RequestController($model);

// Spuštění logiky
$controller->handleRequest();
?>