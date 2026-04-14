<?php
// controller/index-controller.php

class IndexController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest() {
        // Získání dat ze session (z loginu)
        $userId = $_SESSION['user_id'] ?? 1; // Přidáno získání ID pro notifikace
        $fullName = $_SESSION['user_name'] ?? 'Uživateli'; 
        $firstName = explode(' ', trim($fullName))[0];

        // Vytažení formulářů 
        $forms = $this->model->getActiveForms();

        // Výpočet sloupců a řádků pro dynamický grid
        $count = count($forms);
        $gridCols = 4;
        $gridRows = max(1, ceil($count / $gridCols));

        // --- NOTIFIKACE (načtení čísel pro sidebar) ---
        $unreadMessagesCount = $this->model->getUnreadMessagesCount($userId);
        $updatedRequestsCount = $this->model->getUpdatedRequestsCount($userId);

        // Načtení šablony
        require_once __DIR__ . '/../view/index-view.php';
    }
}
?>