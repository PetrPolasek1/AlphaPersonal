<?php
session_start();

require_once 'core/db.php';
require_once 'core/helper.php';
require_once 'core/FormManager.php';
require_auth();
require_once 'models/form-submission-model.php';

if (!is_post()) {
    redirect('index.php');
}

require_csrf();

$formId = post('form_id');
$langId = $_SESSION['lang_id'] ?? (post('lang_id') ?: 1);
$userId = (int) $_SESSION['user_id'];

if (!$formId) {
    $_SESSION['flash_error'] = t('form_submission_error') !== 'form_submission_error' ? t('form_submission_error') : 'Chyba pri odesilani formulare.';
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
                    $tmp = explode('.', $fileName);
                    $typ = strtolower(end($tmp));

                    $timestamp = time();
                    $newFileName = $timestamp . '_user' . $userId . '_' . $i . '.' . $typ;

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

$formManager = new FormManager($pdo);
$fields = $formManager->getFormFields($formId);
$dateFromValue = null;
$dateToValue = null;

foreach ($fields as $field) {
    if (($field['field_type'] ?? '') !== 'date') {
        continue;
    }

    $fieldId = (string) ($field['id'] ?? '');
    $fieldCode = (string) ($field['code'] ?? '');
    $fieldValue = trim((string) ($fieldValues[$fieldId] ?? ''));

    if ($fieldValue === '') {
        continue;
    }

    if ($fieldCode === 'datum_od') {
        $dateFromValue = $fieldValue;
    } elseif ($fieldCode === 'datum_do') {
        $dateToValue = $fieldValue;
    }
}

if ($dateFromValue !== null && $dateToValue !== null && $dateToValue < $dateFromValue) {
    $_SESSION['flash_error'] = t('date_range_invalid') !== 'date_range_invalid' ? t('date_range_invalid') : 'Datum do nemuze byt drive nez datum od.';
    redirect('index.php');
}

$model = new FormSubmissionModel($pdo);
$submissionId = $model->saveSubmission($formId, $langId, $userId, $fieldValues);

if ($submissionId) {
    $_SESSION['flash_success'] = t('form_submission_success') !== 'form_submission_success' ? t('form_submission_success') : 'Tvoje zadost byla uspesne odeslana!';
} else {
    $_SESSION['flash_error'] = t('form_submission_save_error') !== 'form_submission_save_error' ? t('form_submission_save_error') : 'Neco se pokazilo pri ukladani do databaze, zkus to prosim znovu.';
}

redirect('index.php');
?>
