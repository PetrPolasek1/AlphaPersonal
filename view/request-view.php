<?php
/**
 * -------------------------------------------------
 * View: Requests
 * -------------------------------------------------
 * Render request list and detail UI.
 * Desktop uses modal. Mobile uses
 * in-page detail screen like messages.
 */
?>
<?php
$requestPageUrl = static function (int $pageNumber): string {
    return 'request.php?' . http_build_query([
        'page' => max(1, $pageNumber),
    ]);
};

$renderRequestPagination = static function (int $currentPageNumber, int $totalPageCount, callable $urlBuilder): string {
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
                <a class="page-link" href="<?= $currentPageNumber <= 1 ? '#' : htmlspecialchars($urlBuilder($currentPageNumber - 1), ENT_QUOTES, 'UTF-8') ?>">&#8249;</a>
            </li>
            <?php for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++): ?>
                <li class="page-item <?= $pageNumber === $currentPageNumber ? 'active' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($urlBuilder($pageNumber), ENT_QUOTES, 'UTF-8') ?>"><?= $pageNumber ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $currentPageNumber >= $totalPageCount ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $currentPageNumber >= $totalPageCount ? '#' : htmlspecialchars($urlBuilder($currentPageNumber + 1), ENT_QUOTES, 'UTF-8') ?>">&#8250;</a>
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
    <title><?php e(t('requests_title') !== 'requests_title' ? t('requests_title') : 'Requests'); ?> - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
    <style>
        .request-card-main {
            min-width: 0;
        }

        .request-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            min-width: 0;
        }

        .request-card-title-wrap {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            min-width: 0;
            flex: 1 1 auto;
        }

        .request-card-title {
            min-width: 0;
            font-size: 0.95rem;
            line-height: 1.35;
            color: #1f2b3d;
        }

        .request-card-title strong {
            display: block;
            overflow-wrap: anywhere;
        }

        .request-card-type {
            margin-top: 0.2rem;
            color: #7b8794;
            font-size: 0.78rem;
            line-height: 1.35;
            word-break: break-word;
        }

            .request-card-status {
                display: none;
                flex: 0 0 auto;
            }

        #mobile-request-detail-status {
            display: none !important;
        }

        .request-status-badge {
            min-width: 0;
            font-size: 0.72rem !important;
            font-weight: 700;
            padding: 0.26rem 0.68rem !important;
            border-radius: 0.85rem !important;
            white-space: nowrap;
        }

        .request-card-meta {
            font-size: 0.78rem;
            line-height: 1.35;
            color: #98a2b3;
            white-space: nowrap;
        }

        .requests-table .tb-col-status {
            width: 150px;
            text-align: center;
        }

        .requests-table .tb-col-time {
            width: 210px;
            text-align: center;
        }

        .requests-table .tb-col-time .request-card-meta {
            display: inline-flex;
            justify-content: center;
            gap: 0.55rem;
        }

        .request-updated-dot {
            width: 10px;
            height: 10px;
            padding: 0;
            display: inline-block !important;
            flex: 0 0 auto;
            margin-top: 0.35rem;
            border-radius: 999px;
            background-color: #e85347 !important;
            box-shadow: 0 0 0 2px #fff;
        }

        .request-updated-dot-action {
            display: inline-block;
            margin-right: 0.15rem;
        }

        .request-mobile-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .request-mobile-meta {
            line-height: 1.6;
        }

        #mobile-request-detail-view .card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
        }

        .request-detail-list {
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
        }

        .request-detail-item {
            padding: 0.95rem 1rem;
            line-height: 1.65;
        }

        .request-detail-item + .request-detail-item {
            border-top: 1px solid rgba(15, 23, 42, 0.08);
        }

        .request-detail-label {
            font-weight: 700;
            color: #364152;
        }

        .request-detail-value {
            color: #526484;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .request-detail-files {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.55rem;
        }

        .request-status-flow {
            display: grid;
            grid-template-columns: auto 1fr auto 1fr auto;
            align-items: start;
            column-gap: 0;
            width: 100%;
            margin-bottom: 0.6rem;
        }

        .request-status-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.45rem;
            flex: 0 0 auto;
            min-width: 0;
            transition: transform 0.2s ease;
            position: relative;
            z-index: 1;
        }

        .request-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 76px;
            min-height: 40px;
            padding: 0.5rem 0.95rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
            background: #eceef3;
            color: #98a2b3;
            border: 1px solid #d8dee8;
            box-shadow: 0 0 0 3px #fff;
        }

        .request-status-pill.is-active.text-bg-info-soft,
        .request-status-pill.is-active.text-bg-warning-soft,
        .request-status-pill.is-active.text-bg-success-soft,
        .request-status-pill.is-active.text-bg-danger-soft {
            color: inherit;
        }

        .request-status-pill.is-active.text-bg-info-soft {
            color: #07BDF5;
            background-color: #e1f7fe !important;
            border-color: #b5ebfc;
        }

        .request-status-pill.is-active.text-bg-warning-soft {
            color: #f2bc16;
            background-color: #fdf7e3 !important;
            border-color: #fbebb9;
        }

        .request-status-pill.is-active.text-bg-success-soft {
            color: #2dc58c;
            background-color: #e6f8f1 !important;
            border-color: #c0eedd;
        }

        .request-status-pill.is-active.text-bg-danger-soft {
            color: #df3c4e;
            background-color: #fbe8ea !important;
            border-color: #f5c5ca;
        }

        .request-status-step:first-child .request-status-pill {
            min-width: 64px;
            padding-right: 1.2rem;
        }

        .request-status-time {
            font-size: 0.75rem;
            line-height: 1.2;
            color: #98a2b3;
            text-align: center;
            min-height: 2.4em;
            max-width: 88px;
        }

        .request-status-connector {
            position: relative;
            height: 40px;
            z-index: 0;
        }

        .request-status-connector::before {
            content: "";
            position: absolute;
            top: 18px;
            left: -38px;
            right: -38px;
            height: 4px;
            border-radius: 999px;
            background: rgba(17, 24, 39, 0.9);
            opacity: 0.12;
        }

        .request-status-connector.is-active::before {
            opacity: 1;
        }

        @media (max-width: 575.98px) {
            .request-status-flow {
                grid-template-columns: auto 1fr auto 1fr auto;
            }

            .request-status-pill {
                min-width: 0;
                min-height: 36px;
                padding: 0.45rem 0.8rem;
                font-size: 0.8rem;
            }

            .request-status-step:first-child .request-status-pill {
                min-width: 58px;
                padding-right: 1rem;
            }

            .request-status-time {
                max-width: 72px;
                font-size: 0.7rem;
            }

            .request-status-connector {
                height: 36px;
            }

            .request-status-connector::before {
                top: 16px;
                left: -34px;
                right: -34px;
            }
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
            .requests-table tbody {
                display: block;
                padding: 1rem 1.05rem 1.25rem;
            }

            .requests-table tbody tr {
                display: block;
                border: 1px solid rgba(15, 23, 42, 0.08);
                border-radius: 1.05rem;
                background: #fff;
                box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
                padding: 0 !important;
                overflow: hidden;
            }

            .requests-table tbody tr + tr {
                margin-top: 1.05rem;
            }

            .requests-table tbody td {
                display: block;
                width: 100%;
                border: 0;
                padding: 0 1.25rem 0.6rem !important;
            }

            .requests-table tbody td:last-child {
                padding-bottom: 0;
            }

            .requests-table tbody .tb-col {
                padding-top: 1.15rem !important;
                padding-bottom: 0.5rem !important;
            }

            .requests-table tbody .tb-col-md {
                display: none;
            }

            .requests-table tbody .tb-col-status {
                display: none;
            }

            .requests-table tbody .tb-col-end {
                text-align: left !important;
                padding-top: 0.4rem !important;
                padding-bottom: 1.15rem !important;
            }

            .requests-table tbody .tb-col-end > .d-inline-flex {
                display: flex !important;
                width: 100%;
                justify-content: center;
            }

            .requests-table tbody .btn-outline-primary {
                display: inline-flex;
                flex: 1 1 100%;
                width: 100%;
                max-width: 100%;
                justify-content: center;
                min-height: 3rem;
                padding: 0.75rem 0.9rem;
                font-size: 0.88rem;
                font-weight: 600;
                line-height: 1.1;
                margin: 0;
                border-radius: 0.5rem;
                box-sizing: border-box;
                transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
            }

            .requests-table tbody .btn-outline-primary:hover,
            .requests-table tbody .btn-outline-primary:focus,
            .requests-table tbody .btn-outline-primary:active {
                background-color: #4f46e5;
                border-color: #4f46e5;
                color: #fff;
                box-shadow: none;
            }

            .request-card-title {
                font-size: 0.96rem;
                line-height: 1.3;
                margin-top: 0;
            }

            .request-card-type,
            .request-card-meta {
                font-size: 0.76rem !important;
                line-height: 1.35;
            }

            .request-card-header {
                align-items: flex-start;
                gap: 0.75rem;
            }

            .request-card-title-wrap {
                display: block;
                padding-top: 0;
            }

            .request-card-status {
                display: block;
            }

            .request-updated-dot {
                width: 0.95rem;
                height: 0.95rem;
                margin-top: 0;
                box-shadow: 0 0 0 0.16rem #fff;
            }

            .request-updated-dot-action {
                display: inline-block;
            }

            .app-pagination {
                padding: 0.45rem 0.45rem 0.65rem;
            }

            .app-pagination .page-link {
                min-width: 1.65rem;
                padding: 0.16rem 0.35rem;
                font-size: 0.7rem;
            }

            #requests-main-view {
                padding-bottom: 1.25rem;
            }

            #mobile-request-detail-view {
                padding: 0.35rem 0.2rem 1.25rem;
            }

            #mobile-request-detail-view .nk-block-head {
                margin-bottom: 1rem;
            }

            #mobile-request-detail-view .request-mobile-title {
                margin-bottom: 0.5rem;
                font-size: 2rem;
                line-height: 1.15;
            }

            #mobile-request-detail-view .request-status-flow {
                margin-top: 1rem;
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

                                <div id="requests-main-view">
                                    <div class="nk-block-head nk-page-head">
                                        <div class="nk-block-head-between flex-wrap gap g-2">
                                            <div class="nk-block-head-content">
                                                <h2 class="display-6"><?php e(t('requests_heading') !== 'requests_heading' ? t('requests_heading') : 'Requests'); ?></h2>
                                                <p><?php e(t('requests_summary') !== 'requests_summary' ? t('requests_summary') : 'Overview of all your submitted forms.'); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="nk-block">
                                        <div class="card shadow-none">
                                            <table class="table table-middle mb-0 requests-table">
                                                <tbody>
                                                    <?php if (empty($requests)): ?>
                                                        <tr><td class="text-center py-4"><?php e(t('no_requests_found') !== 'no_requests_found' ? t('no_requests_found') : 'No requests found.'); ?></td></tr>
                                                    <?php else: ?>
                                                        <?php foreach ($requests as $request):
                                                            $statusClass = 'text-bg-primary-soft';
                                                            $statusLabel = (string) ($request['status'] ?? '');
                                                            $showUpdatedDot = (int) ($request['is_read'] ?? 1) === 0;
                                                            switch ($request['status']) {
                                                                case 'new':
                                                                    $statusClass = 'text-bg-info-soft';
                                                                    $statusLabel = t('status_new') !== 'status_new' ? t('status_new') : 'New';
                                                                    break;
                                                                case 'processing':
                                                                    $statusClass = 'text-bg-warning-soft';
                                                                    $statusLabel = t('status_processing') !== 'status_processing' ? t('status_processing') : 'Processing';
                                                                    break;
                                                                case 'done':
                                                                    $statusClass = 'text-bg-success-soft';
                                                                    $statusLabel = t('status_done') !== 'status_done' ? t('status_done') : 'Completed';
                                                                    break;
                                                                case 'rejected':
                                                                    $statusClass = 'text-bg-danger-soft';
                                                                    $statusLabel = t('status_rejected') !== 'status_rejected' ? t('status_rejected') : 'Rejected';
                                                                    break;
                                                            }
                                                            $displayTitle = !empty($request['klientsky_nazev']) ? $request['klientsky_nazev'] : (t('untitled_request') !== 'untitled_request' ? t('untitled_request') : 'Untitled request');
                                                        ?>
                                                        <tr data-request-row="<?= (int) $request['submission_id'] ?>">
                                                            <td class="tb-col">
                                                                <div class="request-card-main">
                                                                    <div class="request-card-header">
                                                                        <div class="request-card-title-wrap">
                                                                            <div class="request-card-title">
                                                                                <strong><?php e($displayTitle); ?></strong>
                                                                                <div class="request-card-type">
                                                                                    <?php e(t($request['typ_formulare'])); ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="request-card-status">
                                                                            <div class="badge <?= $statusClass ?> rounded-pill px-2 py-1 fs-6 lh-sm">
                                                                                <?php e($statusLabel); ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="tb-col tb-col-status">
                                                                <div class="badge <?= $statusClass ?> request-status-badge">
                                                                    <?php e($statusLabel); ?>
                                                                </div>
                                                            </td>
                                                            <td class="tb-col tb-col-md tb-col-time">
                                                                <div class="request-card-meta">
                                                                    <span><?= date('M d, Y', strtotime($request['datum'])) ?></span>
                                                                    <span><?= date('h:i A', strtotime($request['datum'])) ?></span>
                                                                </div>
                                                            </td>
                                                            <td class="tb-col tb-col-end text-end">
                                                                <div class="d-inline-flex align-items-center gap-2">
                                                                    <?php if ($showUpdatedDot): ?>
                                                                        <span
                                                                            class="request-updated-dot request-updated-dot-action"
                                                                            data-request-unread-dot-action="<?= (int) $request['submission_id'] ?>"
                                                                            title="<?php e(t('request_updated_label') !== 'request_updated_label' ? t('request_updated_label') : 'Updated request'); ?>"
                                                                        ></span>
                                                                    <?php endif; ?>
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        data-request-id="<?= (int) $request['submission_id'] ?>"
                                                                        data-request-title="<?php e($displayTitle); ?>"
                                                                        onclick="openRequestDetail(this)"
                                                                    >
                                                                        <em class="icon ni ni-eye"></em> <span><?php e(t('request_detail_btn') !== 'request_detail_btn' ? t('request_detail_btn') : 'Detail'); ?></span>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                            <?= $renderRequestPagination($requestsPage, $requestsPages, static fn (int $pageNumber): string => $requestPageUrl($pageNumber)) ?>
                                        </div>
                                    </div>
                                </div>

                                <div id="mobile-request-detail-view" class="d-none">
                                    <div class="nk-block-head">
                                        <button onclick="closeMobileRequestDetail()" class="btn btn-outline-light bg-white d-inline-flex align-items-center mb-3">
                                            <em class="icon ni ni-arrow-left"></em>
                                            <span><?php e(t('back_to_requests') !== 'back_to_requests' ? t('back_to_requests') : 'Back to requests'); ?></span>
                                        </button>
                                        <div class="request-mobile-header">
                                            <div class="min-w-0">
                                                <h2 class="display-6 request-mobile-title" id="mobile-request-detail-title"><?php e(t('request_detail_title') !== 'request_detail_title' ? t('request_detail_title') : 'Request detail'); ?></h2>
                                                <p class="text-soft request-mobile-meta mb-0">
                                                    <span id="mobile-request-detail-type"></span>
                                                    <span id="mobile-request-detail-date"></span>
                                                </p>
                                            </div>
                                            <div id="mobile-request-detail-status"></div>
                                        </div>
                                        <div id="mobile-request-detail-meta" class="mt-4"></div>
                                    </div>
                                    <div class="nk-block">
                                        <div class="card card-bordered">
                                            <div class="card-inner" id="mobile-request-detail-content"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div> </div> </div> </div>

    <div class="modal fade" id="requestDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="request-detail-title"><?php e(t('request_detail_title') !== 'request_detail_title' ? t('request_detail_title') : 'Request detail'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="request-detail-meta" class="mb-4"></div>
                    <div id="request-detail-content"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bundle.js?v1.1.0"></script>
    <script src="assets/js/scripts.js?v1.1.0"></script>
    <script>
        const csrfToken = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>';
        let requestDetailModal;

        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('requestDetailModal');

            if (typeof bootstrap !== 'undefined' && modalEl) {
                requestDetailModal = new bootstrap.Modal(modalEl);
            }

            syncResponsiveRequestState();
        });

        window.addEventListener('resize', syncResponsiveRequestState);

        function openRequestDetail(button) {
            const requestId = button.getAttribute('data-request-id');
            const fallbackTitle = button.getAttribute('data-request-title') || '<?php e(t('request_detail_title') !== 'request_detail_title' ? t('request_detail_title') : 'Request detail'); ?>';
            const isMobile = window.innerWidth < 768;

            const desktopTitleEl = document.getElementById('request-detail-title');
            const desktopMetaEl = document.getElementById('request-detail-meta');
            const desktopContentEl = document.getElementById('request-detail-content');

            const mobileTitleEl = document.getElementById('mobile-request-detail-title');
            const mobileTypeEl = document.getElementById('mobile-request-detail-type');
            const mobileDateEl = document.getElementById('mobile-request-detail-date');
            const mobileStatusEl = document.getElementById('mobile-request-detail-status');
            const mobileMetaEl = document.getElementById('mobile-request-detail-meta');
            const mobileContentEl = document.getElementById('mobile-request-detail-content');

            const loadingHtml = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php e(t('loading') !== 'loading' ? t('loading') : 'Loading...'); ?></span>
                    </div>
                </div>
            `;

            desktopTitleEl.innerText = fallbackTitle;
            desktopMetaEl.innerHTML = '';
            desktopContentEl.innerHTML = loadingHtml;

            mobileTitleEl.innerText = fallbackTitle;
            mobileTypeEl.innerText = '';
            mobileDateEl.innerText = '';
            if (mobileStatusEl) {
                mobileStatusEl.innerHTML = '';
            }
            mobileMetaEl.innerHTML = '';
            mobileContentEl.innerHTML = loadingHtml;

            if (isMobile) {
                document.getElementById('requests-main-view').classList.add('d-none');
                document.getElementById('mobile-request-detail-view').classList.remove('d-none');
            } else if (requestDetailModal) {
                requestDetailModal.show();
            }

            const formData = new FormData();
            formData.append('action', 'detail');
            formData.append('id', requestId);
            formData.append('_csrf', csrfToken);

            fetch('request.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || '<?php e(t('request_detail_load_error') !== 'request_detail_load_error' ? t('request_detail_load_error') : 'Failed to load detail.'); ?>');
                    }

                    markRequestAsReadInUi(data.submissionId, data.updatedRequestsCount);
                    renderRequestDetail(data.detail);
                })
                .catch(error => {
                    const errorHtml = `
                        <div class="alert alert-danger mb-0">
                            <em class="icon ni ni-alert-circle"></em> ${escapeHtml(error.message)}
                        </div>
                    `;

                    desktopContentEl.innerHTML = errorHtml;
                    mobileContentEl.innerHTML = errorHtml;
                });
        }

        function syncResponsiveRequestState() {
            const mobileDetailView = document.getElementById('mobile-request-detail-view');
            const modalEl = document.getElementById('requestDetailModal');
            const isMobile = window.innerWidth < 768;

            if (!isMobile && mobileDetailView && !mobileDetailView.classList.contains('d-none')) {
                closeMobileRequestDetail();
            }

            if (isMobile && requestDetailModal && modalEl && modalEl.classList.contains('show')) {
                requestDetailModal.hide();
            }
        }

        function closeMobileRequestDetail() {
            document.getElementById('mobile-request-detail-view').classList.add('d-none');
            document.getElementById('requests-main-view').classList.remove('d-none');
            document.getElementById('mobile-request-detail-meta').innerHTML = '';
            document.getElementById('mobile-request-detail-content').innerHTML = '';
            const mobileStatusEl = document.getElementById('mobile-request-detail-status');
            if (mobileStatusEl) {
                mobileStatusEl.innerHTML = '';
            }
        }

        function markRequestAsReadInUi(submissionId, updatedRequestsCount) {
            if (!submissionId) {
                return;
            }

            const unreadDot = document.querySelector('[data-request-unread-dot="' + submissionId + '"]');
            if (unreadDot) {
                unreadDot.remove();
            }

            const actionUnreadDot = document.querySelector('[data-request-unread-dot-action="' + submissionId + '"]');
            if (actionUnreadDot) {
                actionUnreadDot.remove();
            }

            const sidebarBadge = document.getElementById('sidebar-request-badge');
            if (!sidebarBadge) {
                return;
            }

            const count = Number(updatedRequestsCount || 0);
            sidebarBadge.textContent = count > 0 ? String(count) : '';
            sidebarBadge.classList.toggle('d-none', count <= 0);
        }

        function renderRequestDetail(detail) {
            const desktopTitleEl = document.getElementById('request-detail-title');
            const desktopMetaEl = document.getElementById('request-detail-meta');
            const desktopContentEl = document.getElementById('request-detail-content');

            const mobileTitleEl = document.getElementById('mobile-request-detail-title');
            const mobileTypeEl = document.getElementById('mobile-request-detail-type');
            const mobileDateEl = document.getElementById('mobile-request-detail-date');
            const mobileStatusEl = document.getElementById('mobile-request-detail-status');
            const mobileMetaEl = document.getElementById('mobile-request-detail-meta');
            const mobileContentEl = document.getElementById('mobile-request-detail-content');

            const phaseOrder = ['new', 'processing', 'final'];
            const phaseLabels = {
                new: '<?php e(t('status_new') !== 'status_new' ? t('status_new') : 'New'); ?>',
                processing: '<?php e(t('status_processing') !== 'status_processing' ? t('status_processing') : 'Processing'); ?>',
                final: ''
            };
            const phaseClasses = {
                new: 'text-bg-info-soft',
                processing: 'text-bg-warning-soft',
                done: 'text-bg-success-soft',
                rejected: 'text-bg-danger-soft'
            };

            const resolvedTitle = detail.display_title || detail.title || '<?php e(t('request_detail_title') !== 'request_detail_title' ? t('request_detail_title') : 'Request detail'); ?>';
            const statusClass = detail.status_class || 'text-bg-primary-soft';
            const statusLabel = detail.status_label || detail.status || '';
            const formTypeLabel = detail.form_type_label || '';

            desktopTitleEl.innerText = resolvedTitle;
            mobileTitleEl.innerText = resolvedTitle;

            const submittedAt = detail.submitted_at
                ? new Date(detail.submitted_at.replace(' ', 'T'))
                : null;

            const formattedDate = submittedAt && !isNaN(submittedAt.getTime())
                ? submittedAt.toLocaleString('cs-CZ')
                : '';

            const normalizedStatus = String(detail.status || '').toLowerCase();
            const timeline = detail.status_timeline || {};
            const isKnownPhase = ['new', 'processing', 'done', 'rejected'].includes(normalizedStatus);
            const finalPhaseKey = normalizedStatus === 'rejected' ? 'rejected' : 'done';

            phaseLabels.final = finalPhaseKey === 'rejected'
                ? '<?php e(t('status_rejected') !== 'status_rejected' ? t('status_rejected') : 'Rejected'); ?>'
                : '<?php e(t('status_done') !== 'status_done' ? t('status_done') : 'Done'); ?>';

            const trackerHtml = isKnownPhase
                ? phaseOrder.map((phase, index) => `
                    <div class="request-status-step">
                        <span class="request-status-pill ${isPhaseReached(phase, normalizedStatus) ? `is-active ${phaseClasses[getPhaseColorKey(phase, finalPhaseKey)]}` : ''}">${escapeHtml(phaseLabels[phase])}</span>
                        <span class="request-status-time">${getPhaseTimestamp(phase, timeline, finalPhaseKey) ? escapeHtml(formatPhaseDate(getPhaseTimestamp(phase, timeline, finalPhaseKey))) : '&nbsp;'}</span>
                    </div>
                    ${index < phaseOrder.length - 1 ? `<span class="request-status-connector ${connectorIsActive(index, normalizedStatus) ? 'is-active' : ''}"></span>` : ''}
                `).join('')
                : '';

            desktopMetaEl.innerHTML = `
                <div class="d-flex flex-column gap-2">
                    ${trackerHtml ? `<div class="request-status-flow">${trackerHtml}</div>` : ''}
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        ${!isKnownPhase && statusLabel ? `<span class="badge ${escapeAttribute(statusClass)} rounded-pill px-3 py-2">${escapeHtml(statusLabel)}</span>` : ''}
                        ${!isKnownPhase && formattedDate ? `<span class="text-soft small">${escapeHtml(formattedDate)}</span>` : ''}
                    </div>
                </div>
            `;

            mobileTypeEl.innerText = formTypeLabel;
            mobileDateEl.innerText = formattedDate ? ` | ${formattedDate}` : '';
            if (mobileStatusEl) {
                mobileStatusEl.innerHTML = statusLabel
                    ? `<span class="badge ${escapeAttribute(statusClass)} rounded-pill px-3 py-2">${escapeHtml(statusLabel)}</span>`
                    : '';
            }
            mobileMetaEl.innerHTML = trackerHtml ? `<div class="request-status-flow">${trackerHtml}</div>` : '';

            if (!detail.rows || !detail.rows.length) {
                const emptyHtml = '<div class="alert alert-light mb-0"><?php e(t('no_request_values') !== 'no_request_values' ? t('no_request_values') : 'This request does not have any saved values yet.'); ?></div>';
                desktopContentEl.innerHTML = emptyHtml;
                mobileContentEl.innerHTML = emptyHtml;
                return;
            }

            const rowsHtml = detail.rows.map(row => {
                if (row.files && row.files.length) {
                    const filesHtml = row.files.map(file => `
                        <a href="${escapeAttribute(file.url)}" download class="btn btn-sm btn-outline-primary">
                            <em class="icon ni ni-download"></em> <span>${escapeHtml(file.name)}</span>
                        </a>
                    `).join('');

                    return `
                        <div class="request-detail-item">
                            <span class="request-detail-label">${escapeHtml(row.label || '<?php e(t('field_file_fallback') !== 'field_file_fallback' ? t('field_file_fallback') : 'File'); ?>')}:</span>
                            <div class="request-detail-files">${filesHtml}</div>
                        </div>
                    `;
                }

                return `
                    <div class="request-detail-item">
                        <span class="request-detail-label">${escapeHtml(row.label || '<?php e(t('field_value_fallback') !== 'field_value_fallback' ? t('field_value_fallback') : 'Field'); ?>')}:</span>
                        <span class="request-detail-value"> ${escapeHtml(row.value || '-')}</span>
                    </div>
                `;
            }).join('');

            const contentHtml = `<div class="request-detail-list">${rowsHtml}</div>`;
            desktopContentEl.innerHTML = contentHtml;
            mobileContentEl.innerHTML = contentHtml;
        }

        function formatPhaseDate(value) {
            const parsed = value ? new Date(String(value).replace(' ', 'T')) : null;

            if (!parsed || isNaN(parsed.getTime())) {
                return '';
            }

            return parsed.toLocaleString('cs-CZ');
        }

        function getPhaseStateKey(phase, finalPhaseKey) {
            if (phase === 'final') {
                return finalPhaseKey;
            }

            return phase;
        }

        function getPhaseColorKey(phase, finalPhaseKey) {
            if (phase === 'final') {
                return finalPhaseKey;
            }

            return phase;
        }

        function getPhaseTimestamp(phase, timeline, finalPhaseKey) {
            const stateKey = getPhaseStateKey(phase, finalPhaseKey);
            return timeline[stateKey] || '';
        }

        function isPhaseReached(phase, currentStatus) {
            if (phase === 'new') {
                return ['new', 'processing', 'done', 'rejected'].includes(currentStatus);
            }

            if (phase === 'processing') {
                return ['processing', 'done', 'rejected'].includes(currentStatus);
            }

            if (phase === 'final') {
                return ['done', 'rejected'].includes(currentStatus);
            }

            return false;
        }

        function connectorIsActive(index, currentStatus) {
            if (index === 0) {
                return ['processing', 'done', 'rejected'].includes(currentStatus);
            }

            if (index === 1) {
                return ['done', 'rejected'].includes(currentStatus);
            }

            return false;
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeAttribute(value) {
            return escapeHtml(value);
        }
    </script>
</body>
</html>
