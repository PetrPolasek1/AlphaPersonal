<?php
class ProfileController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function handleRequest() {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            redirect(get_login_redirect_url());
        }

        $errorMsg = '';
        $successMsg = '';

        // Zpracování formuláře pro změnu hesla
        if (is_post() && post('action') === 'change_password') {
            require_csrf();
            $oldPassword = post('old_password');
            $newPassword = post('new_password');
            $confirmPassword = post('confirm_password');

            $user = $this->model->getUserProfile($userId);

            similar_text(strtolower($oldPassword), strtolower($newPassword), $similarityPercent);
            $levenshteinDist = levenshtein(strtolower($oldPassword), strtolower($newPassword));

            if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
                $errorMsg = t('err_old_password') !== 'err_old_password' ? t('err_old_password') : 'Aktuální heslo není správné.';
            } elseif ($newPassword !== $confirmPassword) {
                $errorMsg = t('err_password_match') !== 'err_password_match' ? t('err_password_match') : 'Nová hesla se neshodují.';
            } elseif (strlen($newPassword) < 6) {
                $errorMsg = t('err_password_length') !== 'err_password_length' ? t('err_password_length') : 'Nové heslo musí mít alespoň 6 znaků.';
            } elseif ($oldPassword === $newPassword) {
                $errorMsg = t('err_password_same') !== 'err_password_same' ? t('err_password_same') : 'Nové heslo nesmí být úplně stejné jako to aktuální.';
            } elseif ($similarityPercent > 70 || $levenshteinDist < 3) {
                $errorMsg = t('err_password_similar') !== 'err_password_similar' ? t('err_password_similar') : 'Nové heslo je příliš podobné tomu starému (změňte více znaků).';
            } else {
                $this->model->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
                $successMsg = t('succ_password_changed') !== 'succ_password_changed' ? t('succ_password_changed') : 'Heslo bylo úspěšně změněno.';
            }
        }

        // Načtení dat pro View
        $userProfile = $this->model->getUserProfile($userId);
        $jmeno = $userProfile['jmeno'] ?? '';
        $prijmeni = $userProfile['prijmeni'] ?? '';
        
        $fullName = trim($jmeno . ' ' . $prijmeni) ?: 'Uživatel';
        $email = $userProfile['login_email'] ?? '';
        
        // NOVÉ: Vytažení adresy a kontaktů pomocí id_pracovnika
        $idPracovnika = $userProfile['id_pracovnika'] ?? null;
        $adresa = '';
        $kontakty = [];
        
        if ($idPracovnika) {
            $adresa = $this->model->getActiveAddress($idPracovnika);
            $kontakty = $this->model->getDefaultContacts($idPracovnika);
        }

        $unreadMessagesCount = $this->model->getUnreadMessagesCount($userId);
        $updatedRequestsCount = $this->model->getUpdatedRequestsCount($userId);

        require_once __DIR__ . '/../view/profile-view.php';
    }
}
?>
