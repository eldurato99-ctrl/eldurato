<?php
session_start();
require_once '../config/database.php';

// 🔐 TIGHT SECURITY FILTER LAYER
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/auth/login.php");
    exit;
}

$admin_role = strtolower($_SESSION['user_role'] ?? 'user');
if ($admin_role !== 'admin') {
    header("Location: ../pages/account/dashboard.php");
    exit;
}

$totalSales = $totalOrders = $totalProducts = $totalUsers = 0;
$months = $sales = $recentOrders = $lowStockProducts = [];
$dayLabels = []; $daySales = [];
$dayColors = ['#8b5cf6', '#06b6d4', '#f97316', '#ec4899', '#10b981', '#3b82f6', '#ef4444'];

try {
    $totalSales    = $pdo->query("SELECT SUM(total_amount) FROM all_orders_list")->fetchColumn() ?? 0;
    $totalOrders   = $pdo->query("SELECT COUNT(*) FROM all_orders_list")->fetchColumn() ?? 0;
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM all_products_list")->fetchColumn() ?? 0;
    $totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?? 0;

    $chartData = $pdo->query("SELECT DATE_FORMAT(created_at, '%b') as m, SUM(total_amount) as t FROM all_orders_list GROUP BY MONTH(created_at), m ORDER BY MONTH(created_at) ASC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    foreach($chartData as $row) {
        $months[] = $row['m'];
        $sales[]  = (float)$row['t'];
    }

    $dailyQuery = $pdo->query("SELECT DATE_FORMAT(created_at, '%a') as day_name, SUM(total_amount) as total FROM all_orders_list WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at), DATE_FORMAT(created_at, '%a') ORDER BY DATE(created_at) ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach($dailyQuery as $d) {
        $dayLabels[] = $d['day_name'];
        $daySales[]  = (float)$d['total'];
    }

    if(empty($daySales)) {
        $dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $daySales  = [1200, 1900, 800, 2500, 1400, 1800, 2100];
    }

    $recentOrders = $pdo->query("SELECT o.id, o.total_amount, o.payment_method, o.order_status, COALESCE(u.name, o.customer_name, 'Guest Customer') as customer_real_name FROM all_orders_list o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $lowStockProducts = $pdo->query("SELECT id, name as product_title, stock FROM all_products_list WHERE stock <= 5 ORDER BY stock ASC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { }

if (empty($months)) { $months = ['Jun']; $sales = [$totalSales]; }

// DRY Loop Config Setup
$statsCards = [
    ['title' => 'Total Revenue', 'value' => '₹' . number_format($totalSales), 'bg' => 'grad-purple text-white', 'icon' => 'ri-wallet-3-fill'],
    ['title' => 'Orders Placed', 'value' => $totalOrders, 'bg' => 'grad-blue text-white', 'icon' => 'ri-shopping-cart-2-fill'],
    ['title' => 'Active Belts', 'value' => $totalProducts, 'bg' => 'grad-orange text-white', 'icon' => 'ri-equalizer-fill'],
    ['title' => 'Registered Users', 'value' => $totalUsers, 'bg' => 'grad-pink text-white', 'icon' => 'ri-group-fill']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELDURATO - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container-fluid p-3">
    <div class="row">
        <!-- admin\index.php -->
        <?php include 'adminSidebar.php'; ?>

        <div class="col-lg-10 offset-lg-2">
            
            <div class="bg-primary bg-gradient p-3 text-white shadow-sm d-flex justify-content-between align-items-center mb-4 rounded-3">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-light d-lg-none px-2.5 py-1.5" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <i class="ri-menu-2-line fs-5 m-0 align-middle"></i>
                    </button>
                    <div>
                        <h5 class="fw-bold m-0 fs-6">Admin Console</h5>
                        <div class="small opacity-75"><?= date('M d, Y') ?></div>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <a href="../index.php" class="nav-link-custom m-0 text-white d-none d-sm-flex d-flex align-items-center gap-2"><i class="ri-store-2-line text-white"></i>View Shop</a>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold small d-flex align-items-center gap-2 shadow-sm">
                        <span class="pulse-dot pulse-animated"></span> SYSTEM LIVE
                    </span>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <?php foreach ($statsCards as $card): ?>
                    <div class="col-sm-6 col-md-3">
                        <div class="card card-custom p-3 shadow-sm <?= $card['bg'] ?>">
                            <h6 class="opacity-75 text-uppercase small fw-bold mb-1" style="font-size: 11px;"><?= $card['title'] ?></h6>
                            <h2 class="fw-bold m-0 fs-3"><?= $card['value'] ?></h2>
                            <i class="<?= $card['icon'] ?> watermark-icon"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm  bg-white rounded-3 h-100">
                        <div class="d-flex bg-info bg-gradient justify-content-between align-items-center mb-3 p-2">
                            <h6 class="fw-bold m-0 text-danger"><i class="ri-file-list-3-line text-success me-1"></i> Recent Orders Flow</h6>
                            <a href="orders/index.php" class="btn btn-sm btn-primary rounded-pill px-3 py-1" style="font-size:11px;">View All</a>
                        </div>
                        <div class="table-responsive border-0">
                            <table class="table table-hover align-middle mb-0" style="min-width: 500px;">
                                <thead class="table-light small text-muted text-uppercase">
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Cash</th>
                                        <th>Gateway</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($recentOrders)): foreach($recentOrders as $order): 
                                        $status = $order['order_status'] ?? 'Pending';
                                        $badge = (strtolower($status) === 'completed' || strtolower($status) === 'delivered') ? 'bg-success text-white' : ((strtolower($status) === 'cancelled') ? 'bg-danger text-white' : 'bg-warning text-dark');
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-primary">#ELD-<?= $order['id'] ?></td>
                                        <td class="fw-medium text-dark"><?= htmlspecialchars($order['customer_real_name']) ?></td>
                                        <td class="fw-bold">₹<?= number_format($order['total_amount']) ?></td>
                                        <td><span class="badge bg-light text-dark border text-uppercase" style="font-size:10px;"><?= htmlspecialchars($order['payment_method'] ?? 'COD') ?></span></td>
                                        <td><span class="badge <?= $badge ?> rounded-pill px-2.5 py-1" style="font-size:11px;"><?= ucfirst($status) ?></span></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No entries found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm bg-white rounded-3 overflow-hidden h-100">
                        <div class="bg-info bg-gradient text-white p-2 px-3 fw-bold"><i class="ri-pie-chart-2-line me-2"></i>Daily Sales Split</div>
                        <div class="p-3 d-flex justify-content-center align-items-center" style="height: 280px;"><canvas id="dailySalesPieChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm bg-white rounded-3 overflow-hidden h-100">
                        <div class="bg-primary bg-gradient text-white p-2 px-3 fw-bold"><i class="ri-line-chart-line me-2"></i>Monthly Performance</div>
                        <div class="p-3" style="height: 280px;"><canvas id="salesChart"></canvas></div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-3 bg-white rounded-3 h-100">
                        <h6 class="fw-bold mb-3 text-danger"><i class="ri-alarm-warning-line me-1"></i> Inventory Alerts</h6>
                        <ul class="list-group list-group-flush">
                            <?php if(!empty($lowStockProducts)): foreach($lowStockProducts as $prod): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-light-subtle">
                                    <span class="text-dark fw-medium small text-truncate" style="max-width: 180px;"><?= htmlspecialchars($prod['product_title']) ?></span>
                                    <span class="badge bg-danger text-white rounded-pill" style="font-size:11px;"><?= $prod['stock'] ?> left</span>
                                </li>
                            <?php endforeach; else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="ri-checkbox-circle-fill fs-2 text-success d-block mb-1"></i>
                                    <small class="fw-medium">Stock levels healthy!</small>
                                </div>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
const salesData = {
    months: <?= json_encode($months) ?>,
    sales: <?= json_encode($sales) ?>,
    dayLabels: <?= json_encode($dayLabels) ?>,
    daySales: <?= json_encode($daySales) ?>,
    dayColors: <?= json_encode($dayColors) ?>
};

new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: salesData.months,
        datasets: [{
            data: salesData.sales,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.08)',
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#4f46e5'
        }]
    },
    options: { plugins: { legend: { display: false } }, responsive: true, maintainAspectRatio: false, scales: { y: { grid: { color: '#f1f3f5' } }, x: { grid: { display: false } } } }
});

new Chart(document.getElementById('dailySalesPieChart'), {
    type: 'doughnut',
    data: {
        labels: salesData.dayLabels,
        datasets: [{
            data: salesData.daySales,
            backgroundColor: salesData.dayColors,
            borderWidth: 2,
            hoverOffset: 6
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 15, font: { size: 11, weight: 'bold' } } } }, cutout: '65%' }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>