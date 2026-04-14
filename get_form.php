<?php
// get_form.php
session_start();

require_once 'core/db.php';
require_once 'core/language.php';
require_once 'core/helper.php';
require_once 'core/FormManager.php';

$formId = get('id');

if (!$formId) {
    echo '<div class="alert alert-danger">Chybějící ID formuláře.</div>';
    exit;
}

$formManager = new FormManager($pdo);
$fields = $formManager->getFormFields($formId);

if (!$fields) {
    echo '<div class="alert alert-warning">Tento formulář zatím nemá žádná pole.</div>';
    exit;
}
?>
<form id="dynamic-request-form" action="process_form.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="form_id" value="<?php e($formId); ?>">
    
    <div class="row justify-content-center mt-5 mt-md-3">
        <div class="col-10 col-md-12">
            <div class="row g-3">
                <?php foreach ($fields as $field): ?>
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <?php e(t($field['label_localized_key']));?>
                            <?php if ($field['is_required']): ?> <span class="text-danger">*</span> <?php endif; ?>
                        </label>
                            
                        <?php 
                        $name = "field_" . $field['id'];
                        $placeholder = t($field['placeholder_localized_key']); 
                            
                        $req = $field['is_required'] ? 'required' : '';
                        $maxAttr = $field['max_length'] ? 'maxlength="' . $field['max_length'] . '"' : '';
                        $jsClass = $field['max_length'] ? 'char-countable' : '';
                            
                        if (in_array($field['field_type'], ['text', 'number', 'date'])): ?>
                            <input type="<?php e($field['field_type']); ?>" name="<?php e($name); ?>" class="form-control <?php e($jsClass); ?>" placeholder="<?php e($placeholder); ?>" <?php echo $req; ?> <?php echo $maxAttr; ?>>
                        
                        <?php elseif ($field['field_type'] === 'textarea'): ?>
                            <textarea name="<?php e($name); ?>" class="form-control <?php e($jsClass); ?>" rows="3" placeholder="<?php e($placeholder); ?>" <?php echo $req; ?> <?php echo $maxAttr; ?>></textarea>
                        
                        <?php elseif ($field['field_type'] === 'file'): ?>
                            <input type="file" name="<?php e($name); ?>[]" class="form-control" multiple <?php echo $req; ?>>
                        
                        <?php elseif ($field['field_type'] === 'select'): ?>
                            <select name="<?php e($name); ?>" class="form-select" <?php echo $req; ?>>
                                <option value=""><?php e($placeholder); ?></option>
                                <?php
                                $optStmt = $pdo->prepare("SELECT * FROM form_field_options WHERE id_form_field = ? AND is_active = 1");
                                $optStmt->execute([$field['id']]);
                                foreach ($optStmt->fetchAll() as $opt): ?>
                                    <option value="<?php e($opt['option_value']); ?>"><?php e(t($opt['label_localized_key'])); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <?php if ($field['max_length']): ?>
                            <div class="form-text text-end small text-muted counter-text">0 / <?php e($field['max_length']); ?> znaků</div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="mt-5 mb-5 mt-md-4 mb-md-0 d-flex justify-content-center justify-content-md-end">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <?php e(t('submit_request_btn') !== 'submit_request_btn' ? t('submit_request_btn') : 'Odeslat žádost'); ?>
        </button>
    </div>
</form>