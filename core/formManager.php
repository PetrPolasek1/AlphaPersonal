<?php
/**
 * -------------------------------------------------
 * Core: Form Manager
 * -------------------------------------------------
 * Pomocna vrstva pro praci s definicemi formularu.
 * Poskytuje načtení polí a starší helper
 * pro ukládání requestů.
 */

class FormManager
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getFormFields($formId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM form_fields WHERE id_form = ? AND is_active = 1 ORDER BY position ASC");
        $stmt->execute([$formId]);
        return $stmt->fetchAll();
    }

    public function saveRequest($userId, $formId, $postData, $uploadedFilePaths = [])
    {
        try
        {
            $this ->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO user_requests (id_user, id_form, status, created_at) VALUES (?, ?, 'pending', NOW())");
            $stmt->execute([$userId, $formId]);
            $requestId = $this->pdo->lastInsertId();

            $stmtVal = $this->pdo->prepare("INSERT INTO user_request_values (id_request, id_form_field, field_value) VALUES (?, ?, ?)");

            foreach ($postData as $key => $value)
            {
                if(strpos($key, 'field_') === 0)
                {
                    $fieldId = str_replace('field_', '', $key);
                    $cleanVal = is_array($value) ? implode(', ', $value) : $value;
                    $stmtVal->execute([$requestId, $fieldId, $cleanVal]);
                }
            }

            foreach ($uploadedFilePaths as $fieldId => $paths) {
                $pathsString = implode(', ', $paths); 
                $stmtVal->execute([$requestId, $fieldId, $pathsString]);
            }

            $this->pdo->commit();
            return $requestId;

        } catch (Exception $e)
        {
            $this->pdo->rollBack();
            app_log("Chyba FormManager: " . $e->getMessage());
            return false;
        }
    }
}
?>
