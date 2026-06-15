<?php
// admin/products/add.php
session_start();
require_once '../../config/database.php';
require_once '../../config/cloudinary.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: /belt/pages/auth/login.php");
    exit;
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brand = !empty($_POST['brand']) ? trim($_POST['brand']) : 'ELDURATO';
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name))) . "-" . time();
    $price = floatval($_POST['price']);
    $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : 0.00;
    $stock = intval($_POST['stock']);
    
    // SAFE FIX: Undefined key warning se bachne ke liye isset check lagaya hai
    $desc = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    $material = trim($_POST['material']);
    $color = trim($_POST['color']);
    $warranty = trim($_POST['warranty']);
    $model_name = trim($_POST['model_name']);
    $belt_width = trim($_POST['belt_width']);
    $weight = trim($_POST['weight']);

    $imageData = [];

    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        try {
            foreach ($_FILES['images']['name'] as $key => $val) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $uploadResult = $cloudinary->uploadApi()->upload($_FILES['images']['tmp_name'][$key], [
                        'folder' => 'belt_store/products'
                    ]);
                    
                    $associatedSizes = !empty($_POST['img_sizes'][$key]) ? trim($_POST['img_sizes'][$key]) : '28,30,32,34,36';
                    
                    $imageData[] = [
                        'url' => $uploadResult['secure_url'],
                        'sizes' => $associatedSizes
                    ];
                }
            }

            $imagesJson = json_encode($imageData);

            $stmt = $pdo->prepare("INSERT INTO all_products_list (brand, name, slug, price, old_price, stock, description, material, color, warranty, images, model_name, belt_width, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$brand, $name, $slug, $price, $old_price, $stock, $desc, $material, $color, $warranty, $imagesJson, $model_name, $belt_width, $weight]);

            $successMessage = "Product successfully logged into catalog ledger!";
        } catch (Exception $e) {
            $errorMessage = "Upload Failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELDURATO - Add Sku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: #f0f4f9; font-family: 'Inter', system-ui, sans-serif; color: #1e293b; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #4f46e5 0%, #3730a3 100%); box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        .nav-link-custom { color: rgba(255, 255, 255, 0.75); padding: 12px 18px; display: flex; align-items: center; text-decoration: none; border-radius: 8px; margin-bottom: 4px; font-weight: 600; font-size: 14.5px; transition: all 0.2s; }
        .nav-link-custom:hover, .nav-link-custom.active { background: rgba(255, 255, 255, 0.15); color: #ffffff; }
        .nav-link-custom i { margin-right: 12px; font-size: 1.2rem; color: rgba(255, 255, 255, 0.6); }
        .nav-link-custom:hover i, .nav-link-custom.active i { color: #fbbf24; }
        
        .header-console { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 12px; }
        .profile-card { border: none; border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .card-header-custom { background: #4f46e5; color: white; border-top-left-radius: 12px; border-top-right-radius: 12px; padding: 12px 18px; font-weight: 600; }
        .form-control-production { border-radius: 6px; padding: 8px 12px; border: 1px solid #cbd5e1; font-size: 14px; color: #0f172a; font-weight: 500; background-color: #f8fafc; }
        .form-control-production:focus { border-color: #4f46e5; background-color: #fff; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); outline: none; }
        .form-label-custom { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .app-image-row { border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 10px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-lg-2 p-3 sidebar position-fixed d-none d-lg-block h-100" style="z-index: 1000;">
            <div class="fs-4 fw-bold p-2 text-center border-bottom border-white border-opacity-10 mb-4 text-white" style="letter-spacing: -1px;">
                <i class="ri-shield-flash-fill text-warning me-1"></i> ELDURATO
            </div>
            <div class="d-flex flex-column">
                <a href="../index.php" class="nav-link-custom"><i class="ri-dashboard-3-line"></i>Dashboard</a>
                <a href="index.php" class="nav-link-custom active"><i class="ri-handbag-line"></i>Products</a>
                <a href="../orders/index.php" class="nav-link-custom"><i class="ri-shopping-bag-line"></i>Orders</a>
                <a href="../users/index.php" class="nav-link-custom"><i class="ri-user-settings-line"></i>Users</a>
                <a href="../profile.php" class="nav-link-custom"><i class="ri-user-line"></i>My Profile</a>
                <hr class="border-white border-opacity-20 my-3">
                <a href="/belt/pages/auth/logout.php" class="nav-link-custom text-white bg-danger"><i class="ri-logout-circle-line me-2"></i>Logout</a>
            </div>
        </div>

        <div class="col-lg-10 p-2 offset-lg-2">
            
            <div class="header-console p-3 text-white shadow-sm d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold m-0">Inventory Console</h5>
                    <div class="opacity-75 fs-7">Catalog Entry Management</div>
                </div>
                <a href="index.php" class="btn btn-sm btn-warning text-dark fw-bold px-3"><i class="ri-grid-line me-1"></i> Back to Catalog</a>
            </div>

            <?php if(!empty($successMessage)): ?>
                <div class="alert alert-success border-0 shadow-sm" role="alert"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>
            <?php if(!empty($errorMessage)): ?>
                <div class="alert alert-danger border-0 shadow-sm" role="alert"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    
                    <div class="col-md-6">
                        <div class="card profile-card overflow-hidden h-100">
                            <div class="card-header-custom"><i class="ri-information-line me-1"></i> General Information</div>
                            <div class="p-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label-custom">Brand Assignment</label>
                                        <select name="brand" class="form-control form-control-production">
                                            <option value="ELDURATO">ELDURATO</option>
                                            <option value="LEATHER KING">LEATHER KING</option>
                                            <option value="PREMIUM BELTS">PREMIUM BELTS</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-custom">Product Title</label>
                                        <input type="text" name="name" class="form-control form-control-production" placeholder="e.g. Genuine Leather Belt" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Selling Price (INR)</label>
                                        <input type="number" step="0.01" name="price" class="form-control form-control-production" placeholder="0.00" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">MRP / Strike Price</label>
                                        <input type="number" step="0.01" name="old_price" class="form-control form-control-production" placeholder="0.00">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-custom">Stock Inventory Units</label>
                                        <input type="number" name="stock" class="form-control form-control-production" placeholder="Available units" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-custom">Product Description</label>
                                        <textarea name="description" class="form-control form-control-production" rows="3" placeholder="Write full product description details here..." required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card profile-card overflow-hidden h-100">
                            <div class="card-header-custom"><i class="ri-equalizer-line me-1"></i> Sku Specifications</div>
                            <div class="p-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label-custom">Model Classification</label>
                                        <select name="model_name" class="form-control form-control-production">
                                            <option value="Classic Leather Belt">Classic Leather Belt</option>
                                            <option value="Formal Leather Belt">Formal Leather Belt</option>
                                            <option value="Casual Leather Belt">Casual Leather Belt</option>
                                            <option value="Automatic Buckle Belt">Automatic Buckle Belt</option>
                                            <option value="Reversible Belt">Reversible Belt</option>
                                            <option value="Premium Leather Belt">Premium Leather Belt</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-custom">Material Build</label>
                                        <select name="material" class="form-control form-control-production">
                                            <option value="Genuine Leather">Genuine Leather</option>
                                            <option value="Full Grain Leather">Full Grain Leather</option>
                                            <option value="Pull Grain Leather">Pull Grain Leather</option>
                                            <option value="Synthetic Leather">Synthetic Leather</option>
                                            <option value="PU Leather">PU Leather</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Color Node</label>
                                        <input type="text" name="color" class="form-control form-control-production" placeholder="e.g. Black">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Strap Width</label>
                                        <input type="text" name="belt_width" class="form-control form-control-production" placeholder="e.g. 1.5 inches">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Net Weight</label>
                                        <input type="text" name="weight" class="form-control form-control-production" placeholder="e.g. 300 g">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Warranty Tenure</label>
                                        <input type="text" name="warranty" class="form-control form-control-production" placeholder="e.g. 6 Months">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card profile-card overflow-hidden">
                            <div class="card-header-custom"><i class="ri-image-add-line me-1"></i> Asset Management</div>
                            <div class="p-4">
                                <div class="mb-3">
                                    <label class="form-label-custom">Select Product Images</label>
                                    <input type="file" id="imageSelector" name="images[]" class="form-control form-control-production" accept="image/*" multiple required>
                                </div>
                                <div id="imageSizesContainer" class="row g-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 text-end mb-4">
                        <button type="submit" class="btn btn-primary bg-gradient px-5 py-2 fw-bold" style="background-color: #4f46e5; border: none; border-radius: 6px;">Publish Product</button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>

<script>
// Live dynamic attachment renderer
document.getElementById('imageSelector').addEventListener('change', function() {
    const container = document.getElementById('imageSizesContainer');
    container.innerHTML = ''; 
    
    Array.from(this.files).forEach((file, index) => {
        const row = document.createElement('div');
        row.className = 'col-12 d-flex align-items-center gap-3 app-image-row mt-2';
        row.innerHTML = `
            <div style="width: 50px; height: 50px; min-width: 50px;" class="bg-white border rounded p-1">
                <img src="${URL.createObjectURL(file)}" class="w-100 h-100 object-fit-contain">
            </div>
            <div class="flex-grow-1">
                <label class="form-label-custom d-block mb-1">Waist Sizes (Img ${index + 1})</label>
                <input type="text" name="img_sizes[]" class="form-control form-control-production py-1 px-2" style="font-size: 13px;" value="28, 30, 32, 34, 36, 38" required>
            </div>
        `;
        container.appendChild(row);
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>