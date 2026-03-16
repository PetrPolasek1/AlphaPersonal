<?php
// Získání tokenu z URL
$token = $_GET['t'] ?? '';

// Funkce pro hezké zobrazení chyby ve stylu aplikace
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

// Připojení k databázi
$host = 'localhost';
$db   = 'alphapersonal';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    showError('Chyba připojení k databázi.');
}

// Dohledání uživatele podle tokenu
$stmt = $pdo->prepare('SELECT * FROM alpha_pracovnici_uzivatele WHERE login_qr_token = ?');
$stmt->execute([$token]);
$dbUser = $stmt->fetch();

if (!$dbUser || $dbUser['is_active'] != 1 || $dbUser['login_qr_enabled'] != 1) {
    showError('Tento odkaz není platný, vypršel, nebo je účet zablokován.');
}

$email = $dbUser['login_email'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <base href="/portal/dist/"> 
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">
    <title>Login - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
</head>

<body class="nk-body ">
    <div class="nk-app-root " data-sidebar-collapse="lg">
        <div class="nk-main">
            <div class="nk-wrap has-shape flex-column">
                <div class="nk-shape bg-shape-blur-a start-0 top-0"></div>
                <div class="nk-shape bg-shape-blur-b end-0 bottom-0"></div>
                <div class="text-center pt-5">
                    <a href="index.html" class="logo-link">
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
                                        <h1 class="nk-block-title mb-1">Log into Your Account</h1>
                                        <p class="small">Sign in to your account to customize your content generation settings and view your history.</p>
                                    </div>
                                </div>
                                
                                <div id="loginError" class="alert alert-danger d-none mb-3"></div>
                                
                                <!-- Přidán blok s informací o uživateli -->
                                <div class="alert alert-info mb-4 text-center">
                                    Přihlašujete se jako:<br><strong><?php echo htmlspecialchars($email); ?></strong>
                                </div>
                                
                                <form id="loginForm" action="api/client/auth/login-by-token.php" method="POST">
                                    <!-- Skrytá pole pro API backend -->
                                    <input type="hidden" name="token" id="token" value="<?php echo htmlspecialchars($_GET['t']); ?>">
                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                    
                                    <div class="row gy-3">
                                        <!-- Odstraněn div s inputem pro email -->
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label" for="password">Password</label>
                                                <div class="form-control-wrap">
                                                    <a href="password" class="password-toggle form-control-icon end" title="Toggle show/hide password">
                                                        <em class="icon ni ni-eye inactive"></em>
                                                        <em class="icon ni ni-eye-off active"></em>
                                                    </a>
                                                    <input class="form-control" type="password" id="password" name="password" placeholder="Enter password" required />
                                                </div>
                                            </div><!-- .form-group -->
                                        </div>
                                        <div class="col-12">
                                            <a class="link small" href="forgot-password.html">Forgot password?</a>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button class="btn btn-primary" type="submit">Login</button>
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
                                        <a class="nav-link" href="/#">Home</a>
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
                    
                    // FormData automaticky vezme skrytý 'token', 'email' a viditelný 'password'
                    fetch(form.action, {
                        method: form.method,
                        body: new FormData(form)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'index.html';
                        } else {
                            errorDiv.textContent = data.message;
                            errorDiv.classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        errorDiv.textContent = 'Došlo k chybě při komunikaci se serverem.';
                        errorDiv.classList.remove('d-none');
                    });
                });
            }
        });
    </script>
</body>
</html>