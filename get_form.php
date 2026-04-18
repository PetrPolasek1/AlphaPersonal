<?php
/**
 * -------------------------------------------------
 * Root Endpoint: Dynamic Form Loader
 * -------------------------------------------------
 * Vraci HTML fragment dynamickeho formulare.
 * Pouziva se pro modalni i mobilni vykresleni
 * formulare na dashboardu.
 */
session_start();

require_once 'core/db.php';
require_once 'core/language.php';
require_once 'core/helper.php';
require_once 'core/formManager.php';
require_auth();

$formId = (int) get('id', 0);

if ($formId <= 0) {
    echo '<div class="alert alert-danger">' . htmlspecialchars(t('form_missing_id') !== 'form_missing_id' ? t('form_missing_id') : 'Chybejici ID formulare.', ENT_QUOTES, 'UTF-8') . '</div>';
    exit;
}

$formManager = new FormManager($pdo);
$fields = $formManager->getFormFields($formId);

if (!$fields) {
    echo '<div class="alert alert-warning">' . htmlspecialchars(t('form_no_fields') !== 'form_no_fields' ? t('form_no_fields') : 'Tento formular zatim nema zadna pole.', ENT_QUOTES, 'UTF-8') . '</div>';
    exit;
}

$dateRangePairs = [];
$pendingDateFromFieldId = null;

foreach ($fields as $field) {
    if (($field['field_type'] ?? '') !== 'date') {
        continue;
    }

    $fieldCode = (string) ($field['code'] ?? '');

    if ($fieldCode === 'datum_od') {
        $pendingDateFromFieldId = (int) $field['id'];
        continue;
    }

    if ($fieldCode === 'datum_do' && $pendingDateFromFieldId !== null) {
        $dateRangePairs[$pendingDateFromFieldId] = (int) $field['id'];
        $pendingDateFromFieldId = null;
    }
}
?>
<form id="dynamic-request-form" action="process_form.php" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="form_id" value="<?php e($formId); ?>">

    <div class="row justify-content-center mt-5 mt-md-3">
        <div class="col-10 col-md-12">
            <div class="row g-3">
                <?php foreach ($fields as $field): ?>
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <?php e(t($field['label_localized_key'])); ?>
                            <?php if ($field['is_required']): ?> <span class="text-danger">*</span> <?php endif; ?>
                        </label>

                        <?php
                        $fieldId = (int) $field['id'];
                        $name = 'field_' . $fieldId;
                        $placeholder = t($field['placeholder_localized_key']);

                        $req = $field['is_required'] ? 'required' : '';
                        $maxAttr = $field['max_length'] ? 'maxlength="' . $field['max_length'] . '"' : '';
                        $jsClass = $field['max_length'] ? 'char-countable' : '';
                        $dateRangeAttrs = '';

                        if (($field['field_type'] ?? '') === 'date') {
                            if (isset($dateRangePairs[$fieldId])) {
                                $dateRangeAttrs = ' data-date-range-role="from" data-date-range-pair="field_' . $dateRangePairs[$fieldId] . '"';
                            } else {
                                $fromFieldId = array_search($fieldId, $dateRangePairs, true);
                                if ($fromFieldId !== false) {
                                    $dateRangeAttrs = ' data-date-range-role="to" data-date-range-pair="field_' . $fromFieldId . '"';
                                }
                            }
                        }

                        if (in_array($field['field_type'], ['text', 'number', 'date'], true)): ?>
                            <input type="<?php e($field['field_type']); ?>" name="<?php e($name); ?>" class="form-control <?php e($jsClass); ?>" placeholder="<?php e($placeholder); ?>" <?php echo $req; ?> <?php echo $maxAttr; ?><?php echo $dateRangeAttrs; ?>>

                        <?php elseif ($field['field_type'] === 'textarea'): ?>
                            <textarea name="<?php e($name); ?>" class="form-control <?php e($jsClass); ?>" rows="3" placeholder="<?php e($placeholder); ?>" <?php echo $req; ?> <?php echo $maxAttr; ?>></textarea>

                        <?php elseif ($field['field_type'] === 'file'): ?>
                            <input type="file" name="<?php e($name); ?>[]" class="form-control" multiple <?php echo $req; ?>>

                        <?php elseif ($field['field_type'] === 'select'): ?>
                            <select name="<?php e($name); ?>" class="form-select" <?php echo $req; ?>>
                                <option value=""><?php e($placeholder); ?></option>
                                <?php
                                $optStmt = $pdo->prepare("SELECT * FROM form_field_options WHERE id_form_field = ? AND is_active = 1");
                                $optStmt->execute([$fieldId]);
                                foreach ($optStmt->fetchAll() as $opt): ?>
                                    <option value="<?php e($opt['option_value']); ?>"><?php e(t($opt['label_localized_key'])); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <?php if ($field['max_length']): ?>
                            <div class="form-text text-end small text-muted counter-text">0 / <?php e($field['max_length']); ?> <?php e(t('characters_suffix') !== 'characters_suffix' ? t('characters_suffix') : 'znaku'); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="mt-5 mb-5 mt-md-4 mb-md-0 d-flex justify-content-center justify-content-md-end">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <?php e(t('submit_request_btn') !== 'submit_request_btn' ? t('submit_request_btn') : 'Odeslat zadost'); ?>
        </button>
    </div>

    <div class="alert alert-danger mt-3 d-none" id="date-range-error" role="alert">
        <?php e(t('date_range_invalid') !== 'date_range_invalid' ? t('date_range_invalid') : 'Datum do nemuze byt drive nez datum od.'); ?>
    </div>
</form>

<script>
    (function() {
        const form = document.getElementById('dynamic-request-form');
        if (!form) {
            return;
        }

        const errorBox = document.getElementById('date-range-error');
        const toInputs = form.querySelectorAll('input[data-date-range-role="to"]');
        const validationMessage = <?php echo json_encode(t('date_range_invalid') !== 'date_range_invalid' ? t('date_range_invalid') : 'Datum do nemuze byt drive nez datum od.'); ?>;

        function validateDateRange() {
            let isValid = true;

            toInputs.forEach(function(toInput) {
                const fromInputName = toInput.getAttribute('data-date-range-pair');
                const fromInput = fromInputName ? form.querySelector('input[name="' + fromInputName + '"]') : null;

                toInput.setCustomValidity('');

                if (!fromInput || !fromInput.value || !toInput.value) {
                    return;
                }

                if (toInput.value < fromInput.value) {
                    isValid = false;
                    toInput.setCustomValidity(validationMessage);
                }
            });

            if (errorBox) {
                errorBox.classList.toggle('d-none', isValid);
            }

            return isValid;
        }

        form.addEventListener('submit', function(event) {
            const isValid = validateDateRange();

            if (!isValid) {
                event.preventDefault();
                const firstInvalid = form.querySelector('input[data-date-range-role="to"]:invalid');
                if (firstInvalid) {
                    firstInvalid.reportValidity();
                    firstInvalid.focus();
                }
            }
        });

        toInputs.forEach(function(toInput) {
            const fromInputName = toInput.getAttribute('data-date-range-pair');
            const fromInput = fromInputName ? form.querySelector('input[name="' + fromInputName + '"]') : null;

            toInput.addEventListener('input', validateDateRange);
            if (fromInput) {
                fromInput.addEventListener('input', validateDateRange);
            }
        });
    })();
</script>
