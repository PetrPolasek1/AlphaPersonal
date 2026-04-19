<?php
/**
 * -------------------------------------------------
 * Controller: Messages
 * -------------------------------------------------
 * Řídí přehled zpráv, jejich čtení,
 * odesílání a změny stavu přes POST akce.
 */

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
        $perPage = 10;
        $currentMessageTab = (string) ($_GET['tab'] ?? 'recents');
        $currentMessageTab = $currentMessageTab === 'trash' ? 'trash' : 'recents';
        $activePage = max(1, (int) ($_GET['active_page'] ?? 1));
        $trashPage = max(1, (int) ($_GET['trash_page'] ?? 1));

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['trash', 'restore', 'delete'], true)) {
            require_csrf();

            $action = (string) $_POST['action'];
            $msgId = (int) ($_POST['id'] ?? 0);

            if ($msgId > 0) {
                $this->model->changeMessageStatus($msgId, $this->userId, $action);
            }

            header('Location: message.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
            require_csrf();

            $recipientEmail = normalize_email($_POST['recipient_email'] ?? '');
            $subject = trim((string) ($_POST['subject'] ?? ''));
            $content = trim((string) ($_POST['content'] ?? ''));
            $recipient = $this->model->getRecipientByEmail($recipientEmail);

            if (!is_valid_email($recipientEmail)) {
                $errorMsg = t('message_recipient_not_found') !== 'message_recipient_not_found' ? t('message_recipient_not_found') : 'Uživatel s tímto e-mailem nebyl nalezen.';
            } elseif ($subject === '' || $content === '') {
                $errorMsg = t('message_validation_failed') !== 'message_validation_failed' ? t('message_validation_failed') : 'Předmět i obsah zprávy jsou povinné.';
            } elseif (!$recipient) {
                $baseMessage = t('message_recipient_not_found') !== 'message_recipient_not_found' ? t('message_recipient_not_found') : 'Uživatel s tímto e-mailem nebyl nalezen.';
                $errorMsg = $baseMessage . ' (' . htmlspecialchars($recipientEmail, ENT_QUOTES, 'UTF-8') . ')';
            } elseif ((int) ($recipient['is_active'] ?? 0) !== 1) {
                $errorMsg = t('message_recipient_inactive') !== 'message_recipient_inactive' ? t('message_recipient_inactive') : 'Účet příjemce není aktivní.';
            } elseif (!empty($recipient['locked_until']) && strtotime((string) $recipient['locked_until']) > time()) {
                $errorMsg = t('message_recipient_locked') !== 'message_recipient_locked' ? t('message_recipient_locked') : 'Účet příjemce je dočasně uzamčen nebo vypršel.';
            } else {
                $this->model->createMessage($this->userId, $recipient['id'], $subject, $content);
                header('Location: message.php?success=1');
                exit;
            }
        }

        $activeMessagesTotal = $this->model->getMessagesCount($this->userId, 0);
        $trashedMessagesTotal = $this->model->getMessagesCount($this->userId, 1);
        $activePages = max(1, (int) ceil($activeMessagesTotal / $perPage));
        $trashPages = max(1, (int) ceil($trashedMessagesTotal / $perPage));
        $activePage = min($activePage, $activePages);
        $trashPage = min($trashPage, $trashPages);

        $activeMessages = $this->model->getMessages($this->userId, 0, $perPage, ($activePage - 1) * $perPage);
        $trashedMessages = $this->model->getMessages($this->userId, 1, $perPage, ($trashPage - 1) * $perPage);
        $fullName = $this->fullName;

        $unreadMessagesCount = $this->model->getUnreadMessagesCount($this->userId);
        $updatedRequestsCount = $this->model->getUpdatedRequestsCount($this->userId);

        require_once __DIR__ . '/../view/message-view.php';
    }
}
