<?php
/**
 * -------------------------------------------------
 * Model: Messages
 * -------------------------------------------------
 * Datova vrstva modulu zprav.
 * Pracuje s inboxem, kosem, ctenim
 * a vytvarenim zprav mezi uzivateli.
 */

class MessageModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getRecipientByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id, is_active, locked_until FROM alpha_pracovnici_uzivatele WHERE login_email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function createMessage($senderId, $recipientId, $subject, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO alpha_zpravy (sender_id, recipient_id, subject, content) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$senderId, $recipientId, $subject, $content]);
    }

    public function changeMessageStatus($msgId, $userId, $action) {
        if ($action === 'trash') {
            $stmt = $this->pdo->prepare("UPDATE alpha_zpravy SET is_deleted = 1 WHERE id = ? AND recipient_id = ?");
        } elseif ($action === 'restore') {
            $stmt = $this->pdo->prepare("UPDATE alpha_zpravy SET is_deleted = 0 WHERE id = ? AND recipient_id = ?");
        } elseif ($action === 'delete') {
            $stmt = $this->pdo->prepare("DELETE FROM alpha_zpravy WHERE id = ? AND recipient_id = ?");
        } else {
            return false;
        }
        return $stmt->execute([$msgId, $userId]);
    }

    public function getMessages($userId, $isDeleted, ?int $limit = null, int $offset = 0) {
        $orderBy = ((int) $isDeleted === 0)
            ? "z.is_read ASC, z.created_at DESC, z.id DESC"
            : "z.created_at DESC, z.id DESC";

        $sql = "
            SELECT z.*, (SELECT login_email FROM alpha_pracovnici_uzivatele WHERE id = z.sender_id LIMIT 1) AS sender_email
            FROM alpha_zpravy z
            WHERE z.recipient_id = ? AND z.is_deleted = ? 
            ORDER BY {$orderBy}
        ";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
        }

        $stmt = $this->pdo->prepare($sql);

        if ($limit !== null) {
            $stmt->bindValue(1, (int) $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, (int) $isDeleted, PDO::PARAM_INT);
            $stmt->bindValue(3, (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(4, max(0, (int) $offset), PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt->execute([$userId, $isDeleted]);
        }

        return $stmt->fetchAll();
    }

    public function getMessagesCount($userId, $isDeleted) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM alpha_zpravy
            WHERE recipient_id = ? AND is_deleted = ?
        ");
        $stmt->execute([(int) $userId, (int) $isDeleted]);
        return (int) $stmt->fetchColumn();
    }

    // --- PŘIDÁNO PRO NOTIFIKACE ---
    public function getReadActiveMessagesCount($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM alpha_zpravy
            WHERE recipient_id = ? AND is_deleted = 0 AND is_read = 1
        ");
        $stmt->execute([(int) $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function trashAllReadMessages($userId) {
        $stmt = $this->pdo->prepare("
            UPDATE alpha_zpravy
            SET is_deleted = 1
            WHERE recipient_id = ? AND is_deleted = 0 AND is_read = 1
        ");
        return $stmt->execute([(int) $userId]);
    }

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

    public function markMessageAsRead($msgId, $userId) {
        $stmt = $this->pdo->prepare("UPDATE alpha_zpravy SET is_read = 1 WHERE id = ? AND recipient_id = ? AND is_deleted = 0");
        return $stmt->execute([$msgId, $userId]);
    }
}
?>
