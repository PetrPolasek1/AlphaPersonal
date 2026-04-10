<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php e(t('messages_title') !== 'messages_title' ? t('messages_title') : 'Zprávy'); ?> - CopyGen</title>
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
                                <li class="nk-menu-item">
                                    <a href="index.php" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-dashboard-fill"></em></span>
                                        <span class="nk-menu-text"><?php e(t('dashboard') !== 'dashboard' ? t('dashboard') : 'Dashboard'); ?></span>
                                    </a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="message.php" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-chat-fill"></em></span>
                                        <span class="nk-menu-text"><?php e(t('messages_menu') !== 'messages_menu' ? t('messages_menu') : 'Zprávy'); ?></span>
                                    </a>
                                </li>
                                <li class="nk-menu-item">
                                    <a href="pozadavky.html" class="nk-menu-link">
                                        <span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span>
                                        <span class="nk-menu-text"><?php e(t('requests_menu') !== 'requests_menu' ? t('requests_menu') : 'Požadavky zaměstnanců'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="nk-sidebar-element nk-sidebar-footer">
                    <div class="nk-sidebar-footer-extended pt-3">
                        <div class="border border-light rounded-3">
                            <a class="d-flex px-3 py-2 bg-primary bg-opacity-10 rounded-bottom-3 rounded-top-3" href="profile.php">
                                <div class="media-group">
                                    <div class="media media-sm media-middle media-circle text-bg-primary">
                                        <img src="images/avatar/a.png" />
                                    </div>
                                    <div class="media-text">
                                        <h6 class="fs-6 mb-0"><?php e($fullName); ?></h6>
                                    </div>
                                    <em class="icon ni ni-chevron-right ms-auto ps-1"></em>
                                </div>
                            </a>
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
                                    <button class="btn btn-md btn-zoom btn-icon sidebar-toggle d-none d-sm-inline-flex"><em class="icon ni ni-menu"></em></button>
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
                                                <div class="border border-light rounded-3">
                                                    <a class="d-flex px-3 py-2 bg-primary bg-opacity-10 rounded-bottom-3 rounded-top-3" href="profile.php">
                                                        <div class="media-group">
                                                            <div class="media media-sm media-middle media-circle text-bg-primary"><img src="images/avatar/a.png" /></div>
                                                            <div class="media-text"><h6 class="fs-6 mb-0"><?php e($fullName); ?></h6></div>
                                                            <em class="icon ni ni-chevron-right ms-auto ps-1"></em>
                                                        </div>
                                                    </a>
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
                                                <h2 class="display-6"><?php e(t('messages_heading') !== 'messages_heading' ? t('messages_heading') : 'Zprávy'); ?></h2>
                                                <p><?php e(t('messages_summary') !== 'messages_summary' ? t('messages_summary') : 'Přehled vašich zpráv'); ?></p>
                                            </div>
                                            <div class="nk-block-head-content">
                                                <ul class="nk-block-tools">
                                                    <li>
                                                        <a class="btn btn-primary" href="#" data-bs-toggle="modal" data-bs-target="#composeMessageModal">
                                                            <em class="icon ni ni-edit"></em><span><?php e(t('write_message') !== 'write_message' ? t('write_message') : 'Napsat zprávu'); ?></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($errorMsg)): ?>
                                        <div class="alert alert-danger mb-4"><?php e($errorMsg); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($successMsg)): ?>
                                        <div class="alert alert-success mb-4"><?php e($successMsg); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="nk-block">
                                        <div class="card shadow-none">
                                            <ul class="nav nav-tabs nav-tabs-s1 px-4">
                                                <li class="nav-item">
                                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#recents-tab"><?php e(t('active_messages') !== 'active_messages' ? t('active_messages') : 'Aktivní zprávy'); ?> (<?= count($activeMessages) ?>)</button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#trash-tab"><?php e(t('trash') !== 'trash' ? t('trash') : 'Koš'); ?> (<?= count($trashedMessages) ?>)</button>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane fade show active" id="recents-tab">
                                                    <table class="table table-middle mb-0">
                                                        <tbody>
                                                            <?php if(empty($activeMessages)): ?>
                                                                <tr><td class="text-center py-4"><?php e(t('no_active_messages') !== 'no_active_messages' ? t('no_active_messages') : 'Žádné aktivní zprávy.'); ?></td></tr>
                                                            <?php else: ?>
                                                                <?php foreach($activeMessages as $msg): 
                                                                    $date = date('M d, Y', strtotime($msg['created_at']));
                                                                    $time = date('h:i A', strtotime($msg['created_at']));
                                                                    $senderFallback = t('system_sender') !== 'system_sender' ? t('system_sender') : 'Systém';
                                                                    $sender = $msg['sender_email'] ?: $senderFallback;
                                                                ?>
                                                                <div id="msg-content-<?= $msg['id'] ?>" class="d-none"><?php e($msg['content']); ?></div>
                                                                
                                                                <tr>
                                                                    <td class="tb-col">
                                                                        <div class="caption-text line-clamp-1"><strong><?php e($msg['subject']); ?></strong></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-sm">
                                                                        <div class="badge text-bg-primary-soft rounded-pill px-2 py-1 fs-6 lh-sm"><?php e($sender); ?></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-md">
                                                                        <div class="fs-6 text-light d-inline-flex flex-wrap gap gx-2"><span><?= $date ?></span> <span><?= $time ?></span></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-end text-end">
                                                                        <button class="btn btn-sm btn-outline-primary me-2" 
                                                                                data-subject="<?php e($msg['subject']); ?>"
                                                                                data-sender="<?php e($sender); ?>"
                                                                                data-date="<?= $date . ' ' . $time ?>"
                                                                                onclick="readMessage(<?= $msg['id'] ?>, this)">
                                                                            <em class="icon ni ni-eye"></em> <span><?php e(t('read_btn') !== 'read_btn' ? t('read_btn') : 'Číst'); ?></span>
                                                                        </button>
                                                                        
                                                                        <div class="dropdown d-inline-block">
                                                                            <button class="btn btn-sm btn-icon btn-zoom" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></button>
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <ul class="link-list link-list-hover-bg-primary link-list-md">
                                                                                    <li><a href="message.php?action=trash&id=<?= $msg['id'] ?>"><em class="icon ni ni-trash"></em><span><?php e(t('move_to_trash') !== 'move_to_trash' ? t('move_to_trash') : 'Do koše'); ?></span></a></li>
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
                                                            <h4 class="mb-2"><?php e(t('trash_empty') !== 'trash_empty' ? t('trash_empty') : 'Koš je prázdný.'); ?></h4>
                                                        </div>
                                                    <?php else: ?>
                                                        <table class="table table-middle mb-0">
                                                            <tbody>
                                                                <?php foreach($trashedMessages as $msg): 
                                                                    $date = date('M d, Y', strtotime($msg['created_at']));
                                                                    $time = date('h:i A', strtotime($msg['created_at']));
                                                                    $senderFallback = t('system_sender') !== 'system_sender' ? t('system_sender') : 'Systém';
                                                                    $sender = $msg['sender_email'] ?: $senderFallback;
                                                                ?>
                                                                <div id="msg-content-<?= $msg['id'] ?>" class="d-none"><?php e($msg['content']); ?></div>
                                                                
                                                                <tr>
                                                                    <td class="tb-col">
                                                                        <div class="caption-text line-clamp-1 text-decoration-line-through text-muted"><strong><?php e($msg['subject']); ?></strong></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-sm">
                                                                        <div class="badge text-bg-dark-soft rounded-pill px-2 py-1 fs-6 lh-sm"><?php e($sender); ?></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-md">
                                                                        <div class="fs-6 text-light d-inline-flex flex-wrap gap gx-2"><span><?= $date ?></span> <span><?= $time ?></span></div>
                                                                    </td>
                                                                    <td class="tb-col tb-col-end text-end">
                                                                        <button class="btn btn-sm btn-outline-secondary me-2" 
                                                                                data-subject="<?php e($msg['subject']); ?>"
                                                                                data-sender="<?php e($sender); ?>"
                                                                                data-date="<?= $date . ' ' . $time ?>"
                                                                                onclick="readMessage(<?= $msg['id'] ?>, this)">
                                                                            <em class="icon ni ni-eye"></em>
                                                                        </button>
                                                                        <div class="dropdown d-inline-block">
                                                                            <button class="btn btn-sm btn-icon btn-zoom" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></button>
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <ul class="link-list link-list-hover-bg-primary link-list-md">
                                                                                    <li><a href="message.php?action=restore&id=<?= $msg['id'] ?>"><em class="icon ni ni-curve-up-left"></em><span><?php e(t('restore_btn') !== 'restore_btn' ? t('restore_btn') : 'Obnovit'); ?></span></a></li>
                                                                                    <li><a href="message.php?action=delete&id=<?= $msg['id'] ?>" class="text-danger"><em class="icon ni ni-trash"></em><span><?php e(t('delete_permanently') !== 'delete_permanently' ? t('delete_permanently') : 'Trvale smazat'); ?></span></a></li>
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
                                            <span><?php e(t('back_to_messages') !== 'back_to_messages' ? t('back_to_messages') : 'Zpět na zprávy'); ?></span>
                                        </button>
                                        <h2 class="display-6" id="mobile-read-subject"><?php e(t('subject_label') !== 'subject_label' ? t('subject_label') : 'Předmět'); ?></h2>
                                        <p class="text-soft"><?php e(t('from_label') !== 'from_label' ? t('from_label') : 'Od:'); ?> <strong id="mobile-read-sender"></strong> | <span id="mobile-read-date"></span></p>
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
                    <h5 class="modal-title"><?php e(t('new_message_title') !== 'new_message_title' ? t('new_message_title') : 'Nová zpráva'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="message.php" method="POST" id="composeMessageForm">
                    <input type="hidden" name="action" value="send">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label"><?php e(t('recipient_email_label') !== 'recipient_email_label' ? t('recipient_email_label') : 'E-mail příjemce'); ?></label>
                            <input type="email" class="form-control" name="recipient_email" required placeholder="<?php e(t('recipient_email_placeholder') !== 'recipient_email_placeholder' ? t('recipient_email_placeholder') : 'např. kolega@firma.cz'); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label"><?php e(t('subject_label') !== 'subject_label' ? t('subject_label') : 'Předmět'); ?></label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label"><?php e(t('message_text_label') !== 'message_text_label' ? t('message_text_label') : 'Text zprávy'); ?></label>
                            <textarea class="form-control" name="content" rows="6" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php e(t('cancel_btn') !== 'cancel_btn' ? t('cancel_btn') : 'Zrušit'); ?></button>
                        <button type="submit" class="btn btn-primary" id="submitBtn"><?php e(t('send_btn') !== 'send_btn' ? t('send_btn') : 'Odeslat'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="readMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="desktop-read-subject"><?php e(t('subject_label') !== 'subject_label' ? t('subject_label') : 'Předmět'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-0">
                    <p class="text-soft border-bottom pb-3 mb-4"><?php e(t('from_label') !== 'from_label' ? t('from_label') : 'Od:'); ?> <strong id="desktop-read-sender"></strong> <br> <span class="small" id="desktop-read-date"></span></p>
                    <div id="desktop-read-content" class="fs-5 pb-4" style="white-space: pre-wrap;"></div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php e(t('close_btn') !== 'close_btn' ? t('close_btn') : 'Zavřít'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('composeMessageForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = document.getElementById('submitBtn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php e(t('sending_btn') !== 'sending_btn' ? t('sending_btn') : 'Odesílám...'); ?>';
                });
            }
        });

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