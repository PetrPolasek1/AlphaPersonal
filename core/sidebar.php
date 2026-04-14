<?php
// Automatická detekce aktuální stránky pro třídu "active"
$currentPage = basename($_SERVER['PHP_SELF']);

// Pomocná funkce pro kontrolu, zda je položka aktivní
function isActive($pageName, $currentPage) {
    return ($pageName === $currentPage) ? 'active' : '';
}
?>

<div class="nk-sidebar nk-sidebar-fixed" id="sidebar">
    <div class="nk-sidebar-element nk-sidebar-head">
        <div class="nk-sidebar-brand">
            <a href="index.php" class="logo-link text-center">
                <div class="logo-wrap">
                    <img class="logo-img logo-light" src="images/logo.png" srcset="images/logo2x.png 2x" alt="Logo">
                    <img class="logo-img logo-dark" src="images/logo-dark.png" srcset="images/logo-dark2x.png 2x" alt="Logo Dark">
                </div>
            </a>
        </div>
        <div class="nk-compact-toggle me-n1">
            <button class="btn btn-xs btn-icon compact-toggle text-light bg-white rounded-3 border border-light">
                <em class="icon off ni ni-chevron-left"></em>
                <em class="icon on ni ni-chevron-right"></em>
            </button>
        </div>
    </div>

    <div class="nk-sidebar-element nk-sidebar-body">
        <div class="nk-sidebar-content h-100" data-simplebar>
            <div class="nk-sidebar-menu">
                <ul class="nk-menu">
                    
                    <li class="nk-menu-item <?= isActive('index.php', $currentPage); ?>">
                        <a href="index.php" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-dashboard-fill"></em></span>
                            <span class="nk-menu-text"><?php e(t('dashboard')); ?></span>
                        </a>
                    </li>

                    <li class="nk-menu-item <?= isActive('message.php', $currentPage); ?>">
                        <a href="message.php" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-chat-fill"></em></span>
                            <span class="nk-menu-text"><?php e(t('messages_menu')); ?></span>
                            
                            <?php if (!empty($unreadMessagesCount) && $unreadMessagesCount > 0): ?>
                                <span class="badge text-bg-danger rounded-pill ms-auto shadow-sm"><?php e($unreadMessagesCount); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nk-menu-item <?= isActive('request.php', $currentPage); ?>">
                        <a href="request.php" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span>
                            <span class="nk-menu-text"><?php e(t('requests_menu')); ?></span>
                            
                            <?php if (!empty($updatedRequestsCount) && $updatedRequestsCount > 0): ?>
                                <span class="badge text-bg-danger rounded-pill ms-auto shadow-sm"><?php e($updatedRequestsCount); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </div>

    <div class="nk-sidebar-element nk-sidebar-footer">
        <div class="nk-sidebar-footer-extended pt-3">
            <div class="border border-light rounded-3 shadow-sm bg-white mx-2 mb-3">
                <a class="d-flex px-3 py-2 align-items-center text-decoration-none" href="profile.php">
                    <div class="media-group flex-grow-1">
                        <div class="media media-sm media-middle media-circle text-bg-primary">
                            <img src="images/avatar/a.png" alt="Avatar">
                        </div>
                        <div class="media-text ms-2">
                            <h6 class="fs-13px mb-0 text-dark"><?php e($fullName ?? 'Uživatel'); ?></h6>
                        </div>
                    </div>
                    <em class="icon ni ni-chevron-right text-light fs-4"></em>
                </a>
            </div>
        </div>
    </div>
</div>