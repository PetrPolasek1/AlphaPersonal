<?php
// controllers/MessageController.php

$fullName = $_SESSION['user_name'] ?? 'Uživatel';

class MessageController {
    private $model;
    private $userId;
    private $fullName;

    public function __construct($model) {
        $this->model = $model;
        
        // Zde ideálně využíváš data ze session
        $this->userId = $_SESSION['user_id'] ?? 1; 
        $this->fullName = $_SESSION['user_name'] ?? 'Uživatel';
    }

    public function handleRequest() {
        $errorMsg = '';
        $successMsg = '';

        if (isset($_GET['success']) && $_GET['success'] == 1) {
            $successMsg = "Zpráva byla úspěšně odeslána.";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
            $recipientEmail = trim($_POST['recipient_email']);
            $subject = trim($_POST['subject']);
            $content = trim($_POST['content']);

            $recipient = $this->model->getRecipientByEmail($recipientEmail);

            if (!$recipient) {
                $errorMsg = "Uživatel s e-mailem '" . htmlspecialchars($recipientEmail) . "' nebyl nalezen.";
            } elseif ($recipient['is_active'] != 1) {
                $errorMsg = "Účet příjemce není aktivní.";
            } elseif (!empty($recipient['locked_until']) && strtotime($recipient['locked_until']) > time()) {
                $errorMsg = "Účet příjemce je dočasně uzamčen nebo vypršel.";
            } else {
                $this->model->createMessage($this->userId, $recipient['id'], $subject, $content);
                header("Location: message.php?success=1");
                exit;
            }
        }

        if (isset($_GET['action']) && isset($_GET['id'])) {
            $action = $_GET['action'];
            $msgId = (int)$_GET['id'];
            
            $this->model->changeMessageStatus($msgId, $this->userId, $action);
            header("Location: message.php");
            exit;
        }

        $activeMessages = $this->model->getMessages($this->userId, 0);
        $trashedMessages = $this->model->getMessages($this->userId, 1);
        $fullName = $this->fullName;


        require_once __DIR__ . '/../view/message-view.php';
    }
}
?>