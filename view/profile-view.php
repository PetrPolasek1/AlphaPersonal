<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">
    <title><?php e(t('profile_title') !== 'profile_title' ? t('profile_title') : 'Můj Profil'); ?> - CopyGen</title>
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
                                <li class="nk-menu-item"><a href="index.php" class="nk-menu-link"><span class="nk-menu-icon"><em class="icon ni ni-dashboard-fill"></em></span><span class="nk-menu-text"><?php e(t('dashboard') !== 'dashboard' ? t('dashboard') : 'Dashboard'); ?></span></a></li>
                                <li class="nk-menu-item"><a href="message.php" class="nk-menu-link"><span class="nk-menu-icon"><em class="icon ni ni-chat-fill"></em></span><span class="nk-menu-text"><?php e(t('messages_menu') !== 'messages_menu' ? t('messages_menu') : 'Zprávy'); ?></span></a></li>
                                <li class="nk-menu-item"><a href="pozadavky.html" class="nk-menu-link"><span class="nk-menu-icon"><em class="icon ni ni-file-docs"></em></span><span class="nk-menu-text"><?php e(t('requests_menu') !== 'requests_menu' ? t('requests_menu') : 'Požadavky zaměstnanců'); ?></span></a></li>
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
                                                    <div class="media-text"><h6 class="fs-6 mb-0"><?php e($fullName); ?></h6></div>
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
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                
                                <div class="row justify-content-center mt-4">
                                    <div class="col-12 col-lg-8 col-xl-6 col-xxl-5">
                                        
                                        <div class="nk-block-head nk-page-head text-center mb-5">
                                            <div class="nk-block-head-content">
                                                <h2 class="display- display-5"><?php e(t('profile_title') !== 'profile_title' ? t('profile_title') : 'Můj Profil'); ?></h2>
                                            </div>
                                        </div>

                                        <?php if (!empty($errorMsg)): ?>
                                            <div class="alert alert-danger mb-4 shadow-sm"><?php e($errorMsg); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($successMsg)): ?>
                                            <div class="alert alert-success mb-4 shadow-sm"><?php e($successMsg); ?></div>
                                        <?php endif; ?>

                                        <div class="nk-block">
                                            
                                            <div class="card card-bordered card-preview shadow-sm mb-4">
                                                <div class="card-inner p-4 p-sm-5">
                                                    <div class="d-flex align-items-center justify-content-center mb-4 pb-2">
                                                        <div class="media media-lg media-circle media-middle text-bg-primary shadow-sm me-3"><img src="images/avatar/a.png" /></div>
                                                        <h4 class="mb-0 fs-2"><?php e(t('personal_details') !== 'personal_details' ? t('personal_details') : 'Osobní údaje'); ?></h4>
                                                    </div>
                                                    
                                                    <table class="table table-flush table-middle mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <td class="tb-col py-3"><span class="fs-15px text-light"><?php e(t('full_name') !== 'full_name' ? t('full_name') : 'Celé jméno'); ?></span></td>
                                                                <td class="tb-col py-3"><span class="fs-15px text-base"><strong><?php e($fullName); ?></strong></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="tb-col py-3"><span class="fs-15px text-light"><?php e(t('email_address') !== 'email_address' ? t('email_address') : 'Login E-mail'); ?></span></td>
                                                                <td class="tb-col py-3"><span class="fs-15px text-base"><strong><?php e($email); ?></strong></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="tb-col py-3"><span class="fs-15px text-light"><?php e(t('address_label') !== 'address_label' ? t('address_label') : 'Adresa'); ?></span></td>
                                                                <td class="tb-col py-3"><span class="fs-15px text-base"><strong><?php e($adresa ?: '-'); ?></strong></span></td>
                                                            </tr>
                                                            
                                                            <?php foreach ($kontakty as $kontakt): ?>
                                                                <tr>
                                                                    <td class="tb-col py-3">
                                                                        <span class="fs-15px text-light">
                                                                            <?php 
                                                                                $contactKey = 'contact_type_' . strtolower($kontakt['typ']);
                                                                                e(t($contactKey) !== $contactKey ? t($contactKey) : ucfirst($kontakt['typ'])); 
                                                                            ?>
                                                                        </span>
                                                                    </td>
                                                                    <td class="tb-col py-3">
                                                                        <span class="fs-15px text-base"><strong><?php e($kontakt['kontakt']); ?></strong></span>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <div class="card card-bordered card-preview shadow-sm">
                                                <div class="card-inner p-4 p-sm-5">
                                                    <div class="text-center mb-5 pb-2">
                                                        <em class="icon ni ni-lock-alt fs-1 text-primary bg-primary bg-opacity-10 p-3 rounded-circle shadow-sm"></em>
                                                        <h4 class="mt-4 mb-0 fs-2"><?php e(t('change_password') !== 'change_password' ? t('change_password') : 'Změna hesla'); ?></h4>
                                                    </div>
                                                    
                                                    <form action="profile.php" method="POST">
                                                        <input type="hidden" name="action" value="change_password">
                                                        
                                                        <div class="form-group mb-3">
                                                            <label class="form-label fs-15px text-soft"><?php e(t('old_password') !== 'old_password' ? t('old_password') : 'Aktuální heslo'); ?></label>
                                                            <div class="form-control-wrap">
                                                                <input type="password" class="form-control form-control-lg fs-16px" name="old_password" required>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label class="form-label fs-15px text-soft"><?php e(t('new_password') !== 'new_password' ? t('new_password') : 'Nové heslo'); ?></label>
                                                            <div class="form-control-wrap">
                                                                <input type="password" class="form-control form-control-lg fs-16px" name="new_password" required minlength="6">
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb-5">
                                                            <label class="form-label fs-15px text-soft"><?php e(t('confirm_password') !== 'confirm_password' ? t('confirm_password') : 'Potvrdit nové heslo'); ?></label>
                                                            <div class="form-control-wrap">
                                                                <input type="password" class="form-control form-control-lg fs-16px" name="confirm_password" required minlength="6">
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="form-group text-center">
                                                            <button type="submit" class="btn btn-lg btn-primary btn-block px-5 shadow"><?php e(t('save_password') !== 'save_password' ? t('save_password') : 'Uložit nové heslo'); ?></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="nk-footer mt-auto">
                    <div class="container-fluid">
                        <div class="nk-footer-copyright fs-6 px-3 text-center text-soft"> &copy; 2023 All Rights Reserved by CopyGen. </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/bundle.js?v1.1.0"></script>
    <script src="assets/js/scripts.js?v1.1.0"></script>
</body>
</html>