<?php
// Active page check karne ke liye current file ka naam nikalte hain
$current_page = basename($_SERVER['PHP_SELF']);
// Agar page kisi subfolder me hai toh folder ka naam bhi check kar sakte hain
$current_uri = $_SERVER['REQUEST_URI'];

// Helper function active class lagane ke liye
function isActive($keyword, $current_uri) {
    return (strpos($current_uri, $keyword) !== false) ? 'active' : '';
}
?>

<div class="col-lg-2 p-3 sidebar-desktop d-none d-lg-block">
    <div class="fs-4 fw-bold p-2 text-center border-bottom border-white border-opacity-10 mb-4 text-white" style="letter-spacing: -1px;">
        <i class="ri-shield-flash-fill text-warning me-1"></i> ELDURATO
    </div>
    <div class="d-flex flex-column gap-1">
        <a href="/belt/admin/index.php" class="nav-link-custom <?= ($current_page == 'index.php' && strpos($current_uri, 'admin/index.php') !== false) ? 'active' : '' ?>">
            <i class="me-2 ri-dashboard-3-line"></i>Dashboard
        </a>
        <a href="/belt/admin/products/index.php" class="nav-link-custom <?= isActive('products/', $current_uri) ?>">
            <i class="me-2 ri-handbag-line"></i>Products
        </a>
        <a href="/belt/admin/orders/index.php" class="nav-link-custom <?= isActive('orders/', $current_uri) ?>">
            <i class="me-2 ri-shopping-bag-line"></i>Orders
        </a>
        <a href="/belt/admin/users/index.php" class="nav-link-custom <?= isActive('users/', $current_uri) ?>">
            <i class="me-2 ri-user-settings-line"></i>Users
        </a>
        <a href="/belt/admin/profile.php" class="nav-link-custom <?= ($current_page == 'profile.php') ? 'active' : '' ?>">
            <i class="me-2 ri-user-line"></i>My Profile
        </a>
        
        <hr class="border-white border-opacity-20 my-3">
        <a href="/belt/pages/auth/logout.php" class="nav-link-custom text-white bg-danger"><i class="ri-logout-circle-line me-2"></i>Logout</a>
    </div>
</div>

<div class="offcanvas offcanvas-start sidebar-mobile" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header border-bottom border-white border-opacity-10 justify-content-between">
        <h5 class="offcanvas-title text-white fw-bold" id="mobileSidebarLabel">
            <i class="ri-shield-flash-fill text-warning me-1"></i> ELDURATO
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        <div class="d-flex flex-column gap-1">
            <a href="/belt/admin/index.php" class="nav-link-custom <?= ($current_page == 'index.php' && strpos($current_uri, 'admin/index.php') !== false) ? 'active' : '' ?>">
                <i class="me-2 ri-dashboard-3-line"></i>Dashboard
            </a>
            <a href="/belt/admin/products/index.php" class="nav-link-custom <?= isActive('products/', $current_uri) ?>">
                <i class="me-2 ri-handbag-line"></i>Products
            </a>
            <a href="/belt/admin/orders/index.php" class="nav-link-custom <?= isActive('orders/', $current_uri) ?>">
                <i class="me-2 ri-shopping-bag-line"></i>Orders
            </a>
            <a href="/belt/admin/users/index.php" class="nav-link-custom <?= isActive('users/', $current_uri) ?>">
                <i class="me-2 ri-user-settings-line"></i>Users
            </a>
            <a href="/belt/admin/profile.php" class="nav-link-custom <?= ($current_page == 'profile.php') ? 'active' : '' ?>">
                <i class="me-2 ri-user-line"></i>My Profile
            </a>
            
            <hr class="border-white border-opacity-20 my-3">
            <a href="/belt/pages/auth/logout.php" class="nav-link-custom text-white bg-danger"><i class="ri-logout-circle-line me-2"></i>Logout</a>
        </div>
    </div>
</div>