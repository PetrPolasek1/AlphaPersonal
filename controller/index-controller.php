<?php
/**
 * -------------------------------------------------
 * Controller: Dashboard
 * -------------------------------------------------
 * Sklada data pro dashboard.
 * Načítá formuláře, badge notifikací
 * a login welcome stav.
 */

class IndexController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest() {
        $userId = $_SESSION['user_id'] ?? 1;
        $fullName = $_SESSION['user_name'] ?? (t('default_user_name') !== 'default_user_name' ? t('default_user_name') : 'Uživatel');
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $firstName = explode(' ', trim($fullName))[0];

        $forms = $this->model->getActiveForms();

        $count = count($forms);
        $gridCols = 4;
        $gridRows = max(1, ceil($count / $gridCols));

        $unreadMessagesCount = $this->model->getUnreadMessagesCount($userId);
        $updatedRequestsCount = $this->model->getUpdatedRequestsCount($userId);
        $notificationMessage = null;

        if (!empty($_SESSION['just_logged_in'])) {
            $totalUnreadNotifications = (int) $unreadMessagesCount + (int) $updatedRequestsCount;

            if ($totalUnreadNotifications === 1) {
                $notificationMessage = t('new_notification_single') !== 'new_notification_single' ? t('new_notification_single') : 'Máte novou notifikaci.';
            } elseif ($totalUnreadNotifications > 1) {
                $notificationMessage = t('new_notification_multiple') !== 'new_notification_multiple' ? t('new_notification_multiple') : 'Máte nové notifikace.';
            }

            unset($_SESSION['just_logged_in']);
        }

        require_once __DIR__ . '/../view/index-view.php';
    }
}
?>
