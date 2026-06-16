<?php
session_start();
require_once '../../config/database.php';

// ==========================================
// 1. ORDER STATUS SYNC & REAL-TIME MAPPING (STRICT DYNAMIC FIXED)
// ==========================================
$confirmed_orders_map = []; 

$stmt_check = $pdo->query("
    SELECT oi.order_id, oi.product_id, oi.size as db_size, o.id as order_db_id, o.payment_method, o.order_status, o.tracking_status
    FROM order_items oi
    JOIN all_orders_list o ON oi.order_id = o.id
    ORDER BY o.id DESC
");
$raw_confirmed = $stmt_check->fetchAll(PDO::FETCH_ASSOC);

foreach ($raw_confirmed as $row) {
    $track_key = $row['product_id'] . '_' . str_replace(' ', '_', trim($row['db_size']));
    
    // Agar key pehle se set nahi hai, toh add karein
    if (!isset($confirmed_orders_map[$track_key])) {
        $pay_method = strtoupper($row['payment_method']);
        $suffix = ($pay_method == 'COD') ? 'COD' : 'ONL';
        
        $confirmed_orders_map[$track_key] = [
            'order_id' => $row['order_db_id'], 
            'custom_order_id' => "ELD-" . $suffix . "-" . $row['order_db_id'],
            'status'   => strtolower($row['order_status']),
            'tracking' => trim($row['tracking_status'] ?? '') 
        ];
    } else {
        // Agar pehle se status check active hai aur purana record completed/cancelled hai, 
        // toh pending/processing ko priority dein taaki dynamic active status handle ho sake
        $existing_status = $confirmed_orders_map[$track_key]['status'];
        $new_status = strtolower($row['order_status']);
        
        if (in_array($existing_status, ['completed', 'cancelled']) && in_array($new_status, ['pending', 'processing'])) {
            $pay_method = strtoupper($row['payment_method']);
            $suffix = ($pay_method == 'COD') ? 'COD' : 'ONL';
            
            $confirmed_orders_map[$track_key] = [
                'order_id' => $row['order_db_id'], 
                'custom_order_id' => "ELD-" . $suffix . "-" . $row['order_db_id'],
                'status'   => $new_status,
                'tracking' => trim($row['tracking_status'] ?? '') 
            ];
        }
    }
}
// ==========================================
// 2. AJAX - Update Quantity Engine
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
    $cart_key = $_POST['cart_key'];
    $new_qty = intval($_POST['quantity']);

    if (isset($_SESSION['cart'][$cart_key]) && $new_qty > 0) {
        $_SESSION['cart'][$cart_key]['quantity'] = $new_qty;
        
        $cart_items = $_SESSION['cart'];
        $product_ids = array_unique(array_column($cart_items, 'product_id'));
        
        $grandTotal = 0;
        $db_prices = [];

        if (!empty($product_ids)) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("SELECT id, price FROM all_products_list WHERE id IN ($placeholders)");
            $stmt->execute(array_values($product_ids));
            $db_prices = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            foreach ($cart_items as $k => $item) {
                $p_id = $item['product_id'];
                
                $current_size_key = $p_id . '_' . str_replace(' ', '_', trim($item['size']));
                $isOrdered = isset($confirmed_orders_map[$current_size_key]);
                $orderStatus = $isOrdered ? $confirmed_orders_map[$current_size_key]['status'] : '';
                
                if ($isOrdered && ($orderStatus === 'completed' || $orderStatus === 'cancelled')) {
                    continue; 
                }

                if (isset($db_prices[$p_id])) {
                    $grandTotal += (float)$db_prices[$p_id] * $item['quantity'];
                }
            }
        }

        $current_product_id = $_SESSION['cart'][$cart_key]['product_id'];
        $item_unit_price = isset($db_prices[$current_product_id]) ? (float)$db_prices[$current_product_id] : 0;

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'item_total' => '₹' . number_format($item_unit_price * $new_qty),
            'grand_total' => '₹' . number_format($grandTotal),
            'total_unique_items' => count($cart_items)
        ]);
        exit;
    }
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error']);
    exit;
}

// ==========================================
// 3. Cart Actions Framework Configuration
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_to_cart']) || isset($_POST['buy_now']))) {
    $product_id = intval($_POST['product_id']);
    if ($product_id <= 0) { die("Invalid Product"); }
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $size = isset($_POST['size']) && !empty($_POST['size']) ? trim($_POST['size']) : '32';
    $custom_image = isset($_POST['selected_image']) ? trim($_POST['selected_image']) : '';

    $cart_key = $product_id . '_' . str_replace(' ', '_', $size);

    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id, 'quantity' => $quantity, 'size' => $size, 'custom_image' => $custom_image
        ];
    }

    if (isset($_POST['buy_now'])) {
        header("Location: /pages/products/checkout.php?target_key=" . $cart_key);
    } else {
        header("Location: /pages/products/cart.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $cart_key = $_POST['remove_item_key']; 
    if (isset($_SESSION['cart'][$cart_key])) { 
        $item = $_SESSION['cart'][$cart_key];
        $p_id = $item['product_id'];
        $size_key = $p_id . '_' . str_replace(' ', '_', trim($item['size']));
        
        if (isset($confirmed_orders_map[$size_key])) {
            $db_order_id = $confirmed_orders_map[$size_key]['order_id'];
            $current_status = $confirmed_orders_map[$size_key]['status'];
            
            if ($current_status !== 'completed' && $current_status !== 'cancelled') {
                try {
                    $upStmt = $pdo->prepare("UPDATE all_orders_list SET order_status = 'cancelled', tracking_status = 'Cancelled by Customer' WHERE id = ?");
                    $upStmt->execute([$db_order_id]);
                } catch (PDOException $e) {
                    // Safety trace boundary fallback node
                }
            }
        } else {
            unset($_SESSION['cart'][$cart_key]);
        }
    }
    header("Location: /pages/products/cart.php");
    exit;
}

$cart_items = $_SESSION['cart'] ?? [];

include '../../includes/header.php';
include '../../includes/navbar.php';

if (empty($cart_items)) {
    ?>
    <div class="container py-5 text-center">
        <div style="border: none !important; border-radius: 16px !important; background: #fff !important; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important; padding: 48px; max-width: 400px; margin: 0 auto;">
            <div class="mb-3 text-muted"><i class="ri-shopping-cart-2-line" style="font-size: 3rem;"></i></div>
            <h5 class="fw-bold text-dark mb-3">आपका CART खाली है!</h5>
            <a href="/pages/products/products.php" class="btn text-white py-2" style="background: #4f46e5 !important; border-radius: 8px; width: 100%;">Shop Now</a>
        </div>
    </div>
    <?php
    include '../../includes/footer.php';
    exit;
}

$product_ids = array_unique(array_filter(array_column($cart_items, 'product_id')));
$all_products = [];

if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM all_products_list WHERE id IN ($placeholders)");
    $stmt->execute(array_values($product_ids));
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$db_products = [];
foreach ($all_products as $row) { $db_products[$row['id']] = $row; }

$grandTotal = 0;
foreach ($cart_items as $item) {
    $p_id = $item['product_id'];
    $current_size_key = $p_id . '_' . str_replace(' ', '_', trim($item['size']));
    $isOrdered = isset($confirmed_orders_map[$current_size_key]);
    $orderStatus = $isOrdered ? $confirmed_orders_map[$current_size_key]['status'] : '';
    
    if ($isOrdered && ($orderStatus === 'completed' || $orderStatus === 'cancelled')) {
        continue; 
    }
    if (isset($db_products[$p_id])) {
        $grandTotal += (float)$db_products[$p_id]['price'] * $item['quantity'];
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

<style>
    body { background-color: #eef2f7 !important; }
    .table-card-wrapper { border: none !important; border-radius: 16px !important; background: #fff !important; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important; padding: 0px; overflow: hidden; }
    .stat-card { border: none; border-radius: 14px; color: #fff !important; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.02); padding: 20px; }
    .bg-stat-orders { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; }
    .badge-track { font-weight: 700; font-size: 11px; padding: 6px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; border: none !important; }
    .track-pending { background: #fef3c7 !important; color: #d97706 !important; }
    .track-processing { background: #e0f2fe !important; color: #0284c7 !important; }
    .track-completed { background: #dcfce7 !important; color: #15803d !important; }
    .track-cancelled { background: #fee2e2 !important; color: #dc2626 !important; }
    .live-tracking-badge { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-weight: 600; font-size: 11px; padding: 6px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; }
    .hover-link-blue { color: #1e293b; transition: color 0.2s ease; }
    .hover-link-blue:hover { color: #4f46e5 !important; text-decoration: none; }
    .product-hover-effect { transition: transform 0.2s ease, border-color 0.2s ease; cursor: pointer; }
    .product-hover-effect:hover { transform: scale(1.04); border-color: #4f46e5 !important; }
    /* Footer actions layout architecture */
.cart-actions-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.qty-counter-zone {
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn-action-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-action {
    font-size: 11px !important;
    font-weight: 700 !important;
    padding: 6px 12px !important;
    border-radius: 6px !important;
    border: none !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    white-space: nowrap;
}

.btn-check-details {
    background: #ffa011 !important;
    color: #fff !important;
}

.btn-buy-now {
    background: #4f46e5 !important;
    color: #fff !important;
}

/* Responsive Mobile Breaks (<= 768px) */
@media (max-width: 768px) {
    .cart-actions-footer {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .qty-counter-zone {
        justify-content: space-between;
        background: #f8fafc;
        padding: 6px 10px;
        border-radius: 8px;
    }

    .btn-action-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        width: 100%;
    }
    
    /* Conditional catch: Agar pipeline active ho aur buy button hide ho, to check details full width le */
    .btn-action-group:not(:has(.btn-buy-now)) {
        grid-template-columns: 1fr;
    }

    .btn-action {
        padding: 9px !important;
        font-size: 12px !important;
    }
}
</style>

<div class="container py-4">
   <div class="row g-4 justify-content-center">
    <div class="col-12 col-md-10">
 <h4 class="fw-bold text-dark mb-0" style="font-size: 26px; letter-spacing: -0.5px;">Shopping Cart</h4>
        <span class="text-secondary small fw-medium">Total Session Items (<?php echo count($cart_items); ?>)</span>


            <div class="table-card-wrapper mt-2" style="border: 1px solid #e2e8f0;">
                <?php 
                foreach ($cart_items as $key => $item): 
                    $p_id = $item['product_id'];
                    if (!isset($db_products[$p_id])) continue;
                    $product = $db_products[$p_id];

                    $itemImage = !empty($item['custom_image']) ? $item['custom_image'] : '';
                    if (empty($itemImage)) {
                        $imagesArray = !empty($product['images']) ? json_decode($product['images'], true) : [];
                        $itemImage = 'https://via.placeholder.com/100';
                        if (!empty($imagesArray) && isset($imagesArray[0])) {
                            $itemImage = is_array($imagesArray[0]) ? ($imagesArray[0]['url'] ?? $itemImage) : $imagesArray[0];
                        }
                    }
                    
                    $current_size_key = $p_id . '_' . str_replace(' ', '_', trim($item['size']));
                    $isOrdered = isset($confirmed_orders_map[$current_size_key]);
                    $orderStatus = $isOrdered ? $confirmed_orders_map[$current_size_key]['status'] : '';
                    $liveTrackingText = $isOrdered ? $confirmed_orders_map[$current_size_key]['tracking'] : '';
                    
                    $isPipelineActive = ($isOrdered && ($orderStatus === 'pending' || $orderStatus === 'processing'));
                    $isCompletedOrCancelled = ($isOrdered && ($orderStatus === 'completed' || $orderStatus === 'cancelled'));
                ?>
                    <div class="d-flex p-3 border-bottom align-items-center gap-3 position-relative cart-item-row" data-key="<?php echo $key; ?>">
                        
                        <a href="/pages/products/product-details.php?id=<?php echo $p_id; ?>">
                            <img src="<?php echo $itemImage; ?>" class="border rounded-3 p-1 bg-light product-hover-effect" style="width: 85px; height: 85px; object-fit: contain;" alt="Product">
                        </a>
                        
                       <div class="flex-grow-1 min-w-0 cart-details-area">
    <div class="d-flex justify-content-between align-items-start gap-2">
        <div class="min-w-0">
            <span class="text-uppercase text-secondary fw-bold d-block" style="font-size: 10px; letter-spacing: 0.5px;">
                <?php echo htmlspecialchars($product['brand']); ?>
            </span>
            <h6 class="mb-1 text-truncate fw-semibold" style="font-size: 16px; margin: 0;">
                <a href="/pages/products/product-details.php?id=<?php echo $p_id; ?>" class="text-decoration-none hover-link-blue">
                    <?php echo htmlspecialchars($product['name']); ?>
                </a>
            </h6>
        </div>
        
        <?php if (!$isCompletedOrCancelled): ?>
            <form action="" method="POST" onsubmit="return confirm('Do you want to cancel this order from pipeline?');" class="ms-auto">
                <input type="hidden" name="remove_item" value="1">
                <input type="hidden" name="remove_item_key" value="<?php echo $key; ?>">
                <button type="submit" class="btn p-1 text-muted border-0 bg-transparent shadow-none" title="Cancel/Remove Order">
                    <i class="ri-delete-bin-line" style="font-size: 1.1rem;"></i>
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <div class="d-flex align-items-center gap-2 my-2 flex-wrap">
        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px; border-radius: 4px;">Size: <strong><?php echo htmlspecialchars($item['size']); ?></strong></span>
        
        <?php if ($isOrdered): ?>
            <?php if ($orderStatus === 'pending'): ?>
                <span class="badge-track track-pending"><i class="ri-time-line"></i> Order Pending (<?= $confirmed_orders_map[$current_size_key]['custom_order_id'] ?>)</span>
            <?php elseif ($orderStatus === 'processing'): ?>
                <span class="badge-track track-processing"><i class="ri-loader-2-line ri-spin"></i> Processing (<?= $confirmed_orders_map[$current_size_key]['custom_order_id'] ?>)</span>
            <?php elseif ($orderStatus === 'completed'): ?>
                <span class="badge-track track-completed"><i class="ri-checkbox-circle-fill"></i> Delivered (<?= $confirmed_orders_map[$current_size_key]['custom_order_id'] ?>)</span>
            <?php elseif ($orderStatus === 'cancelled'): ?>
                <span class="badge-track track-cancelled"><i class="ri-close-circle-line"></i> Cancelled (<?= $confirmed_orders_map[$current_size_key]['custom_order_id'] ?>)</span>
            <?php endif; ?>
            
            <?php if(!empty($liveTrackingText)): ?>
                <span class="live-tracking-badge">
                    <i class="ri-map-pin-user-line text-primary"></i> Live Status: <strong><?= htmlspecialchars($liveTrackingText) ?></strong>
                </span>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="cart-actions-footer mt-3">
        <div class="qty-counter-zone">
            <?php if (!$isPipelineActive): ?>
                <div class="input-group input-group-sm border rounded-2" style="width: 95px; background: #fff; overflow: hidden; border-color: #cbd5e1 !important;">
                    <button class="btn btn-link link-dark btn-minus p-0 border-0 text-decoration-none fw-bold" style="width: 28px; text-align: center;" type="button">-</button>
                    <input type="text" class="form-control text-center p-0 border-0 qty-input fw-bold" value="<?php echo $item['quantity']; ?>" readonly style="font-size: 13px; background: #fff; height: 28px;">
                    <button class="btn btn-link link-dark btn-plus p-0 border-0 text-decoration-none fw-bold" style="width: 28px; text-align: center;" type="button">+</button>
                </div>
            <?php else: ?>
                <span class="text-secondary small fw-bold"><i class="ri-survey-line me-1"></i> Ordered Qty: <strong class="text-dark fs-6"><?= $item['quantity'] ?></strong></span>
            <?php endif; ?>
            
            <span class="fw-bold text-dark item-total-price" style="font-size: 16px;">₹<?php echo number_format($product['price'] * $item['quantity']); ?></span>
        </div>

        <div class="btn-action-group">
            <a href="/pages/products/product-details.php?id=<?php echo $p_id; ?>" class="btn btn-action btn-check-details">
                Check Details <i class="ri-arrow-right-s-line"></i>
            </a>

            <?php if (!$isPipelineActive): ?>
                <a href="/pages/products/checkout.php?target_key=<?php echo $key; ?>" class="btn btn-action btn-buy-now">
                    Buy This Item <i class="ri-arrow-right-s-line"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-plus').forEach(button => {
        button.addEventListener('click', function() {
            let row = this.closest('.cart-item-row');
            let input = row.querySelector('.qty-input');
            if(!input) return; 
            let currentQty = parseInt(input.value);
            if (currentQty < 10) { 
                let newQty = currentQty + 1;
                input.value = newQty;
                updateCartQuantity(row.dataset.key, newQty, row);
            }
        });
    });

    document.querySelectorAll('.btn-minus').forEach(button => {
        button.addEventListener('click', function() {
            let row = this.closest('.cart-item-row');
            let input = row.querySelector('.qty-input');
            if(!input) return; 
            let currentQty = parseInt(input.value);
            if (currentQty > 1) { 
                let newQty = currentQty - 1;
                input.value = newQty;
                updateCartQuantity(row.dataset.key, newQty, row);
            }
        });
    });
    
    function updateCartQuantity(cartKey, quantity, row) {
        row.querySelectorAll('button').forEach(btn => btn.disabled = true);
        let formData = new FormData();
        formData.append('action', 'update_quantity');
        formData.append('cart_key', cartKey);
        formData.append('quantity', quantity);

        fetch(window.location.href, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                row.querySelector('.item-total-price').innerText = data.item_total;
            }
        })
        .catch(error => console.error('Error:', error))
        .finally(() => {
            let checkInput = row.querySelector('.qty-input');
            if(checkInput) {
                row.querySelectorAll('button').forEach(btn => btn.disabled = false);
            }
        });
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
