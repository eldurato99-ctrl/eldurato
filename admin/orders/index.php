<?php
session_start();
require_once '../../config/database.php';

// ==========================================
// 1. AJAX STATUS UPDATE WITH STATE IMMUTABILITY CONSTRAINT
// ==========================================
if(isset($_POST['ajax_update_status'])) {
    header('Content-Type: application/json');
    $order_id = intval($_POST['order_id']);
    $new_status = strtolower(trim($_POST['status']));
    
    // Database se current status nikalein
    $chkStmt = $pdo->prepare("SELECT order_status FROM all_orders_list WHERE id = ?");
    $chkStmt->execute([$order_id]);
    $current_state = strtolower($chkStmt->fetchColumn() ?: 'pending');
    
    // Define State Hierarchy (Workflow levels)
    $levels = ['pending' => 1, 'processing' => 2, 'completed' => 3, 'cancelled' => 3];
    
    $current_level = $levels[$current_state] ?? 1;
    $new_level = $levels[$new_status] ?? 1;
    
    // 🔒 STRICT REVERSE BLOCK
    if($new_level < $current_level || $current_state === 'completed' || $current_state === 'cancelled') {
        echo json_encode(['success' => false, 'message' => 'Status reverse operation is strictly blocked! Workflow must move forward.']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();

        // 1. Order Status Update karein
        $stmt = $pdo->prepare("UPDATE all_orders_list SET order_status=? WHERE id=?");
        $stmt->execute([$new_status, $order_id]);

        // 🚀 NEW LOGIC: Agar admin order COMPLETE karta hai, toh product ko OUT OF STOCK mark karo
        if($new_status === 'completed') {
            $itemStmt = $pdo->prepare("SELECT product_id FROM order_items WHERE order_id = ?");
            $itemStmt->execute([$order_id]);
            $product_id = $itemStmt->fetchColumn();

            if($product_id) {
                $updateStock = $pdo->prepare("UPDATE all_products_list SET stock_status = 'out_of_stock' WHERE id = ?");
                $updateStock->execute([$product_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 2. AJAX LIVE LOCATION / LOGISTICS TRACKING UPDATE
// ==========================================
if(isset($_POST['ajax_update_tracking'])) { 
    header('Content-Type: application/json');
    $order_id = intval($_POST['order_id']);
    $tracking_info = trim($_POST['tracking_info']);
    
    try {
        $success = $pdo->prepare("UPDATE all_orders_list SET tracking_status=? WHERE id=?")
                        ->execute([$tracking_info, $order_id]);
        echo json_encode(['success' => $success]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error during tracking update.']);
    }
    exit;
}

// ==========================================
// 3. FETCH CONFIGURATION STREAM WITH MULTI-ITEM SPEC & TRANSACTION DETAILS
// ==========================================
$query = "
    SELECT 
        o.id,
        o.customer_name,
        o.customer_phone,
        o.total_amount,
        o.payment_method,
        o.order_status,
        o.tracking_status,
        o.transaction_id,
        o.created_at,
        COALESCE(u.name, o.customer_name, 'Guest Customer') as customer_real_name,
        oi.size as ordered_size,
        oi.quantity as ordered_qty,
        p.id as product_db_id,
        p.name as product_name,
        p.images as product_images,
        p.brand as product_brand,
        p.color as product_color,
        p.material as product_material,
        p.description as product_desc,
        p.model_name as product_model
    FROM all_orders_list o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN all_products_list p ON oi.product_id = p.id
    ORDER BY o.id DESC
";

$orders = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

$status_map = [
    'pending'    => 'bg-warning text-dark border-0 fw-bold',
    'processing' => 'bg-info text-white border-0 fw-bold',
    'completed'  => 'bg-success text-white border-0 fw-bold',
    'cancelled'  => 'bg-danger text-white border-0 fw-bold'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELDURATO - Orders Control Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include '../adminSidebar.php'; ?>

        <div class="col-lg-10 p-2 offset-lg-2">
            
            <div class="bg-primary bg-gradient p-3 text-white shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 rounded-3">
                <div class="d-flex align-items-center">
                     <button class="btn btn-outline-light d-lg-none px-2.5 py-1.5 me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <i class="ri-menu-2-line fs-5 m-0 align-middle"></i>
                    </button>
                   <div>
                     <h5 class="fw-bold m-0 fs-6">Fulfillment Pipeline</h5>
                    <div class="opacity-75 small"><?= date('M d, Y') ?></div>
                   </div>
                </div>
                <div class="position-relative" style="min-width: 320px;">
                    <i class="ri-search-2-line position-absolute top-50 start-0 translate-middle-y text-muted ms-3 fs-5"></i>
                    <input type="text" id="orderSearchInput" class="form-control bg-white border-0 ps-5 py-2 rounded-3 text-dark small" placeholder="Search Customer, ID, Phone, Status, Transaction...">
                </div>
                <a href="../../index.php" class="nav-link-custom m-0 text-white d-flex align-items-center gap-1 small"><i class="ri-store-2-line"></i>View Shop</a>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-4 rounded-3 bg-white">
                <div class="bg-primary bg-gradient text-white p-3 fw-bold d-flex justify-content-between align-items-center">
                    <span class="m-0"><i class="ri-file-list-3-line me-1"></i> Live Orders Stream</span>
                    <span class="badge bg-white text-dark rounded-pill fw-bold px-2.5 py-1" style="font-size: 11px;">Live Items: <?= count($orders) ?></span>
                </div>
                
                <div class="p-0" style="font-size: 0.8rem;">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light small text-muted text-uppercase">
                                <tr>
                                    <th style="width: 12%;">Order Id</th>
                                    <th style="width: 25%;">Product Specification</th>
                                    <th style="width: 18%;">Customer</th>
                                    <th style="width: 10%;">Net Total</th>
                                    <th style="width: 12%;">Method & Payment</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 20%;">Live Location Tracking</th>
                                    <th style="width: 5%;" class="text-center">Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($orders)): foreach($orders as $o): 
                                    $curr_status = strtolower($o['order_status'] ?? 'pending');
                                    $pay_method = strtoupper($o['payment_method'] ?? 'COD');
                                    $suffix = ($pay_method == 'COD') ? 'COD' : 'ONL';
                                    $custom_order_id = "ELD-" . $suffix . "-" . $o['id'];
                                    $txn_id = !empty($o['transaction_id']) ? htmlspecialchars($o['transaction_id'], ENT_QUOTES) : '';

                                    $prod_img = 'https://via.placeholder.com/60?text=No+Image';
                                    if (!empty($o['product_images'])) {
                                        $img_json = json_decode($o['product_images'], true);
                                        if (is_array($img_json) && isset($img_json[0]['url'])) {
                                            $prod_img = $img_json[0]['url'];
                                        } elseif (!is_array($img_json)) {
                                            $prod_img = $o['product_images'];
                                        }
                                    }

                                    $p_name = htmlspecialchars($o['product_name'] ?: 'Canceled Product', ENT_QUOTES);
                                    $p_brand = htmlspecialchars($o['product_brand'] ?? 'N/A', ENT_QUOTES);
                                    $p_color = htmlspecialchars($o['product_color'] ?? 'N/A', ENT_QUOTES);
                                    $p_material = htmlspecialchars($o['product_material'] ?? 'N/A', ENT_QUOTES);
                                    $p_model = htmlspecialchars($o['product_model'] ?? 'N/A', ENT_QUOTES);
                                    $p_desc = !empty($o['product_desc']) ? htmlspecialchars(str_replace(["\r", "\n", "'", '"'], [" ", " ", "\\'", '\\"'], $o['product_desc']), ENT_QUOTES) : 'No Description Available.';
                                    
                                    $isLockedState = ($curr_status === 'completed' || $curr_status === 'cancelled');
                                ?>
                                <tr class="order-row" data-search="<?= strtolower($custom_order_id . ' ' . htmlspecialchars($o['customer_real_name'] . ' ' . ($o['customer_phone'] ?? '') . ' ' . $curr_status . ' ' . $pay_method . ' ' . $p_name . ' ' . ($o['tracking_status'] ?? '') . ' ' . $txn_id)) ?>">
                                    <td>
                                        <p class="fw-bold text-primary mb-0 s"><?= $custom_order_id ?></p>
                                        <small class="text-secondary d-block mt-0" style="font-size: 11px;"><i class="ri-time-line"></i> <?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></small>
                                    </td>
                                    
                                    <td style="cursor: pointer;" onclick="showProductDetails('<?= $p_name ?>', '<?= htmlspecialchars($prod_img, ENT_QUOTES) ?>', '<?= htmlspecialchars($o['ordered_size'] ?? 'Free') ?>', '<?= intval($o['ordered_qty'] ?? 1) ?>', '<?= $custom_order_id ?>', '<?= $p_brand ?>', '<?= $p_color ?>', '<?= $p_material ?>', '<?= $p_model ?>', '<?= $p_desc ?>', '<?= $pay_method ?>', '<?= $txn_id ?>')">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-white p-1 rounded border shadow-sm d-inline-flex">
                                                <img src="<?= htmlspecialchars($prod_img) ?>" class="rounded object-fit-contain" width="44" height="44" alt="product">
                                            </div>
                                            <div style="max-width: 180px;">
                                                <div class="fw-bold text-dark text-truncate small mb-1" title="<?= htmlspecialchars($o['product_name'] ?? 'N/A') ?>">
                                                    <?= htmlspecialchars($o['product_name'] ?: 'Canceled Product') ?>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <span class="badge bg-dark text-white border px-1.5 py-0.5" style="font-size: 9px;">Size: <?= htmlspecialchars($o['ordered_size'] ?? 'Free') ?></span>
                                                    <span class="badge bg-secondary text-white border px-1.5 py-0.5" style="font-size: 9px;">Qty: <?= intval($o['ordered_qty'] ?? 1) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="fw-bold text-dark mb-1 small"><i class="ri-user-3-line text-muted me-1"></i><?= htmlspecialchars($o['customer_real_name']) ?></div>
                                        <small class="text-muted fw-medium" style="font-size: 12px;"><i class="ri-phone-line text-primary me-1"></i><?= htmlspecialchars($o['customer_phone'] ?? '') ?></small>
                                    </td>
                                    
                                    <td><div class="fw-bold text-dark fs-5">₹<?= number_format($o['total_amount']) ?></div></td>
                                    
                                    <td style="cursor: pointer;" onclick="showProductDetails('<?= $p_name ?>', '<?= htmlspecialchars($prod_img, ENT_QUOTES) ?>', '<?= htmlspecialchars($o['ordered_size'] ?? 'Free') ?>', '<?= intval($o['ordered_qty'] ?? 1) ?>', '<?= $custom_order_id ?>', '<?= $p_brand ?>', '<?= $p_color ?>', '<?= $p_material ?>', '<?= $p_model ?>', '<?= $p_desc ?>', '<?= $pay_method ?>', '<?= $txn_id ?>')">
                                        <?php if($pay_method === 'COD'): ?>
                                            <span class="badge bg-light text-dark border px-2 py-1 rounded font-monospace fw-bold d-block text-center mb-1" style="font-size: 10px;">
                                                💵 COD
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success text-white border border-success px-2 py-1 rounded font-monospace fw-bold d-block text-center mb-1" style="font-size: 10px;">
                                                💳 ONLINE PAID
                                            </span>
                                            <?php if(!empty($txn_id)): ?>
                                                <small class="d-block text-muted text-center font-monospace" style="font-size: 9px;" title="Razorpay Reference Token">
                                                    <i class="ri-shield-check-line text-success"></i> <?= substr($txn_id, 0, 12) ?>...
                                                </small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <select class="form-select form-select-sm border-0 cursor-pointer py-1.5 px-2 rounded-3 text-center small <?= $status_map[$curr_status] ?? 'bg-secondary text-white' ?>" 
                                                onchange="updateOrderStatus(<?= $o['id'] ?>, this)" 
                                                <?= $isLockedState ? 'disabled' : '' ?>>
                                            
                                            <?php if($curr_status === 'pending'): ?>
                                                <option value="pending" selected class="text-dark bg-white fw-semibold">PENDING</option>
                                            <?php endif; ?>
                                            
                                            <?php if($curr_status === 'pending' || $curr_status === 'processing'): ?>
                                                <option value="processing" <?= $curr_status == 'processing' ? 'selected' : '' ?> class="text-dark bg-white fw-semibold">PROCESSING</option>
                                            <?php endif; ?>
                                            
                                            <option value="completed" <?= $curr_status == 'completed' ? 'selected' : '' ?> class="text-dark bg-white fw-semibold">COMPLETED</option>
                                            <option value="cancelled" <?= $curr_status == 'cancelled' ? 'selected' : '' ?> class="text-dark bg-white fw-semibold">CANCELLED</option>
                                        </select>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="text" class="form-control form-control-sm text-dark border bg-white rounded-3 px-2 py-1 small" 
                                                   id="track_input_<?= $o['id'] ?>" 
                                                   value="<?= htmlspecialchars($o['tracking_status'] ?? '') ?>" 
                                                   placeholder="e.g. Gorakhpur Hub..." style="width: 180px;">
                                            <button type="button" class="btn btn-sm btn-primary py-1 px-2 border-0" 
                                                    onclick="saveTrackingInfo(<?= $o['id'] ?>)" title="Save Location">
                                                <i class="ri-save-2-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <a href="invoice.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-sm btn-light border bg-white d-inline-flex align-items-center justify-content-center p-2 rounded-3 text-dark transition-all" title="Print Invoice" style="width:34px; height:34px;"><i class="ri-printer-fill"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="8" class="text-center text-muted py-5 fw-bold fs-5">🚫 No Active Fulfillment Records Found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
      UPGRADED SYSTEM MODAL DATA ENGINE
========================================== -->
<div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-3 shadow-lg">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2"><i class="ri-survey-line text-warning"></i> Full Product Specifications</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                    <img id="modalProductImg" src="" class="img-fluid rounded border bg-light object-fit-contain p-1" style="width: 70px; height: 70px;" alt="Asset">
                    <div>
                        <h6 class="fw-bold text-dark mb-1" id="modalProductName">-</h6>
                        <span class="badge bg-primary text-white font-monospace" id="modalOrderRef">-</span>
                    </div>
                </div>
                
                <div class="row g-2 mb-3 text-center">
                    <div class="col-6">
                        <div class="bg-light py-2 rounded border">
                            <small class="text-secondary d-block fw-bold mb-0.5" style="font-size: 10px;">ORDERED SIZE</small>
                            <span class="fw-bold text-dark fs-6" id="modalProductSize">-</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light py-2 rounded border">
                            <small class="text-secondary d-block fw-bold mb-0.5" style="font-size: 10px;">TOTAL QTY</small>
                            <span class="fw-bold text-dark fs-6" id="modalProductQty">-</span>
                        </div>
                    </div>
                </div>

                <!-- 💵 FINANCIAL AUDIT LOGS EXPANSION NODE -->
                <div class="card border-0 bg-light p-2.5 mb-3 rounded-3 border-start border-3" id="modalPaymentCardContext">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-secondary text-uppercase tracking-wider" style="font-size: 10px;">Gateway Status</span>
                        <span id="modalPaymentBadge" class="badge font-monospace fw-bold px-2 py-0.5" style="font-size: 10px;">-</span>
                    </div>
                    <div class="mt-1.5 small font-monospace text-dark d-flex justify-content-between align-items-center" id="modalTxnContainer" style="font-size: 11.5px;">
                        <span class="text-muted">Transaction ID:</span>
                        <strong id="modalTxnId" class="text-primary">-</strong>
                    </div>
                </div>

                <table class="table table-sm table-bordered mb-0" style="font-size: 13px;">
                    <tbody>
                        <tr><td class="text-secondary fw-bold bg-light px-2 py-1.5" style="width:30%;">Brand</td><td class="text-dark fw-bold px-2 py-1.5" id="modalProductBrand">-</td></tr>
                        <tr><td class="text-secondary fw-bold bg-light px-2 py-1.5">Color Code</td><td class="text-dark px-2 py-1.5 fw-medium" id="modalProductColor">-</td></tr>
                        <tr><td class="text-secondary fw-bold bg-light px-2 py-1.5">Material</td><td class="text-dark px-2 py-1.5 fw-medium" id="modalProductMaterial">-</td></tr>
                        <tr><td class="text-secondary fw-bold bg-light px-2 py-1.5">Model</td><td class="text-dark px-2 py-1.5 font-monospace" id="modalProductModel">-</td></tr>
                        <tr><td class="text-secondary fw-bold bg-light px-2 py-1.5">Description</td><td class="text-dark px-2 py-1.5" style="line-height:1.4;" id="modalProductDesc">-</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer border-0 p-2 bg-light">
                <button type="button" class="btn btn-sm btn-dark w-100 py-2 fw-bold" data-bs-dismiss="modal">Dismiss Spec Sheet</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('orderSearchInput').addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.order-row').forEach(r => {
            const text = r.dataset.search || '';
            r.classList.toggle('d-none', !text.includes(q));
        });
    });

    function updateOrderStatus(id, el) {
        const formData = new FormData();
        formData.append('ajax_update_status', '1');
        formData.append('order_id', id);
        formData.append('status', el.value);

        fetch('', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) { location.reload(); } 
            else { 
                alert(data.message || 'Failed to update status!'); 
                location.reload();
            }
        }).catch(() => alert('Network Error!'));
    }

    function saveTrackingInfo(id) {
        const trackingValue = document.getElementById('track_input_' + id).value.trim();
        const formData = new FormData();
        formData.append('ajax_update_tracking', '1');
        formData.append('order_id', id);
        formData.append('tracking_info', trackingValue);

        fetch('', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert('🚀 Tracking details updated successfully!');
            } else {
                alert(data.message || 'Failed to update tracking route!');
            }
        }).catch(() => alert('Network Sync Error!'));
    }

    function showProductDetails(name, img, size, qty, orderId, brand, color, material, model, desc, payMethod, txnId) {
        document.getElementById('modalProductName').innerText = name;
        document.getElementById('modalProductImg').src = img;
        document.getElementById('modalProductSize').innerText = size;
        document.getElementById('modalProductQty').innerText = qty;
        document.getElementById('modalOrderRef').innerText = orderId;  
        document.getElementById('modalProductBrand').innerText = brand;
        document.getElementById('modalProductColor').innerText = color;
        document.getElementById('modalProductMaterial').innerText = material;
        document.getElementById('modalProductModel').innerText = model;
        document.getElementById('modalProductDesc').innerHTML = desc;
        
        // 🛡️ DYNAMIC AUDIT BADGING INJECTOR
        const cardContext = document.getElementById('modalPaymentCardContext');
        const badge = document.getElementById('modalPaymentBadge');
        const txnContainer = document.getElementById('modalTxnContainer');
        const txnIdText = document.getElementById('modalTxnId');

        if(payMethod === 'ONLINE') {
            cardContext.className = "card border-0 bg-success-subtle p-2.5 mb-3 rounded-3 border-start border-success border-3";
            badge.className = "badge bg-success text-white font-monospace fw-bold px-2 py-0.5";
            badge.innerHTML = "<i class='ri-shield-check-fill'></i> ONLINE PAID (RAZORPAY)";
            
            if(txnId !== '') {
                txnContainer.style.display = 'flex';
                txnIdText.innerText = txnId;
            } else {
                txnContainer.style.display = 'flex';
                txnIdText.innerHTML = "<span class='text-danger'>Missing Token!</span>";
            }
        } else {
            cardContext.className = "card border-0 bg-warning-subtle p-2.5 mb-3 rounded-3 border-start border-warning border-3";
            badge.className = "badge bg-warning text-dark font-monospace fw-bold px-2 py-0.5";
            badge.innerHTML = "💵 CASH ON DELIVERY";
            txnContainer.style.display = 'none';
        }
        
        new bootstrap.Modal(document.getElementById('productDetailsModal')).show();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>