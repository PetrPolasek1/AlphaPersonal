<?php
/**
 * -------------------------------------------------
 * Controller: Requests
 * -------------------------------------------------
 * Zobrazuje přehled klientských požadavků.
 * Současně vrací detail požadavku jako JSON
 * pro AJAX dialog.
 */

class RequestController {
    private $model;
    private $userId;
    private $fullName;

    public function __construct($model) {
        $this->model = $model;

        $this->userId = (int) ($_SESSION['user_id'] ?? 0);
        $this->fullName = $_SESSION['user_name'] ?? (t('default_user_name') !== 'default_user_name' ? t('default_user_name') : 'Uživatel');
    }

    public function handleRequest() {
        $this->userId = (int) ($_SESSION['user_id'] ?? 0);
        $perPage = 10;
        $requestsPage = max(1, (int) ($_GET['page'] ?? 1));

        if (is_post() && post('action') === 'detail') {
            require_csrf();
            $this->handleDetailRequest();
            return;
        }

        $requestsTotal = $this->model->getRequestsCount($this->userId);
        $requestsPages = max(1, (int) ceil($requestsTotal / $perPage));
        $requestsPage = min($requestsPage, $requestsPages);
        $requests = $this->model->getRequests($this->userId, $perPage, ($requestsPage - 1) * $perPage);
        $fullName = $this->fullName;

        $unreadMessagesCount = $this->model->getUnreadMessagesCount($this->userId);
        $updatedRequestsCount = $this->model->getUpdatedRequestsCount($this->userId);

        require_once __DIR__ . '/../view/request-view.php';
    }

    private function handleDetailRequest() {
        $submissionId = (int) post('id', 0);
        $detail = $submissionId > 0 ? $this->model->getRequestDetail($submissionId, $this->userId) : null;

        if (!$detail) {
            json_response([
                'success' => false,
                'message' => t('request_not_found') !== 'request_not_found' ? t('request_not_found') : 'Požadavek nebyl nalezen.'
            ], 404);
        }

        $this->model->markRequestAsRead($submissionId, $this->userId);

        $fieldIds = array_column($detail['fields'], 'field_id');
        $optionLabels = $this->model->getOptionLabelsForFields($fieldIds);
        $rows = [];

        foreach ($detail['fields'] as $field) {
            $fieldType = (string) ($field['field_type'] ?? 'text');
            $value = $this->normalizeFieldValue($field);

            $row = [
                'label' => t($field['label_localized_key'] ?? ''),
                'type' => $fieldType,
                'value' => null,
                'files' => [],
            ];

            if ($fieldType === 'file') {
                $files = array_filter(array_map('trim', explode(',', (string) $value)));

                foreach ($files as $file) {
                    $safeFile = basename($file);

                    if ($safeFile === '') {
                        continue;
                    }

                    $row['files'][] = [
                        'name' => $safeFile,
                        'url' => 'download-document.php?file=' . rawurlencode($safeFile),
                    ];
                }
            } elseif (isset($optionLabels[(int) $field['field_id']]) && $value !== null && $value !== '') {
                $parts = array_filter(array_map('trim', explode(',', (string) $value)));
                $mappedParts = [];

                foreach ($parts as $part) {
                    $labelKey = $optionLabels[(int) $field['field_id']][$part] ?? null;
                    $mappedParts[] = $labelKey ? t($labelKey) : $part;
                }

                $row['value'] = implode(', ', $mappedParts);
            } else {
                $row['value'] = $value;
            }

            $rows[] = $row;
        }

        json_response([
            'success' => true,
            'submissionId' => $submissionId,
            'updatedRequestsCount' => (int) $this->model->getUpdatedRequestsCount($this->userId),
            'detail' => [
                'title' => t($detail['form_title_key'] ?? ''),
                'status' => $detail['status'],
                'submitted_at' => $detail['submitted_at'],
                'status_timeline' => [
                    'new' => $detail['submitted_at'],
                    'processing' => $detail['processing_at'] ?? null,
                    'done' => $detail['done_at'] ?? null,
                    'rejected' => $detail['rejected_at'] ?? null,
                ],
                'rows' => $rows,
            ],
        ]);
    }

    private function normalizeFieldValue(array $field) {
        if ($field['value_text'] !== null && $field['value_text'] !== '') {
            return $field['value_text'];
        }

        if ($field['value_string'] !== null && $field['value_string'] !== '') {
            return $field['value_string'];
        }

        if ($field['value_int'] !== null) {
            return (string) $field['value_int'];
        }

        if ($field['value_decimal'] !== null) {
            return (string) $field['value_decimal'];
        }

        if ($field['value_date'] !== null && $field['value_date'] !== '') {
            return date('d.m.Y', strtotime((string) $field['value_date']));
        }

        if ($field['value_datetime'] !== null && $field['value_datetime'] !== '') {
            return date('d.m.Y H:i', strtotime((string) $field['value_datetime']));
        }

        if ($field['value_bool'] !== null) {
            return (int) $field['value_bool'] === 1
                ? (t('boolean_yes') !== 'boolean_yes' ? t('boolean_yes') : 'Ano')
                : (t('boolean_no') !== 'boolean_no' ? t('boolean_no') : 'Ne');
        }

        return '';
    }
}
?>
