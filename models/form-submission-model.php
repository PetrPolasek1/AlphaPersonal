<?php
/**
 * -------------------------------------------------
 * Model: Form Submission
 * -------------------------------------------------
 * Uklada odeslana formularova podani.
 * Mapuje hodnoty poli podle typu
 * do spravnych databazovych sloupcu.
 */

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
                (id_form, id_lang, id_client, ip_address, user_agent, is_read) 
                VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $id_form,
                $id_lang,
                $id_client,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                1
            ]);

            $submissionId = $this->pdo->lastInsertId();

            // 2. Vložíme jednotlivé hodnoty polí
            // Pro jednoduchost ukládáme do value_string/value_text, 
            // ale v ostrém provozu by se zde rozlišovaly typy (int, date atd.)
            $fieldDefinitions = $this->getFieldDefinitions(array_keys($fieldValues));
            $valueStmt = $this->pdo->prepare("INSERT INTO form_submission_values 
                (id_submission, id_form_field, value_text, value_string, value_int, value_decimal, value_date, value_datetime, value_bool) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($fieldValues as $fieldId => $value) {
                // Pokud je text delší než 500 znaků, uložíme ho do value_text
                $fieldId = (int) $fieldId;
                $fieldType = $fieldDefinitions[$fieldId]['field_type'] ?? 'text';
                $typedValue = $this->mapFieldValue($fieldType, $value);

                $valueStmt->execute([
                    $submissionId,
                    $fieldId,
                    $typedValue['value_text'],
                    $typedValue['value_string'],
                    $typedValue['value_int'],
                    $typedValue['value_decimal'],
                    $typedValue['value_date'],
                    $typedValue['value_datetime'],
                    $typedValue['value_bool']
                ]);
            }

            $this->pdo->commit();
            return $submissionId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Zde by mělo být logování chyby
            app_log("Chyba ukládání formuláře: " . $e->getMessage());
            return false;
        }
    }

    private function getFieldDefinitions(array $fieldIds): array {
        $fieldIds = array_values(array_unique(array_map('intval', $fieldIds)));

        if (empty($fieldIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($fieldIds), '?'));
        $stmt = $this->pdo->prepare("SELECT id, field_type FROM form_fields WHERE id IN ($placeholders)");
        $stmt->execute($fieldIds);

        $definitions = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $definitions[(int) $row['id']] = $row;
        }

        return $definitions;
    }

    private function mapFieldValue(string $fieldType, $value): array {
        $payload = [
            'value_text' => null,
            'value_string' => null,
            'value_int' => null,
            'value_decimal' => null,
            'value_date' => null,
            'value_datetime' => null,
            'value_bool' => null,
        ];

        $normalizedValue = $this->normalizeScalarValue($value);

        if ($normalizedValue === null) {
            return $payload;
        }

        switch ($fieldType) {
            case 'textarea':
                $payload['value_text'] = $normalizedValue;
                return $payload;

            case 'date':
                $payload['value_date'] = $this->normalizeDateValue($normalizedValue);
                return $payload;

            case 'datetime':
                $payload['value_datetime'] = $this->normalizeDateTimeValue($normalizedValue);
                return $payload;

            case 'number':
                $numericValue = str_replace(',', '.', $normalizedValue);

                if (preg_match('/^-?\d+$/', $numericValue)) {
                    $payload['value_int'] = (int) $numericValue;
                    return $payload;
                }

                if (is_numeric($numericValue)) {
                    $payload['value_decimal'] = (float) $numericValue;
                    return $payload;
                }

                return $this->assignStringValue($normalizedValue, $payload);

            case 'checkbox':
                $payload['value_bool'] = $this->normalizeBooleanValue($normalizedValue);
                return $payload;

            case 'text':
            case 'email':
            case 'tel':
            case 'select':
            case 'radio':
            case 'checkbox_group':
            case 'file':
            default:
                return $this->assignStringValue($normalizedValue, $payload);
        }
    }

    private function normalizeScalarValue($value): ?string {
        if (is_array($value)) {
            $value = implode(', ', array_filter(array_map(static function ($item) {
                $item = trim((string) $item);
                return $item === '' ? null : $item;
            }, $value), static function ($item) {
                return $item !== null;
            }));
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function assignStringValue(string $value, array $payload): array {
        if (strlen($value) > 500) {
            $payload['value_text'] = $value;
            return $payload;
        }

        $payload['value_string'] = $value;
        return $payload;
    }

    private function normalizeDateValue(string $value): ?string {
        $formats = ['Y-m-d', 'd.m.Y', 'd-m-Y'];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $value);

            if ($date instanceof DateTime) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function normalizeDateTimeValue(string $value): ?string {
        $formats = ['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s'];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $value);

            if ($date instanceof DateTime) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function normalizeBooleanValue(string $value): int {
        $truthyValues = ['1', 'true', 'on', 'yes'];
        return in_array(strtolower($value), $truthyValues, true) ? 1 : 0;
    }
}
