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
                    fs.id AS submission_id,
                    fs.status AS status, 
                    fs.submitted_at AS datum,
                    /* SQL vyhledá hodnotu pole 48 POUZE pro toto konkrétní ID submission */
                    (SELECT v.value_string 
                     FROM form_submission_values v 
                     WHERE v.id_submission = fs.id 
                     AND v.id_form_field = 48 
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

    // --- PŘIDÁNO PRO NOTIFIKACE ---
    public function getUnreadMessagesCount($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM alpha_zpravy WHERE recipient_id = ? AND is_deleted = 0 AND is_read = 0");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function getUpdatedRequestsCount($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM form_submissions WHERE id_client = ? AND status IN ('new', 'zmeneno') AND is_read = 0");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function markRequestAsRead($submissionId, $userId) {
        $stmt = $this->pdo->prepare("UPDATE form_submissions SET is_read = 1 WHERE id = ? AND id_client = ?");
        return $stmt->execute([$submissionId, $userId]);
    }
}
?>