<?php
class ProfileModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Vytažení dat o uživateli (Přidáno u.id_pracovnika)
    public function getUserProfile($userId) {
        $stmt = $this->pdo->prepare('
            SELECT u.id_pracovnika, u.login_email, u.password_hash, p.jmeno, p.prijmeni 
            FROM alpha_pracovnici_uzivatele u 
            LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id 
            WHERE u.id = ?
        ');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getActiveAddress($idPracovnik) {
        $stmt = $this->pdo->prepare('SELECT adresa FROM alpha_pracovnici_adresy WHERE id_pracovnik = ? AND platnost = 1 LIMIT 1');
        $stmt->execute([$idPracovnik]);
        return $stmt->fetchColumn();
    }

    public function getDefaultContacts($idPracovnik) {
        $stmt = $this->pdo->prepare('SELECT typ, kontakt FROM alpha_pracovnici_kontakty WHERE id_pracovnik = ? AND platnost = 1 AND vychozi = 1 AND typ IN ("email", "telefon")');
        $stmt->execute([$idPracovnik]);
        return $stmt->fetchAll();
    }

    // Aktualizace hesla
    public function updatePassword($userId, $newHash) {
        $stmt = $this->pdo->prepare('UPDATE alpha_pracovnici_uzivatele SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$newHash, $userId]);
    }
}
?>