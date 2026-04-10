<?php
// process_form.php
session_start();
require_once 'core/db.php';
require_once 'core/helper.php';
require_once 'core/FormManager.php';

if (!is_post()) {
    redirect('index.php');
}

$formId = post('form_id');
$userId = $_SESSION['user_id'] ?? 1;

if (!$formId) {
    $_SESSION['flash_error'] = "Chyba při odesílání formuláře.";
    redirect('index.php');
}

$uploadedFilePaths = [];


if (!empty($_FILES)) {
    $uploadDir = './documents/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES as $inputName => $fileArray) {
        if (strpos($inputName, 'field_') === 0 && !empty($fileArray['name'][0])) {
            $fieldId = str_replace('field_', '', $inputName);
            $uploadedFilePaths[$fieldId] = [];

            $fileCount = is_array($fileArray['name']) ? count($fileArray['name']) : 1;

            for ($i = 0; $i < $fileCount; $i++) {
                $fileName = is_array($fileArray['name']) ? $fileArray['name'][$i] : $fileArray['name'];
                $tmpName = is_array($fileArray['tmp_name']) ? $fileArray['tmp_name'][$i] : $fileArray['tmp_name'];
                $fileSize = is_array($fileArray['size']) ? $fileArray['size'][$i] : $fileArray['size'];
                $fileError = is_array($fileArray['error']) ? $fileArray['error'][$i] : $fileArray['error'];

                if ($fileError === UPLOAD_ERR_OK && ($fileSize / 1000000) < 20) {
                    $tmp = explode(".", $fileName);
                    $typ = strtolower(end($tmp)); // end() vezme poslední prvek (příponu)
                    
                    $timestamp = time();
                    $newFileName = $timestamp . "_user" . $userId . "_" . $i . "." . $typ;
                    
                    if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
                        $uploadedFilePaths[$fieldId][] = $newFileName;
                    }
                }
            }
        }
    }
}

// Inicializace manažera a uložení do DB
$formManager = new FormManager($pdo);
$requestId = $formManager->saveRequest($userId, $formId, $_POST, $uploadedFilePaths);

if ($requestId) {
    $_SESSION['flash_success'] = "Tvoje žádost byla úspěšně odeslána!";
    app_log("Žádost ID $requestId úspěšně vytvořena.");
} else {
    $_SESSION['flash_error'] = "Něco se pokazilo, zkus to prosím znovu.";
}

redirect('index.php');
?>