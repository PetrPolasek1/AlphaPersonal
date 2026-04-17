<div class="nk-header nk-header-fixed">
    <div class="container-fluid">
        <div class="nk-header-wrap">
            <div class="nk-header-logo ms-n1">
                <div class="nk-sidebar-toggle me-1">
                    <button class="btn btn-sm btn-zoom btn-icon sidebar-toggle d-sm-none">
                        <em class="icon ni ni-menu"></em>
                    </button>
                </div>
            </div>
            <div class="nk-header-tools">
                <ul class="nk-quick-nav ms-2">
                    <li class="dropdown d-inline-flex">
                        <a data-bs-toggle="dropdown" class="d-inline-flex" href="#">
                            <div class="media media-md media-circle media-middle text-bg-primary">
                                <img src="images/avatar/a.png" alt="Avatar">
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-md rounded-3">
                            <div class="dropdown-content py-3">
                                <div class="d-flex px-3 py-2 bg-primary bg-opacity-10 rounded-bottom-3">
                                    <div class="media-text">
                                        <h6 class="fs-6 mb-0"><?php e($fullName ?? (t('default_user_name') !== 'default_user_name' ? t('default_user_name') : 'Uživatel')); ?></h6>
                                    </div>
                                </div>
                                <div class="px-3 pt-3 d-grid gap-2">
                                    <a href="profile.php" class="btn btn-outline-primary btn-sm"><?php e(t('profile_btn') !== 'profile_btn' ? t('profile_btn') : 'Profil'); ?></a>
                                    <form action="api/client/auth/logout.php" method="POST" class="d-grid" onsubmit="clearClientStoredTokens()">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><?php e(t('logout_btn') !== 'logout_btn' ? t('logout_btn') : 'Odhlásit se'); ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
    function clearClientStoredTokens() {
        try {
            sessionStorage.removeItem('client_access_token');
            sessionStorage.removeItem('client_refresh_token');
        } catch (error) {
        }
    }
</script>
