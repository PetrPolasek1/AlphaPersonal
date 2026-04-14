<?php
// process_form.php
session_start();
require_once 'core/db.php';
require_once 'core/helper.php';
// Místo starého FormManager načteme náš nový model
require_once 'models/form-submission-model.php';

if (!is_post()) {
    redirect('index.php');
}

$formId = post('form_id');
// Pokud neposíláš jazyk z formuláře, dáme výchozí hodnotu 1 (čeština)
$langId = post('lang_id') ?: 1; 
$userId = $_SESSION['user_id'] ?? 1;

if (!$formId) {
    $_SESSION['flash_error'] = "Chyba při odesílání formuláře.";
    redirect('index.php');
}

$uploadedFilePaths = [];

// === TVOJE LOGIKA PRO NAHRÁVÁNÍ SOUBORŮ (ZŮSTÁVÁ BEZE ZMĚNY) ===
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
                    $typ = strtolower(end($tmp));
                    
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


$fieldValues = [];

foreach ($_POST as $key => $value) {
    if (strpos($key, 'field_') === 0) {
        $fieldId = str_replace('field_', '', $key);
        $fieldValues[$fieldId] = $value;
    }
}

foreach ($uploadedFilePaths as $fieldId => $pathsArray) {
    $fieldValues[$fieldId] = implode(',', $pathsArray); 
}

$model = new FormSubmissionModel($pdo);
$submissionId = $model->saveSubmission($formId, $langId, $userId, $fieldValues);

if ($submissionId) {
    $_SESSION['flash_success'] = "Tvoje žádost byla úspěšně odeslána!";

} else {
    $_SESSION['flash_error'] = "Něco se pokazilo při ukládání do databáze, zkus to prosím znovu.";
}

redirect('index.php');
?>