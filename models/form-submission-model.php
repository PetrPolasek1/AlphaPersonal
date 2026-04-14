<?php
// models/form-submission-model.php

class FormSubmissionModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Uloží odeslaný formulář do DB (Transakčně)
     */
    public function saveSubmission($id_form, $id_lang, $id_client, $fieldValues) {
        try {
            $this->pdo->beginTransaction();

            // 1. Vložíme "obálku" do form_submissions
            $stmt = $this->pdo->prepare("INSERT INTO form_submissions 
                (id_form, id_lang, id_client, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $id_form,
                $id_lang,
                $id_client,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            $submissionId = $this->pdo->lastInsertId();

            // 2. Vložíme jednotlivé hodnoty polí
            // Pro jednoduchost ukládáme do value_string/value_text, 
            // ale v ostrém provozu by se zde rozlišovaly typy (int, date atd.)
            $valueStmt = $this->pdo->prepare("INSERT INTO form_submission_values 
                (id_submission, id_form_field, value_string, value_text) 
                VALUES (?, ?, ?, ?)");

            foreach ($fieldValues as $fieldId => $value) {
                // Pokud je text delší než 500 znaků, uložíme ho do value_text
                $isLong = strlen((string)$value) > 500;
                
                $valueStmt->execute([
                    $submissionId,
                    $fieldId,
                    $isLong ? null : $value,
                    $isLong ? $value : null
                ]);
            }

            $this->pdo->commit();
            return $submissionId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Zde by mělo být logování chyby
            return false;
        }
    }
}