<?php
session_start();
require_once 'core/db.php';
require_once 'core/helper.php';
require_once 'models/auth-model.php';
require_once 'controller/forgot-password-controller.php';

$model = new AuthModel($pdo);
$controller = new ForgotPasswordController($model);
$controller->handleRequest();
?>