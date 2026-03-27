<?php
// language.php

/**
 * Funkce pro načtení překladů z databáze.
 * Můžeme ji zavolat kdykoliv potřebujeme změnit jazyk za běhu (např. při loginu).
 */
function loadTranslations($pdo, $langId, $typ = 'admin') {
    try {
        $stmtLang = $pdo->prepare("SELECT name, content FROM localized WHERE id_lang = ? AND typ = ?");
        $stmtLang->execute([$langId, $typ]);
        $GLOBALS['translations'] = $stmtLang->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (\PDOException $e) {
        $GLOBALS['translations'] = [];
    }
}

// Při běžném načtení stránky (např. index.php) se texty rovnou načtou ze session
if (isset($pdo)) {
    $currentLangId = $_SESSION['lang_id'] ?? 1;
    $currentPageType = $page_type ?? 'admin'; // Pokud není definováno, bere 'admin'
    
    loadTranslations($pdo, $currentLangId, $currentPageType);
}

/**
 * Pomocná funkce pro vypsání textu.
 */
function t($key) {
    return $GLOBALS['translations'][$key] ?? $key;
}
?>