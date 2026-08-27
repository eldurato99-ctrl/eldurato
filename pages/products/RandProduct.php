<?php
// pages/products/RandProduct.php - SEO FIXED VERSION
require_once __DIR__ . '/../../config/database.php';

// Dynamic URL helper
if (!function_exists('url')) {
    function url($path) {
        $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '/eldurato';
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM all_products_list ORDER BY RAND() LIMIT 6");
    $randProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $randProducts = [];
}

$cartActionUrl = url('pages/products/cart.php');
$allProductsUrl = url('pages/products/products.php');
$loginUrl = url('pages/auth/login.php');

// ✅ Get Wishlist for logged-in user
$loggedInUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$userWishlistItems = [];
if ($loggedInUserId > 0) {
    try {
        $wlStmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
        $wlStmt->execute([$loggedInUserId]);
        $userWishlistItems = $wlStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $userWishlistItems = [];
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 34px; }
    .hover-shadow:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.06) !important; transition: all 0.2s ease; }
</style>

<!-- ✅ H2 TAG for Section Heading -->
<section class="container-fluid my-3 bg-white p-1 rounded-3 shadow-sm" aria-label="Suggested Products For You">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
        <div class="d-flex align-items-center gap-2">
            <h2 class="fw-bold text-dark m-0" style="font-size: 1.1rem;">Suggested For You</h2>
            <span class="badge bg-warning text-dark small" style="font-size: 8px;">Handpicked</span>
        </div>
        <a href="<?php echo $allProductsUrl; ?>" class="btn btn-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center border" style="width: 32px; height: 32px;" aria-label="View all products">
            <i class="ri-arrow-right-s-line fs-5 text-dark"></i>
        </a>
    </div>

    <div class="row g-2 g-md-3">
        <?php if (empty($randProducts)): ?>
            <div class="col-12 text-center py-5 bg-white rounded-3 border">
                <p class="text-muted small mb-0">No suggested items found.</p>
            </div>
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
                $productName = htmlspecialchars($product['name']);
                
                $stock = isset($product['stock']) ? (int)$product['stock'] : null;
                $stockStatus = isset($product['stock_status']) ? trim($product['stock_status']) : '';
                $isOutOfStock = ($stock !== null && $stock <= 0) || ($stockStatus === 'out_of_stock');

                // ✅ Wishlist status
                $isWishlisted = in_array((int)$product['id'], $userWishlistItems);
                ?>

                <!-- ✅ Each Product with Schema -->
                <div class="col-6 col-md-4 col-lg-2 d-flex" itemscope itemtype="https://schema.org/Product">
                    <meta itemprop="name" content="<?php echo $productName; ?>">
                    <meta itemprop="brand" content="<?php echo htmlspecialchars($brand); ?>">
                    <meta itemprop="image" content="<?php echo $firstImage; ?>">
                    <meta itemprop="price" content="<?php echo $price; ?>">
                    <meta itemprop="priceCurrency" content="INR">
                    <meta itemprop="availability" content="<?php echo $isOutOfStock ? 'OutOfStock' : 'InStock'; ?>">
                    
                    <div class="card w-100 border rounded-3 position-relative shadow-sm hover-shadow p-1 <?php echo $isOutOfStock ? 'opacity-75' : ''; ?>">
                        
                        <div class="position-relative text-center bg-light rounded-2 overflow-hidden" style="aspect-ratio: 1/1;">
                            <a href="<?php echo $detailsUrl; ?>" class="w-100 h-100 d-flex align-items-center justify-content-center" aria-label="View <?php echo $productName; ?> details">
                                <img class="w-100 h-100 object-fit-contain p-2" 
                                     src="<?php echo $firstImage; ?>" 
                                     alt="Buy <?php echo $productName; ?> premium leather belt online at Eldurato - Best quality" 
                                     loading="lazy"
                                     width="300" 
                                     height="300">
                            </a>
                            
                            <?php if ($isOutOfStock): ?>
                                <div class="position-absolute top-0 start-0 m-2 bg-danger text-white px-2 py-1 rounded fw-bold text-uppercase shadow-sm" style="font-size: 9px; z-index:3;">
                                    <i class="ri-error-warning-line me-1"></i>Sold Out
                                </div>
                            <?php elseif ($discount > 0): ?>
                                <span class="badge bg-danger position-absolute bottom-0 start-0 m-2" style="font-size: 9px; font-weight: 700;"><?php echo $discount; ?>% OFF</span>
                            <?php endif; ?>
                            
                            <div class="position-absolute top-0 end-0 m-2">
                                <button type="button" class="btn btn-white btn-sm rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center wishlist-btn" data-product-id="<?php echo $product['id']; ?>" style="width:30px; height:30px; background: rgba(255,255,255,0.95);" aria-label="Add to wishlist">
                                    <i class="<?php echo $isWishlisted ? 'ri-heart-fill text-danger' : 'ri-heart-line text-secondary'; ?>" style="font-size: 14px;"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                            <div class="mb-2">
                                <div class="text-muted text-uppercase fw-bold" style="font-size: 9px; letter-spacing:0.3px;"><?php echo htmlspecialchars($brand); ?></div>
                                <h3 class="text-dark mt-0.5 mb-1 text-truncate-2 fw-medium" style="font-size: 12.5px; line-height: 1.3;"><?php echo $productName; ?></h3>
                                
                                <div class="d-flex align-items-baseline gap-1 flex-wrap mt-1">
                                    <span class="fw-bold text-dark" style="font-size:14px;" itemprop="price">₹<?php echo number_format($price, 0, '.', ','); ?></span>
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
</section>

<!-- ✅ Wishlist AJAX Script -->
<script>
document.addEventListener('click', async function(e) {
    const btn = e.target.closest('.wishlist-btn');
    if (!btn) return;

    const productId = parseInt(btn.dataset.productId);
    const formData = new FormData();
    formData.append('product_id', productId);

    try {
        const response = await fetch('/pages/products/wishlist.php', { 
            method: 'POST', 
            body: formData 
        });
        
        const data = await response.json();
        
        if (data.success) {
            const icon = btn.querySelector('i');
            if (data.action === 'added') {
                icon.className = 'ri-heart-fill text-danger';
            } else {
                icon.className = 'ri-heart-line text-secondary';
            }
            
            // Update wishlist count badges
            const wishlistBadges = document.querySelectorAll('#wishlist-count, #mobile-wishlist-count');
            wishlistBadges.forEach(badge => {
                if (badge) badge.innerText = data.count;
            });
            
        } else if (data.message && data.message.toLowerCase().includes('login')) {
            if (confirm('Please login to add items to wishlist')) {
                window.location.href = '<?php echo url("pages/auth/login.php"); ?>';
            }
        } else {
            // Silent fail for guest users - just redirect to login
            if (data.message && data.message.toLowerCase().includes('login')) {
                window.location.href = '<?php echo url("pages/auth/login.php"); ?>';
            }
        }
    } catch(error) { 
        console.error('Wishlist AJAX Error:', error); 
    }
});
</script>
