<?php
// ✅ SESSION START
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ INCLUDE CONFIG
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

// ✅ DYNAMIC SEO DATA
$page_title = "Buy Premium Leather Belts Online - Eldurato | Best Quality Belts for Men & Women";
$page_description = "Shop premium genuine leather belts at Eldurato. Find formal, casual, and luxury belts for men & women. Free shipping, COD, and 7-day replacement available.";
$page_keywords = "leather belts, men belts, women belts, formal belts, casual belts, premium leather, buy belts online India";
$canonical_url = "https://eldurato.com/pages/products/products.php";
$og_image = "https://eldurato.com/assets/images/logo.png";

// ✅ CHECK IF INCLUDED IN HERO
$isIncluded = defined('INCLUDED_IN_HERO') || basename($_SERVER['SCRIPT_FILENAME']) !== 'products.php';

if (!$isIncluded) {
    include __DIR__ . '/../../includes/header.php';
    include __DIR__ . '/../../includes/navbar.php';
}

// ✅ URL FUNCTION
if (!function_exists('url')) {
    function url($path) {
        return '/' . ltrim($path, '/');
    }
}

// ✅ GET USER WISHLIST
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

// ✅ CHECK TABLE COLUMNS
try {
    $colQuery = $pdo->query("SHOW COLUMNS FROM all_products_list");
    $columns = $colQuery->fetchAll(PDO::FETCH_COLUMN);
    $hasCategory = in_array('category', $columns);
    $hasBrand = in_array('brand', $columns);
    $hasRating = in_array('rating', $columns);
    $hasColor = in_array('color', $columns);
    $hasMaterial = in_array('material', $columns);
} catch (PDOException $e) {
    $hasCategory = $hasBrand = $hasRating = $hasColor = $hasMaterial = false;
}

// ✅ GET FILTERS DATA
$allCategories = $hasCategory ? $pdo->query("SELECT DISTINCT category FROM all_products_list WHERE category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN) : [];
$allBrands = $hasBrand ? $pdo->query("SELECT DISTINCT brand FROM all_products_list WHERE brand IS NOT NULL AND brand != '' ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN) : [];
$allColors = $hasColor ? $pdo->query("SELECT DISTINCT color FROM all_products_list WHERE color IS NOT NULL AND color != '' ORDER BY color")->fetchAll(PDO::FETCH_COLUMN) : [];
$allMaterials = $hasMaterial ? $pdo->query("SELECT DISTINCT material FROM all_products_list WHERE material IS NOT NULL AND material != '' ORDER BY material")->fetchAll(PDO::FETCH_COLUMN) : [];

// ✅ GET PRODUCTS
$stmt = $pdo->query("SELECT * FROM all_products_list ORDER BY id DESC");
$dbProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ BUILD PRODUCTS ARRAY
$jsProducts = [];
foreach ($dbProducts as $product) {
    $price = (int)$product['price'];
    $oldPrice = isset($product['old_price']) ? (int)$product['old_price'] : 0;
    
    $imagesArray = !empty($product['images']) ? json_decode($product['images'], true) : [];
    $firstImage = 'https://via.placeholder.com/300x300?text=No+Image';
    if (!empty($imagesArray) && isset($imagesArray[0])) {
        $firstImage = is_array($imagesArray[0]) ? ($imagesArray[0]['url'] ?? $firstImage) : $imagesArray[0];
    }

    $jsProducts[] = [
        'id' => (int)$product['id'],
        'name' => trim($product['name']),
        'brand' => isset($product['brand']) ? trim($product['brand']) : 'Premium Collection',
        'category' => isset($product['category']) ? trim($product['category']) : '',
        'color' => isset($product['color']) ? trim($product['color']) : '',
        'material' => isset($product['material']) ? trim($product['material']) : '',
        'price' => $price,
        'discount' => ($oldPrice > $price) ? round((($oldPrice - $price) / $oldPrice) * 100) : 0,
        'old_price' => $oldPrice,
        'rating' => isset($product['rating']) ? (float)$product['rating'] : 0,
        'image' => $firstImage,
        'is_wishlisted' => in_array((int)$product['id'], $userWishlistItems),
        'details_url' => url('pages/products/product-details.php?id=' . $product['id']),
        'stock' => isset($product['stock']) ? (int)$product['stock'] : null,
        'stock_status' => isset($product['stock_status']) ? trim($product['stock_status']) : 'available'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ✅ TITLE - DYNAMIC -->
    <title><?php echo $page_title; ?></title>
    
    <!-- ✅ META DESCRIPTION - CRITICAL FIX -->
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    <meta name="robots" content="index, follow">
    
    <!-- ✅ CANONICAL TAG - CRITICAL FIX -->
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    
    <!-- ✅ OPEN GRAPH TAGS -->
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_description; ?>">
    <meta property="og:image" content="<?php echo $og_image; ?>">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Eldurato">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $page_title; ?>">
    <meta name="twitter:description" content="<?php echo $page_description; ?>">
    <meta name="twitter:image" content="<?php echo $og_image; ?>">
    
    <!-- ✅ COLLECTION PAGE SCHEMA -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "CollectionPage",
      "name": "Premium Leather Belts Collection",
      "description": "<?php echo $page_description; ?>",
      "url": "<?php echo $canonical_url; ?>",
      "brand": {
        "@type": "Brand",
        "name": "Eldurato"
      }
    }
    </script>
    
    <!-- ✅ BREADCRUMB SCHEMA -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "https://eldurato.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Products",
          "item": "https://eldurato.com/pages/products/products.php"
        }
      ]
    }
    </script>

    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .product-card { 
            background: #ffffff; 
            border: 1px solid #e2e8f0 !important; 
            border-radius: 0px !important; 
            overflow: hidden; 
            box-shadow: none;
            transition: transform 0.2s ease; 
        }
        .product-card:active { transform: scale(0.98); }
        
        .text-truncate-2 { 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
            overflow: hidden; 
            height: 34px; 
        }
        
        .product-img-wrapper { 
            position: relative; 
            background-color: #ffffff; 
            border-radius: 0px !important;
            margin: 0px;
            aspect-ratio: 1/1; 
            overflow: hidden; 
        }
        .discount-badge { 
            position: absolute; 
            bottom: 6px; 
            left: 6px; 
            background: #dc3545; 
            color: white; 
            padding: 3px 6px; 
            border-radius: 0px !important; 
            font-size: 9px; 
            font-weight: 700; 
            z-index: 2; 
        }
        .wishlist-btn { 
            width: 32px; 
            height: 32px; 
            border-radius: 0px !important; 
            background: rgba(255, 255, 255, 0.9); 
            border: 1px solid #e2e8f0; 
        }
        .add-to-cart-btn { 
            background: #1a202c; 
            border: none; 
            color: white; 
            font-weight: 600; 
            font-size: 11px; 
            padding: 8px; 
            border-radius: 0px !important; 
        }
        
        .filter-card { border-radius: 0px !important; border: 1px solid #e2e8f0; background: white; box-shadow: none; }
        .filter-section-title { font-size: 10px; font-weight: 700; color: #a0aec0; letter-spacing: 0.8px; margin-bottom: 12px; text-transform: uppercase; }
        .custom-checkbox { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer; }
        .custom-checkbox input { width: 17px; height: 17px; accent-color: #1a202c; cursor: pointer; border-radius: 0px !important; }
        .custom-checkbox span { font-size: 13px; color: #4a5568; }
        .price-input { border-radius: 0px !important; border: 1px solid #e2e8f0; padding: 6px 12px; font-size: 13px; background: #f8fafc; }
        .price-input:focus { border-color: #1a202c; box-shadow: none; background: #fff; }
        
        .rating-option { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer; }
        .rating-option input { width: 17px; height: 17px; accent-color: #1a202c; }
        .rating-option span { font-size: 13px; color: #4a5568; }
        .clear-btn { background: #fff5f5; border: 1px solid #fed7d7; color: #e53e3e; font-weight: 600; font-size: 12px; padding: 9px; border-radius: 0px !important; }
        
        .skeleton-card { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: loading 1.5s infinite; border-radius: 0px !important; }
        @keyframes loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        
        .pagination .page-link { color: #4a5568; border-radius: 0px !important; margin: 0 3px; border: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; box-shadow: none !important; }
        .pagination .page-item.active .page-link { background-color: #1a202c; border-color: #1a202c; color: #fff; }
    </style>
</head>
<body class="bg-light">

<!-- ✅ H1 TAG - CRITICAL FIX -->
<h1 class="visually-hidden">Buy Premium Leather Belts Online - Eldurato India's Best Belt Store</h1>

<!-- ✅ BREADCRUMB NAVIGATION -->
<nav aria-label="breadcrumb" class="container-fluid px-2 px-md-3 mt-2">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Products</li>
    </ol>
</nav>

<div class="<?php echo $isIncluded ? 'p-0' : 'container-fluid'; ?>">
    <div class="container-fluid py-2 px-2 px-md-3">
        
        <!-- ✅ H2 TAG -->
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <div>
                <h2 class="fw-bold mb-0" style="font-size: 1.25rem;">Premium Leather Belts Collection</h2>
                <small class="text-muted">Handcrafted genuine leather belts for men & women</small>
            </div>
            <button class="btn btn-sm btn-dark rounded-0 px-3 py-1.5 fw-bold d-md-none" style="font-size: 12px;" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                <i class="ri-filter-3-line"></i> Filter
            </button>
        </div>

        <div class="row g-2 g-md-3">
            <!-- FILTER SIDEBAR -->
            <div class="col-md-4 col-lg-3 d-none d-md-block">
                <div class="filter-card p-3 sticky-top" style="top: 20px; z-index: 100;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <i class="ri-filter-3-line text-dark fs-5"></i>
                        <span class="fw-bold text-dark" style="font-size: 15px;">Filters</span>
                    </div>
                    <div id="desktopFilterContainer"></div>
                </div>
            </div>

            <!-- PRODUCT GRID -->
            <div class="col-md-8 col-lg-9">
                <div class="row g-2 g-md-3" id="productGrid">
                    <?php for($i = 0; $i < 8; $i++): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="skeleton-card" style="height: 280px;"></div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- ✅ PAGINATION WITH REL="prev/next" -->
                <div class="d-flex justify-content-center mt-4 mb-2">
                    <nav aria-label="Product navigation">
                        <ul class="pagination pagination-sm m-0" id="paginationContainer"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MOBILE FILTER OFFCANVAS -->
<div class="offcanvas offcanvas-bottom h-75 rounded-0" tabindex="-1" id="filterOffcanvas">
    <div class="offcanvas-header border-bottom py-3 px-3">
        <h6 class="offcanvas-title fw-bold text-dark" style="font-size: 15px;"><i class="ri-filter-3-line me-1"></i> Sort & Filter</h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3" id="mobileFilterContainer"></div>
</div>

<!-- FILTER FORM TEMPLATE -->
<div id="masterFormTemplate" class="d-none">
    <form id="filterForm" onsubmit="event.preventDefault();">
        <?php if ($hasCategory && !empty($allCategories)): ?>
            <div class="mb-4">
                <div class="filter-section-title">Categories</div>
                <div style="max-height: 140px; overflow-y: auto;" class="pe-1">
                    <?php foreach ($allCategories as $cat): ?>
                        <label class="custom-checkbox">
                            <input type="checkbox" class="filter-checkbox" data-type="category" value="<?php echo htmlspecialchars(trim($cat)); ?>">
                            <span><?php echo htmlspecialchars($cat); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($hasBrand && !empty($allBrands)): ?>
            <div class="mb-4">
                <div class="filter-section-title">Brands</div>
                <div style="max-height: 140px; overflow-y: auto;" class="pe-1">
                    <?php foreach ($allBrands as $brand): ?>
                        <label class="custom-checkbox">
                            <input type="checkbox" class="filter-checkbox" data-type="brand" value="<?php echo htmlspecialchars(trim($brand)); ?>">
                            <span><?php echo htmlspecialchars($brand); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($hasColor && !empty($allColors)): ?>
            <div class="mb-4">
                <div class="filter-section-title">Colors</div>
                <div style="max-height: 140px; overflow-y: auto;" class="pe-1">
                    <?php foreach ($allColors as $clr): ?>
                        <label class="custom-checkbox">
                            <input type="checkbox" class="filter-checkbox" data-type="color" value="<?php echo htmlspecialchars(trim($clr)); ?>">
                            <span><?php echo htmlspecialchars($clr); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($hasMaterial && !empty($allMaterials)): ?>
            <div class="mb-4">
                <div class="filter-section-title">Material Build</div>
                <div style="max-height: 140px; overflow-y: auto;" class="pe-1">
                    <?php foreach ($allMaterials as $mat): ?>
                        <label class="custom-checkbox">
                            <input type="checkbox" class="filter-checkbox" data-type="material" value="<?php echo htmlspecialchars(trim($mat)); ?>">
                            <span><?php echo htmlspecialchars($mat); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <div class="filter-section-title">Price Range</div>
            <div class="d-flex gap-2">
                <input type="number" class="form-control price-input w-50 shadow-none" id="minPrice" placeholder="Min ₹">
                <input type="number" class="form-control price-input w-50 shadow-none" id="maxPrice" placeholder="Max ₹">
            </div>
        </div>

        <?php if ($hasRating): ?>
            <div class="mb-4">
                <div class="filter-section-title">Customer Rating</div>
                <label class="rating-option">
                    <input type="radio" name="rating" value="4" class="rating-radio">
                    <span>4★ & above</span>
                </label>
                <label class="rating-option">
                    <input type="radio" name="rating" value="3" class="rating-radio">
                    <span>3★ & above</span>
                </label>
                <label class="rating-option">
                    <input type="radio" name="rating" value="any" class="rating-radio" checked>
                    <span>Any rating</span>
                </label>
            </div>
        <?php endif; ?>

        <button type="button" class="btn w-100 clear-btn shadow-none mt-2" id="btnClearAll">
            <i class="ri-close-circle-line me-1"></i> Clear All Filters
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const allProducts = <?php echo json_encode($jsProducts); ?>;
    const cartActionUrl = "<?php echo url('pages/products/cart.php'); ?>";
    const urlParams = new URLSearchParams(window.location.search);
    const globalSalesFilter = urlParams.get('filter') ? urlParams.get('filter').trim() : '';

    let currentFilteredProducts = []; 
    let currentPage = 1;
    const itemsPerPage = 30; 

    function formatMoney(num) { return new Intl.NumberFormat('en-IN').format(num); }

    function setupFilterFormLocation() {
        const form = document.getElementById('filterForm');
        if (!form) return;
        const targetContainer = window.innerWidth < 768 ? 'mobileFilterContainer' : 'desktopFilterContainer';
        document.getElementById(targetContainer).appendChild(form);
    }

    function renderProducts(productsList) {
        const grid = document.getElementById('productGrid');
        if (!grid) return;

        if (productsList.length === 0) {
            grid.innerHTML = `
                <div class="col-12 text-center py-5 bg-white rounded-0 shadow-sm w-100 border">
                    <i class="ri-inbox-line text-muted" style="font-size: 44px;"></i>
                    <p class="mt-2 text-muted small mb-0">No matching products discovered</p>
                </div>`;
            document.getElementById('paginationContainer').innerHTML = '';
            return;
        }

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const paginatedProducts = productsList.slice(startIndex, endIndex);

        grid.innerHTML = paginatedProducts.map(product => {
            const oldPriceHTML = product.old_price > 0 ? `
                <span class="text-muted text-decoration-line-through text-nowrap" style="font-size: 11px;">₹${formatMoney(product.old_price)}</span>` : '';

            const starsHTML = '<i class="ri-star-fill text-warning" style="font-size: 9px; margin-right:1px;"></i>'.repeat(Math.floor(product.rating)) + 
                              (product.rating % 1 >= 0.5 ? '<i class="ri-star-half-fill text-warning" style="font-size: 9px;"></i>' : '');
            
            const finalStarsView = product.rating > 0 ? `
                <div class="d-flex align-items-center gap-1 my-1 bg-light px-2 py-0.5 rounded-0" style="width: fit-content;">
                    <div class="d-flex align-items-center">${starsHTML}</div> 
                    <span style="font-size: 10px; color: #4a5568; font-weight:700;">${product.rating}</span>
                </div>` : '';

            const isOutOfStock = (product.stock !== null && product.stock <= 0) || product.stock_status === 'out_of_stock';
            
            let badgeHTML = '';
            if (isOutOfStock) {
                badgeHTML = `<div class="position-absolute top-0 start-0 m-2 bg-danger text-white px-2 py-1 rounded-0 fw-bold text-uppercase shadow-sm" style="font-size: 9px; z-index:3;"><i class="ri-error-warning-line me-1"></i>Stock Not Available</div>`;
            } else if (product.discount > 0) {
                badgeHTML = `<div class="discount-badge">${product.discount}% OFF</div>`;
            }

            // ✅ PRODUCT SCHEMA - EACH PRODUCT
            const productSchema = {
                "@context": "https://schema.org",
                "@type": "Product",
                "name": product.name,
                "brand": { "@type": "Brand", "name": product.brand },
                "image": product.image,
                "offers": {
                    "@type": "Offer",
                    "price": product.price,
                    "priceCurrency": "INR",
                    "availability": isOutOfStock ? "https://schema.org/OutOfStock" : "https://schema.org/InStock",
                    "url": product.details_url
                }
            };

            return `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card card h-100 p-1 d-flex flex-column justify-content-between rounded-0 ${isOutOfStock ? 'opacity-75' : ''}" 
                         itemscope itemtype="https://schema.org/Product">
                        <meta itemprop="name" content="${product.name}">
                        <meta itemprop="brand" content="${product.brand}">
                        <meta itemprop="image" content="${product.image}">
                        <meta itemprop="price" content="${product.price}">
                        <meta itemprop="priceCurrency" content="INR">
                        <meta itemprop="availability" content="${isOutOfStock ? 'OutOfStock' : 'InStock'}">
                        
                        <div>
                            <div class="product-img-wrapper d-flex align-items-center justify-content-center bg-white position-relative rounded-0">
                                ${badgeHTML}
                                <a href="${product.details_url}" class="w-100 h-100 d-flex align-items-center justify-content-center">
                                    <!-- ✅ FIXED: Descriptive Alt Text -->
                                    <img src="${product.image}" 
                                         class="w-100 h-100 object-fit-contain p-1 rounded-0" 
                                         alt="Buy ${product.name} premium leather belt online at Eldurato - Best quality" 
                                         loading="lazy"
                                         width="300" 
                                         height="300">
                                </a>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <button type="button" class="wishlist-btn d-flex align-items-center justify-content-center shadow-none rounded-0" data-product-id="${product.id}">
                                        <i class="${product.is_wishlisted ? 'ri-heart-fill text-danger' : 'ri-heart-line text-secondary'}" style="font-size: 15px;"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="px-2 pt-1">
                                <div style="font-size: 9px; color: #a0aec0; font-weight: 700; text-transform: uppercase; letter-spacing:0.3px;">${product.brand}</div>
                                <div class="text-truncate-2 mt-0.5 text-dark fw-medium" style="font-size: 12.5px; line-height: 1.3;" itemprop="description">${product.name}</div>
                                ${finalStarsView}
                            </div>
                        </div>
                        <div class="px-2 pb-2 mt-auto">
                            <div class="d-flex align-items-baseline gap-1 mb-2">
                                <span class="fw-bold text-dark" style="font-size: 15px;" itemprop="price">₹${formatMoney(product.price)}</span> ${oldPriceHTML}
                            </div>
                            <form action="${cartActionUrl}" method="POST" class="m-0">
                                <input type="hidden" name="product_id" value="${product.id}">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="size" value="32">
                                
                                ${isOutOfStock ? 
                                    `<button type="button" class="btn btn-secondary w-100 disabled py-2 text-uppercase fw-bold rounded-0" style="font-size:10px;"><i class="ri-close-circle-line me-1"></i> Sold Out</button>` : 
                                    `<button type="submit" name="add_to_cart" class="btn add-to-cart-btn w-100 shadow-none rounded-0"><i class="ri-shopping-bag-line me-1"></i> Add to Cart</button>`
                                }
                            </form>
                        </div>
                    </div>
                </div>`;
        }).join('');

        renderPagination(productsList.length);
    }

    function renderPagination(totalItems) {
        const container = document.getElementById('paginationContainer');
        if (!container) return;

        const totalPages = Math.ceil(totalItems / itemsPerPage);
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        
        // ✅ PREV BUTTON WITH rel="prev"
        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;" rel="${currentPage > 1 ? 'prev' : ''}">
                    <i class="ri-arrow-left-s-line"></i>
                </a>
            </li>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `
                    <li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${i}); return false;" ${i > currentPage ? 'rel="next"' : ''}>
                            ${i}
                        </a>
                    </li>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        // ✅ NEXT BUTTON WITH rel="next"
        html += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;" rel="${currentPage < totalPages ? 'next' : ''}">
                    <i class="ri-arrow-right-s-line"></i>
                </a>
            </li>`;

        container.innerHTML = html;
    }

    function changePage(page) {
        currentPage = page;
        renderProducts(currentFilteredProducts);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function applyFilters() {
        const selectedCategories = Array.from(document.querySelectorAll('.filter-checkbox[data-type="category"]:checked')).map(el => el.value.trim());
        const selectedBrands = Array.from(document.querySelectorAll('.filter-checkbox[data-type="brand"]:checked')).map(el => el.value.trim());
        
        const selectedColors = Array.from(document.querySelectorAll('.filter-checkbox[data-type="color"]:checked')).map(el => el.value.trim().toLowerCase());
        const selectedMaterials = Array.from(document.querySelectorAll('.filter-checkbox[data-type="material"]:checked')).map(el => el.value.trim().toLowerCase());

        const minPrice = parseInt(document.getElementById('minPrice').value) || 0;
        const maxPrice = parseInt(document.getElementById('maxPrice').value) || Infinity;
        const selectedRatingRadio = document.querySelector('.rating-radio:checked');
        const ratingValue = selectedRatingRadio ? selectedRatingRadio.value : 'any';

        let filtered = allProducts.filter(product => {
            if (globalSalesFilter === 'under-499' && product.price >= 499) return false;
            if (globalSalesFilter === 'high-discount' && product.discount < 45) return false;
            if (selectedCategories.length > 0 && !selectedCategories.includes(product.category.trim())) return false;
            if (selectedBrands.length > 0 && !selectedBrands.includes(product.brand.trim())) return false;
            
            if (selectedColors.length > 0 && !selectedColors.includes(product.color.trim().toLowerCase())) return false;
            if (selectedMaterials.length > 0 && !selectedMaterials.includes(product.material.trim().toLowerCase())) return false;

            if (product.price < minPrice || product.price > maxPrice) return false;
            if (ratingValue !== 'any' && product.rating < parseInt(ratingValue)) return false;
            return true;
        });

        if (globalSalesFilter === 'hot-sales') {
            filtered.sort((a, b) => b.discount !== a.discount ? b.discount - a.discount : a.price - b.price);
        } else if (globalSalesFilter === 'under-499') {
            filtered.sort((a, b) => a.price - b.price);
        } else if (globalSalesFilter === 'high-discount') {
            filtered.sort((a, b) => b.discount - a.discount);
        }

        currentFilteredProducts = filtered;
        currentPage = 1; 
        renderProducts(currentFilteredProducts);
    }

    document.addEventListener('change', e => { if (e.target.classList.contains('filter-checkbox') || e.target.classList.contains('rating-radio')) applyFilters(); });
    document.addEventListener('input', e => { if (e.target.classList.contains('price-input')) applyFilters(); });
    
    document.addEventListener('click', e => {
        if (e.target.closest('#btnClearAll')) {
            document.getElementById('filterForm').reset();
            if(window.location.search) window.history.replaceState({}, document.title, window.location.pathname);
            applyFilters();
        }
    });

    window.addEventListener('resize', setupFilterFormLocation);

    document.addEventListener("DOMContentLoaded", () => {
        const templateForm = document.querySelector('#masterFormTemplate form');
        const desktopContainer = document.getElementById('desktopFilterContainer');
        const mobileContainer = document.getElementById('mobileFilterContainer');
        
        if(templateForm) {
            const isMobile = window.innerWidth < 768;
            if(isMobile && mobileContainer) {
                mobileContainer.appendChild(templateForm);
            } else if(desktopContainer) {
                desktopContainer.appendChild(templateForm);
            }
        }

        const globalCategoryQuery = urlParams.get('category') ? urlParams.get('category').trim().toLowerCase() : '';

        if(globalCategoryQuery) {
            document.querySelectorAll('.filter-checkbox[data-type="category"]').forEach(cb => {
                if(cb.value.trim().toLowerCase() === globalCategoryQuery) {
                    cb.checked = true;
                }
            });
        }
        
        currentPage = 1;
        applyFilters();
    });

    // ✅ WISHLIST AJAX
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.wishlist-btn');
        if(!btn) return;

        const productId = parseInt(btn.dataset.productId);
        const formData = new FormData();
        formData.append('product_id', productId);

        try {
            const response = await fetch('/pages/products/wishlist.php', { 
                method: 'POST', 
                body: formData 
            });
            
            const data = await response.json();
            
            if(data.success) {
                const icon = btn.querySelector('i');
                if(data.action === 'added') {
                    icon.className = 'ri-heart-fill text-danger';
                } else {
                    icon.className = 'ri-heart-line text-secondary';
                }
                
                const targetProduct = allProducts.find(p => p.id === productId);
                if(targetProduct) targetProduct.is_wishlisted = (data.action === 'added');
                
                const wishlistBadges = document.querySelectorAll('#wishlist-count, #mobile-wishlist-count');
                wishlistBadges.forEach(badge => {
                    if(badge) badge.innerText = data.count;
                });
                
            } else if(data.message.toLowerCase().includes('login') || data.message === 'Please login first') {
                if(confirm('Please login to add items to wishlist')) {
                    window.location.href = '/pages/auth/login.php';
                }
            } else {
                alert(data.message);
            }
        } catch(error) { 
            console.error('Wishlist AJAX Error:', error); 
        }
    });
</script>

<?php if (!$isIncluded) {
    include __DIR__ . '/../../includes/footer.php';
} ?>
</body>
</html>
