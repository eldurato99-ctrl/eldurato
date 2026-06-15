<?php
// pages/products/wishlist.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit;
    }
    
    $user_id = (int)$_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product id sent']);
        exit;
    }
    
    try {
        $check = $pdo->prepare("SELECT `id` FROM `wishlist` WHERE `user_id` = ? AND `product_id` = ?");
        $check->execute([$user_id, $product_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $delete = $pdo->prepare("DELETE FROM `wishlist` WHERE `user_id` = ? AND `product_id` = ?");
            $delete->execute([$user_id, $product_id]);
            $action = 'removed';
        } else {
            $insert = $pdo->prepare("INSERT INTO `wishlist` (`user_id`, `product_id`) VALUES (?, ?)");
            $insert->execute([$user_id, $product_id]);
            $action = 'added';
        }
        
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `wishlist` WHERE `user_id` = ?");
        $countStmt->execute([$user_id]);
        $count = (int)$countStmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'action' => $action,
            'count' => $count
        ]);
        exit;
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

include '../../includes/header.php';
include '../../includes/navbar.php';

try {
    $query = "
        SELECT 
            w.id as wishlist_entry_id,
            p.id as product_core_id,
            p.name,
            p.brand,
            p.price,
            p.old_price,
            p.images,
            p.stock
        FROM wishlist w
        INNER JOIN all_products_list p ON w.product_id = p.id
        WHERE w.user_id = ?
        ORDER BY w.id DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $wishlistItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ELDURATO - My Wishlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        /* Mobile App Card Behavior (Haptic press feedback) */
        .app-card {
            border: none !important;
            border-radius: 16px !important;
            transition: transform 0.2s ease, opacity 0.25s ease;
        }
        .app-card:active {
            transform: scale(0.97); 
        }
        /* Title text max 2 lines me wrap karne ke liye */
        .app-title-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 38px;
            font-size: 13px;
        }
        /* Navigation Highlight active color */
        .app-nav-link.active {
            color: #dc3545 !important;
        }
    </style>
</head>
<body>

<!-- Dynamic App Bar Header (Sticky top using Bootstrap) -->
<div class="sticky-top bg-white bg-opacity-95 border-bottom py-3 px-3 mb-3 shadow-sm">
    <div class="container d-flex align-items-center justify-content-between p-0">
        <div class="d-flex align-items-center">
            <a href="javascript:history.back()" class="text-dark me-3 d-md-none text-decoration-none">
                <i class="ri-arrow-left-line fs-4"></i>
            </a>
            <div>
                <h5 class="fw-bold text-dark mb-0">Wishlist</h5>
                <p class="text-muted small mb-0 d-none d-md-block">Manage your curated products</p>
            </div>
        </div>
        <div>
            <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold" id="wishlistCount"><?= count($wishlistItems) ?> Items</span>
        </div>
    </div>
</div>

<div class="container">
    <div class="row g-2 g-md-3" id="wishlistGrid">
        <?php if (!empty($wishlistItems)): ?>
            <?php foreach ($wishlistItems as $item): 
                $rawImages = $item['images'] ?: '';
                $imagesArray = json_decode($rawImages, true);
                $displayImage = (is_array($imagesArray) && isset($imagesArray[0]['url'])) ? $imagesArray[0]['url'] : (is_array($imagesArray) && !empty($imagesArray) ? $imagesArray[0] : ($rawImages ?: 'https://via.placeholder.com/200?text=No+Image'));
                
                $stockCount = (int)($item['stock'] ?? 0);
            ?>
                <!-- App Grid Layout using Bootstrap Utilities -->
                <div class="col-6 col-md-4 col-lg-3" id="wish-row-<?= $item['product_core_id'] ?>">
                    <div class="card app-card shadow-sm p-2 h-100 d-flex flex-column justify-content-between position-relative">
                        
                        <!-- Image Container Box -->
                        <div class="position-relative bg-light rounded-3 overflow-hidden d-flex align-items-center justify-content-center p-2" style="height: 150px;">
                            
                            <!-- Floating Delete Icon -->
                            <button type="button" class="btn btn-sm btn-white position-absolute top-0 end-0 m-2 rounded-circle shadow-sm border-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; z-index: 5;" onclick="removeFromWishlist(<?= $item['product_core_id'] ?>)">
                                <i class="ri-heart-3-fill text-danger fs-5"></i>
                            </button>
                            
                            <img src="<?= $displayImage ?>" class="img-fluid h-100 object-fit-contain" alt="product">
                            
                            <!-- Stock Badges -->
                            <?php if ($stockCount <= 0): ?>
                                <span class="position-absolute start-0 bottom-0 m-2 badge bg-danger text-white fw-bold" style="font-size: 9px;">SOLD OUT</span>
                            <?php elseif ($stockCount < 5): ?>
                                <span class="position-absolute start-0 bottom-0 m-2 badge bg-warning text-dark fw-bold" style="font-size: 9px;">ONLY <?= $stockCount ?> LEFT</span>
                            <?php endif; ?>
                        </div>

                        <!-- Details Section -->
                        <div class="px-1 py-2 d-flex flex-column flex-grow-1">
                            <span class="text-uppercase text-muted fw-bold tracking-wider" style="font-size: 10px;"><?= htmlspecialchars($item['brand'] ?: 'ELDURATO') ?></span>
                            <h6 class="fw-bold text-dark app-title-clamp mt-1 mb-2" title="<?= htmlspecialchars($item['name']) ?>">
                                <?= htmlspecialchars($item['name']) ?>
                            </h6>

                            <div class="d-flex align-items-center gap-2 mt-auto mb-2">
                                <span class="fw-bold text-dark fs-6">₹<?= number_format($item['price']) ?></span>
                                <?php if (($item['old_price'] ?? 0) > $item['price']): ?>
                                    <span class="text-muted text-decoration-line-through small" style="font-size: 11px;">₹<?= number_format($item['old_price']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="w-100">
                                <?php if ($stockCount > 0): ?>
                                    <a href="/belt/pages/products/product-details.php?id=<?= $item['product_core_id'] ?>" class="btn btn-sm btn-dark w-100 py-2 rounded-3 fw-bold fs-7">
                                        <i class="ri-shopping-bag-line me-1"></i> View Details
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-light w-100 py-2 rounded-3 text-muted border fw-bold" style="font-size: 12px;" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State Layout -->
            <div class="col-12 text-center py-5" id="emptyStateView">
                <div class="py-5 mx-auto" style="max-width: 350px;">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4 shadow-sm" style="width: 90px; height: 90px;">
                        <i class="ri-heart-line text-muted display-5"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Your Wishlist is Empty</h5>
                    <p class="text-muted small px-3 mb-4">Explore items you love and save them here for easy checking anytime.</p>
                    <a href="/belt/pages/products/products.php" class="btn btn-dark w-100 py-2.5 rounded-3 fw-bold">Explore Products</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Native App Mobile Bottom Navigation Bar (Bootstrap Controlled) -->
<div class="fixed-bottom bg-white border-top py-2 d-flex justify-content-around d-md-none shadow-lg" style="z-index: 1040;">
    <a href="/belt/index.php" class="text-center text-decoration-none text-secondary small app-nav-link" style="font-size: 11px;">
        <i class="ri-home-5-line fs-4 d-block mx-auto text-muted"></i>Home
    </a>
    <a href="/belt/pages/products/products.php" class="text-center text-decoration-none text-secondary small app-nav-link" style="font-size: 11px;">
        <i class="ri-search-2-line fs-4 d-block mx-auto text-muted"></i>Shop
    </a>
    <a href="#" class="text-center text-decoration-none small app-nav-link active" style="font-size: 11px;">
        <i class="ri-heart-fill fs-4 d-block mx-auto"></i>Wishlist
    </a>
    <a href="/belt/pages/customer/dashboard.php" class="text-center text-decoration-none text-secondary small app-nav-link" style="font-size: 11px;">
        <i class="ri-user-3-line fs-4 d-block mx-auto text-muted"></i>Profile
    </a>
</div>

<script>
function removeFromWishlist(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success && data.action === 'removed') {
            const element = document.getElementById('wish-row-' + productId);
            if(element) {
                // Card remove hone par smooth transition scale scale down logic
                element.style.opacity = '0';
                element.style.transform = 'scale(0.85)';
                
                setTimeout(() => {
                    element.remove();
                    
                    // Live counters layout sync updating
                    const countBadge = document.getElementById('wishlistCount');
                    if(countBadge) {
                        countBadge.innerText = data.count + ' Items';
                    }
                    
                    const badges = document.querySelectorAll('#wishlist-count, #mobile-wishlist-count');
                    badges.forEach(badge => {
                        if(badge) badge.innerText = data.count;
                    });
                    
                    if(data.count === 0) {
                        renderEmptyAppState();
                    }
                }, 200);
            }
        }
    })
    .catch(error => console.error('Error handling request:', error));
}

function renderEmptyAppState() {
    const grid = document.getElementById('wishlistGrid');
    grid.innerHTML = `
        <div class="col-12 text-center py-5" id="emptyStateView">
            <div class="py-5 mx-auto" style="max-width: 350px;">
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4 shadow-sm" style="width: 90px; height: 90px;">
                    <i class="ri-heart-line text-muted display-5"></i>
                </div>
                <h5 class="fw-bold text-dark">Your Wishlist is Empty</h5>
                <p class="text-muted small px-3 mb-4">Explore items you love and save them here for easy checking anytime.</p>
                <a href="/belt/pages/products/products.php" class="btn btn-dark w-100 py-2.5 rounded-3 fw-bold">Explore Products</a>
            </div>
        </div>
    `;
}
</script>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>