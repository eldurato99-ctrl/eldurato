<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';

// =========================================================================
// 🔄 AUTOMATIC MINIMAL .ENV PARSER MATRIX (Bina Iske Keys Read Nahi Hongi)
// =========================================================================
if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Check if request method is valid
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /pages/products/cart.php");
    exit;
}

// Check if trigger is single item checkout button context
if (!isset($_POST['checkout_target_key']) || empty($_SESSION['cart'])) {
    header("Location: /pages/products/cart.php");
    exit;
}

// Targeted single item verification node
$checkout_target_key = isset($_POST['checkout_target_key']) ? trim($_POST['checkout_target_key']) : '';
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'COD';

if (empty($checkout_target_key) || !isset($_SESSION['cart'][$checkout_target_key])) {
    header("Location: /pages/products/cart.php");
    exit;
}

// Extract target checkout specs
$targeted_item = $_SESSION['cart'][$checkout_target_key];
$p_id = intval($targeted_item['product_id']);
$item_size = trim($targeted_item['size']);

// Fetch single product data details safely (Direct from Admin Database)
$stmt_prod = $pdo->prepare("SELECT id, name, price FROM all_products_list WHERE id = ?");
$stmt_prod->execute([$p_id]);
$product_data = $stmt_prod->fetch(PDO::FETCH_ASSOC);

if (!$product_data) {
    header("Location: /pages/products/cart.php");
    exit;
}

// Exact 1-to-1 matrix calculations (Server Side Expected Price)
$grandTotal = (float)$product_data['price'] * $targeted_item['quantity'];

// =========================================================================
// 🛡️ CRITICAL ACTION: Cryptographic Signature Interception & Processing
// =========================================================================
$rz_payment_id = isset($_POST['razorpay_payment_id']) ? trim($_POST['razorpay_payment_id']) : '';
$rz_order_id   = isset($_POST['razorpay_order_id']) ? trim($_POST['razorpay_order_id']) : '';
$rz_signature  = isset($_POST['razorpay_signature']) ? trim($_POST['razorpay_signature']) : '';

// Hacker Bypass Protection: If a payment payload exists, FORCE upgrade validation to ONLINE
if (!empty($rz_payment_id)) {
    $payment_method = 'ONLINE';
}

$orderStatus = ($payment_method === 'ONLINE') ? 'processing' : 'pending';

if ($payment_method === 'ONLINE') {
    if (empty($rz_payment_id) || empty($rz_order_id) || empty($rz_signature)) {
        die("Security Exception: Missing dynamic verification checksum tokens.");
    }
    
    // FIX: Aligned exactly to your .env variable naming tokens -> RAZOR_PAY_SECRET_KEY
    $razorpaySecret = isset($_ENV['RAZOR_PAY_SECRET_KEY']) ? $_ENV['RAZOR_PAY_SECRET_KEY'] : getenv('RAZOR_PAY_SECRET_KEY');
    
    if (empty($razorpaySecret)) {
        die("Security Exception: Server configuration missing secret signature components.");
    }
    
    // HMAC SHA256 Signature Verification Strategy
    $expectedSignature = hash_hmac('sha256', $rz_order_id . '|' . $rz_payment_id, $razorpaySecret);
    
    if (!hash_equals($expectedSignature, $rz_signature)) {
        error_log("CRITICAL: Razorpay Cryptographic Signature Spoofing Attempt Detected for order: " . $rz_order_id);
        die("Tampering detected. Transaction rejected by server infrastructure security matrix.");
    }
    
    // Signature verified successfully -> Upgrade order status immediately
    $orderStatus = "paid"; 
}
// =========================================================================

// Collect and sanitize input attributes
$customer_name    = htmlspecialchars(trim($_POST['customer_name']));
$customer_phone   = htmlspecialchars(trim($_POST['customer_phone']));
$shipping_address = htmlspecialchars(trim($_POST['shipping_address']));
$city             = htmlspecialchars(trim($_POST['city']));
$pincode          = htmlspecialchars(trim($_POST['pincode']));
$user_id          = $_SESSION['user_id'] ?? null;

$order_id = 0;
$inserted_products_summary = [];

try {
    $pdo->beginTransaction();

    // 1. Database table schema serialization (Order Insert)
    $order_query = "INSERT INTO all_orders_list (user_id, customer_name, customer_phone, shipping_address, city, pincode, total_amount, payment_method, order_status, transaction_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $order_stmt = $pdo->prepare($order_query);
    $order_stmt->execute([
        $user_id,
        $customer_name,
        $customer_phone,
        $shipping_address,
        $city,
        $pincode,
        $grandTotal,
        $payment_method,
        $orderStatus,
        !empty($rz_payment_id) ? $rz_payment_id : null
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // 2. Insert items safely into order_items
    $item_query = "INSERT INTO order_items (order_id, product_id, size, quantity, price) VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $pdo->prepare($item_query);
    $item_stmt->execute([
        $order_id,
        $p_id,
        $item_size,
        $targeted_item['quantity'],
        $product_data['price']
    ]);

    // =========================================================================
    // 🔥 STEP 2.5: AUTOMATIC INVENTORY DEDUCTION (Stock Kam Karne Ka Logic)
    // =========================================================================
    $update_stock_query = "UPDATE all_products_list SET stock = stock - ? WHERE id = ? AND stock >= ?";
    $stock_stmt = $pdo->prepare($update_stock_query);
    $stock_stmt->execute([
        $targeted_item['quantity'], 
        $p_id,
        $targeted_item['quantity'] // Taaki stock minus me na chala jaye
    ]);

    // Agar stock update nahi hua (yani product out of stock ho gaya tha check-out karte karte)
    if ($stock_stmt->rowCount() === 0) {
        throw new Exception("Requested quantity exceeds available stock layers.");
    }
    // =========================================================================

    $inserted_products_summary[] = [
        'name'  => $product_data['name'],
        'price' => $product_data['price'],
        'id'    => $p_id
    ];

    // 3. Destruct processed inventory indices from ongoing cart sessions
    unset($_SESSION['cart'][$checkout_target_key]);
    
    $pdo->commit();

} catch (Exception $e) { // PDOException ko general Exception kiya taaki stock check bhi catch ho sake
    $pdo->rollBack();
    error_log("Order Database Write Failure Exception: " . $e->getMessage());
    die("Internal Engine Error processing order synchronization workflows. Details: " . $e->getMessage());
}
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .shopsy-green { color: #008c45; }
    .bg-shopsy-light { background-color: #f0f9f4; }
    .pulse-animation { animation: pulse 1.5s infinite; }
    @keyframes pulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.03); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow border rounded-4 text-center p-4 bg-white mb-3">
                <div class="my-2">
                    <div class="bg-shopsy-light d-inline-flex align-items-center justify-content-center rounded-circle p-3 pulse-animation" style="width: 80px; height: 80px;">
                        <i class="ri-checkbox-circle-fill shopsy-green fs-1"></i>
                    </div>
                </div>

                <h4 class="fw-bold text-dark mb-1">Order Confirmed!</h4>
                <p class="text-success small fw-semibold mb-3">YAY! Your order has been placed successfully.</p>
                <hr class="text-muted opacity-25 my-2">

                <div class="text-start bg-light p-3 rounded-3 mb-3" style="font-size: 13px;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Order ID</span>
                        <span class="fw-bold text-dark">ELD-<?php echo strtoupper($payment_method) === 'COD' ? 'COD' : 'ONL'; ?>-<?php echo $order_id; ?></span>
                    </div>
                    <?php if(!empty($rz_payment_id)): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Txn ID</span>
                        <span class="fw-medium text-muted"><?php echo htmlspecialchars($rz_payment_id); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Delivery to</span>
                        <span class="fw-medium text-dark text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($customer_name); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Payment Method</span>
                        <span class="fw-medium text-dark text-uppercase"><?php echo htmlspecialchars($payment_method); ?> (<?php echo strtoupper($orderStatus); ?>)</span>
                    </div>
                    <hr class="my-2 opacity-25">
                    
                    <div class="mb-2">
                        <span class="text-secondary d-block mb-1">Newly Ordered Items:</span>
                        <?php foreach ($inserted_products_summary as $prod): ?>
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span class="text-truncate me-2">• <?php echo htmlspecialchars($prod['name']); ?></span>
                                <span class="fw-medium text-nowrap">
                                    <a href="/pages/products/product-details.php?id=<?= $prod['id'] ?>" class="text-decoration-none text-primary me-2 fw-semibold" style="font-size: 11px;">View Product</a>
                                    ₹<?php echo number_format($prod['price'], 2); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr class="my-2 opacity-25">
                    <div class="d-flex justify-content-between align-items-center pt-1">
                        <span class="fw-bold text-secondary">Total Amount</span>
                        <span class="fw-bold text-dark fs-5">₹<?php echo number_format($grandTotal, 2); ?></span>
                    </div>
                </div>

                <div class="d-grid">
                    <a href="/pages/products/cart.php" class="btn btn-dark py-2 fw-semibold text-white" style="background-color: #1a202c; border: none; font-size: 13px; border-radius: 8px;">
                        GO TO CART <i class="ri-shopping-cart-2-line ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-3 rounded-3 text-center bg-shopsy-light border-start border-success border-3">
                <p class="mb-0 text-dark fw-medium" style="font-size: 11.5px;">
                    <i class="ri-truck-line me-1 shopsy-green"></i> Delivery updates will be sent to <strong><?php echo htmlspecialchars($customer_phone); ?></strong>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        var duration = 2 * 1000;
        var end = Date.now() + duration;

        (function frame() {
            confetti({ particleCount: 4, angle: 60, spread: 55, origin: { x: 0 }, colors: ['#008c45', '#ff4500', '#ff007f', '#ffc107'] });
            confetti({ particleCount: 4, angle: 120, spread: 55, origin: { x: 1 }, colors: ['#008c45', '#ff4500', '#ff007f', '#ffc107'] });
            if (Date.now() < end) { requestAnimationFrame(frame); }
        }());
    });
</script>
<?php include '../../includes/footer.php'; ?>