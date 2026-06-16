<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';

$order_found = false;
$order_details = null;
$order_items = [];

$search_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
$search_phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';

if (!empty($search_id) && !empty($search_phone)) {
    $clean_id = preg_replace('/[^0-9]/', '', $search_id);

    // 1. Fetch Order Details
    $stmt = $pdo->prepare("SELECT * FROM all_orders_list WHERE id = ? AND customer_phone = ?");
    $stmt->execute([$clean_id, $search_phone]);
    $order_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order_details) {
        $order_found = true;
        
        // 2. Fetch ALL items for this order with full specifications
        $items_stmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.images as product_images, p.brand, p.model_name
            FROM order_items oi
            LEFT JOIN all_products_list p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $items_stmt->execute([$order_details['id']]);
        $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

<style>
    .status-timeline { position: relative; padding-left: 30px; }
    .status-timeline::before {
        content: ''; position: absolute; left: 9px; top: 5px; bottom: 5px; width: 2px; background: #dee2e6;
    }
    .timeline-dot {
        position: absolute; left: 0; top: 2px; width: 20px; height: 20px; border-radius: 50%; background: #fff; border: 4px solid #dee2e6;
    }
    .timeline-item.active .timeline-dot { border-color: #0d6efd; background: #0d6efd; }
    .timeline-item.success .timeline-dot { border-color: #198754; background: #198754; }
    .timeline-item.danger .timeline-dot { border-color: #dc3545; background: #dc3545; }
    .completed-item-card { transition: all 0.3s ease; border: 1px solid #198754 !important; }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4 bg-white">
                <h5 class="fw-bold text-dark mb-3"><i class="ri-radar-line text-primary me-1"></i> Track Your Order</h5>
                <form method="GET" action="">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <input type="text" name="order_id" class="form-control" placeholder="Order ID (e.g. 12)" value="<?= htmlspecialchars($search_id) ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <input type="text" name="phone" class="form-control" placeholder="Registered Phone" value="<?= htmlspecialchars($search_phone) ?>" required>
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">CHECK LIVE STATUS</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (!empty($search_id) && !$order_found): ?>
                <div class="alert alert-danger rounded-3 text-center" role="alert">
                    <i class="ri-error-warning-line me-1"></i> No active order found with these credentials.
                </div>
            <?php endif; ?>

            <?php if ($order_found && $order_details): 
                $status = strtolower($order_details['order_status'] ?? 'pending');
                $pay_method = strtoupper($order_details['payment_method'] ?? 'COD');
                $custom_id = "ELD-" . (($pay_method == 'COD') ? 'COD' : 'ONL') . "-" . $order_details['id'];
            ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                    <div class="p-3 bg-dark text-white d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50 d-block" style="font-size: 11px;">ORDER REF</small>
                            <span class="fw-bold text-warning"><?= $custom_id ?></span>
                        </div>
                        <div class="text-end">
                            <small class="text-white-50 d-block" style="font-size: 11px;">CURRENT STATUS</small>
                            <span class="badge uppercase px-2.5 py-1 fw-bold border 
                                <?= $status == 'pending' ? 'bg-warning text-dark border-warning' : '' ?>
                                <?= $status == 'processing' ? 'bg-info text-dark border-info' : '' ?>
                                <?= $status == 'completed' ? 'bg-success text-white border-success' : '' ?>
                                <?= $status == 'cancelled' ? 'bg-danger text-white border-danger' : '' ?>
                            ">
                                <?= strtoupper($status) ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        
                        <?php if($status == 'completed'): ?>
                            <div class="alert alert-success border-0 d-flex align-items-center gap-3 p-3 rounded-3 mb-4" style="background-color: #e8f5e9;">
                                <i class="ri-checkbox-circle-fill text-success fs-3"></i>
                                <div>
                                    <h6 class="fw-bold text-success mb-0">Your Order is Successfully Delivered!</h6>
                                    <small class="text-secondary">Thank you for shopping with us. Product warranty and returns are now active.</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <h6 class="fw-bold text-secondary mb-3 small text-uppercase tracking-wider">Fulfillment Timeline</h6>
                        <div class="status-timeline ps-4">
                            <div class="timeline-item pb-3 position-relative <?= in_array($status, ['pending', 'processing', 'completed']) ? ($status == 'pending' ? 'active text-warning fw-bold' : 'success text-success') : 'text-muted' ?>">
                                <div class="timeline-dot"></div>
                                <div class="small">Order Placed & Pending</div>
                            </div>
                            <div class="timeline-item pb-3 position-relative <?= in_array($status, ['processing', 'completed']) ? ($status == 'processing' ? 'active text-info fw-bold' : 'success text-success') : 'text-muted' ?>">
                                <div class="timeline-dot"></div>
                                <div class="small">Confirmed & Packing</div>
                            </div>
                            <?php if($status == 'cancelled'): ?>
                                <div class="timeline-item pb-1 position-relative danger text-danger fw-bold">
                                    <div class="timeline-dot"></div>
                                    <div class="small">Cancelled</div>
                                </div>
                            <?php else: ?>
                                <div class="timeline-item pb-1 position-relative <?= $status == 'completed' ? 'success text-success fw-bold' : 'text-muted' ?>">
                                    <div class="timeline-dot"></div>
                                    <div class="small">Dispatched & Delivered</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr class="my-4 opacity-25">

                        <h6 class="fw-bold text-dark mb-3"><i class="ri-shopping-bag-line me-1 text-secondary"></i> Items Summary</h6>
                        <?php foreach($order_items as $item): 
                            $img_src = 'https://via.placeholder.com/50?text=No+Image';
                            if(!empty($item['product_images'])) {
                                $img_arr = json_decode($item['product_images'], true);
                                $img_src = (is_array($img_arr) && isset($img_arr[0]['url'])) ? $img_arr[0]['url'] : $item['product_images'];
                            }
                        ?>
                            <div class="p-3 bg-light rounded-3 mb-3 border d-flex flex-column gap-2 <?= $status == 'completed' ? 'completed-item-card' : '' ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= htmlspecialchars($img_src) ?>" class="rounded border bg-white object-fit-contain" width="55" height="55" alt="product">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-bold text-dark text-truncate small"><?= htmlspecialchars($item['product_name'] ?: 'Product Item') ?></div>
                                        <div class="text-muted mt-0.5" style="font-size: 11px;">
                                            <span class="me-2">Size: <strong><?= htmlspecialchars($item['size'] ?? 'Free') ?></strong></span>
                                            <span class="me-2">Qty: <strong><?= intval($item['quantity']) ?></strong></span>
                                            <?php if(!empty($item['brand'])): ?>
                                                <span>Brand: <strong><?= htmlspecialchars($item['brand']) ?></strong></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="fw-bold text-dark text-end small">₹<?= number_format($item['price'] * $item['quantity']) ?></div>
                                </div>

                                <?php if($status == 'completed'): ?>
                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top style-none" style="font-size: 11.5px;">
                                        <span class="text-success fw-semibold"><i class="ri-verified-badge-line"></i> Item Verified & Delivered</span>
                                        <div class="d-flex gap-2">
                                            <a href="../products/review.php?product_id=<?= $item['product_id'] ?>" class="btn btn-xs btn-success py-0.5 px-2 rounded fw-bold text-white" style="font-size: 11px;">
                                                <i class="ri-star-line"></i> Write Review
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="bg-light p-3 rounded-3 border mt-3" style="font-size: 13px;">
                            <div class="d-flex justify-content-between mb-1.5">
                                <span class="text-secondary">Customer Name</span>
                                <span class="fw-medium text-dark"><?= htmlspecialchars($order_details['customer_name']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1.5">
                                <span class="text-secondary">Payment Method</span>
                                <span class="fw-bold text-dark text-uppercase"><?= htmlspecialchars($pay_method) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1.5">
                                <span class="text-secondary">Shipping Address</span>
                                <span class="fw-medium text-dark text-end text-truncate" style="max-width:220px;" title="<?= htmlspecialchars($order_details['shipping_address']) ?>"><?= htmlspecialchars($order_details['shipping_address'] . ', ' . $order_details['city']) ?></span>
                            </div>
                            <hr class="my-2 opacity-25">
                            <div class="d-flex justify-content-between align-items-center pt-1">
                                <span class="fw-bold text-secondary">Total Paid Amount</span>
                                <span class="fw-bold text-primary fs-5">₹<?= number_format($order_details['total_amount']) ?></span>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
