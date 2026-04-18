# CopyGen Client Portal

Profesionální klientský portál postavený v PHP nad jednoduchou MVC strukturou.  
Aplikace slouží pro přihlášení klienta, zobrazení dashboardu, práci s dynamickými formuláři, správu zpráv, prohlížení odeslaných požadavků a správu profilu včetně změny hesla a resetu přístupu.

## Obsah

1. Přehled aplikace
2. Hlavní funkční oblasti
3. Architektura
4. Struktura složek
5. Přehled souborů
6. Tok požadavku aplikací
7. Bezpečnostní prvky
8. Datové a integrační závislosti
9. Doporučení pro další rozvoj

## Přehled aplikace

Tato aplikace představuje klientskou zónu, která:

- autentizuje uživatele přes přihlašovací token a heslo
- udržuje server-side session nad databázovou session tabulkou
- načítá lokalizované texty z databáze
- zobrazuje dashboard s dostupnými formuláři
- umožňuje odeslat dynamický formulář včetně příloh
- zobrazuje zprávy a stav požadavků
- poskytuje profil uživatele a změnu hesla
- obsahuje flow pro „forgot password“ a jednorázový reset hesla

## Hlavní funkční oblasti

### 1. Autentizace

- vstup přes `login.php`
- ověření tokenu přes `LoginModel` a `LoginController`
- přihlášení přes API endpoint `api/client/auth/login-by-token.php`
- odhlášení přes `api/client/auth/logout.php`
- pomocné session utility v `core/helper.php`

### 2. Dashboard a formuláře

- dashboard běží přes `index.php`
- aktivní formuláře načítá `IndexModel`
- HTML formuláře se generují dynamicky přes `get_form.php`
- odeslání a uložení formuláře řeší `process_form.php` a `FormSubmissionModel`

### 3. Zprávy

- přehled zpráv běží přes `message.php`
- čtení, označení jako přečtené a změna stavu se řeší v `MessageController`
- data poskytuje `MessageModel`

### 4. Požadavky

- přehled klientských požadavků běží přes `request.php`
- detail požadavku se načítá AJAXem
- data a oprávnění k souborům řeší `RequestModel`

### 5. Profil a hesla

- profil běží přes `profile.php`
- změna hesla se řeší v `ProfileController` a `ProfileModel`
- reset hesla je rozdělený do `forgot-password.php`, `check-email.php` a `reset-password.php`

## Architektura

Projekt používá lehkou MVC organizaci:

- `root *.php` soubory fungují jako vstupní body
- `controller/` obsahuje orchestrace business logiky
- `models/` obsahují přístup do databáze a datové transformace
- `view/` obsahuje HTML šablony
- `core/` obsahuje sdílené utility, session, CSRF, překlady a layout prvky
- `api/` obsahuje endpointy pro login/logout a pomocné skripty

### Typický tok requestu

1. Uživatel otevře root vstupní soubor, např. `index.php`.
2. Soubor načte `core/helper.php`, `core/db.php` a příslušný model/controller.
3. Controller připraví data pro view.
4. View vykreslí stránku nebo vrátí JSON/HTML fragment.

## Struktura složek

### Root

- vstupní skripty aplikace
- router-like entry points bez centralizovaného front controlleru

### `controller/`

- business orchestrace jednotlivých obrazovek a flow

### `core/`

- databáze, helpery, překlady, navigace a opakovaně používané layout prvky

### `models/`

- práce s databází a převod dat mezi DB a aplikací

### `view/`

- HTML šablony obrazovek

### `api/`

- endpointy pro autentizaci a pomocné interní utility

### `assets/`

- CSS, JavaScript, fonty a statické front-end assety

### `images/`

- loga, ilustrace, avatar a další grafika aplikace

### `documents/`

- úložiště nahraných souborů klientů
- přístup je omezen přes `.htaccess` a aplikační autorizaci

### `vendor/`

- Composer závislosti
- aktuálně zejména `phpmailer/phpmailer`

### `not-needed/`

- historické nebo nepoužívané HTML šablony

## Přehled souborů

### Root vstupní soubory

| Soubor | Účel |
|---|---|
| `index.php` | Vstupní bod dashboardu. Ověří přihlášení, načte model/controller a zobrazí přehled formulářů. |
| `login.php` | Vstupní bod přihlašovací obrazovky. Ověřuje login token a připraví view pro zadání hesla. |
| `message.php` | Vstupní bod modulu zpráv. Zobrazuje inbox, koš a zpracovává odeslání nebo změnu stavu zpráv. |
| `profile.php` | Vstupní bod profilu uživatele. Zobrazuje osobní údaje a zpracovává změnu hesla. |
| `request.php` | Vstupní bod seznamu požadavků klienta a jejich detailů. |
| `forgot-password.php` | Spouští flow pro vyžádání resetu hesla. |
| `check-email.php` | Informační mezikrok po odeslání žádosti o reset hesla. |
| `reset-password.php` | Dokončení resetu hesla přes jednorázový token. |
| `get_form.php` | Vrací HTML fragment dynamického formuláře pro modal nebo mobilní zobrazení. |
| `process_form.php` | Bezpečně zpracuje odeslaný formulář, validuje uploady a ukládá data do DB. |
| `download-document.php` | Bezpečný download přílohy na základě autorizace uživatele. |

### `controller/`

| Soubor | Účel |
|---|---|
| `controller/index-controller.php` | Připravuje dashboard, formuláře a notifikační počty. |
| `controller/login-controller.php` | Ověřuje login token, nastavuje jazyk a připravuje přihlašovací view. |
| `controller/message-controller.php` | Řídí čtení, odeslání a změny stavu zpráv včetně CSRF ochrany. |
| `controller/profile-controller.php` | Řídí profil, změnu hesla a načtení doplňkových údajů uživatele. |
| `controller/request-controller.php` | Načítá seznam požadavků a vrací detail požadavku jako JSON payload. |
| `controller/forgot-password-controller.php` | Zpracuje žádost o reset hesla a připraví navazující krok flow. |
| `controller/reset-password-controller.php` | Ověří reset token a provede finální změnu hesla. |

### `models/`

| Soubor | Účel |
|---|---|
| `models/index-model.php` | Načítá aktivní formuláře a notifikační počty pro dashboard. |
| `models/login-model.php` | Načítá uživatele podle login tokenu a umí rotovat login token. |
| `models/message-model.php` | Načítá a ukládá zprávy, mění jejich stav a vrací notifikační počty. |
| `models/profile-model.php` | Poskytuje data profilu, adresy, kontakty a změnu hesla. |
| `models/request-model.php` | Načítá seznam požadavků, detail požadavku, labely voleb a oprávnění k souborům. |
| `models/auth-model.php` | Vytváří hashované reset tokeny a spotřebovává je při resetu hesla. |
| `models/form-submission-model.php` | Ukládá odeslaný formulář a mapuje hodnoty do správných DB sloupců podle typu pole. |

### `core/`

| Soubor | Účel |
|---|---|
| `core/helper.php` | Centrální utilitní vrstva: escaping, redirecty, session, CSRF, tokeny, logování a pomocné funkce. |
| `core/db.php` | Inicializuje PDO připojení a navazuje jazykovou vrstvu. |
| `core/language.php` | Načítá lokalizované texty z databáze a poskytuje funkci `t()`. |
| `core/formManager.php` | Vrací definice formulářových polí a obsahuje starší helper pro ukládání requestů. |
| `core/sidebar.php` | Vykresluje levé menu a badge notifikací. |
| `core/header.php` | Vykresluje horní panel s profilem a odhlášením. |

### `view/`

| Soubor | Účel |
|---|---|
| `view/index-view.php` | Dashboard s kartami formulářů, modálem a mobilním zobrazením formuláře. |
| `view/login-view.php` | Přihlašovací obrazovka se zadáním hesla a AJAX loginem. |
| `view/message-view.php` | UI pro zprávy, čtení detailu a práci s inboxem/košem. |
| `view/profile-view.php` | UI profilu, osobních údajů a změny hesla. |
| `view/request-view.php` | Přehled požadavků a modal detailu požadavku. |
| `view/forgot-password-view.php` | Formulář pro zadání e-mailu k resetu hesla. |
| `view/check-email-view.php` | Potvrzovací obrazovka po vytvoření reset žádosti. |
| `view/reset-password-view.php` | Formulář pro zadání nového hesla přes reset token. |
| `view/error-view.php` | Jednoduché chybové zobrazení například pro neplatný login token. |

### `api/`

| Soubor | Účel |
|---|---|
| `api/client/auth/login-by-token.php` | Přihlášení klienta na základě login tokenu a hesla. |
| `api/client/auth/login.php` | Alternativní přihlášení e-mailem a heslem. |
| `api/client/auth/logout.php` | Odhlášení, revokace DB session a rotace login tokenu. |
| `api/client/client_creation.php` | Lokální pomocný skript pro vytvoření klientského účtu a QR/login odkazu. |

## Tok požadavku aplikací

### Přihlášení

1. Uživatel přijde na `login.php?t=...`.
2. `LoginController` ověří token.
3. `view/login-view.php` zobrazí formulář s heslem.
4. AJAX odešle data na `api/client/auth/login-by-token.php`.
5. API ověří heslo, vytvoří DB session, otočí token a uloží PHP session.

### Dashboard a formuláře

1. `index.php` načte dashboard.
2. `IndexController` připraví formuláře a notifikace.
3. Po kliknutí na kartu si front-end stáhne přes `get_form.php` HTML formuláře.
4. Formulář se odešle na `process_form.php`.
5. `FormSubmissionModel` uloží hlavičku podání i hodnoty polí.

### Reset hesla

1. `forgot-password.php` přijme e-mail.
2. `AuthModel` vytvoří jednorázový hashovaný reset token.
3. Uživatel přejde na `reset-password.php?token=...`.
4. `ResetPasswordController` ověří token a změní heslo.

## Bezpečnostní prvky

- CSRF tokeny přes `core/helper.php`
- server-side session ověřovaná proti DB session tabulce
- hashování hesel pomocí `password_hash()`
- hashování reset tokenů a login tokenů v databázi
- autorizovaný download dokumentů
- whitelist upload přípon a MIME typů
- blokace přímého přístupu do `documents/`
- základní rate-limit na žádosti o reset hesla přes session časování

## Datové a integrační závislosti

### Databáze

Aplikace používá MySQL/MariaDB a spoléhá mimo jiné na tabulky:

- `alpha_pracovnici_uzivatele`
- `alpha_pracovnici_uzivatele_sessions`
- `alpha_zpravy`
- `forms`
- `form_fields`
- `form_field_options`
- `form_submissions`
- `form_submission_values`
- `password_resets`
- `localized`

### Composer závislosti

- `phpmailer/phpmailer`

## Doporučení pro další rozvoj

- přesunout uploady mimo public web root po nasazení na server
- oddělit konfiguraci prostředí do plnohodnotného `.env` řešení
- doplnit automatické testy pro autentizaci, reset hesla a uploady
- zvážit centrální router/front controller pro jednotnější architekturu
- rozdělit velké view soubory na menší komponenty nebo partials
