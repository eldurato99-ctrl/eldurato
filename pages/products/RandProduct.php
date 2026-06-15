<?php
// pages/products/RandProduct.php
require_once __DIR__ . '/../../config/database.php';

if (!function_exists('url')) {
    function url($path) {
        return '/belt/' . ltrim($path, '/');
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM all_products_list ORDER BY RAND() LIMIT 6");
    $randProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $randProducts = [];
}

$cartActionUrl = url('pages/products/cart.php');
?>
<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 34px; }
    .hover-shadow:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.06) !important; transition: all 0.2s ease; }
</style>

<div class="container-fluid my-3 bg-white p-3 rounded-3 shadow-sm">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold text-dark m-0">Suggested For You</h5>
        </div>
        <a href="<?php echo 'pages/products/products.php'; ?>" class="btn btn-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center border" style="width: 32px; height: 32px;">
            <i class="ri-arrow-right-s-line fs-5 text-dark"></i>
        </a>
    </div>

    <div class="row g-2 g-md-3">
        <?php if (empty($randProducts)): ?>
            <div class="col-12 text-center py-5 bg-white rounded-3 border">
                <p class="text-muted small mb-0">No suggested items found.</p>
            </div>
        <?php autumn: ?>
        <?php else: ?>
            <?php foreach ($randProducts as $product): ?>
                <?php
                $price = (int)$product['price'];
                $oldPrice = isset($product['old_price']) ? (int)$product['old_price'] : 0;
                $discount = ($oldPrice > 0 && $oldPrice > $price) ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
                
                $imagesArray = !empty($product['images']) ? json_decode($product['images'], true) : [];
                $firstImage = 'https://via.placeholder.com/300x300?text=No+Image';
                if (!empty($imagesArray) && isset($imagesArray[0])) {
                    $firstImage = is_array($imagesArray[0]) ? ($imagesArray[0]['url'] ?? $firstImage) : $imagesArray[0];
                }
                
                $detailsUrl = url('pages/products/product-details.php?id=' . $product['id']);
                $brand = isset($product['brand']) ? trim($product['brand']) : 'Premium Collection';
                
                // 🚫 Stock Status Check
                $stockStatus = isset($product['stock_status']) ? trim($product['stock_status']) : 'available';
                $isOutOfStock = ($stockStatus === 'out_of_stock');
                ?>
                
                <div class="col-6 col-md-4 col-lg-2 d-flex">
                    <div class="card w-100 border rounded-3 position-relative shadow-sm hover-shadow p-1 <?php echo $isOutOfStock ? 'opacity-75' : ''; ?>">
                        
                        <div class="position-relative text-center bg-light rounded-2 overflow-hidden" style="aspect-ratio: 1/1;">
                            <a href="<?php echo $detailsUrl; ?>" class="w-100 h-100 d-flex align-items-center justify-content-center">
                                <img class="w-100 h-100 object-fit-contain p-2" src="<?php echo $firstImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            
                            <?php if ($isOutOfStock): ?>
                                <div class="position-absolute top-0 start-0 m-2 bg-danger text-white px-2 py-1 rounded fw-bold text-uppercase shadow-sm" style="font-size: 9px; z-index:3;">
                                    <i class="ri-error-warning-line me-1"></i>Stock Not Available
                                </div>
                            <?php elseif ($discount > 0): ?>
                                <span class="badge bg-danger position-absolute bottom-0 start-0 m-2" style="font-size: 9px; font-weight: 700;"><?php echo $discount; ?>% OFF</span>
                            <?php endif; ?>
                            
                            <div class="position-absolute top-0 end-0 m-2">
                                <button type="button" class="btn btn-white btn-sm rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center wishlist-btn" data-product-id="<?php echo $product['id']; ?>" style="width:30px; height:30px; background: rgba(255,255,255,0.95);">
                                    <i class="ri-heart-line text-secondary" style="font-size: 14px;"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                            <div class="mb-2">
                                <div class="text-muted text-uppercase fw-bold" style="font-size: 9px; letter-spacing:0.3px;"><?php echo htmlspecialchars($brand); ?></div>
                                <h6 class="text-dark mt-0.5 mb-1 text-truncate-2 fw-medium" style="font-size: 12.5px; line-height: 1.3;"><?php echo htmlspecialchars($product['name']); ?></h6>
                                
                                <div class="d-flex align-items-baseline gap-1 flex-wrap mt-1">
                                    <span class="fw-bold text-dark" style="font-size:14px;">₹<?php echo number_format($price, 0, '.', ','); ?></span>
                                    <?php if ($oldPrice > 0 && $oldPrice > $price): ?>
                                        <span class="text-muted text-decoration-line-through small" style="font-size: 11px;">₹<?php echo number_format($oldPrice, 0, '.', ','); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <form action="<?php echo $cartActionUrl; ?>" method="POST" class="m-0">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="size" value="32">
                                
                                <?php if ($isOutOfStock): ?>
                                    <button type="button" class="btn btn-secondary w-100 py-1.5 text-uppercase fw-bold disabled" style="font-size: 10px; border-radius: 8px;">
                                        <i class="ri-close-circle-line me-1"></i> Sold Out
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="add_to_cart" class="btn btn-dark w-100 py-1.5 fw-bold shadow-none" style="background-color: #1a202c; border:none; font-size: 11px; border-radius: 8px;">
                                        <i class="ri-shopping-bag-line me-1"></i> Add to Cart
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>