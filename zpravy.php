<?php
session_start();
// Získání dat z přihlášení
$userId = $_SESSION['user_id'] ?? 1; // Pro testování je default 1
$fullName = $_SESSION['user_name'] ?? 'Uživatel';

// Připojení k DB
require_once 'db.php';

$errorMsg = '';
$successMsg = '';

// Odchycení úspěchu po přesměrování (ochrana proti F5)
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMsg = "Zpráva byla úspěšně odeslána.";
}

// 1. Zpracování odeslání nové zprávy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $recipientEmail = trim($_POST['recipient_email']);
    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);

    $stmtUser = $pdo->prepare("SELECT id, is_active, locked_until FROM alpha_pracovnici_uzivatele WHERE login_email = ?");
    $stmtUser->execute([$recipientEmail]);
    $recipient = $stmtUser->fetch();

    if (!$recipient) {
        $errorMsg = "Uživatel s e-mailem '" . htmlspecialchars($recipientEmail) . "' nebyl nalezen.";
    } elseif ($recipient['is_active'] != 1) {
        $errorMsg = "Účet příjemce není aktivní.";
    } elseif (!empty($recipient['locked_until']) && strtotime($recipient['locked_until']) > time()) {
        $errorMsg = "Účet příjemce je dočasně uzamčen nebo vypršel.";
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO alpha_zpravy (sender_id, recipient_id, subject, content) VALUES (?, ?, ?, ?)");
        $stmtInsert->execute([$userId, $recipient['id'], $subject, $content]);
        
        // PŘESMĚROVÁNÍ (Ochrana proti dvojitému odeslání při F5)
        header("Location: zpravy.php?success=1");
        exit;
    }
}

// 2. Zpracování akcí (koš, obnova, mazání)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $msgId = (int)$_GET['id'];

    if ($action === 'trash') {
        $stmt = $pdo->prepare("UPDATE alpha_zpravy SET is_deleted = 1 WHERE id = ? AND recipient_id = ?");
        $stmt->execute([$msgId, $userId]);
    } elseif ($action === 'restore') {
        $stmt = $pdo->prepare("UPDATE alpha_zpravy SET is_deleted = 0 WHERE id = ? AND recipient_id = ?");
        $stmt->execute([$msgId, $userId]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM alpha_zpravy WHERE id = ? AND recipient_id = ?");
        $stmt->execute([$msgId, $userId]);
    }
    header("Location: zpravy.php");
    exit;
}

// 3. Načtení aktivních zpráv (OPRAVENO: Bez duplikací pomocí poddotazu s LIMIT 1)
$stmtActive = $pdo->prepare("
    SELECT z.*, (SELECT login_email FROM alpha_pracovnici_uzivatele WHERE id = z.sender_id LIMIT 1) AS sender_email
    FROM alpha_zpravy z
    WHERE z.recipient_id = ? AND z.is_deleted = 0 
    ORDER BY z.created_at DESC
");
$stmtActive->execute([$userId]);
$activeMessages = $stmtActive->fetchAll();

// 4. Načtení zpráv v koši (OPRAVENO: Bez duplikací)
$stmtTrashed = $pdo->prepare("
    SELECT z.*, (SELECT login_email FROM alpha_pracovnici_uzivatele WHERE id = z.sender_id LIMIT 1) AS sender_email
    FROM alpha_zpravy z
    WHERE z.recipient_id = ? AND z.is_deleted = 1 
    ORDER BY z.created_at DESC
");
$stmtTrashed->execute([$userId]);
$trashedMessages = $stmtTrashed->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zprávy - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
</head>
<body class="nk-body ">
    <div class="nk-app-root " data-sidebar-collapse="lg">
        <div class="nk-main">
            <div class="nk-sidebar nk-sidebar-fixed" id="sidebar">
                <div class="nk-compact-toggle">
                    <button class="btn btn-xs btn-outline-light btn-icon compact-toggle text-light bg-white rounded-3">
                        <em class="icon off ni ni-chevron-left"></em>
                        <em class="icon on ni ni-chevron-right"></em>
                    </button>
                </div>
                <div class="nk-sidebar-element nk-sidebar-head">
                    <div class="nk-sidebar-brand">
                        <a href="index.php" class="logo-link">
                            <div class="logo-wrap">
                                <img class="logo-img logo-light" src="images/logo.png" srcset="images/logo2x.png 2x" alt="">
                                <img class="logo-img logo-dark" src="images/logo-dark.png" srcset="images/logo-dark2x.png 2x" alt="">
                            </div>
                        </a>
                    </div>
                </div>
                <div class="nk-sidebar-element nk-sidebar-body">
                    <div class="nk-sidebar-content h-100" data-simplebar>
                        <div class="nk-sidebar-menu">
                            <ul class="nk-menu">
                                <li class="nk-menu-item"><a href="index.php" class="nk-menu-link"><span class="nk-menu-icon"><em class="icon ni ni-dashboard-fill"></em></span><span class="nk-menu-text">Dashboard</span></a></li>
                                <li class="nk-menu-item"><a href="zpravy.php" class="nk-menu-link"><span class="nk-menu-icon"><em class="icon ni ni-chat-fill"></em></span><span class="nk-menu-text">Zprávy</span></a></li>
                                <li class="nk-menu-item"><a href="pozadavky.html" class="nk-menu-link"><span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span><span class="nk-menu-text">Požadavky zaměstnanců</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="nk-wrap">
                <div class="nk-header nk-header-fixed">
                    <div class="container-fluid">
                        <div class="nk-header-wrap">
                            <div class="nk-header-logo ms-n1">
                                <div class="nk-sidebar-toggle me-1">
                                    <button class="btn btn-sm btn-zoom btn-icon sidebar-toggle d-sm-none"><em class="icon ni ni-menu"></em></button>
                                </div>
                            </div>
                            <div class="nk-header-tools">
                                <ul class="nk-quick-nav ms-2">
                                    <li class="dropdown d-inline-flex">
                                        <a data-bs-toggle="dropdown" class="d-inline-flex" href="#">
                                            <div class="media media-md media-circle media-middle text-bg-primary"><img src="images/avatar/a.png" /></div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-md rounded-3">
                                            <div class="dropdown-content py-3">
                                                <div class="d-flex px-3 py-2 bg-primary bg-opacity-10 rounded-bottom-3">
                                                    <div class="media-text"><h6 class="fs-6 mb-0"><?= htmlspecialchars($fullName) ?></h6></div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="nk-content">
                    <div class="container-xl">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                
                                <div id="messages-main-view">
                                    <div class="nk-block-head nk-page-head">
                                        <div class="nk-block-head-between flex-wrap gap g-2">
                                            <div class="nk-block-head-content">
                                                <h2 class="display-6">Messages</h2>
                                                <p>Summary of your messages</p>
                                            </div>
                                            <div class="nk-block-head-content">
                                                <ul class="nk-block-tools">
                                                    <li>
                                                        <a class="btn btn-primary" href="#" data-bs-toggle="modal" data-bs-target="#composeMessageModal">
                                                            <em class="icon ni ni-edit"></em><span>Write Message</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($errorMsg)): ?>
                                        <div class="alert alert-danger mb-4"><?= htmlspecialchars($errorMsg) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($successMsg)): ?>
                                        <div class="alert alert-success mb-4"><?= htmlspecialchars($successMsg) ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="nk-block">
                                        <div class="card shadow-none">
                                            <ul class="nav nav-tabs nav-tabs-s1 px-4">
                                                <li class="nav-item">
                                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#recents-tab">Aktivní zprávy (<?= count($activeMessages) ?>)</button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#trash-tab">Koš (<?= count($trashedMessages) ?>)</button>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane fade show active" id="recents-tab">
                                                    <table class="table table-middle mb-0">
                                                        <tbody>
                                                            <?php if(empty($activeMessages)): ?>
                                                                <tr><td class="text-center py-4">Žádné aktivní zprávy.</td></tr>
                                                            <?php else: ?>
                                                                <?php foreach($activeMessages as $msg): 
                                                                    $date = date('M d, Y', strtotime($msg['created_at']));
                                                                    $time = date('h:i A', strtotime($msg['created_at']));
                                                                    $sender = $msg['sender_email'] ?: 'Systém';
                                                                ?>
                                                                <div id="msg-content-<?= $msg['id'] ?>" class="d-none"><?= htmlspecialchars($msg['content']) ?></div>
                                                                
                                                                <tr>
                                                                    <td class="tb-col">
                                                                        <div class="caption-text line-clamp-1"><strong><?= htmlspecialchars($msg['subject']) ?></strong></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-sm">
                                                                        <div class="badge text-bg-primary-soft rounded-pill px-2 py-1 fs-6 lh-sm"><?= htmlspecialchars($sender) ?></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-md">
                                                                        <div class="fs-6 text-light d-inline-flex flex-wrap gap gx-2"><span><?= $date ?></span> <span><?= $time ?></span></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-end text-end">
                                                                        <button class="btn btn-sm btn-outline-primary me-2" 
                                                                                data-subject="<?= htmlspecialchars($msg['subject']) ?>"
                                                                                data-sender="<?= htmlspecialchars($sender) ?>"
                                                                                data-date="<?= $date . ' ' . $time ?>"
                                                                                onclick="readMessage(<?= $msg['id'] ?>, this)">
                                                                            <em class="icon ni ni-eye"></em> <span>Číst</span>
                                                                        </button>
                                                                        
                                                                        <div class="dropdown d-inline-block">
                                                                            <button class="btn btn-sm btn-icon btn-zoom" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></button>
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <ul class="link-list link-list-hover-bg-primary link-list-md">
                                                                                    <li><a href="zpravy.php?action=trash&id=<?= $msg['id'] ?>"><em class="icon ni ni-trash"></em><span>Do koše</span></a></li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="tab-pane fade" id="trash-tab">
                                                    <?php if(empty($trashedMessages)): ?>
                                                        <div class="text-center py-5">
                                                            <h4 class="mb-2">Koš je prázdný.</h4>
                                                        </div>
                                                    <?php else: ?>
                                                        <table class="table table-middle mb-0">
                                                            <tbody>
                                                                <?php foreach($trashedMessages as $msg): 
                                                                    $date = date('M d, Y', strtotime($msg['created_at']));
                                                                    $time = date('h:i A', strtotime($msg['created_at']));
                                                                    $sender = $msg['sender_email'] ?: 'Systém';
                                                                ?>
                                                                <div id="msg-content-<?= $msg['id'] ?>" class="d-none"><?= htmlspecialchars($msg['content']) ?></div>
                                                                
                                                                <tr>
                                                                    <td class="tb-col">
                                                                        <div class="caption-text line-clamp-1 text-decoration-line-through text-muted"><strong><?= htmlspecialchars($msg['subject']) ?></strong></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-sm">
                                                                        <div class="badge text-bg-dark-soft rounded-pill px-2 py-1 fs-6 lh-sm"><?= htmlspecialchars($sender) ?></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-md">
                                                                        <div class="fs-6 text-light d-inline-flex flex-wrap gap gx-2"><span><?= $date ?></span> <span><?= $time ?></span></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-end text-end">
                                                                        <button class="btn btn-sm btn-outline-secondary me-2" 
                                                                                data-subject="<?= htmlspecialchars($msg['subject']) ?>"
                                                                                data-sender="<?= htmlspecialchars($sender) ?>"
                                                                                data-date="<?= $date . ' ' . $time ?>"
                                                                                onclick="readMessage(<?= $msg['id'] ?>, this)">
                                                                            <em class="icon ni ni-eye"></em>
                                                                        </button>
                                                                        <div class="dropdown d-inline-block">
                                                                            <button class="btn btn-sm btn-icon btn-zoom" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></button>
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <ul class="link-list link-list-hover-bg-primary link-list-md">
                                                                                    <li><a href="zpravy.php?action=restore&id=<?= $msg['id'] ?>"><em class="icon ni ni-curve-up-left"></em><span>Obnovit</span></a></li>
                                                                                    <li><a href="zpravy.php?action=delete&id=<?= $msg['id'] ?>" class="text-danger"><em class="icon ni ni-trash"></em><span>Trvale smazat</span></a></li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="mobile-read-view" class="d-none">
                                    <div class="nk-block-head">
                                        <button onclick="closeMobileRead()" class="btn btn-outline-light bg-white d-inline-flex align-items-center mb-3">
                                            <em class="icon ni ni-arrow-left"></em>
                                            <span>Zpět na zprávy</span>
                                        </button>
                                        <h2 class="display-6" id="mobile-read-subject">Předmět</h2>
                                        <p class="text-soft">Od: <strong id="mobile-read-sender"></strong> | <span id="mobile-read-date"></span></p>
                                    </div>
                                    <div class="nk-block">
                                        <div class="card card-bordered">
                                            <div class="card-inner fs-5" id="mobile-read-content" style="white-space: pre-wrap;"></div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/bundle.js?v1.1.0"></script>
    <script src="assets/js/scripts.js?v1.1.0"></script>

    <div class="modal fade" id="composeMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nová zpráva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="zpravy.php" method="POST" id="composeMessageForm">
                    <input type="hidden" name="action" value="send">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label">E-mail příjemce</label>
                            <input type="email" class="form-control" name="recipient_email" required placeholder="např. kolega@firma.cz">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Předmět</label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Text zprávy</label>
                            <textarea class="form-control" name="content" rows="6" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Odeslat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="readMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="desktop-read-subject">Předmět</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-0">
                    <p class="text-soft border-bottom pb-3 mb-4">Od: <strong id="desktop-read-sender"></strong> <br> <span class="small" id="desktop-read-date"></span></p>
                    <div id="desktop-read-content" class="fs-5 pb-4" style="white-space: pre-wrap;"></div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Zavřít</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Zabránění dvojkliku na tlačítko odeslat
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('composeMessageForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = document.getElementById('submitBtn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Odesílám...';
                });
            }
        });

        // Logika pro čtení zpráv (Modální okno vs Mobilní zobrazení)
        let readModal;
        document.addEventListener('DOMContentLoaded', function() {
            const readModalEl = document.getElementById('readMessageModal');
            if (typeof bootstrap !== 'undefined' && readModalEl) {
                readModal = new bootstrap.Modal(readModalEl);
            }
        });

        function readMessage(id, btnElement) {
            const subject = btnElement.getAttribute('data-subject');
            const sender = btnElement.getAttribute('data-sender');
            const date = btnElement.getAttribute('data-date');
            const content = document.getElementById('msg-content-' + id).textContent;
            const isMobile = window.innerWidth < 768;

            if (isMobile) {
                document.getElementById('mobile-read-subject').innerText = subject;
                document.getElementById('mobile-read-sender').innerText = sender;
                document.getElementById('mobile-read-date').innerText = date;
                document.getElementById('mobile-read-content').innerText = content;
                
                document.getElementById('messages-main-view').classList.add('d-none');
                document.getElementById('mobile-read-view').classList.remove('d-none');
            } else {
                document.getElementById('desktop-read-subject').innerText = subject;
                document.getElementById('desktop-read-sender').innerText = sender;
                document.getElementById('desktop-read-date').innerText = date;
                document.getElementById('desktop-read-content').innerText = content;
                
                if(readModal) readModal.show();
            }
        }

        function closeMobileRead() {
            document.getElementById('mobile-read-view').classList.add('d-none');
            document.getElementById('messages-main-view').classList.remove('d-none');
        }
    </script>
</body>
</html>