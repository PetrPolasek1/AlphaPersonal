<?php
// models/index-model.php

class IndexModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getActiveModules() {
        try {
            $stmt = $this->pdo->query('SELECT * FROM alpha_moduly WHERE is_active = 1 ORDER BY order_index ASC');
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }
}
?>