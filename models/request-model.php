<?php
// models/request-model.php

class RequestModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getRequests($userId) {
        $sql = "SELECT 
                    f.title_localized_key AS typ_formulare, 
                    fs.id_form AS form_id,
                    fs.id AS submission_id,
                    fs.is_read,
                    fs.status AS status, 
                    fs.submitted_at AS datum,
                    /* SQL vyhledá hodnotu pole 48 POUZE pro toto konkrétní ID submission */
                    (SELECT v.value_string 
                     FROM form_submission_values v 
                     JOIN form_fields ff ON ff.id = v.id_form_field
                     WHERE v.id_submission = fs.id 
                     AND ff.code = 'predmet'
                     LIMIT 1) AS klientsky_nazev
                FROM form_submissions fs
                JOIN forms f ON fs.id_form = f.id
                WHERE fs.id_client = :user_id
                ORDER BY fs.submitted_at DESC";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }

    public function getRequestDetail($submissionId, $userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                fs.id AS submission_id,
                fs.id_form,
                fs.status,
                fs.submitted_at,
                f.title_localized_key AS form_title_key
            FROM form_submissions fs
            JOIN forms f ON fs.id_form = f.id
            WHERE fs.id = ? AND fs.id_client = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $submissionId, (int) $userId]);
        $detail = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$detail) {
            return null;
        }

        $fieldsStmt = $this->pdo->prepare("
            SELECT
                ff.id AS field_id,
                ff.field_type,
                ff.label_localized_key,
                ff.position,
                v.value_text,
                v.value_string,
                v.value_int,
                v.value_decimal,
                v.value_date,
                v.value_datetime,
                v.value_bool
            FROM form_submission_values v
            JOIN form_fields ff ON ff.id = v.id_form_field
            WHERE v.id_submission = ?
            ORDER BY ff.position ASC, ff.id ASC
        ");
        $fieldsStmt->execute([(int) $submissionId]);
        $detail['fields'] = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);

        return $detail;
    }

    public function getOptionLabelsForFields(array $fieldIds) {
        $fieldIds = array_values(array_unique(array_filter(array_map('intval', $fieldIds))));

        if (empty($fieldIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($fieldIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT id_form_field, option_value, label_localized_key
            FROM form_field_options
            WHERE id_form_field IN ($placeholders) AND is_active = 1
        ");
        $stmt->execute($fieldIds);

        $labels = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $labels[(int) $row['id_form_field']][(string) $row['option_value']] = (string) $row['label_localized_key'];
        }

        return $labels;
    }

    // --- PŘIDÁNO PRO NOTIFIKACE ---
    public function getUnreadMessagesCount($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM alpha_zpravy WHERE recipient_id = ? AND is_deleted = 0 AND is_read = 0");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function getUpdatedRequestsCount($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM form_submissions WHERE id_client = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function markRequestAsRead($submissionId, $userId) {
        $stmt = $this->pdo->prepare("UPDATE form_submissions SET is_read = 1 WHERE id = ? AND id_client = ?");
        return $stmt->execute([$submissionId, $userId]);
    }

    public function userCanAccessFile(int $userId, string $fileName): bool {
        $normalizedFileName = trim($fileName);

        if ($userId <= 0 || $normalizedFileName === '') {
            return false;
        }

        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM form_submission_values v
            JOIN form_submissions fs ON fs.id = v.id_submission
            WHERE fs.id_client = ?
              AND (
                    FIND_IN_SET(?, REPLACE(COALESCE(v.value_string, ''), ', ', ',')) > 0
                 OR FIND_IN_SET(?, REPLACE(COALESCE(v.value_text, ''), ', ', ',')) > 0
              )
            LIMIT 1
        ");
        $stmt->execute([$userId, $normalizedFileName, $normalizedFileName]);

        return (bool) $stmt->fetchColumn();
    }

}
?>
