<?php
// controllers/MessageController.php

$fullName = $_SESSION['user_name'] ?? (t('default_user_name') !== 'default_user_name' ? t('default_user_name') : 'Uživatel');

class MessageController {
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
        $errorMsg = '';
        $successMsg = '';

        if (isset($_GET['success']) && $_GET['success'] == 1) {
            $successMsg = t('message_sent_success') !== 'message_sent_success' ? t('message_sent_success') : 'Zpráva byla úspěšně odeslána.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
            require_csrf();
            $msgId = (int) ($_POST['id'] ?? 0);
            $success = $msgId > 0 ? $this->model->markMessageAsRead($msgId, $this->userId) : false;

            json_response([
                'success' => (bool) $success,
                'unreadMessagesCount' => (int) $this->model->getUnreadMessagesCount($this->userId),
            ]);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
            require_csrf();
            $recipientEmail = trim($_POST['recipient_email']);
            $subject = trim($_POST['subject']);
            $content = trim($_POST['content']);

            $recipient = $this->model->getRecipientByEmail($recipientEmail);

            if (!$recipient) {
                $baseMessage = t('message_recipient_not_found') !== 'message_recipient_not_found' ? t('message_recipient_not_found') : 'Uživatel s tímto e-mailem nebyl nalezen.';
                $errorMsg = $baseMessage . ' (' . htmlspecialchars($recipientEmail, ENT_QUOTES, 'UTF-8') . ')';
            } elseif ($recipient['is_active'] != 1) {
                $errorMsg = t('message_recipient_inactive') !== 'message_recipient_inactive' ? t('message_recipient_inactive') : 'Účet příjemce není aktivní.';
            } elseif (!empty($recipient['locked_until']) && strtotime($recipient['locked_until']) > time()) {
                $errorMsg = t('message_recipient_locked') !== 'message_recipient_locked' ? t('message_recipient_locked') : 'Účet příjemce je dočasně uzamčen nebo vypršel.';
            } else {
                $this->model->createMessage($this->userId, $recipient['id'], $subject, $content);
                header("Location: message.php?success=1");
                exit;
            }
        }

        if (isset($_GET['action']) && isset($_GET['id'])) {
            $action = $_GET['action'];
            $msgId = (int) $_GET['id'];

            $this->model->changeMessageStatus($msgId, $this->userId, $action);
            header("Location: message.php");
            exit;
        }

        $activeMessages = $this->model->getMessages($this->userId, 0);
        $trashedMessages = $this->model->getMessages($this->userId, 1);
        $fullName = $this->fullName;

        $unreadMessagesCount = $this->model->getUnreadMessagesCount($this->userId);
        $updatedRequestsCount = $this->model->getUpdatedRequestsCount($this->userId);

        require_once __DIR__ . '/../view/message-view.php';
    }
}
?>
