<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';

// Automatic Minimal .env Parser Matrix
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

// GET se targeted product key check karein jo user ne select ki hai
$target_key = isset($_GET['target_key']) ? trim($_GET['target_key']) : '';

if (empty($_SESSION['cart']) || empty($target_key) || !isset($_SESSION['cart'][$target_key])) {
    header("Location: cart.php");
    exit;
}

if (!function_exists('url')) {
    function url($path) {
        return '/' . ltrim($path, '/');
    }
}

$default_name = "";
$default_mobile = "";

if (isset($_SESSION['user_id'])) {
    try {
        $user_stmt = $pdo->prepare("SELECT name, mobile FROM users WHERE id = ?");
        $user_stmt->execute([$_SESSION['user_id']]);
        $logged_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        if ($logged_user) {
            $default_name = $logged_user['name'] ?? "";
            $default_mobile = $logged_user['mobile'] ?? "";
        }
    } catch (PDOException $e) {
        error_log("Database User Fetch Error: " . $e->getMessage());
    }
}

// Target item configuration mapping node
$target_item = $_SESSION['cart'][$target_key];
$product_id = intval($target_item['product_id']);

$stmt = $pdo->prepare("SELECT * FROM all_products_list WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: cart.php");
    exit;
}

// Dynamic target 1-to-1 calculation matrix (Strict Server Price Lock)
$grandTotal = (float)$product['price'] * $target_item['quantity'];

// =========================================================================
// SECURE LAYER: Server-Side Razorpay Order ID Creation & Error Logs
// =========================================================================
$amountInPaise = (int) round($grandTotal * 100);
$razorpayOrderId = "";

// Aligned exactly to your .env variable naming tokens
$razorpayKeyId = isset($_ENV['RAZORPAY_KEY_ID']) ? $_ENV['RAZORPAY_KEY_ID'] : getenv('RAZORPAY_KEY_ID');
$razorpaySecret = isset($_ENV['RAZOR_PAY_SECRET_KEY']) ? $_ENV['RAZOR_PAY_SECRET_KEY'] : getenv('RAZOR_PAY_SECRET_KEY');

if (!empty($razorpayKeyId) && !empty($razorpaySecret)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    
    $orderData = [
        'amount' => $amountInPaise, 
        'currency' => 'INR',
        'receipt' => 'rcpt_' . time() . '_' . $product_id
    ];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
    curl_setopt($ch, CURLOPT_USERPWD, $razorpayKeyId . ':' . $razorpaySecret);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    // Bypass local development server SSL validation issues
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    
    if ($response === false) {
        error_log("Razorpay cURL Error: " . curl_error($ch));
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resultData = json_decode($response, true);
        
        if ($http_code !== 200) {
            error_log("Razorpay API HTTP Error [Code $http_code]: " . $response);
        } else {
            if (isset($resultData['id']) && isset($resultData['amount']) && (int)$resultData['amount'] === $amountInPaise) {
                $razorpayOrderId = $resultData['id']; 
            } else {
                error_log("Razorpay Response Data Mismatch structure: " . $response);
            }
        }
    }
    curl_close($ch);
} else {
    error_log("Production Critical Alert: Razorpay environment keys are not parsing into runtime memory.");
}
// =========================================================================
?>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .google-card-delivery { background: #ffffff; border-radius: 20px; border: none !important; box-shadow: 0 10px 25px rgba(63, 81, 181, 0.06); overflow: hidden; }
    .google-card-summary { background: #ffffff; border-radius: 20px; border: none !important; box-shadow: 0 10px 25px rgba(244, 63, 94, 0.05); }
    .card-ribbon-primary { height: 5px; width: 100%; background: linear-gradient(90deg, #4f46e5, #6366f1); }
    .card-ribbon-pink { height: 5px; width: 100%; background: linear-gradient(90deg, #ec4899, #f43f5e); }
    .form-control { border: 1px solid #e2e8f0 !important; background-color: #f8fafc !important; border-radius: 12px !important; padding: 11px 16px; font-size: 14px; font-weight: 500; color: #1e293b; transition: all 0.2s ease-in-out; }
    .form-control:focus { background-color: #ffffff !important; border-color: #4f46e5 !important; outline: 0 !important; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12) !important; }
    .google-payment-box { border: 1.5px solid #cbd5e1 !important; border-radius: 14px; box-shadow: 0 6px 15px rgba(0,0,0,0.02); transition: all 0.2s ease; }
    .google-payment-box.active-pay { border: 1.5px solid transparent !important; background: linear-gradient(#fff, #fff) padding-box, linear-gradient(90deg, #10b981, #34d399) border-box !important; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.08); }
</style>

<div class="container py-3 py-md-5">
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="google-card-delivery">
                <div class="card-ribbon-primary"></div>
                <div class="p-4">
                    <div class="border-bottom pb-3 mb-4">
                        <span class="fw-bold text-dark text-uppercase small tracking-wider"><i class="ri-map-pin-2-fill text-primary me-1 fs-5"></i> Delivery Details</span>
                    </div>

                    <form id="checkoutForm" action="<?php echo url('pages/products/order_confirmation.php'); ?>" method="POST" onsubmit="return handleCheckout(event)">
                        <input type="hidden" name="checkout_target_key" value="<?php echo htmlspecialchars($target_key); ?>">
                        
                        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="<?php echo htmlspecialchars($razorpayOrderId); ?>">
                        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id" value="">
                        <input type="hidden" name="razorpay_signature" id="razorpay_signature" value="">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Full Name</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Enter full name" value="<?php echo htmlspecialchars($default_name); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Mobile Number</label>
                                <input type="tel" name="customer_phone" id="checkout_phone" class="form-control" placeholder="e.g. 9876543210" value="<?php echo htmlspecialchars($default_mobile); ?>" minlength="10" required>
                                <div id="phone_error" class="text-danger small mt-1 d-none"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">Flat, House no., Building, Street Area</label>
                                <input type="text" name="shipping_address" id="shipping_address" class="form-control" placeholder="Complete address details..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Town / City</label>
                                <input type="text" name="city" id="city" class="form-control" placeholder="e.g. Nichlaul" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Pincode</label>
                                <input type="text" name="pincode" id="pincode" class="form-control" placeholder="e.g. 273304" required>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <span class="fw-bold text-dark text-uppercase small tracking-wider mb-3 d-block"><i class="ri-bank-card-2-fill text-success me-1 fs-5"></i> Payment Mode</span>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <div class="google-payment-box p-3 active-pay" id="box_cod">
                                <div class="form-check m-0 d-flex align-items-start gap-1">
                                    <input class="form-check-input mt-1 shadow-none" type="radio" name="payment_method" id="cod" value="COD" checked onclick="togglePayBox('COD')" style="accent-color: #10b981;">
                                    <label class="form-check-label ms-1" for="cod">
                                        <strong class="text-dark d-block small fw-bold">Cash on Delivery (COD)</strong>
                                        <span class="text-muted small mt-0.5 d-block" style="font-size: 12px; line-height: 1.4;">Pay via cash or digital UPI scan directly during package handover.</span>
                                    </label>
                                </div>
                            </div>

                            <div class="google-payment-box p-3" id="box_online">
                                <div class="form-check m-0 d-flex align-items-start gap-1">
                                    <input class="form-check-input mt-1 shadow-none" type="radio" name="payment_method" id="online" value="ONLINE" onclick="togglePayBox('ONLINE')" style="accent-color: #10b981;">
                                    <label class="form-check-label ms-1" for="online">
                                        <strong class="text-dark d-block small fw-bold">Pay Online (UPI, Cards, NetBanking)</strong>
                                        <span class="text-muted small mt-0.5 d-block" style="font-size: 12px; line-height: 1.4;">Secure payment experience handled via Razorpay APIs instantly.</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="fixed-bottom bg-white border-top py-2 px-3 shadow-lg d-lg-none" style="z-index: 1040;">
                            <div class="d-flex align-items-center justify-content-between mx-auto" style="max-width: 500px;">
                                <div>
                                    <span class="text-muted d-block small text-uppercase fw-bold" style="font-size: 9px;">Total Payable</span>
                                    <strong class="text-dark fs-5">₹<?php echo number_format($grandTotal); ?></strong>
                                </div>
                                <button type="submit" name="place_order_btn" class="btn btn-dark py-2 px-4 fw-bold text-uppercase rounded-3" style="background: linear-gradient(90deg, #1e293b, #0f172a); border:none; font-size: 13px;">
                                    Book Order <i class="ri-arrow-right-line ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-none d-lg-block mt-4">
                            <button type="submit" name="place_order_btn" class="btn btn-dark w-100 py-2.5 text-uppercase fw-bold rounded-3" style="background: linear-gradient(90deg, #1e293b, #0f172a); border:none; font-size: 14px;">
                                Confirm & Place Order • ₹<?php echo number_format($grandTotal); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="google-card-summary position-sticky overflow-hidden" style="top: 24px;">
                <div class="card-ribbon-pink"></div>
                <div class="p-4">
                    <span class="fw-bold text-secondary text-uppercase small tracking-wider border-bottom pb-3 mb-3 d-block"><i class="ri-shopping-bag-3-line me-1 text-danger fs-5"></i> Price Summary</span>
                    
                    <div class="pb-2 mb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div class="min-w-0">
                                <h6 class="text-dark mb-0 text-truncate fw-bold" style="font-size: 13.5px;"><?php echo htmlspecialchars($product['name']); ?></h6>
                                <span class="text-muted d-block mt-0.5 small fw-medium">Size: <strong class="text-dark"><?php echo $target_item['size']; ?></strong> • Qty: <strong class="text-dark"><?php echo $target_item['quantity']; ?></strong></span>
                            </div>
                            <span class="fw-bold text-dark small text-nowrap">₹<?php echo number_format($grandTotal); ?></span>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2.5 small text-secondary">
                        <span class="fw-medium">Price (1 Item)</span>
                        <span class="text-dark fw-bold">₹<?php echo number_format($grandTotal); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2.5 small text-secondary">
                        <span class="fw-medium">Delivery Charges</span>
                        <span class="text-success fw-bold">FREE</span>
                    </div>
                    <hr style="border-top: 1px dashed #cbd5e1;" class="my-3">
                    <div class="d-flex justify-content-between align-items-center fw-bold text-dark">
                        <span style="font-size: 14.5px;">Amount Payable</span>
                        <span class="fs-4 text-dark fw-bold">₹<?php echo number_format($grandTotal); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
    function togglePayBox(mode) {
        if(mode === 'COD') {
            document.getElementById('box_cod').classList.add('active-pay');
            document.getElementById('box_online').classList.remove('active-pay');
        } else {
            document.getElementById('box_online').classList.add('active-pay');
            document.getElementById('box_cod').classList.remove('active-pay');
        }
    }

    function handleCheckout(e) {
        e.preventDefault();
        
        const phoneInput = document.getElementById('checkout_phone');
        const errorDiv = document.getElementById('phone_error');
        const phoneValue = phoneInput.value.trim();
        const phoneRegex = /^\+?[0-9\s\-]+$/;
        const digitsOnly = phoneValue.replace(/\D/g, '');

        if (!phoneRegex.test(phoneValue)) {
            errorDiv.textContent = "Invalid characters! Only numbers, space, - and + are allowed.";
            errorDiv.classList.remove('d-none');
            phoneInput.focus();
            return false;
        }
        if (digitsOnly.length < 10) {
            errorDiv.textContent = "Mobile number must have at least 10 digits.";
            errorDiv.classList.remove('d-none');
            phoneInput.focus();
            return false;
        }
        errorDiv.classList.add('d-none');

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (paymentMethod === 'ONLINE') {
            const serverOrderId = document.getElementById('razorpay_order_id').value;
            
            if (!serverOrderId || serverOrderId.trim() === "") {
                alert("Online Payment Initiation Failed. Please choose Cash on Delivery or reload the session.");
                return false;
            }

            var options = {
                "key": "<?php echo $razorpayKeyId; ?>", 
                "order_id": serverOrderId,
                "name": "ELDURATO",
                "description": "THE LEGACY OF GENUINE LEATHER",
                "handler": function (response){
                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                    document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                    document.getElementById('razorpay_signature').value = response.razorpay_signature;
                    document.getElementById('checkoutForm').submit();
                },
                "prefill": {
                    "name": document.getElementById('customer_name').value,
                    "contact": phoneValue
                },
                "theme": {
                    "color": "#4f46e5"
                },
                "modal": {
                    "ondismiss": function(){
                        console.log('Checkout modal closed by customer.');
                    }
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.open();
        } else {
            document.getElementById('checkoutForm').submit();
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../../includes/footer.php'; ?>