<!DOCTYPE html>
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
                        <?php e($message); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>