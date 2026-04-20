<?php
/**
 * -------------------------------------------------
 * View: Messages
 * -------------------------------------------------
 * Renderuje inbox, kos a detail zprav.
 * Obsahuje UI akce pro cteni, odpoved
 * a zmenu stavu zpravy.
 */
?>
<?php
$messagePageUrl = static function (string $tab, int $activePageNumber, int $trashPageNumber): string {
    return 'message.php?' . http_build_query([
        'tab' => $tab,
        'active_page' => max(1, $activePageNumber),
        'trash_page' => max(1, $trashPageNumber),
    ]);
};

$renderPagination = static function (int $currentPageNumber, int $totalPageCount, callable $urlBuilder): string {
    if ($totalPageCount <= 1) {
        return '';
    }

    $startPage = max(1, $currentPageNumber - 2);
    $endPage = min($totalPageCount, $currentPageNumber + 2);

    ob_start();
    ?>
    <nav class="app-pagination" aria-label="Pagination">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $currentPageNumber <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $currentPageNumber <= 1 ? '#' : htmlspecialchars($urlBuilder($currentPageNumber - 1), ENT_QUOTES, 'UTF-8') ?>">‹</a>
            </li>
            <?php for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++): ?>
                <li class="page-item <?= $pageNumber === $currentPageNumber ? 'active' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($urlBuilder($pageNumber), ENT_QUOTES, 'UTF-8') ?>"><?= $pageNumber ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $currentPageNumber >= $totalPageCount ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $currentPageNumber >= $totalPageCount ? '#' : htmlspecialchars($urlBuilder($currentPageNumber + 1), ENT_QUOTES, 'UTF-8') ?>">›</a>
            </li>
        </ul>
    </nav>
    <?php
    return (string) ob_get_clean();
};
?>
<!DOCTYPE html>
<html lang="<?= (($_SESSION['lang_id'] ?? 1) == 3) ? 'en' : 'cs' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php e(t('messages_title') !== 'messages_title' ? t('messages_title') : 'Zprávy'); ?> - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
    <style>
        .message-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .message-tabs::-webkit-scrollbar {
            display: none;
        }

        .message-tabs .nav-link {
            white-space: nowrap;
        }

        .message-row {
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .message-main {
            min-width: 0;
        }

        .message-subject {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
            font-size: 0.95rem;
            line-height: 1.35;
            color: #1f2b3d;
        }

        .message-subject-text {
            min-width: 0;
        }

        .message-email {
            display: none;
            margin-top: 0.25rem;
            font-size: 0.78rem;
            line-height: 1.35;
            color: #7b8794;
            word-break: break-word;
        }

        .message-row.is-read {
            background-color: #fafbfc;
        }

        .message-row.is-read .message-subject {
            color: #6b7280;
        }

        .message-row.is-read .message-subject strong {
            font-weight: 600;
        }

        .message-row.is-read .message-email,
        .message-row.is-read .tb-col-md .text-light {
            color: #9aa5b1 !important;
        }

        .message-row.is-read .badge {
            opacity: 0.7;
        }

        .mobile-read-meta {
            line-height: 1.6;
        }

        #mobile-read-view .card {
            border-radius: 1rem;
            overflow: hidden;
        }

        #mobile-read-content {
            line-height: 1.7;
        }

        #mobile-compose-view .card {
            border-radius: 1rem;
            overflow: hidden;
        }

        .messages-table .message-actions-dropdown {
            position: relative;
        }

        .messages-table .message-actions-dropdown .dropdown-menu {
            min-width: 12.5rem;
            padding: 0.3rem;
            border: 0;
            border-radius: 0.85rem;
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.14);
        }

        .messages-table .message-actions-dropdown .link-list a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 0.55rem;
            text-align: center;
            padding: 0.55rem 0.7rem !important;
            white-space: nowrap;
        }

        .app-pagination {
            display: flex;
            justify-content: center;
            padding: 1rem 1rem 1.25rem;
        }

        .app-pagination .page-link {
            min-width: 2.1rem;
            text-align: center;
        }

        @media (max-width: 767.98px) {
            .messages-table tbody {
                display: block;
                padding: 0.4rem;
            }

            .messages-table tbody tr {
                display: block;
                border: 1px solid rgba(15, 23, 42, 0.08);
                border-radius: 0.7rem;
                background: #fff;
                box-shadow: 0 8px 18px rgba(15, 23, 42, 0.045);
                padding: 0.52rem 0.58rem;
            }

            .messages-table tbody tr + tr {
                margin-top: 0.45rem;
            }

            .messages-table tbody td {
                display: block;
                width: 100%;
                border: 0;
                padding: 0 0 0.28rem;
            }

            .messages-table tbody td:last-child {
                padding-bottom: 0;
            }

            .messages-table tbody .tb-col {
                padding-bottom: 0.1rem;
            }

            .messages-table tbody .tb-col-sm {
                display: none;
            }

            .messages-table tbody .tb-col-md {
                padding-bottom: 0.28rem;
            }

            .messages-table tbody .tb-col-md .text-light {
                font-size: 0.6rem;
                line-height: 1.2;
                color: #98a2b3 !important;
            }

            .messages-table tbody .tb-col-end {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.4rem;
                padding-top: 0.32rem;
                border-top: 1px solid rgba(15, 23, 42, 0.08);
            }

            .messages-table tbody .tb-col-end .btn-outline-primary,
            .messages-table tbody .tb-col-end .btn-outline-secondary {
                flex: 1;
                justify-content: center;
                min-height: 1.7rem;
                padding: 0.18rem 0.4rem;
                font-size: 0.7rem;
                line-height: 1.1;
            }

            .messages-table tbody .tb-col-end .btn-outline-primary .icon,
            .messages-table tbody .tb-col-end .btn-outline-secondary .icon {
                font-size: 0.85rem;
            }

            .messages-table tbody .btn-icon {
                width: 1.7rem;
                height: 1.7rem;
            }

            .messages-table tbody .message-actions-dropdown {
                position: relative;
            }

            .messages-table tbody .message-actions-dropdown .dropdown-menu {
                inset: calc(100% + 0.45rem) 0 auto auto !important;
                transform: none !important;
                min-width: 9.75rem;
                padding: 0.25rem;
                border: 0;
                border-radius: 0.75rem;
                box-shadow: 0 18px 35px rgba(15, 23, 42, 0.14);
            }

            .messages-table tbody .message-actions-dropdown .link-list a {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.55rem;
                border-radius: 0.55rem;
                text-align: center;
                padding: 0.45rem 0.55rem !important;
                font-size: 0.72rem;
            }

            .message-subject {
                align-items: flex-start;
                gap: 0;
                font-size: 0.69rem;
                line-height: 1.15;
            }

            .message-subject strong {
                font-weight: 600;
            }

            .message-email {
                display: block;
                margin-top: 0.05rem;
                font-size: 0.56rem;
                line-height: 1.15;
            }

            #mobile-read-view {
                padding: 0.35rem 0.2rem 1.25rem;
            }

            #mobile-read-view .nk-block-head {
                margin-bottom: 1rem;
            }

            #mobile-read-subject {
                margin-bottom: 0.85rem;
                font-size: 2rem;
                line-height: 1.15;
            }

            .mobile-read-meta {
                margin-bottom: 1rem;
                font-size: 0.95rem;
            }

            #mobile-reply-btn {
                margin-bottom: 1.1rem !important;
            }

            #mobile-read-view .card {
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            }

            #mobile-read-content {
                padding: 1rem 1rem 1.15rem !important;
                font-size: 0.95rem !important;
            }

            #mobile-compose-view {
                padding: 0.35rem 0.2rem 1.25rem;
            }

            #mobile-compose-view .nk-block-head {
                margin-bottom: 0.85rem;
            }

            #mobile-compose-view .card {
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            }

            #mobile-compose-view .card-inner {
                padding: 0.95rem !important;
            }

            .app-pagination {
                padding: 0.45rem 0.45rem 0.65rem;
            }

            .app-pagination .page-link {
                min-width: 1.65rem;
                padding: 0.16rem 0.35rem;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body class="nk-body ">
    <div class="nk-app-root " data-sidebar-collapse="lg">
        <div class="nk-main">

            <?php include __DIR__ . '/../core/sidebar.php'; ?>

            <div class="nk-wrap">
                <?php include __DIR__ . '/../core/header.php'; ?>

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
                                                        <a class="btn btn-primary d-none" href="#" data-bs-toggle="modal" data-bs-target="#composeMessageModal">
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
                                            <ul class="nav nav-tabs nav-tabs-s1 px-4 message-tabs">
                                                <li class="nav-item">
                                                    <button class="nav-link <?= $currentMessageTab === 'recents' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#recents-tab" type="button" aria-selected="<?= $currentMessageTab === 'recents' ? 'true' : 'false' ?>"><?php e(t('active_messages') !== 'active_messages' ? t('active_messages') : 'Aktivní zprávy'); ?> (<span id="active-messages-count"><?= (int) $unreadMessagesCount ?></span>)</button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link <?= $currentMessageTab === 'trash' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#trash-tab" type="button" aria-selected="<?= $currentMessageTab === 'trash' ? 'true' : 'false' ?>"><?php e(t('trash') !== 'trash' ? t('trash') : 'Koš'); ?> (<?= (int) $trashedMessagesTotal ?>)</button>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane fade <?= $currentMessageTab === 'recents' ? 'show active' : '' ?>" id="recents-tab">
                                                    <div class="d-none" aria-hidden="true">
                                                        <?php foreach ($activeMessages as $msg): ?>
                                                            <div id="msg-content-<?= $msg['id'] ?>"><?php e($msg['content']); ?></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <table class="table table-middle mb-0 messages-table">
                                                        <tbody>
                                                            <?php if (empty($activeMessages)): ?>
                                                                <tr><td class="text-center py-4"><?php e(t('no_active_messages') !== 'no_active_messages' ? t('no_active_messages') : 'Žádné aktivní zprávy.'); ?></td></tr>
                                                            <?php else: ?>
                                                                <?php foreach ($activeMessages as $msg):
                                                                    $date = date('M d, Y', strtotime($msg['created_at']));
                                                                    $time = date('h:i A', strtotime($msg['created_at']));
                                                                    $senderFallback = t('system_sender') !== 'system_sender' ? t('system_sender') : 'Systém';
                                                                    $sender = $msg['sender_email'] ?: $senderFallback;
                                                                    $senderEmail = trim((string) ($msg['sender_email'] ?? ''));
                                                                    $canReply = filter_var($senderEmail, FILTER_VALIDATE_EMAIL) !== false;
                                                                ?>
                                                                <?php $isRead = (int) ($msg['is_read'] ?? 0) === 1; ?>
                                                                <tr class="message-row <?= $isRead ? 'is-read' : 'is-unread' ?>" data-message-row="<?= (int) $msg['id'] ?>">
                                                                    <td class="tb-col">
                                                                        <div class="message-main">
                                                                            <div class="message-subject line-clamp-1">
                                                                                <strong class="message-subject-text"><?php e($msg['subject']); ?></strong>
                                                                            </div>
                                                                            <div class="message-email"><?php e($sender); ?></div>
                                                                        </div>
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
                                                                                data-message-id="<?= $msg['id'] ?>"
                                                                                data-reply-email="<?php e($senderEmail); ?>"
                                                                                data-can-reply="<?= $canReply ? '1' : '0' ?>"
                                                                                onclick="readMessage(<?= $msg['id'] ?>, this)">
                                                                            <em class="icon ni ni-eye"></em> <span><?php e(t('read_btn') !== 'read_btn' ? t('read_btn') : 'Číst'); ?></span>
                                                                        </button>

                                                                        <div class="dropdown d-inline-block message-actions-dropdown">
                                                                            <button class="btn btn-sm btn-icon btn-zoom" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></button>
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <ul class="link-list link-list-hover-bg-primary link-list-md">
                                                                                    <li><a href="#" onclick="submitMessageAction('trash', <?= (int) $msg['id'] ?>); return false;"><em class="icon ni ni-trash"></em><span><?php e(t('move_to_trash') !== 'move_to_trash' ? t('move_to_trash') : 'Do koše'); ?></span></a></li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                    <?= $renderPagination($activePage, $activePages, static fn (int $pageNumber): string => $messagePageUrl('recents', $pageNumber, $trashPage)) ?>
                                                </div>

                                                <div class="tab-pane fade <?= $currentMessageTab === 'trash' ? 'show active' : '' ?>" id="trash-tab">
                                                    <div class="d-none" aria-hidden="true">
                                                        <?php foreach ($trashedMessages as $msg): ?>
                                                            <div id="msg-content-<?= $msg['id'] ?>"><?php e($msg['content']); ?></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php if (empty($trashedMessages)): ?>
                                                        <div class="text-center py-5">
                                                            <h4 class="mb-2"><?php e(t('trash_empty') !== 'trash_empty' ? t('trash_empty') : 'Koš je prázdný.'); ?></h4>
                                                        </div>
                                                    <?php else: ?>
                                                        <table class="table table-middle mb-0 messages-table">
                                                            <tbody>
                                                                <?php foreach ($trashedMessages as $msg):
                                                                    $date = date('M d, Y', strtotime($msg['created_at']));
                                                                    $time = date('h:i A', strtotime($msg['created_at']));
                                                                    $senderFallback = t('system_sender') !== 'system_sender' ? t('system_sender') : 'Systém';
                                                                    $sender = $msg['sender_email'] ?: $senderFallback;
                                                                ?>
                                                                <tr class="message-row is-read" data-message-row="<?= (int) $msg['id'] ?>">
                                                                    <td class="tb-col">
                                                                        <div class="message-main">
                                                                            <div class="message-subject line-clamp-1 text-decoration-line-through">
                                                                                <strong class="message-subject-text"><?php e($msg['subject']); ?></strong>
                                                                            </div>
                                                                            <div class="message-email"><?php e($sender); ?></div>
                                                                        </div>
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
                                                                                data-message-id="<?= $msg['id'] ?>"
                                                                                data-reply-email=""
                                                                                data-can-reply="0"
                                                                                onclick="readMessage(<?= $msg['id'] ?>, this)">
                                                                            <em class="icon ni ni-eye"></em>
                                                                        </button>
                                                                        <div class="dropdown d-inline-block message-actions-dropdown">
                                                                            <button class="btn btn-sm btn-icon btn-zoom" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></button>
                                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                                <ul class="link-list link-list-hover-bg-primary link-list-md">
                                                                                    <li><a href="#" onclick="submitMessageAction('restore', <?= (int) $msg['id'] ?>); return false;"><em class="icon ni ni-curve-up-left"></em><span><?php e(t('restore_btn') !== 'restore_btn' ? t('restore_btn') : 'Obnovit'); ?></span></a></li>
                                                                                    <li><a href="#" class="text-danger" onclick="submitMessageAction('delete', <?= (int) $msg['id'] ?>); return false;"><em class="icon ni ni-trash"></em><span><?php e(t('delete_permanently') !== 'delete_permanently' ? t('delete_permanently') : 'Trvale smazat'); ?></span></a></li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                        <?= $renderPagination($trashPage, $trashPages, static fn (int $pageNumber): string => $messagePageUrl('trash', $activePage, $pageNumber)) ?>
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
                                        <p class="text-soft mobile-read-meta"><?php e(t('from_label') !== 'from_label' ? t('from_label') : 'Od:'); ?> <strong id="mobile-read-sender"></strong> | <span id="mobile-read-date"></span></p>
                                        <button type="button" id="mobile-reply-btn" class="btn btn-primary btn-sm mb-3 d-none" onclick="replyToCurrentMessage()">
                                            <em class="icon ni ni-curve-up-left"></em> <span><?php e(t('reply_btn') !== 'reply_btn' ? t('reply_btn') : 'Odpovědět'); ?></span>
                                        </button>
                                    </div>
                                    <div class="nk-block">
                                        <div class="card card-bordered">
                                            <div class="card-inner fs-5" id="mobile-read-content" style="white-space: pre-wrap;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div id="mobile-compose-view" class="d-none">
                                    <div class="nk-block-head">
                                        <button onclick="closeMobileCompose()" class="btn btn-outline-light bg-white d-inline-flex align-items-center mb-3">
                                            <em class="icon ni ni-arrow-left"></em>
                                            <span><?php e(t('back_to_messages') !== 'back_to_messages' ? t('back_to_messages') : 'Zpět na zprávy'); ?></span>
                                        </button>
                                        <h2 class="display-6 mb-2"><?php e(t('reply_message_title') !== 'reply_message_title' ? t('reply_message_title') : 'Odpovědět na zprávu'); ?></h2>
                                        <p class="text-soft mobile-read-meta mb-0"><?php e(t('from_label') !== 'from_label' ? t('from_label') : 'Od:'); ?> <strong id="mobile-compose-sender"></strong></p>
                                    </div>
                                    <div class="nk-block">
                                        <div class="card card-bordered">
                                            <div class="card-inner">
                                                <form action="message.php" method="POST" id="mobileComposeMessageForm">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="send">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label"><?php e(t('recipient_email_label') !== 'recipient_email_label' ? t('recipient_email_label') : 'E-mail příjemce'); ?></label>
                                                        <input type="email" class="form-control" name="recipient_email" required readonly>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="form-label"><?php e(t('subject_label') !== 'subject_label' ? t('subject_label') : 'Předmět'); ?></label>
                                                        <input type="text" class="form-control" name="subject" required>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="form-label"><?php e(t('message_text_label') !== 'message_text_label' ? t('message_text_label') : 'Text zprávy'); ?></label>
                                                        <textarea class="form-control" name="content" rows="8" required></textarea>
                                                    </div>
                                                    <div class="d-grid mt-3">
                                                        <button type="submit" class="btn btn-primary" id="mobileSubmitBtn"><?php e(t('send_btn') !== 'send_btn' ? t('send_btn') : 'Odeslat'); ?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div> </div> </div> </div> <script src="assets/js/bundle.js?v1.1.0"></script>
    <script src="assets/js/scripts.js?v1.1.0"></script>

    <div class="modal fade" id="composeMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php e(t('new_message_title') !== 'new_message_title' ? t('new_message_title') : 'Nová zpráva'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="message.php" method="POST" id="composeMessageForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="send">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label"><?php e(t('recipient_email_label') !== 'recipient_email_label' ? t('recipient_email_label') : 'E-mail příjemce'); ?></label>
                            <input type="email" class="form-control" name="recipient_email" required placeholder="<?php e(t('recipient_email_placeholder') !== 'recipient_email_placeholder' ? t('recipient_email_placeholder') : 'např. kolega@firma.cz'); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label"><?php e(t('subject_label') !== 'subject_label' ? t('subject_label') : 'Předmět'); ?></label>
                            <input type="text" class="form-control" id="reply-subject" name="subject" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label"><?php e(t('message_text_label') !== 'message_text_label' ? t('message_text_label') : 'Text zprávy'); ?></label>
                            <textarea class="form-control" id="reply-content" name="content" rows="6" required></textarea>
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
                    <button type="button" id="desktop-reply-btn" class="btn btn-outline-primary d-none" onclick="replyToCurrentMessage()">
                        <em class="icon ni ni-curve-up-left"></em> <span><?php e(t('reply_btn') !== 'reply_btn' ? t('reply_btn') : 'Odpovědět'); ?></span>
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php e(t('close_btn') !== 'close_btn' ? t('close_btn') : 'Zavřít'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>';

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('composeMessageForm');
            const mobileForm = document.getElementById('mobileComposeMessageForm');
            const replyModalEl = document.getElementById('composeMessageModal');

            if (typeof bootstrap !== 'undefined' && replyModalEl) {
                replyModal = new bootstrap.Modal(replyModalEl);
            }

            if (replyModalEl) {
                const modalTitle = replyModalEl.querySelector('.modal-title');

                if (modalTitle) {
                    modalTitle.innerText = '<?php e(t('reply_message_title') !== 'reply_message_title' ? t('reply_message_title') : 'Odpovědět na zprávu'); ?>';
                }
            }

            [form, mobileForm].forEach(function(currentForm) {
                const recipientInput = currentForm ? currentForm.querySelector('[name="recipient_email"]') : null;

                if (recipientInput) {
                    recipientInput.readOnly = true;
                }
            });

            [form, mobileForm].forEach(function(currentForm) {
                if (currentForm) {
                    currentForm.addEventListener('submit', function() {
                        const submitBtn = currentForm.id === 'mobileComposeMessageForm'
                            ? document.getElementById('mobileSubmitBtn')
                            : document.getElementById('submitBtn');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php e(t('sending_btn') !== 'sending_btn' ? t('sending_btn') : 'Odesílám...'); ?>';
                        }
                    });
                }
            });
        });

        let readModal;
        let replyModal;
        let currentReplyEmail = '';
        let currentReplySubject = '';
        let pendingReplyOpen = false;
        document.addEventListener('DOMContentLoaded', function() {
            const readModalEl = document.getElementById('readMessageModal');
            if (typeof bootstrap !== 'undefined' && readModalEl) {
                readModal = new bootstrap.Modal(readModalEl);
            }

            if (readModalEl) {
                readModalEl.addEventListener('hidden.bs.modal', function() {
                    if (pendingReplyOpen && replyModal) {
                        pendingReplyOpen = false;
                        replyModal.show();
                    }
                });
            }
        });

        function syncResponsiveMessageState() {
            const mobileReadView = document.getElementById('mobile-read-view');
            const mobileComposeView = document.getElementById('mobile-compose-view');
            const readModalEl = document.getElementById('readMessageModal');
            const isMobile = window.innerWidth < 768;

            if (!isMobile && mobileReadView && !mobileReadView.classList.contains('d-none')) {
                closeMobileRead();
            }

            if (!isMobile && mobileComposeView && !mobileComposeView.classList.contains('d-none')) {
                mobileComposeView.classList.add('d-none');
                document.getElementById('messages-main-view').classList.remove('d-none');
            }

            if (isMobile && readModal && readModalEl && readModalEl.classList.contains('show')) {
                pendingReplyOpen = false;
                readModal.hide();
            }
        }

        window.addEventListener('resize', syncResponsiveMessageState);

        document.addEventListener('DOMContentLoaded', function() {
            syncResponsiveMessageState();
        });

        function updateSidebarMessageBadge(count) {
            const badge = document.getElementById('sidebar-message-badge');
            const activeMessagesCount = document.getElementById('active-messages-count');

            if (activeMessagesCount) {
                activeMessagesCount.textContent = count;
            }

            if (!badge) {
                return;
            }

            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('d-none');
            } else {
                badge.textContent = '';
                badge.classList.add('d-none');
            }
        }

        function applyMessageReadState(messageId, rowElement) {
            const row = rowElement || document.querySelector('[data-message-row="' + messageId + '"]');

            if (!row) {
                return;
            }

            row.classList.remove('is-unread');
            row.classList.add('is-read');
        }

        function markMessageAsRead(messageId) {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('id', messageId);
            formData.append('_csrf', csrfToken);

            fetch('message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && typeof data.unreadMessagesCount !== 'undefined') {
                    updateSidebarMessageBadge(Number(data.unreadMessagesCount) || 0);
                }
            })
            .catch(() => {
            });
        }

        function submitMessageAction(action, messageId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'message.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = messageId;
            form.appendChild(idInput);

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_csrf';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }

        function setReplyAvailability(canReply, replyEmail, subject) {
            currentReplyEmail = canReply ? replyEmail : '';
            currentReplySubject = canReply ? subject : '';

            const mobileReplyBtn = document.getElementById('mobile-reply-btn');
            const desktopReplyBtn = document.getElementById('desktop-reply-btn');

            [mobileReplyBtn, desktopReplyBtn].forEach(button => {
                if (!button) {
                    return;
                }

                if (canReply) {
                    button.classList.remove('d-none');
                } else {
                    button.classList.add('d-none');
                }
            });
        }

        function buildReplySubject(subject) {
            return /^re\s*:/i.test(subject) ? subject : '<?php e(t('reply_subject_prefix') !== 'reply_subject_prefix' ? t('reply_subject_prefix') : 'Re:'); ?> ' + subject;
        }

        function prepareReplyForm(form, submitButtonId) {
            if (!form) {
                return null;
            }

            const recipientInput = form.querySelector('[name="recipient_email"]');
            const subjectInput = form.querySelector('[name="subject"]');
            const contentInput = form.querySelector('[name="content"]');
            const submitBtn = document.getElementById(submitButtonId);

            if (recipientInput) {
                recipientInput.value = currentReplyEmail;
                recipientInput.readOnly = true;
            }

            if (subjectInput) {
                subjectInput.value = buildReplySubject(currentReplySubject);
            }

            if (contentInput) {
                contentInput.value = '';
            }

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = '<?php e(t('send_btn') !== 'send_btn' ? t('send_btn') : 'Odeslat'); ?>';
            }

            return contentInput;
        }

        function replyToCurrentMessage() {
            if (!currentReplyEmail) {
                return;
            }

            const form = document.getElementById('composeMessageForm');
            const mobileForm = document.getElementById('mobileComposeMessageForm');

            if (!form && !mobileForm) {
                return;
            }

            if (window.innerWidth < 768) {
                const mobileContentInput = prepareReplyForm(mobileForm, 'mobileSubmitBtn');
                const mobileSender = document.getElementById('mobile-compose-sender');

                if (mobileSender) {
                    mobileSender.innerText = currentReplyEmail;
                }

                document.getElementById('mobile-read-view').classList.add('d-none');
                document.getElementById('messages-main-view').classList.add('d-none');
                document.getElementById('mobile-compose-view').classList.remove('d-none');

                if (mobileContentInput) {
                    mobileContentInput.focus();
                }
                return;
            }

            const contentInput = prepareReplyForm(form, 'submitBtn');

            if (contentInput) {
                contentInput.focus();
            }

            if (readModal) {
                pendingReplyOpen = true;
                readModal.hide();
                return;
            }

            if (replyModal) {
                replyModal.show();
            }
        }

        function readMessage(id, btnElement) {
            const subject = btnElement.getAttribute('data-subject');
            const sender = btnElement.getAttribute('data-sender');
            const date = btnElement.getAttribute('data-date');
            const replyEmail = btnElement.getAttribute('data-reply-email') || '';
            const canReply = btnElement.getAttribute('data-can-reply') === '1';
            const contentNode = document.getElementById('msg-content-' + id);
            const content = contentNode ? contentNode.textContent : '';
            const isMobile = window.innerWidth < 768;
            const rowElement = btnElement.closest('[data-message-row]');

            setReplyAvailability(canReply, replyEmail, subject);
            applyMessageReadState(id, rowElement);
            markMessageAsRead(id);

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

                if (readModal) readModal.show();
            }
        }

        function closeMobileRead() {
            document.getElementById('mobile-read-view').classList.add('d-none');
            document.getElementById('messages-main-view').classList.remove('d-none');
        }

        function closeMobileCompose() {
            document.getElementById('mobile-compose-view').classList.add('d-none');
            document.getElementById('mobile-read-view').classList.remove('d-none');
        }
    </script>
</body>
</html>
