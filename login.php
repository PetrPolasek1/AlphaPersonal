<?php
session_start();

$token = $_GET['t'] ?? '';

function showError($message) {
    die('<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <base href="/portal/dist/">
    <title>Chyba přihlášení</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
</head>
<body class="nk-body">
    <div class="nk-app-root">
        <div class="nk-main">
            <div class="nk-wrap align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="container p-4 text-center">
                    <div class="alert alert-danger d-inline-block px-5 py-4 fs-5 rounded shadow-sm">
                        ' . htmlspecialchars($message) . '
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>');
}

if (empty($token)) {
    showError('Neplatný nebo chybějící odkaz.');
}

// 1. Připojení k DB (to automaticky načte i language.php)
require_once 'db.php'; 

// 2. Najdeme uživatele a vytáhneme i jeho sloupec `jazyk` z tabulky alpha_pracovnici
$stmt = $pdo->prepare('SELECT u.*, p.jmeno, p.prijmeni, p.jazyk 
                       FROM alpha_pracovnici_uzivatele u 
                       LEFT JOIN alpha_pracovnici p ON u.id_pracovnika = p.id 
                       WHERE u.login_qr_token = ?');
$stmt->execute([$token]);
$dbUser = $stmt->fetch();

if (!$dbUser || $dbUser['is_active'] != 1 || $dbUser['login_qr_enabled'] != 1) {
    showError('Tento odkaz není platný, vypršel, nebo je účet zablokován.');
}

// 3. Uložíme zjištěný jazyk do session (pokud v DB jazyk chybí, dáme 1)
$_SESSION['lang_id'] = $dbUser['jazyk'] ?? 1;

// 4. KOUZLO: Přenačteme texty pro správný jazyk a sekci 'front'
loadTranslations($pdo, $_SESSION['lang_id'], 'front');

// Příprava dat pro HTML
$email = $dbUser['login_email'];
$jmeno = $dbUser['jmeno'] ?? '';
$prijmeni = $dbUser['prijmeni'] ?? '';
$display_name = trim($jmeno . ' ' . $prijmeni) ?: $email;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <base href="/portal/dist/"> 
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">
    <title><?= htmlspecialchars(t('mess_loggin_title')) ?> - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
</head>

<body class="nk-body ">
    <div class="nk-app-root " data-sidebar-collapse="lg">
        <div class="nk-main">
            <div class="nk-wrap has-shape flex-column">
                <div class="nk-shape bg-shape-blur-a start-0 top-0"></div>
                <div class="nk-shape bg-shape-blur-b end-0 bottom-0"></div>
                <div class="text-center pt-5">
                    <a href="javascript:void(0)" class="logo-link">
                        <div class="logo-wrap">
                            <img class="logo-img logo-light" src="images/logo.png" srcset="images/logo2x.png 2x" alt="">
                            <img class="logo-img logo-dark" src="images/logo-dark.png" srcset="images/logo-dark2x.png 2x" alt="">
                            <img class="logo-img logo-icon" src="images/logo-icon.png" srcset="images/logo-icon2x.png 2x" alt="">
                        </div>
                    </a>
                </div>
                <div class="container p-2 p-sm-4 mt-auto">
                    <div class="row justify-content-center">
                        <div class="col-md-7 col-lg-5 col-xl-5 col-xxl-4">
                            <div class="nk-block">
                                <div class="nk-block-head text-center mb-4 pb-2">
                                    <div class="nk-block-head-content">
                                        <h1 class="nk-block-title mb-1"><?= htmlspecialchars(t('mess_loggin_title')) ?></h1>
                                        
                                        <p class="small"><?= htmlspecialchars(t('loggin_desc') !== 'loggin_desc' ? t('loggin_desc') : 'Sign in to your account to customize your content generation settings and view your history.') ?></p>
                                    </div>
                                </div>
                                
                                <div id="loginError" class="alert alert-danger d-none mb-3"></div>
                                
                                <div class="alert alert-info mb-4 text-center">
                                    <?= htmlspecialchars(t('loggin_as') !== 'loggin_as' ? t('loggin_as') : 'Přihlašujete se jako:') ?><br><strong><?php echo htmlspecialchars($display_name); ?></strong>
                                </div>
                                
                                <form id="loginForm" action="api/client/auth/login-by-token.php" method="POST">
                                    <input type="hidden" name="token" id="token" value="<?php echo htmlspecialchars($_GET['t']); ?>">
                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                    
                                    <div class="row gy-3">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label" for="password"><?= htmlspecialchars(t('loggin_pass')) ?></label>
                                                <div class="form-control-wrap">
                                                    <a href="password" class="password-toggle form-control-icon end" title="Toggle show/hide password">
                                                        <em class="icon ni ni-eye inactive"></em>
                                                        <em class="icon ni ni-eye-off active"></em>
                                                    </a>
                                                    <input class="form-control" type="password" id="password" name="password" placeholder="<?= htmlspecialchars(t('loggin_pass')) ?>" required />
                                                </div>
                                            </div></div>
                                        <div class="col-12">
                                            <a class="link small" href="forgot-password.html"><?= htmlspecialchars(t('forgot_password') !== 'forgot_password' ? t('forgot_password') : 'Forgot password?') ?></a>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button class="btn btn-primary" type="submit"><?= htmlspecialchars(t('btn_loggin')) ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nk-footer">
                    <div class="container-xl">
                        <div class="d-flex align-items-center flex-wrap justify-content-between mx-n3">
                            <div class="nk-footer-links px-3">
                                <ul class="nav nav-sm">
                                    <li class="nav-item">
                                        <a class="nav-link" href="/#"><?= htmlspecialchars(t('home_admin') !== 'home_admin' ? t('home_admin') : 'Home') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/#">Pricing</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/#">Privacy Policy</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/#">FAQ</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/#">Contact</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="nk-footer-copyright fs-6 px-3"> &copy; 2023 All Rights Reserved to <a href="#">Copygen</a>. </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/bundle.js?v1.1.0"></script>
    <script src="assets/js/scripts.js?v1.1.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const errorDiv = document.getElementById('loginError');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); 
                    errorDiv.classList.add('d-none');
                    
                    fetch(form.action, {
                        method: form.method,
                        body: new FormData(form)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'index.php';
                        } else {
                            // Chybové hlášky, které vrací api/client/auth/login-by-token.php by ideálně také měly
                            // brát texty z tvé DB, ale jelikož to vyhodnocuje backend, řeší se to jinde.
                            errorDiv.textContent = data.message || 'Chyba přihlášení.';
                            errorDiv.classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        errorDiv.textContent = '<?= htmlspecialchars(t("error_server_com") !== "error_server_com" ? t("error_server_com") : "Došlo k chybě při komunikaci se serverem.") ?>';
                        errorDiv.classList.remove('d-none');
                    });
                });
            }
        });
    </script>
</body>
</html>