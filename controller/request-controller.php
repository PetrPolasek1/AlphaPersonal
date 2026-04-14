<?php
// controllers/request-controller.php

class RequestController {
    private $model;
    private $userId;
    private $fullName;

    public function __construct($model) {
        $this->model = $model;
        
        $this->userId = $_SESSION['user_id'] ?? 1; 
        $this->fullName = $_SESSION['user_name'] ?? 'Uživatel';
    }

    public function handleRequest() {
        $requests = $this->model->getRequests($this->userId);
        $fullName = $this->fullName;

        // --- NOTIFIKACE (načtení čísel pro sidebar) ---
        $unreadMessagesCount = $this->model->getUnreadMessagesCount($this->userId);
        $updatedRequestsCount = $this->model->getUpdatedRequestsCount($this->userId);

        require_once __DIR__ . '/../view/request-view.php';
    }
}
?>