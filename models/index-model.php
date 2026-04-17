<?php
// models/index-model.php

class IndexModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getActiveForms() {
        try {
            $stmt = $this->pdo->query('SELECT * FROM forms WHERE is_active = 1 ORDER BY position ASC');
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
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
}
?>
