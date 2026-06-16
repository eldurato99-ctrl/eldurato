<?php
// pages\account\dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'user') {
    header("Location: ../../admin/index.php");
    exit;
}

require_once '../../config/database.php';
$user_id = $_SESSION['user_id'];

// DRY: मेट्रिक्स डेटा को स्ट्रक्चर किया ताकि लूप चलाया जा सके
$metrics = [
    'orders'   => ['count' => 0, 'title' => 'Total Orders', 'icon' => 'ri-shopping-cart-line', 'color' => '--dashboard-teal'],
    'wishlist' => ['count' => 0, 'title' => 'Wishlist Items', 'icon' => 'ri-heart-3-line', 'color' => '--dashboard-green']
];
$recentOrders = [];

try {
    // DRY: काउंट क्वेरीज के लिए एक लूप का इस्तेमाल किया
    foreach (['orders' => 'all_orders_list', 'wishlist' => 'wishlist'] as $key => $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $metrics[$key]['count'] = $stmt->fetchColumn() ?? 0;
    }

    $stmt = $pdo->prepare("SELECT id, created_at, total_amount, order_status, payment_method FROM all_orders_list WHERE user_id = ? ORDER BY id DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e){ }

// स्क्रीनशॉट मैचिंग सॉलिड और ब्राइट स्टेटस बैज
$status_badges = [
    'pending'    => 'bg-warning text-dark fw-bold',
    'processing' => 'bg-info text-white fw-bold',
    'completed'  => 'bg-success text-white fw-bold',
    'cancelled'  => 'bg-danger text-white fw-bold'
];

$current_page = 'dashboard'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BELTSTORE - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Exact Colors Mapping from Screenshot */
        :root {
            --dashboard-teal: #17a2b8;     /* First box color */
            --dashboard-green: #28a745;    /* Second box color */
            --dashboard-yellow: #ffc107;   /* Third box color */
            --dashboard-red: #dc3545;      /* Fourth box color & Main brand accents */
            --sidebar-bg: #222d32;         /* Pro Dashboard Dark Left Sidebar */
            --main-light-bg: #ecf0f5;      /* Standard clean canvas wrapper background */
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--main-light-bg);
            color: #333;
        }

        /* Sidebar Styling */
        .sidebar-container {
            background-color: var(--sidebar-bg) !important;
            min-height: 100vh;
        }
        .sidebar-link {
            transition: all 0.2s ease;
            color: #b8c7ce !important;
        }
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff !important;
        }
        .sidebar-link.active-link {
            color: #fff !important;
            background: rgba(0, 0, 0, 0.15);
            border-left: 4px solid var(--dashboard-teal);
        }

        /* Screenshot Inspired Solid Cards Components */
        .sc-card {
            border: none !important;
            border-radius: 4px; /* Classic flat-styled dashboard metrics */
            color: #fff !important;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .sc-card .card-icon {
            font-size: 3.5rem;
            opacity: 0.2;
            position: absolute;
            right: 15px;
            bottom: 5px;
        }

        /* Welcome Alert Box mimicking Dashboard Alerts */
        .welcome-banner {
            background-color: var(--dashboard-red) !important;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }

        /* Table Dashboard View */
        .data-table-container {
            border-radius: 4px;
            border-top: 3px solid #d2d6de;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }

        @media (min-width: 992px) {
            .sidebar-offcanvas {
                transform: none !important;
                visibility: visible !important;
                position: fixed !important;
                z-index: 1000 !important;
                height: 100vh !important;
                width: 16.666667% !important;
                top: 0;
                left: 0;
            }
            .main-content {
                margin-left: 16.666667% !important;
                width: 83.333333% !important;
            }
        }

        /* मोबाइल पर आइकन का साइज थोड़ा छोटा करने के लिए ताकि लेआउट न बिगड़े */
        @media (max-width: 576px) {
            .sc-card { padding: 1rem !important; }
            .sc-card .card-icon { font-size: 2.5rem; right: 8px; bottom: 0; }
            .sc-card h2 { font-size: 1.5rem; }
            .sc-card h6 { font-size: 0.7rem !important; }
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="d-lg-none p-3 d-flex justify-content-between align-items-center" style="background-color: var(--sidebar-bg);">
        <a href="#" class="text-white fs-5 text-decoration-none fw-bold">
            <i class="ri-handbag-line me-2" style="color: var(--dashboard-teal);"></i><span>BELTSTORE</span>
        </a>
        <button class="btn btn-outline-light border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="ri-menu-line fs-4"></i>
        </button>
    </div>

    <div class="row g-0">
        <div class="col-lg-2 offcanvas-lg offcanvas-start sidebar-offcanvas sidebar-container text-white" tabindex="-1" id="sidebarMenu">
            <div class="p-3 d-flex flex-column justify-content-between h-100 w-100">
                <div class="w-100">
                    <div class="d-flex justify-content-center align-items-center pb-3 mb-4 border-bottom border-secondary border-opacity-25">
                        <a href="#" class="text-white fs-4 text-decoration-none fw-bold py-2">
                            <i class="ri-handbag-line me-2" style="color: var(--dashboard-teal);"></i><span>BELTSTORE</span>
                        </a>
                    </div>
                    
                    <div class="nav flex-column">
                        <a href="../../index.php" class="nav-link mb-2 p-3 rounded d-flex align-items-center text-decoration-none sidebar-link">
                            <i class="ri-arrow-left-line me-3 fs-5"></i>Go to Shop
                        </a>
                        
                        <a href="/pages/account/dashboard.php" class="nav-link mb-2 p-3 d-flex align-items-center text-decoration-none fw-semibold sidebar-link <?= $current_page == 'dashboard' ? 'active-link' : '' ?>">
                            <i class="ri-dashboard-line me-3 fs-5"></i>Dashboard
                        </a>
                        
                        <a href="../products/cart.php" class="nav-link mb-2 p-3 rounded d-flex align-items-center text-decoration-none sidebar-link">
                            <i class="ri-shopping-cart-2-line me-3 fs-5"></i>My Cart
                        </a>
                        
                        <a href="../products/wishlist.php" class="nav-link mb-2 p-3 rounded d-flex align-items-center text-decoration-none sidebar-link">
                            <i class="ri-heart-line me-3 fs-5"></i>Wishlist
                        </a>
                        
                        <a href="/pages/auth/profile.php" class="nav-link mb-2 p-3 d-flex align-items-center text-decoration-none fw-semibold sidebar-link <?= $current_page == 'profile' ? 'active-link' : '' ?>">
                            <i class="ri-user-line me-3 fs-5"></i>My Profile
                        </a>
                    </div>
                </div>
                
                <div class="w-100 pt-3 border-top border-secondary border-opacity-25">
                    <a href="../auth/logout.php" class="btn btn-sm btn-danger text-start w-100 border-0 py-2 px-3 d-flex align-items-center justify-content-center fw-bold">
                        <i class="ri-logout-circle-line me-2 fs-5"></i>Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-10 main-content p-4">
            
            <div class="p-4 mb-4 text-white welcome-banner d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold m-0">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?> ✨</h3>
                    <p class="text-white-50 small mb-0 mt-1">Manage your profile ledger and check transaction states</p>
                </div>
                <span class="badge bg-white text-danger fw-bold px-3 py-2 d-none d-sm-inline">LIVE METRICS</span>
            </div>

           <div class="row g-2 g-md-3">
    <?php /* PC/Tab पर col-12 (पूरी चौड़ाई) और मोबाइल पर col-6 (आधा-आधा) */ ?>
    <?php foreach($metrics as $metric): ?>
    <div class="col-6 col-lg-6">
        <div class="card sc-card p-4 position-relative row-metric-card" style="background-color: var(<?= $metric['color'] ?>) !important;">
            <h6 class="text-white-50 small text-uppercase fw-bold mb-1 text-truncate"><?= $metric['title'] ?></h6>
            <h2 class="fw-bold m-0 text-white"><?= $metric['count'] ?></h2>
            <i class="<?= $metric['icon'] ?> card-icon"></i>
        </div>
    </div>
    <?php endforeach; ?>
</div>

            <div class="card data-table-container overflow-hidden border-0 bg-white mt-4">
                <div class="p-3 border-bottom bg-white d-flex align-items-center">
                    <h5 class="mb-0 fw-bold text-dark fs-6 text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="ri-history-line me-2 text-primary"></i>Your Recent Logs
                    </h5>
                </div>
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-sm">
                            <thead class="table-light text-secondary small text-uppercase" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="ps-4 py-3">Order ID</th>
                                    <th class="py-3">Date</th>
                                    <th class="py-3">Amount</th>
                                    <th class="text-center py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($recentOrders) > 0): foreach($recentOrders as $order): 
                                    $curr_status = strtolower($order['order_status'] ?? 'pending');
                                    $pay_suffix = (strtoupper($order['payment_method'] ?? 'COD') == 'COD') ? 'COD' : 'ONL';
                                ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">BST-<?= $pay_suffix ?>-<?= $order['id'] ?></td>
                                        <td class="text-secondary small"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                                        <td class="fw-bold text-dark">₹<?= number_format($order['total_amount']) ?></td>
                                        <td class="text-center">
                                            <span class="badge rounded-1 px-3 py-2 text-uppercase <?= $status_badges[$curr_status] ?? 'bg-secondary text-white' ?>">
                                                <?= $curr_status ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5 fw-semibold">
                                            <div class="fs-2 mb-2">🚫</div> No pipeline records mapped for your identifier.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
