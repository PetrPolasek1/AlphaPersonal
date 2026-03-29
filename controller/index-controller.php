<?php
// controller/index-controller.php

class IndexController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest() {
        // Získání dat ze session (z loginu)
        $fullName = $_SESSION['user_name'] ?? 'Uživateli'; 
        $firstName = explode(' ', trim($fullName))[0];

        // Vytažení modulů přes Model
        $modules = $this->model->getActiveModules();

        // Výpočet sloupců a řádků pro dynamický grid
        $count = count($modules);
        $gridCols = ($count <= 6) ? 2 : 3;
        $gridRows = max(1, ceil($count / $gridCols));

        // Načtení šablony
        require_once __DIR__ . '/../view/index-view.php';
    }
}
?>