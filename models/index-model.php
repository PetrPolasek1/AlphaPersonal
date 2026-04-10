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
}
?>