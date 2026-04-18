<?php
/**
 * -------------------------------------------------
 * Root Endpoint: Form Submission Processor
 * -------------------------------------------------
 * Bezpecne zpracovava odeslane formularove podani.
 * Resi validaci vstupu, uploady souboru
 * a ulozeni dat do databaze.
 */
session_start();

require_once 'core/db.php';
require_once 'core/helper.php';
require_once 'core/formManager.php';
require_auth();
require_once 'models/form-submission-model.php';

if (!is_post()) {
    redirect('index.php');
}

require_csrf();

$formId = (int) post('form_id', 0);
$langId = (int) ($_SESSION['lang_id'] ?? (post('lang_id') ?: 1));
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($formId <= 0 || $userId <= 0) {
    $_SESSION['flash_error'] = t('form_submission_error') !== 'form_submission_error' ? t('form_submission_error') : 'Chyba pri odesilani formulare.';
    redirect('index.php');
}

$formManager = new FormManager($pdo);
$fields = $formManager->getFormFields($formId);

if (empty($fields)) {
    $_SESSION['flash_error'] = t('form_submission_error') !== 'form_submission_error' ? t('form_submission_error') : 'Chyba pri odesilani formulare.';
    redirect('index.php');
}

$fieldDefinitions = [];

foreach ($fields as $field) {
    $fieldDefinitions[(int) $field['id']] = $field;
}

$uploadDir = __DIR__ . '/documents/';
$allowedUploadTypes = [
    'pdf' => ['application/pdf'],
    'doc' => ['application/msword', 'application/octet-stream'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
    'xls' => ['application/vnd.ms-excel', 'application/octet-stream'],
    'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
    'jpg' => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png' => ['image/png'],
    'gif' => ['image/gif'],
    'txt' => ['text/plain'],
    'csv' => ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'],
    'mp3' => ['audio/mpeg', 'audio/mp3'],
    'wav' => ['audio/wav', 'audio/x-wav'],
    'm4a' => ['audio/mp4', 'audio/x-m4a'],
];
$maxUploadBytes = 20 * 1024 * 1024;
$uploadedFilePaths = [];
$uploadedAbsolutePaths = [];
$uploadErrors = [];

$removeUploadedFiles = static function (array $absolutePaths): void {
    foreach ($absolutePaths as $absolutePath) {
        if (is_string($absolutePath) && $absolutePath !== '' && is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
};

if (!empty($_FILES)) {
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0750, true);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    foreach ($_FILES as $inputName => $fileArray) {
        if (strpos($inputName, 'field_') !== 0) {
            continue;
        }

        $fieldIdRaw = str_replace('field_', '', $inputName);

        if (!ctype_digit((string) $fieldIdRaw)) {
            continue;
        }

        $fieldId = (int) $fieldIdRaw;
        $fieldDefinition = $fieldDefinitions[$fieldId] ?? null;

        if (!$fieldDefinition || ($fieldDefinition['field_type'] ?? '') !== 'file') {
            continue;
        }

        $names = is_array($fileArray['name'] ?? null) ? $fileArray['name'] : [$fileArray['name'] ?? ''];
        $tmpNames = is_array($fileArray['tmp_name'] ?? null) ? $fileArray['tmp_name'] : [$fileArray['tmp_name'] ?? ''];
        $sizes = is_array($fileArray['size'] ?? null) ? $fileArray['size'] : [$fileArray['size'] ?? 0];
        $errors = is_array($fileArray['error'] ?? null) ? $fileArray['error'] : [$fileArray['error'] ?? UPLOAD_ERR_NO_FILE];

        foreach ($names as $index => $originalName) {
            $originalName = trim((string) $originalName);
            $tmpName = (string) ($tmpNames[$index] ?? '');
            $fileSize = (int) ($sizes[$index] ?? 0);
            $fileError = (int) ($errors[$index] ?? UPLOAD_ERR_NO_FILE);

            if ($fileError === UPLOAD_ERR_NO_FILE || $originalName === '') {
                continue;
            }

            if ($fileError !== UPLOAD_ERR_OK) {
                $uploadErrors[] = t('file_upload_failed') !== 'file_upload_failed'
                    ? t('file_upload_failed')
                    : 'Nahrani souboru selhalo.';
                continue;
            }

            if ($fileSize <= 0 || $fileSize > $maxUploadBytes) {
                $uploadErrors[] = t('file_upload_too_large') !== 'file_upload_too_large'
                    ? t('file_upload_too_large')
                    : 'Soubor presahl maximalni povolenou velikost 20 MB.';
                continue;
            }

            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!isset($allowedUploadTypes[$extension])) {
                $uploadErrors[] = t('file_upload_invalid_type') !== 'file_upload_invalid_type'
                    ? t('file_upload_invalid_type')
                    : 'Tento typ souboru neni povolen.';
                continue;
            }

            $mimeType = $finfo->file($tmpName);

            if ($mimeType === false || !in_array($mimeType, $allowedUploadTypes[$extension], true)) {
                $uploadErrors[] = t('file_upload_invalid_type') !== 'file_upload_invalid_type'
                    ? t('file_upload_invalid_type')
                    : 'Tento typ souboru neni povolen.';
                continue;
            }

            $newFileName = generate_secure_token(16) . '.' . $extension;
            $absolutePath = $uploadDir . $newFileName;

            if (!move_uploaded_file($tmpName, $absolutePath)) {
                $uploadErrors[] = t('file_upload_failed') !== 'file_upload_failed'
                    ? t('file_upload_failed')
                    : 'Nahrani souboru selhalo.';
                continue;
            }

            $uploadedFilePaths[$fieldId][] = $newFileName;
            $uploadedAbsolutePaths[] = $absolutePath;
        }
    }
}

if (!empty($uploadErrors)) {
    $removeUploadedFiles($uploadedAbsolutePaths);
    $_SESSION['flash_error'] = $uploadErrors[0];
    redirect('index.php');
}

$fieldValues = [];

foreach ($_POST as $key => $value) {
    if (strpos($key, 'field_') !== 0) {
        continue;
    }

    $fieldIdRaw = str_replace('field_', '', $key);

    if (!ctype_digit((string) $fieldIdRaw)) {
        continue;
    }

    $fieldId = (int) $fieldIdRaw;
    $fieldDefinition = $fieldDefinitions[$fieldId] ?? null;

    if (!$fieldDefinition || ($fieldDefinition['field_type'] ?? '') === 'file') {
        continue;
    }

    $fieldValues[$fieldId] = $value;
}

foreach ($uploadedFilePaths as $fieldId => $pathsArray) {
    $fieldValues[$fieldId] = implode(',', $pathsArray);
}

$dateFromValue = null;
$dateToValue = null;

foreach ($fields as $field) {
    if (($field['field_type'] ?? '') !== 'date') {
        continue;
    }

    $fieldId = (int) ($field['id'] ?? 0);
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
    $removeUploadedFiles($uploadedAbsolutePaths);
    $_SESSION['flash_error'] = t('date_range_invalid') !== 'date_range_invalid' ? t('date_range_invalid') : 'Datum do nemuze byt drive nez datum od.';
    redirect('index.php');
}

$model = new FormSubmissionModel($pdo);
$submissionId = $model->saveSubmission($formId, $langId, $userId, $fieldValues);

if ($submissionId) {
    $_SESSION['flash_success'] = t('form_submission_success') !== 'form_submission_success' ? t('form_submission_success') : 'Tvoje zadost byla uspesne odeslana!';
} else {
    $removeUploadedFiles($uploadedAbsolutePaths);
    $_SESSION['flash_error'] = t('form_submission_save_error') !== 'form_submission_save_error' ? t('form_submission_save_error') : 'Neco se pokazilo pri ukladani do databaze, zkus to prosim znovu.';
}

redirect('index.php');
