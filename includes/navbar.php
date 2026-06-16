<?php require_once __DIR__ . '/../config/functions.php'; ?>
<!DOCTYPE html>
<!-- includes\header.php -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?></title>
    <link class="rounded-pill" rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>/images/logo.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<?php
require_once __DIR__ . '/../config/database.php';
// includes\navbar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$wishlistCount = 0;

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_name = ""; 
$final_avatar = "https://cdn-icons-png.flaticon.com/512/3135/3135715.png"; 
$dashboard_url = SITE_URL . "/pages/account/dashboard.php";

if ($user_id) {
    try {
        $wishQuery = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
        $wishQuery->execute([$user_id]);
        $wishlistCount = (int)$wishQuery->fetchColumn();

        $user_stmt = $pdo->prepare("SELECT name, profile_pic, role FROM users WHERE id = ?");
        $user_stmt->execute([$user_id]);
        $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $name_parts = explode(' ', trim($user_data['name']));
            $user_name = htmlspecialchars($name_parts[0]);
            
            if (!empty($user_data['profile_pic']) && filter_var($user_data['profile_pic'], FILTER_VALIDATE_URL)) {
                $final_avatar = $user_data['profile_pic']; 
            } else if (!empty($_SESSION['user_pic'])) {
                $final_avatar = $_SESSION['user_pic'];
            }

            $_SESSION['user_name'] = $user_data['name'];
            $_SESSION['user_role'] = $user_data['role'];
            $_SESSION['user_pic']  = $user_data['profile_pic'];

            if (strtolower($user_data['role'] ?? 'user') === 'admin') {
                $dashboard_url = SITE_URL . "/admin/index.php";
            }
        } else {
            unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role'], $_SESSION['user_pic']);
            $user_id = null;
        }
    } catch (PDOException $e) {
        if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
            $name_parts = explode(' ', trim($_SESSION['user_name']));
            $user_name = htmlspecialchars($name_parts[0]);
        }
        if (isset($_SESSION['user_pic']) && !empty($_SESSION['user_pic'])) {
            $final_avatar = $_SESSION['user_pic'];
        }
    }
}

$currentQ = isset($_GET['q']) ? htmlspecialchars(trim($_GET['q'])) : '';
$currentCat = isset($_GET['category']) ? htmlspecialchars(trim($_GET['category'])) : '';
?>

<style>
    :root {
        --app-primary: #4f46e5;
        --app-dark: #1e293b;
    }
    /* Sticky Top App-Bar Configs */
    .app-sticky-header {
        position: sticky;
        top: 0;
        z-index: 1040;
        background: var(--app-dark);
        box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .16);
        padding: 12px 16px;
    }
    .app-search-input-group {
        background-color: #ffffff;
        border-radius: 10px;
        height: 40px;
        border: 1px solid #cbd5e1 !important;
        overflow: hidden;
    }
    
    .app-mobile-drawer { width: 280px !important; }
    .drawer-user-section { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 20px; color: #ffffff; }
    
    /* Real Premium E-Commerce Navigation Sizing */
    .row-menu .nav-link {
        font-size: 16px !important; 
        font-weight: 600 !important;
        padding: 14px 18px !important; 
        color: #334155 !important;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: color 0.15s ease;
    }
    .row-menu .nav-link:hover { color: var(--app-primary) !important; }
    
    /* Hover triggers sirf Mega Dropdowns ke liye handles */
    .has-mega:hover .mega-menu-content { display: block; }
    
    .mega-menu-content { 
        position: absolute; 
        top: 100%; left: 0; right: 0; width: 100%; 
        background: #ffffff; display: none; z-index: 1050; 
        border-radius: 0 0 20px 20px; 
        box-shadow: 0 25px 50px rgba(15,23,42,0.12) !important; 
        padding: 35px 50px !important;
        border: 1px solid #e2e8f0; border-top: none;
    }
    
    .mega-menu-title {
        font-size: 13px !important; font-weight: 700 !important;
        color: var(--app-primary) !important; letter-spacing: 0.8px; text-transform: uppercase;
        border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;
    }
    
    .hover-link { 
        font-size: 15px !important; font-weight: 500; color: #475569 !important; 
        text-decoration: none; display: block; padding: 4px 0; transition: all 0.15s ease;
    }
    .hover-link:hover { color: var(--app-primary) !important; transform: translateX(6px); }

    /* Bootstrap Dropdown Custom Tuning */
    .hot-sales-btn {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: #ffffff !important; border-radius: 8px; padding: 10px 20px !important;
        box-shadow: 0 4px 12px rgba(234, 88, 12, 0.15);
        display: flex; align-items: center; gap: 6px; border: none;
    }
    .hot-sales-btn:hover, .hot-sales-btn:focus { background: linear-gradient(135deg, #ea580c 0%, #dd6b20 100%); color: #ffffff !important; }
    
    .dropdown-menu-sales {
        border-radius: 12px !important;
        box-shadow: 0 15px 35px rgba(30,41,59,0.15) !important;
        border: 1px solid #e2e8f0 !important;
        min-width: 250px;
        padding: 6px 0;
    }

    /* Pure Dynamic App-Vibrant Color Palettes */
    .c-blue { color: #2563eb !important; }
    .c-pink { color: #db2777 !important; }
    .c-red { color: #ef4444 !important; }
    .c-amber { color: #eab308 !important; }
</style>

<!-- HYBRID HEADER ARCHITECTURE -->
<div class="app-sticky-header">
    <div class="container-fluid px-0 px-md-4 d-flex align-items-center justify-content-between">
        
        <div class="d-flex align-items-center gap-3">
            <button class="btn p-0 border-0 text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuDrawer">
                <i class="ri-menu-2-line fs-3 align-middle"></i>
            </button>
    <a href="<?php echo SITE_URL; ?>" class="d-flex flex-column align-items-center text-decoration-none text-center">
        <!-- Logo Section -->
        <div class="mb-1">
            <img src="/assets/images/logo.jpg" class="rounded-pill border border-2 border-secondary" width="90" alt="Logo">
        </div>
        
        <!-- Text Section (Logo ke niche)
        <span class="text-white-50 font-monospace text-uppercase" style="font-size: 10px; font-weight: 700; letter-spacing: 1px;">
            The Legacy of Genuine Leather
        </span>
         -->
    </a>
        </div>

        <!-- Center Search Box: Desktop Integration with End Button -->
        <div class="flex-grow-1 mx-3 mx-md-5 d-none d-md-block">
            <form action="<?php echo SITE_URL; ?>/pages/products/products.php" method="GET" class="m-0">
                <div class="input-group app-search-input-group align-items-center ps-3">
                    <input type="text" name="q" value="<?php echo $currentQ; ?>" class="form-control border-0 bg-transparent py-0 pe-2 shadow-none" style="font-size: 13.5px;" placeholder="Search for premium genuine leather belts, brands...">
                    <button type="submit" class="btn border-0 h-100 px-4 text-white d-flex align-items-center justify-content-center" style="background-color: var(--app-primary); border-radius: 0 9px 9px 0;">
                        <i class="ri-search-2-line fs-5"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="d-flex align-items-center gap-3 md-gap-4">
            <?php if(!empty($_SESSION['user_id']) && !empty($user_name)): ?>
                <div class="dropdown">
                    <a href="#" class="text-white fw-bold text-decoration-none d-flex align-items-center gap-1.5 small" data-bs-toggle="dropdown">
                        <img src="<?= $final_avatar ?>" alt="<?= $user_name ?>" class="rounded-circle border me-1" style="width: 26px; height: 26px; object-fit: cover;">
                        <?= $user_name ?>  <i class="ri-arrow-down-s-line opacity-50"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3" style="font-size: 13.5px;">
                        <li><a class="dropdown-item py-2" href="<?php echo $dashboard_url; ?>"><i class="ri-dashboard-line me-2 text-primary"></i>Dashboard</a></li>
                        <li><a class="dropdown-item py-2" href="<?php echo SITE_URL; ?>/pages/auth/profile.php"><i class="ri-user-settings-line me-2 text-success"></i>My Profile</a></li>
                        <li><a class="dropdown-item py-2" href="<?php echo SITE_URL; ?>/pages/products/cart.php"><i class="ri-box-3-line me-2 text-warning"></i>Orders</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?php echo SITE_URL; ?>/pages/auth/logout.php"><i class="ri-logout-box-r-line me-2"></i>Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/pages/auth/login.php" class="btn btn-sm btn-primary px-3 rounded-2 fw-bold" style="font-size:12px; background-color: #2563eb; border:none;">LOGIN</a>
            <?php endif; ?>

            <!-- shifted: 24/7 Support Link Button Near Cart/Wishlist -->
            <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="text-white text-decoration-none fs-4 lh-1" title="24/7 Support">
                <i class="ri-customer-service-2-line"></i>
            </a>

            <a href="<?php echo SITE_URL; ?>/pages/products/wishlist.php" class="text-white text-decoration-none position-relative fs-4 lh-1">
                <i class="ri-heart-3-line text-nowrap"></i>
                <span id="wishlist-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 8px; padding: 2px 4px;"><?php echo $wishlistCount; ?></span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/pages/products/cart.php" class="text-white text-decoration-none position-relative fs-4 lh-1">
                <i class="ri-shopping-bag-3-line text-nowrap"></i>
                <span id="desktop-cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 8px; padding: 2px 4px;"><?php echo $cartCount; ?></span>
            </a>
        </div>
    </div>

    <!-- Mobile View Search Field Frame -->
    <div class="d-md-none mt-2">
        <form action="<?php echo SITE_URL; ?>/pages/products/products.php" method="GET" class="m-0">
            <div class="input-group app-search-input-group align-items-center ps-3">
                <input type="text" name="q" value="<?php echo $currentQ; ?>" class="form-control border-0 bg-transparent py-0 pe-2 shadow-none" style="font-size: 13px;" placeholder="Search belts, brands, buckles...">
                <button type="submit" class="btn border-0 h-100 px-3 text-white d-flex align-items-center justify-content-center" style="background-color: var(--app-primary);">
                    <i class="ri-search-2-line" style="font-size: 14px;"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- NATIVE SIDE BAR MENU DRAWER -->
<div class="offcanvas offcanvas-start app-mobile-drawer" tabindex="-1" id="mobileMenuDrawer">
    <div class="drawer-user-section">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <img src="<?= $final_avatar ?>" class="rounded-circle border border-2 border-primary" style="width: 40px; height: 44px; object-fit: cover;">
            <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <h6 class="fw-bold mb-0"><?= !empty($user_name) ? "Hello, " . $user_name : "Welcome Guest" ?></h6>
    </div>
    <div class="offcanvas-body p-0">
        <div class="list-group list-group-flush small fw-semibold">
            <a href="<?php echo SITE_URL; ?>" class="list-group-item list-group-item-action py-3 text-primary"><i class="ri-home-5-line me-2"></i>Home</a>
            <a href="<?php echo SITE_URL; ?>/pages/about.php" class="list-group-item list-group-item-action py-3"><i class="ri-information-line me-2"></i>About Us</a>
            <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="list-group-item list-group-item-action py-3"><i class="ri-customer-service-2-line me-2"></i>24/7 Support</a>
            <?php if(!empty($_SESSION['user_id'])): ?>
                <a href="<?php echo $dashboard_url; ?>" class="list-group-item list-group-item-action py-3"><i class="ri-dashboard-3-line me-2"></i>My Dashboard</a>
                <a href="<?php echo SITE_URL; ?>/pages/auth/profile.php" class="list-group-item list-group-item-action py-3"><i class="ri-user-settings-line me-2 text-success"></i>My Profile</a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/pages/auth/login.php" class="list-group-item list-group-item-action py-3 text-success"><i class="ri-login-box-line me-2"></i>Login / Register</a>
            <?php endif; ?>

            <div class="bg-light px-3 py-2 small text-uppercase text-muted fw-bold border-top border-bottom">Categories</div>
            <a href="<?php echo SITE_URL; ?>/pages/products/products.php?q=formal" class="list-group-item list-group-item-action py-2.5 ps-4 text-secondary">Formal Office Belts</a>
            <a href="<?php echo SITE_URL; ?>/pages/products/products.php?q=casual" class="list-group-item list-group-item-action py-2.5 ps-4 text-secondary">Casual Jeans Belts</a>
            <a href="<?php echo SITE_URL; ?>/pages/products/combos.php" class="list-group-item list-group-item-action py-3"><i class="ri-gift-line me-2 text-danger"></i>Gift Combos</a>
            <a href="<?php echo SITE_URL; ?>/pages/products/products.php?filter=hot-sales" class="list-group-item list-group-item-action py-3 text-white" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);"><i class="ri-percent-line me-2"></i>HOT SALES ZONE</a>

            <?php if(!empty($_SESSION['user_id'])): ?>
                <a href="<?php echo SITE_URL; ?>/pages/auth/logout.php" class="list-group-item list-group-item-action py-3 text-danger border-top"><i class="ri-logout-box-r-line me-2"></i>Logout</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- DESKTOP REAL E-COMMERCE DROP-SHEET ENGINE -->
<div class="border-bottom d-none d-md-block bg-white shadow-sm">
    <div class="container-fluid position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <ul class="nav mb-0 list-unstyled d-flex align-items-center gap-1 fw-semibold row-menu">
                
                <li class="nav-item-wrapper">
                    <a href="<?php echo SITE_URL; ?>" class="nav-link"><i class="ri-home-5-fill c-blue"></i> Home</a>
                </li>   
                <li class="nav-item-wrapper">
                    <a href="<?php echo SITE_URL; ?>/pages/about.php" class="nav-link "><i class="ri-information-line"></i> About Us</a>
                </li>   
                
                <!-- Men's Section Dropdown -->
                <li class="nav-item-wrapper has-mega">
                    <a href="#" class="nav-link text-dark"><i class="ri-men-line c-blue"></i> Men's Section <i class="ri-arrow-down-s-line opacity-50 small"></i></a>
                    <div class="mega-menu-content shadow border rounded-4">
                        <div class="row g-4 text-start">
                            <div class="col-md-3">
                                <div class="mega-menu-title">By Usage Style</div>
                                <ul class="list-unstyled d-flex flex-column gap-2">
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=men&style=formal" class="hover-link">Formal Office Belts</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=men&style=casual" class="hover-link">Casual Jeans Belts</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=men&style=party" class="hover-link">Party Wear Collection</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=men&style=vintage" class="hover-link">Vintage Suede Belts</a></li>
                                </ul>
                            </div>
                            <div class="col-md-3">
                                <div class="mega-menu-title">Buckle Mechanics</div>
                                <ul class="list-unstyled d-flex flex-column gap-2">
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=men&buckle=automatic" class="hover-link">Automatic Click Buckle</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=men&buckle=classic" class="hover-link">Classic Pin Buckle</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=men&buckle=reversible" class="hover-link">Reversible Twist Buckle</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=men&buckle=double-ring" class="hover-link">Double D-Ring Lock</a></li>
                                </ul>
                            </div>
                            <div class="col-md-3">
                                <div class="mega-menu-title">Pure Leather Material</div>
                                <ul class="list-unstyled d-flex flex-column gap-2">
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?material=full-grain" class="hover-link">Full Grain Italian Leather</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?material=top-grain" class="hover-link">Top Grain Genuine Hide</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?material=textured" class="hover-link">Crocodile Textured Finish</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?material=braided" class="hover-link">Braided Woven Leather</a></li>
                                </ul>
                            </div>
                            <div class="col-md-3">
                                <div class="p-4 rounded-3 text-center h-100 d-flex flex-column justify-content-center bg-light border-dashed">
                                    <span class="fw-bold text-dark d-block mb-1" style="font-size: 15px;">Men's Club Special</span>
                                    <small class="text-muted d-block mb-3" style="font-size:11.5px;">Buy 1 Get 1 Free on Select Classics</small>
                                    <a href="<?php echo SITE_URL; ?>/pages/products/products.php?filter=hot-sales" class="btn btn-sm btn-dark py-2 px-4 rounded-pill fw-bold text-white align-self-center mt-1" style="font-size:12px; background:#4f46e5; border:none;">Explore Deal</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Women's Section Dropdown -->
                <li class="nav-item-wrapper has-mega">
                    <a href="#" class="nav-link text-dark"><i class="ri-women-line c-pink"></i> Women's Section <i class="ri-arrow-down-s-line opacity-50 small"></i></a>
                    <div class="mega-menu-content shadow border rounded-4">
                        <div class="row g-4 text-start">
                            <div class="col-md-3">
                                <div class="mega-menu-title" style="color: #db2777 !important; border-color: #fce7f3;">By Style Selection</div>
                                <ul class="list-unstyled d-flex flex-column gap-2">
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=women&style=slim" class="hover-link">Slim Dress Belts</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=women&style=corset" class="hover-link">Corset Wide Waist Belts</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=women&style=stretch" class="hover-link">Elastic Stretchable Bands</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=women&style=designer" class="hover-link">Statement Designer Pieces</a></li>
                                </ul>
                            </div>
                            <div class="col-md-3">
                                <div class="mega-menu-title" style="color: #db2777 !important; border-color: #fce7f3;">Buckle & Accents</div>
                                <ul class="list-unstyled d-flex flex-column gap-2">
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=women&buckle=golden" class="hover-link">Golden Interlocking Buckle</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=women&buckle=minimalist" class="hover-link">Minimalist Round Loop</a></li>
                                    <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?category=women&buckle=rhinestone" class="hover-link">Rhinestone Embedded Studs</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="p-4 rounded-3 text-center shadow-sm h-100 d-flex flex-column justify-content-center" style="background: #fdf2f8; border: 1px solid #fbcfe8;">
                                    <span class="fw-bold text-dark d-block mb-1" style="font-size: 15px;">Flat 30% OFF Luxury Wardrobe</span>
                                    <small class="text-muted d-block mb-3" style="font-size:11.5px;">Premium accessories crafted for luxury dress pairings</small>
                                    <a href="<?php echo SITE_URL; ?>/pages/products/products.php?filter=women-discounts" class="btn btn-sm btn-danger py-2 px-4 rounded-pill fw-bold text-white align-self-center" style="font-size:12px; border:none; background-color:#db2777;">View All Outfits</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Gift Combos Link -->
                <li class="nav-item-wrapper">
                    <a href="<?php echo SITE_URL; ?>/pages/products/combos.php" class="nav-link text-dark"><i class="ri-gift-fill c-red"></i> Gift Combos</a>
                </li>

                <!-- New Launches Link -->
                <li class="nav-item-wrapper">
                    <a href="<?php echo SITE_URL; ?>/pages/products/new-arrivals.php" class="nav-link text-dark"><i class="ri-fire-fill c-amber"></i> New Launches</a>
                </li>

                <!-- Hot Sales Zone Trigger with Bootstrap Dropdown Alignment -->
                <li class="dropdown d-flex align-items-center ms-3">
                    <button class="hot-sales-btn dropdown-toggle" type="button" id="hotSalesMenuBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-percent-fill"></i> HOT SALES ZONE
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-sales mt-1 border-0 shadow" aria-labelledby="hotSalesMenuBtn">
                        <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?filter=high-discount" class="dropdown-item py-2 px-3 text-danger fw-bold"><i class="ri-coupon-5-line me-1"></i> Flat 50% Off Counter</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/products/products.php?filter=under-499" class="dropdown-item py-2.5 px-3 fw-medium text-dark"><i class="ri-price-tag-3-line me-1 text-secondary"></i> Under ₹499 Store</a></li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
