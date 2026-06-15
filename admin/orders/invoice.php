<?php
require_once '../../config/database.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    die("<div class='container my-4 alert alert-danger'>Invalid Invoice Request ID.</div>");
}

// डेटाबेस से लाइव रिकॉर्ड फेच करना
$stmt = $pdo->prepare("
    SELECT o.*, 
           COALESCE(u.name, o.customer_name, 'Guest Customer') as customer_real_name,
           COALESCE(u.email, 'N/A') as customer_email
    FROM all_orders_list o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("<div class='container my-4 alert alert-danger'>Invoice data not found for Order #" . htmlspecialchars($id) . "</div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $order['id'] ?> - ELDURATO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { 
            background: #f8fafc; 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            color: #1e293b;
        }
        
        /* 📐 Dashed Border & Dark Blue Core Theme Design */
        .invoice-wrapper {
            background: #ffffff;
            border: 2px dashed #1e3a8a; /* Premium Dark Blue Dashed Border */
            border-radius: 8px;
            padding: 40px;
            margin-top: 30px;
            margin-bottom: 30px;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .theme-bg-dark-blue {
            background-color: #1e3a8a !important; /* गहरा नीला रंग */
            color: #ffffff !important;
        }

        .theme-text-dark-blue {
            color: #1e3a8a !important;
        }

        .table-billing th {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        /* 🖨️ Professional Print Overrides */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; }
            .invoice-wrapper { 
                border: 2px dashed #1e3a8a !important; 
                box-shadow: none !important;
                padding: 20px !important;
                margin: 0 !important;
            }
            .theme-bg-dark-blue {
                background-color: #1e3a8a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="container col-md-9">
    
    <div class="d-flex justify-content-between mt-4 no-print px-2">
        <a href="index.php" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="ri-arrow-left-line"></i> Orders Panel
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" style="background: #1e3a8a; border-color: #1e3a8a;">
            <i class="ri-printer-line"></i> Print Document
        </button>
    </div>

    <div class="invoice-wrapper">
        
        <div class="row align-items-center mb-4">
            <div class="col-sm-6">
                <!-- <h1 class="fw-bold theme-text-dark-blue m-0" style="font-size: 2.4rem; letter-spacing: -1px;">ELDURATO</h1> -->
                <img src="/belt/assets/images/logo.jpg" class="rounded-3"  width="130" alt="ELDURATO">
                <p class="text-muted small mb-1">Premium Leather Craftsmanship</p>
                <span class="badge bg-secondary-subtle text-secondary border small">GSTIN: 09AAACE1234F1Z5</span>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <h3 class="fw-bold text-uppercase text-secondary m-0" style="font-size: 1.4rem;">Tax Invoice</h3>
                <p class="mb-0 text-dark small"><strong>Invoice No:</strong> #INV-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></p>
                <p class="mb-0 text-dark small"><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                <p class="mb-0 text-dark small"><strong>Mode of Payment:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'COD') ?></p>
            </div>
        </div>
        
        <hr style="border-top: 1px solid #cbd5e1;">
        
        <div class="row mb-5" style="font-size: 0.9rem;">
            <div class="col-md-6 mb-3 mb-md-0">
                <h6 class="text-uppercase text-muted fw-bold small mb-2"><i class="ri-user-invoice-line"></i> Billed To:</h6>
                <div class="p-3 bg-light rounded" style="border-left: 3px solid #1e3a8a;">
                    <strong class="text-dark fs-6"><?= htmlspecialchars($order['customer_real_name']) ?></strong><br>
                    <span class="text-secondary">Email:</span> <?= htmlspecialchars($order['customer_email']) ?><br>
                    <span class="text-secondary">Contact:</span> <?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <h6 class="text-uppercase text-muted fw-bold small mb-2"><i class="ri-truck-line"></i> Shipping Destination:</h6>
                <div class="p-3 bg-light rounded text-md-end" style="border-right: 3px solid #1e3a8a; display: inline-block; width: 100%; min-height: 86px;">
                    <p class="mb-0 text-dark fw-medium">
                        <?= nl2br(htmlspecialchars($order['shipping_address'] ?? '')) ?>
                    </p>
                    <span class="text-dark small">
                        <strong>City:</strong> <?= htmlspecialchars($order['city'] ?? '') ?> | 
                        <strong>Pincode:</strong> <?= htmlspecialchars($order['pincode'] ?? '') ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-billing align-middle mb-0">
                <thead>
                    <tr class="theme-bg-dark-blue text-uppercase">
                        <th class="py-2.5 px-3">Product Description / Service Item</th>
                        <th class="text-center py-2.5" style="width: 80px;">Qty</th>
                        <th class="text-end py-2.5" style="width: 150px;">Unit Price</th>
                        <th class="text-end py-2.5" style="width: 150px;">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-3 px-3">
                            <strong class="text-dark">ELDURATO Premium Leather Belt</strong><br>
                            <small class="text-muted">Genuine Hide Construction with Antique Finish Buckle.</small>
                        </td>
                        <td class="text-center text-dark font-monospace py-3">1</td>
                        <td class="text-end text-dark font-monospace py-3">₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td class="text-end text-dark font-monospace py-3">₹<?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                    
                    <tr>
                        <td colspan="2" class="border-0"></td>
                        <td class="text-end text-secondary small fw-medium py-2">Subtotal:</td>
                        <td class="text-end font-monospace text-dark py-2">₹<?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="border-0"></td>
                        <td class="text-end text-secondary small fw-medium py-2">Shipping Charges:</td>
                        <td class="text-end font-monospace text-success fw-medium py-2">FREE</td>
                    </tr>
                    <tr class="table-secondary" style="border-top: 2px solid #1e3a8a;">
                        <td colspan="2" class="border-0 bg-transparent"></td>
                        <td class="text-end fw-bold theme-text-dark-blue py-2.5">Grand Total:</td>
                        <td class="text-end font-monospace fw-bold theme-text-dark-blue py-2.5" style="font-size: 1.15rem;">₹<?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="row mt-5 pt-4 align-items-end">
            <div class="col-sm-7 mb-4 mb-sm-0">
                <div class="alert alert-secondary p-3 bg-light border-0 m-0" style="font-size: 0.78rem; line-height: 1.5; color: #475569;">
                    <strong>Terms & Conditions:</strong><br>
                    1. Goods once sold will not be taken back or exchanged.<br>
                    2. This document is a valid electronic tax statement for retail purchases.<br>
                    3. For any support, contact operations at support@eldurato.com
                </div>
            </div>
            <div class="col-sm-5 text-center text-sm-end">
                <p class="text-muted small mb-5">For <strong>ELDURATO RETAILS PVT. LTD.</strong></p>
                <div class="d-inline-block border-top border-dark pt-2 text-center" style="width: 180px;">
                    <span class="text-dark small fw-bold">Authorized Signatory</span>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5 pt-3 border-top border-secondary-subtle">
            <h6 class="fw-bold m-0 text-muted" style="font-size:0.85rem; letter-spacing: 0.5px;">Thank you for your choice. Walk with confidence!</h6>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>