<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

$columns = [];
$stmt = $pdo->query("SHOW COLUMNS FROM form_submissions");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $columns[] = $row['Field'];
}

$missingColumns = array_diff(['processing_at', 'done_at', 'rejected_at'], $columns);

if (!empty($missingColumns)) {
    $alterParts = [];

    if (in_array('processing_at', $missingColumns, true)) {
        $alterParts[] = "ADD COLUMN `processing_at` datetime DEFAULT NULL AFTER `submitted_at`";
    }

    if (in_array('done_at', $missingColumns, true)) {
        $alterParts[] = "ADD COLUMN `done_at` datetime DEFAULT NULL AFTER `processing_at`";
    }

    if (in_array('rejected_at', $missingColumns, true)) {
        $alterParts[] = "ADD COLUMN `rejected_at` datetime DEFAULT NULL AFTER `done_at`";
    }

    $pdo->exec("ALTER TABLE `form_submissions` " . implode(', ', $alterParts));
}

// Backfill only current visible state from submitted_at for legacy rows with no history.
$pdo->exec("
    UPDATE `form_submissions`
    SET
        `processing_at` = CASE
            WHEN `status` = 'processing' AND `processing_at` IS NULL THEN `submitted_at`
            ELSE `processing_at`
        END,
        `done_at` = CASE
            WHEN `status` = 'done' AND `done_at` IS NULL THEN `submitted_at`
            ELSE `done_at`
        END,
        `rejected_at` = CASE
            WHEN `status` = 'rejected' AND `rejected_at` IS NULL THEN `submitted_at`
            ELSE `rejected_at`
        END
");

$pdo->exec("DROP TRIGGER IF EXISTS `form_submissions_before_insert_status_timestamps`");
$pdo->exec("DROP TRIGGER IF EXISTS `form_submissions_before_update_status_timestamps`");

$pdo->exec("
    CREATE TRIGGER `form_submissions_before_insert_status_timestamps`
    BEFORE INSERT ON `form_submissions`
    FOR EACH ROW
    BEGIN
        IF NEW.`status` = 'processing' AND NEW.`processing_at` IS NULL THEN
            SET NEW.`processing_at` = CURRENT_TIMESTAMP();
            SET NEW.`done_at` = NULL;
            SET NEW.`rejected_at` = NULL;
        END IF;

        IF NEW.`status` = 'done' AND NEW.`done_at` IS NULL THEN
            SET NEW.`done_at` = CURRENT_TIMESTAMP();
            SET NEW.`rejected_at` = NULL;
        END IF;

        IF NEW.`status` = 'rejected' AND NEW.`rejected_at` IS NULL THEN
            SET NEW.`rejected_at` = CURRENT_TIMESTAMP();
            SET NEW.`done_at` = NULL;
        END IF;
    END
");

$pdo->exec("
    CREATE TRIGGER `form_submissions_before_update_status_timestamps`
    BEFORE UPDATE ON `form_submissions`
    FOR EACH ROW
    BEGIN
        IF NOT (OLD.`status` <=> NEW.`status`) THEN
            IF NEW.`status` = 'processing' THEN
                IF NEW.`processing_at` IS NULL THEN
                    SET NEW.`processing_at` = CURRENT_TIMESTAMP();
                END IF;

                SET NEW.`done_at` = NULL;
                SET NEW.`rejected_at` = NULL;
            END IF;

            IF NEW.`status` = 'done' THEN
                IF NEW.`done_at` IS NULL THEN
                    SET NEW.`done_at` = CURRENT_TIMESTAMP();
                END IF;

                SET NEW.`rejected_at` = NULL;
            END IF;

            IF NEW.`status` = 'rejected' THEN
                IF NEW.`rejected_at` IS NULL THEN
                    SET NEW.`rejected_at` = CURRENT_TIMESTAMP();
                END IF;

                SET NEW.`done_at` = NULL;
            END IF;
        END IF;
    END
");

echo "form_submissions migration complete.\n";
