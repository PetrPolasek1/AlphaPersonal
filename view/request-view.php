<?php
/**
 * -------------------------------------------------
 * View: Requests
 * -------------------------------------------------
 * Renderuje seznam klientskych pozadavku
 * a modal pro detail vybraneho podani.
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
    <title><?php e(t('requests_title') !== 'requests_title' ? t('requests_title') : 'Požadavky'); ?> - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
    <style>
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
                padding: 0.4rem;
            }

            .requests-table tbody tr {
                display: block;
                border: 1px solid rgba(15, 23, 42, 0.08);
                border-radius: 0.7rem;
                background: #fff;
                box-shadow: 0 8px 18px rgba(15, 23, 42, 0.045);
                padding: 0.52rem 0.58rem;
            }

            .requests-table tbody tr + tr {
                margin-top: 0.45rem;
            }

            .requests-table tbody td {
                display: block;
                width: 100%;
                border: 0;
                padding: 0 0 0.3rem;
            }

            .requests-table tbody td:last-child {
                padding-bottom: 0;
            }

            .requests-table tbody .tb-col-end {
                text-align: left !important;
            }

            .requests-table tbody .tb-col-end > .d-inline-flex {
                width: 100%;
                justify-content: space-between;
            }

            .requests-table tbody .btn-outline-primary {
                flex: 1;
                justify-content: center;
                min-height: 1.7rem;
                padding: 0.18rem 0.4rem;
                font-size: 0.7rem;
                line-height: 1.1;
            }

            .requests-table .caption-text {
                font-size: 0.7rem;
                line-height: 1.15;
            }

            .requests-table .sub-text,
            .requests-table .text-light {
                font-size: 0.58rem !important;
                line-height: 1.15;
            }

            .requests-table .badge {
                font-size: 0.6rem !important;
                padding: 0.1rem 0.38rem !important;
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

                                <div class="nk-block-head nk-page-head">
                                    <div class="nk-block-head-between flex-wrap gap g-2">
                                        <div class="nk-block-head-content">
                                            <h2 class="display-6"><?php e(t('requests_heading') !== 'requests_heading' ? t('requests_heading') : 'Požadavky'); ?></h2>
                                            <p><?php e(t('requests_summary') !== 'requests_summary' ? t('requests_summary') : 'Přehled vašich odeslaných požadavků'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="nk-block">
                                    <div class="card shadow-none">
                                        <table class="table table-middle mb-0 requests-table">
                                            <tbody>
                                                <?php if (empty($requests)): ?>
                                                    <tr><td class="text-center py-4"><?php e(t('no_requests_found') !== 'no_requests_found' ? t('no_requests_found') : 'Žádné požadavky nebyly nalezeny.'); ?></td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($requests as $request):
                                                        $statusClass = 'text-bg-primary-soft';
                                                        switch ($request['status']) {
                                                            case 'new': $statusClass = 'text-bg-info-soft'; break;
                                                            case 'processing': $statusClass = 'text-bg-warning-soft'; break;
                                                            case 'done': $statusClass = 'text-bg-success-soft'; break;
                                                            case 'rejected': $statusClass = 'text-bg-danger-soft'; break;
                                                        }
                                                        $displayTitle = !empty($request['klientsky_nazev']) ? $request['klientsky_nazev'] : (t('untitled_request') !== 'untitled_request' ? t('untitled_request') : 'Bez názvu');
                                                    ?>
                                                    <tr data-request-row="<?= (int) $request['submission_id'] ?>">
                                                        <td class="tb-col">
                                                            <div class="caption-text line-clamp-1">
                                                                <?php if ((int) ($request['is_read'] ?? 1) === 0): ?>
                                                                    <span class="badge rounded-pill bg-danger flex-shrink-0"
                                                                        data-request-unread-dot="<?= (int) $request['submission_id'] ?>"
                                                                        style="width: 10px; height: 10px; padding: 0;"
                                                                        title="<?php e(t('request_updated_label') !== 'request_updated_label' ? t('request_updated_label') : 'Změněný požadavek'); ?>"></span>
                                                                <?php endif; ?>
                                                                <strong><?php e($displayTitle); ?></strong>
                                                            </div>
                                                            <div class="sub-text text-soft">
                                                                <?php e(t($request['typ_formulare'])); ?>
                                                            </div>
                                                        </td>

                                                        <td class="tb-col tb-col-sm">
                                                            <div class="badge <?= $statusClass ?> rounded-pill px-2 py-1 fs-6 lh-sm">
                                                                <?php e($request['status']); ?>
                                                            </div>
                                                        </td>
                                                        <td class="tb-col tb-col-md">
                                                            <div class="fs-6 text-light d-inline-flex flex-wrap gap gx-2">
                                                                <span><?= date('M d, Y', strtotime($request['datum'])) ?></span>
                                                                <span><?= date('h:i A', strtotime($request['datum'])) ?></span>
                                                            </div>
                                                        </td>
                                                        <td class="tb-col tb-col-end text-end">
                                                            <div class="d-inline-flex align-items-center gap-2">
                                                                <?php if ((int) ($request['is_read'] ?? 1) === 0): ?>
                                                                    <span
                                                                        class="d-inline-block rounded-circle bg-danger flex-shrink-0"
                                                                        data-request-unread-dot-action="<?= (int) $request['submission_id'] ?>"
                                                                        style="width: 10px; height: 10px;"
                                                                        title="<?php e(t('request_updated_label') !== 'request_updated_label' ? t('request_updated_label') : 'Změněný požadavek'); ?>"
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
                        </div>
                    </div>
                </div> </div> </div> </div>

    <div class="modal fade" id="requestDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="request-detail-title"><?php e(t('request_detail_title') !== 'request_detail_title' ? t('request_detail_title') : 'Detail požadavku'); ?></h5>
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
        });

        function openRequestDetail(button) {
            const requestId = button.getAttribute('data-request-id');
            const fallbackTitle = button.getAttribute('data-request-title') || '<?php e(t('request_detail_title') !== 'request_detail_title' ? t('request_detail_title') : 'Detail požadavku'); ?>';
            const titleEl = document.getElementById('request-detail-title');
            const metaEl = document.getElementById('request-detail-meta');
            const contentEl = document.getElementById('request-detail-content');

            titleEl.innerText = fallbackTitle;
            metaEl.innerHTML = '';
            contentEl.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php e(t('loading') !== 'loading' ? t('loading') : 'Načítání...'); ?></span>
                    </div>
                </div>
            `;

            if (requestDetailModal) {
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
                        throw new Error(data.message || '<?php e(t('request_detail_load_error') !== 'request_detail_load_error' ? t('request_detail_load_error') : 'Detail se nepodařilo načíst.'); ?>');
                    }

                    markRequestAsReadInUi(data.submissionId, data.updatedRequestsCount);
                    renderRequestDetail(data.detail);
                })
                .catch(error => {
                    contentEl.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            <em class="icon ni ni-alert-circle"></em> ${escapeHtml(error.message)}
                        </div>
                    `;
                });
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
            const titleEl = document.getElementById('request-detail-title');
            const metaEl = document.getElementById('request-detail-meta');
            const contentEl = document.getElementById('request-detail-content');

            titleEl.innerText = detail.title || '<?php e(t('request_detail_title') !== 'request_detail_title' ? t('request_detail_title') : 'Detail požadavku'); ?>';

            const submittedAt = detail.submitted_at
                ? new Date(detail.submitted_at.replace(' ', 'T'))
                : null;

            const formattedDate = submittedAt && !isNaN(submittedAt.getTime())
                ? submittedAt.toLocaleString('cs-CZ')
                : '';

            metaEl.innerHTML = `
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge text-bg-primary-soft rounded-pill px-3 py-2">${escapeHtml(detail.status || '')}</span>
                    ${formattedDate ? `<span class="text-soft small">${escapeHtml(formattedDate)}</span>` : ''}
                </div>
            `;

            if (!detail.rows || !detail.rows.length) {
                contentEl.innerHTML = '<div class="alert alert-light mb-0"><?php e(t('no_request_values') !== 'no_request_values' ? t('no_request_values') : 'Tento požadavek zatím nemá uložené žádné hodnoty.'); ?></div>';
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
                            <span class="request-detail-label">${escapeHtml(row.label || '<?php e(t('field_file_fallback') !== 'field_file_fallback' ? t('field_file_fallback') : 'Soubor'); ?>')}:</span>
                            <div class="request-detail-files">${filesHtml}</div>
                        </div>
                    `;
                }

                return `
                    <div class="request-detail-item">
                        <span class="request-detail-label">${escapeHtml(row.label || '<?php e(t('field_value_fallback') !== 'field_value_fallback' ? t('field_value_fallback') : 'Pole'); ?>')}:</span>
                        <span class="request-detail-value"> ${escapeHtml(row.value || '-')}</span>
                    </div>
                `;
            }).join('');

            contentEl.innerHTML = `<div class="request-detail-list">${rowsHtml}</div>`;
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
