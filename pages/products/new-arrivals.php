<?php
// pages/products/new-arrivals.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

if (!function_exists('url')) {
    function url($path) {
        return '/' . ltrim($path, '/');
    }
}

// Logged In User details aur uski wishlist load karna sync rakhne ke liye
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

try {
    $stmt = $pdo->query("SELECT * FROM all_products_list ORDER BY id DESC LIMIT 8");
    $newArrivals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $newArrivals = [];
}

$cartActionUrl = url('pages/products/cart.php');
// Products page wale smooth standard fetch framework se link karne ke liye common endpoint
$wishlistActionUrl = '/pages/products/wishlist.php'; 

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    
    /* Premium Exclusive Card Design */
    .exclusive-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px !important;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #ffffff;
    }
    .exclusive-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08) !important;
        border-color: #cbd5e1;
    }
    
    /* Image Section Animations */
    .img-container {
        position: relative;
        background: #f8fafc;
        overflow: hidden;
        aspect-ratio: 4/3;
    }
    .exclusive-card:hover .product-img {
        transform: scale(1.06);
    }
    .product-img {
        transition: transform 0.5s ease;
        object-fit: cover;
    }

    /* Wishlist Button Core Styling */
    .premium-wishlist-btn {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(4px);
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease;
        z-index: 10;
    }
    .premium-wishlist-btn:hover {
        transform: scale(1.1);
        background: #ffffff;
    }

    /* Custom Tag */
    .new-tag {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 5;
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        color: white;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 4px 10px;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
    }
</style>

<div class="container-fluid my-4">
    <div class="p-4 rounded-4 text-white d-flex align-items-center justify-content-between shadow-sm" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div>
            <span class="badge bg-danger mb-2 px-3 py-1.5 rounded-pill uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">WEEKLY DROPS</span>
            <h2 class="fw-bold m-0 tracking-tight">The Fresh Arrival Showcase</h2>
            <p class="text-muted mb-0 small d-none d-md-block mt-1">Explore our latest genuine leather masterworks freshly updated in our inventory.</p>
        </div>
        <div class="d-none d-sm-block">
            <i class="ri-sparkling-2-fill text-warning" style="font-size: 3rem; opacity: 0.8;"></i>
        </div>
    </div>
</div>

<div class="container-fluid my-4">
    <div class="row g-3 g-md-4">
        <?php if (empty($newArrivals)): ?>
            <div class="col-12 text-center py-5 bg-white rounded-3 border">
                <i class="ri-inbox-line text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mb-0 mt-2">No new products available at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($newArrivals as $product): ?>
                <?php
                $price = (int)$product['price'];
                $oldPrice = isset($product['old_price']) ? (int)$product['old_price'] : 0;
                $discount = ($oldPrice > 0 && $oldPrice > $price) ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
                
                $imagesArray = !empty($product['images']) ? json_decode($product['images'], true) : [];
                $firstImage = 'https://via.placeholder.com/400x300?text=No+Image';
                if (!empty($imagesArray) && isset($imagesArray[0])) {
                    $firstImage = is_array($imagesArray[0]) ? ($imagesArray[0]['url'] ?? $firstImage) : $imagesArray[0];
                }
                
                $detailsUrl = url('pages/products/product-details.php?id=' . $product['id']);
                $brand = isset($product['brand']) ? trim($product['brand']) : 'Premium Collection';
                $isWishlisted = in_array((int)$product['id'], $userWishlistItems);
                
                // 🚫 Stock Status Core Validation Logic
                $stockStatus = isset($product['stock_status']) ? trim($product['stock_status']) : 'available';
                $isOutOfStock = ($stockStatus === 'out_of_stock');
                ?>
                
                <div class="col-6 col-md-4 col-lg-3 d-flex">
                    <div class="card w-100 exclusive-card shadow-none <?php echo $isOutOfStock ? 'opacity-75' : ''; ?>">
                        
                        <div class="img-container text-center">
                            <span class="new-tag">NEW LAUNCH</span>
                            
                            <a href="<?php echo $detailsUrl; ?>" class="d-block w-100 h-100">
                                <img class="w-100 h-100 product-img" src="<?php echo $firstImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            
                            <?php if ($isOutOfStock): ?>
                                <div class="position-absolute top-0 start-0 m-2 bg-danger text-white px-2 py-1 rounded fw-bold text-uppercase shadow-sm" style="font-size: 9px; z-index:6; margin-top: 38px !important;">
                                    <i class="ri-error-warning-line me-1"></i>Stock Not Available
                                </div>
                            <?php endif; ?>
                            
                            <div class="position-absolute top-0 end-0 m-2">
                                <button type="button" 
                                        class="btn premium-wishlist-btn wishlist-btn shadow-sm" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                    <i class="<?php echo $isWishlisted ? 'ri-heart-fill text-danger' : 'ri-heart-line text-dark'; ?> fw-bold" style="font-size: 16px;"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body p-3 d-flex flex-column justify-content-between bg-white">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-primary text-uppercase fw-bold tracking-wider" style="font-size: 10px;"><?php echo htmlspecialchars($brand); ?></span>
                                    <?php if ($discount > 0 && !$isOutOfStock): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold" style="font-size: 10px;">%<?php echo $discount; ?> OFF</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h5 class="card-title text-dark mb-2 text-truncate-2 fw-semibold" style="font-size: 14px; line-height: 1.4; height: 40px;">
                                    <a href="<?php echo $detailsUrl; ?>" class="text-decoration-none text-dark hover-link"><?php echo htmlspecialchars($product['name']); ?></a>
                                </h5>
                                
                                <div class="d-flex align-items-baseline gap-2 mt-2">
                                    <span class="fw-bold text-dark" style="font-size: 18px;">₹<?php echo number_format($price, 0, '.', ','); ?></span>
                                    <?php if ($oldPrice > 0): ?>
                                        <span class="text-muted text-decoration-line-through small">₹<?php echo number_format($oldPrice, 0, '.', ','); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <form action="<?php echo $cartActionUrl; ?>" method="POST" class="mt-3">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="size" value="32">
                                
                                <?php if ($isOutOfStock): ?>
                                    <button type="button" class="btn btn-secondary w-100 py-2 text-uppercase fw-bold disabled" style="font-size: 12px; border-radius: 8px;">
                                        <i class="ri-close-circle-line me-1"></i> Sold Out
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="add_to_cart" class="btn btn-dark w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-none" style="background-color: #0f172a; border: none; font-size: 12px; border-radius: 8px; transition: background 0.2s;">
                                        <i class="ri-shopping-bag-3-line" style="font-size: 14px;"></i> Add To Cart
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

<script>
document.querySelectorAll('.wishlist-btn').forEach(button => {
    button.addEventListener('click', async function(e) {
        e.preventDefault();
        e.stopPropagation(); 
        
        const productId = parseInt(this.getAttribute('data-product-id'));
        const heartIcon = this.querySelector('i');
        
        // Products.php block ke logic se direct alignment
        const formData = new FormData();
        formData.append('product_id', productId);

        try {
            const response = await fetch('<?php echo $wishlistActionUrl; ?>', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // UI smooth toggle update handler
                if (data.action === 'added') {
                    heartIcon.className = 'ri-heart-fill text-danger fw-bold';
                } else {
                    heartIcon.className = 'ri-heart-line text-dark fw-bold';
                }
                
                // Realtime Header Navigation count sync badge update
                const wishlistBadges = document.querySelectorAll('#wishlist-count, #mobile-wishlist-count');
                wishlistBadges.forEach(badge => {
                    if (badge) badge.innerText = data.count;
                });
                
            } else if (data.message.toLowerCase().includes('login') || data.message === 'Please login first') {
                if (confirm('Please login to add items to wishlist')) {
                    window.location.href = '/pages/auth/login.php';
                }
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Wishlist AJAX Error:', error);
            alert('Network error ya server response template update fail!');
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
