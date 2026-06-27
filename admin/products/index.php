<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/cloudinary.php'; 

// Login Check
if (!isset($_SESSION['user_id'])) {
    header("Location: /pages/auth/login.php");
    exit;
}

// 🛡️ SECURITY CHECK: Kahin koyi normal customer is page par na aa jaye
// Agar aapke session me 'role' ya 'is_admin' jaisa variable hai toh use lagayein
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Agar admin nahi hai toh home page par fenk do
    header("Location: /index.php"); 
    exit;
}

// 🔴 CLEAN DELETE LOGIC
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $pdo->prepare("DELETE FROM all_products_list WHERE id=?")->execute([$id]);
    } catch (PDOException $e) { 
        $_SESSION['error_msg'] = "Failed to delete product."; 
    }
    header("Location: index.php");
    exit;
}

// FETCH DYNAMIC PRODUCTS
$products = $pdo->query("SELECT * FROM all_products_list ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELDURATO - Catalog Base</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">

</head>
<body>

<div class="container-fluid">
    <div class="row">
      
<!-- admin\products\index.php -->
    <?php include '../adminSidebar.php'; ?>


        <div class="col-lg-10 p-2 offset-lg-2">
            
          <div class="header-console p-3 text-white shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    
    <div class="d-flex align-items-center gap-3 order-1">
        <button class="btn btn-outline-light d-lg-none px-2.5 py-1.5 me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="ri-menu-2-line fs-5 m-0 align-middle"></i>
        </button>
        <div>
            <h5 class="fw-bold m-0">Inventory Console</h5>
            <div class="opacity-75 fs-7"><?= date('M d, Y') ?></div>
        </div>
    </div>
    
    <a href="add.php" class="btn btn-warning text-dark fw-bold px-3 d-flex align-items-center gap-1 shadow-sm order-2 order-md-3">
        <i class="ri-plus-line fs-5"></i> Add Product
    </a>

    <div class="position-relative order-3 order-md-2 w-100 w-md-auto flex-md-grow-1" style="max-width: 450px; min-width: 320px;">
        <i class="ri-search-2-line position-absolute top-50 start-0 translate-middle-y text-muted ms-3 fs-5"></i>
        <input type="text" id="catalogSearch" class="form-control search-box ps-5 py-2 rounded-3 text-dark" placeholder="Search Title, Brand, Model...">
    </div>

</div>

            <div class="card profile-card overflow-hidden mb-4">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <span class="m-0"><i class="ri-stack-line me-1"></i> Stock Ledger</span>
                    <span class="badge bg-white text-dark rounded-pill fw-bold px-2.5 py-1" style="font-size: 11px;">Active Skus: <?= count($products) ?></span>
                </div>
                
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase">
                                <tr>
                                    <th style="width: 80px;">Visual</th>
                                    <th style="width: 15%;">Brand</th>
                                    <th style="width: 40%;">Product Details</th>
                                    <th style="width: 15%;">Price Tracking</th>
                                    <th style="width: 15%;">Stock Status</th>
                                    <th class="text-center" style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($products)): foreach($products as $p): 
                                    $rawImages = $p['images'] ?: $p['image'] ?: '';
                                    $imagesArray = json_decode($rawImages, true);
                                    $displayImage = (is_array($imagesArray) && isset($imagesArray[0]['url'])) ? $imagesArray[0]['url'] : (is_array($imagesArray) && !empty($imagesArray) ? $imagesArray[0] : ($rawImages ?: 'https://via.placeholder.com/60x60?text=No+Img'));
                                    
                                    $searchMeta = strtolower(($p['brand'] ?? '') . ' ' . $p['name'] . ' ' . ($p['color'] ?? '') . ' ' . ($p['model_name'] ?? ''));
                                    $stock = intval($p['stock'] ?? 0);
                                ?>
                                <tr class="catalog-row" data-filter="<?= htmlspecialchars($searchMeta, ENT_QUOTES) ?>">
                                    <td>
                                        <div class="product-img-box shadow-sm">
                                            <img src="<?= $displayImage ?>" width="44" height="44" class="rounded object-fit-contain" alt="product" onerror="this.src='https://via.placeholder.com/60x60?text=No+Img';">
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark text-uppercase border px-2 py-1 rounded" style="font-size: 11px;">
                                            <?= htmlspecialchars($p['brand'] ?: 'ELDURATO') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-truncate small mb-0.5" style="max-width: 280px;" title="<?= htmlspecialchars($p['name']) ?>">
                                            <?= htmlspecialchars($p['name']) ?>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center text-muted" style="font-size: 11px;">
                                            <span><i class="ri-palette-line me-0.5"></i><?= htmlspecialchars($p['color'] ?: 'Standard') ?></span>
                                            <?php if(!empty($p['model_name'])): ?>
                                                <span>| Mod: <?= htmlspecialchars($p['model_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">₹<?= number_format($p['price']) ?></div>
                                        <?php if(($p['old_price'] ?? 0) > $p['price']): ?>
                                            <small class="text-muted text-decoration-line-through" style="font-size: 11px;">₹<?= number_format($p['old_price']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($stock > 10): ?>
                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded" style="font-size: 11px;">Available (<?= $stock ?>)</span>
                                        <?php elseif($stock <= 10 && $stock > 0): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 rounded" style="font-size: 11px;">Low Stock (<?= $stock ?>)</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1 rounded" style="font-size: 11px;">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1.5">
                                            <a href="edit.php?id=<?= $p['id'] ?>" class="action-btn text-primary" title="Edit Listing"><i class="ri-pencil-line"></i></a>
                                            <a href="index.php?delete=<?= $p['id'] ?>" class="action-btn text-danger" onclick="return confirm('⚠️ Delete this listing permanently?')" title="Delete Listing"><i class="ri-delete-bin-5-line"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted border-0">
                                        <i class="ri-inbox-archive-line d-block mb-1 text-secondary fs-2"></i>
                                        <small class="fw-bold">No items recorded in inventory ledger.</small>
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

<script>
    // Live Search Pipeline
    document.getElementById('catalogSearch').addEventListener('input', function() {
        let query = this.value.toLowerCase().trim();
        document.querySelectorAll('.catalog-row').forEach(row => {
            let meta = row.getAttribute('data-filter') || '';
            row.style.display = meta.includes(query) ? '' : 'none';
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
