<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php e(t('requests_title') !== 'requests_title' ? t('requests_title') : 'Požadavky'); ?> - CopyGen</title>
    <link rel="stylesheet" href="assets/css/style.css?v1.1.0">
</head>
<body class="nk-body ">
    <div class="nk-app-root " data-sidebar-collapse="lg">
        <div class="nk-main">
            
            <?php include __DIR__ . '/../Core/sidebar.php'; ?>
            
            <div class="nk-wrap">
                
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
                                        <table class="table table-middle mb-0">
                                            <tbody>
                                                <?php if(empty($requests)): ?>
                                                    <tr><td class="text-center py-4">Žádné požadavky nebyly nalezeny.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach($requests as $request): 
                                                        $date = date('M d, Y', strtotime($request['datum']));
                                                        $time = date('h:i A', strtotime($request['datum']));
                                                        
                                                        // Barvy pro status kolonku
                                                        $statusClass = 'text-bg-primary-soft';
                                                        switch($request['status']) {
                                                            case 'new': $statusClass = 'text-bg-info-soft'; break;
                                                            case 'processing': $statusClass = 'text-bg-warning-soft'; break;
                                                            case 'done': $statusClass = 'text-bg-success-soft'; break;
                                                            case 'rejected': $statusClass = 'text-bg-danger-soft'; break;
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td class="tb-col">
                                                            <div class="caption-text line-clamp-1">
                                                                <strong>
                                                                    <?php 
                                                                        // Vypíšeme název z pole 48, pokud neexistuje, dáme náhradní text
                                                                        $displayTitle = !empty($request['klientsky_nazev']) ? $request['klientsky_nazev'] : 'Bez názvu';
                                                                        e($displayTitle); 
                                                                    ?>
                                                                </strong>
                                                            </div>
                                                            <div class="sub-text text-soft">
                                                                <?php e(t($request['typ_formulare'])); ?>
                                                            </div>
                                                        </td>

                                                        <td class="tb-col tb-col-sm">
                                                            <?php 
                                                                $statusClass = 'text-bg-primary-soft';
                                                                switch($request['status']) {
                                                                    case 'new': $statusClass = 'text-bg-info-soft'; break;
                                                                    case 'processing': $statusClass = 'text-bg-warning-soft'; break;
                                                                    case 'done': $statusClass = 'text-bg-success-soft'; break;
                                                                    case 'rejected': $statusClass = 'text-bg-danger-soft'; break;
                                                                }
                                                            ?>
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
                                                            <a href="view-request.php?id=<?= $request['submission_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                                <em class="icon ni ni-eye"></em> <span>Detail</span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div> </div> </div> </div> <script src="assets/js/bundle.js?v1.1.0"></script>
    <script src="assets/js/scripts.js?v1.1.0"></script>
</body>
</html>