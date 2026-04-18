<?php
/**
 * -------------------------------------------------
 * Root Entry: Reset Password
 * -------------------------------------------------
 * Dokoncovaci krok resetu hesla.
 * Ověřuje reset token a předává řízení
 * reset password controlleru.
 */
session_start();

require_once 'core/db.php';
require_once 'core/helper.php';
require_once 'models/auth-model.php';
require_once 'controller/reset-password-controller.php';

$model = new AuthModel($pdo);
$controller = new ResetPasswordController($model);
$controller->handleRequest();
