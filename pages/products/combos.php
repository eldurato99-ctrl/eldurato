<?php
// pages/products/combos.php
require_once __DIR__ . '/../../config/database.php';

if (!function_exists('url')) {
    function url($path) {
        return '/belt/' . ltrim($path, '/');
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM all_products_list WHERE name LIKE '%combo%' OR name LIKE '%set%' ORDER BY id DESC");
    $comboProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $comboProducts = [];
}

$cartActionUrl = url('pages/products/cart.php');

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 35px; }
    .card-hover:hover { transform: translateY(-3px); transition: transform 0.2s ease-in-out; }
</style>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 border-danger border-2 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="ri-gift-fill text-danger fs-2"></i>
            <div>
                <h4 class="fw-bold m-0 text-dark">Premium Gift Combos</h4>
                <small class="text-muted text-uppercase tracking-wider" style="font-size: 11px;">Perfect gifting options for every occasion</small>
            </div>
        </div>
        <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill shadow-sm" style="font-size: 11px; background: linear-gradient(45deg, #ff416c, #ff4b2b) !important;">
            <i class="ri-sparkles-line"></i> BEST SAVINGS
        </span>
    </div>
    
    <div class="row g-3">
        <?php if (empty($comboProducts)): ?>
            <div class="col-12 text-center py-5 bg-light rounded-3 border border-dashed">
                <img src="<?php echo SITE_URL; ?>/assets/images/gift.gif" class="img-fluid mb-2" style="max-width: 150px;">
                <p class="text-muted fw-semibold">No gift combos found. Check back soon!</p>
            </div>
        <?php else: ?>
            <?php foreach ($comboProducts as $product): ?>
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
                $brand = !empty($product['brand']) ? trim($product['brand']) : 'Luxury Pack';
                ?>
                
                <div class="col-6 col-md-4 col-lg-3 d-flex">
                    <div class="card card-hover w-100 border-0 shadow-sm rounded-3 overflow-hidden position-relative d-flex flex-column justify-content-between bg-white">
                        
                        <div>
                            <div class="position-relative ratio ratio-1x1 bg-light">
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2" style="z-index: 2; font-size: 9px; font-weight: 700;">COMBO PACK</span>
                                
                                <a href="<?php echo $detailsUrl; ?>">
                                    <img class="w-100 h-100 object-fit-cover" src="<?php echo $firstImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </a>
                                
                                <div class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
                                    <button type="button" class="btn btn-white btn-sm rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0 wishlist-btn" data-product-id="<?php echo $product['id']; ?>" style="width: 32px; height: 32px; background: white;">
                                        <i class="ri-heart-fill text-danger fs-6"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 9px; letter-spacing: 0.5px;"><?php echo htmlspecialchars($brand); ?></span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded px-1" style="font-size: 9px;"><i class="ri-star-fill"></i> 4.5</span>
                                </div>

                                <h6 class="text-dark fw-semibold text-truncate-2 mb-2" style="font-size: 13px; line-height: 1.4;">
                                    <a href="<?php echo $detailsUrl; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($product['name']); ?></a>
                                </h6>
                                
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-bold text-dark fs-5">₹<?php echo number_format($price, 0, '.', ','); ?></span>
                                    <?php if ($oldPrice > 0): ?>
                                        <span class="text-muted text-decoration-line-through small" style="font-size: 11px;">₹<?php echo number_format($oldPrice, 0, '.', ','); ?></span>
                                        <span class="text-danger fw-bold" style="font-size: 11px;"><?php echo $discount; ?>% OFF</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-3 pb-3">
                            <form action="<?php echo $cartActionUrl; ?>" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="size" value="32">
                                <button type="submit" name="add_to_cart" class="btn fw-bold text-white w-100 d-flex align-items-center justify-content-center gap-2 py-2" style="background-color: #06003f; font-size: 12px; border-radius: 6px;">
                                    <i class="ri-shopping-bag-line"></i> Add to Cart
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>