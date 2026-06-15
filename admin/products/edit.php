<!-- admin\products\edit.php -->
<?php
require_once '../../config/database.php';
require_once '../../config/cloudinary.php'; 

include '../../includes/header.php';
include '../../includes/navbar.php';

$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM all_products_list WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<div class='container-fluid py-3'><div class='alert alert-danger border-0 small text-center' style='border-radius:8px;'>Product not found!</div></div>";
    include '../../includes/footer.php';
    exit;
}

$rawImages = !empty($product['images']) ? $product['images'] : (!empty($product['image']) ? $product['image'] : '');
$currentImages = !empty($rawImages) ? json_decode($rawImages, true) : [];

if (!empty($rawImages) && (json_last_error() !== JSON_ERROR_NONE || !is_array($currentImages))) {
    $currentImages = [$rawImages];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brand = trim($_POST['brand']); 
    $name = trim($_POST['name']); 
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $price = floatval($_POST['price']); 
    $old_price = floatval($_POST['old_price']); 
    $stock = intval($_POST['stock']); 
    $desc = trim($_POST['description']);
    $material = trim($_POST['material']);
    $color = trim($_POST['color']);
    $warranty = trim($_POST['warranty']);
    
    $imageUrls = $currentImages; 

    if (!empty($_FILES["images"]["name"][0])) {
        try {
            $newImages = [];
            foreach ($_FILES['images']['name'] as $key => $val) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $uploadResult = $cloudinary->uploadApi()->upload($_FILES['images']['tmp_name'][$key], [
                        'folder' => 'belt_store/products'
                    ]);
                    $newImages[] = $uploadResult['secure_url'];
                }
            }
            if (!empty($newImages)) {
                $imageUrls = $newImages; 
            }
        } catch (Exception $e) {
            echo "<script>alert('Upload Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }

    $imagesJson = json_encode($imageUrls);

    $stmt = $pdo->prepare("UPDATE all_products_list SET brand=?, name=?, slug=?, price=?, old_price=?, stock=?, description=?, material=?, color=?, warranty=?, images=? WHERE id=?");
    $stmt->execute([$brand, $name, $slug, $price, $old_price, $stock, $desc, $material, $color, $warranty, $imagesJson, $id]);
    
    echo "<script>window.location.href='index.php';</script>";
    exit;
}
?>

<style>
    body { background-color: #f7f9fc !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    .app-header { background: #fff; padding: 14px 16px; border-bottom: 1px solid #edf2f7; display: flex; align-items: center; gap: 12px; }
    .app-form-section { background: #fff; padding: 16px; margin-bottom: 8px; border-bottom: 1px solid #edf2f7; }
    .app-label { font-size: 11px; font-weight: 600; color: #718096; text-uppercase: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; display: block; }
    .app-input { width: 100%; border: none; background: #f1f5f9; padding: 10px 12px; font-size: 14px; color: #1a202c; border-radius: 6px; transition: all 0.2s; }
    .app-input:focus { outline: none; background: #fff; box-shadow: inset 0 0 0 2px #e61a61; }
    .app-gallery-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; background: #f8fafc; border: 1px solid #e2e8f0; }
    .app-btn-submit { background: #e61a61; color: #fff; border: none; width: 100%; padding: 12px; font-size: 14px; font-weight: 700; border-radius: 6px; letter-spacing: 0.3px; text-transform: uppercase; }
    @media (min-width: 768px) {
        .app-container { max-width: 680px; margin: 20px auto; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border-radius: 8px; overflow: hidden; border: 1px solid #edf2f7; }
        .app-form-section { border-bottom: 1px solid #edf2f7; }
    }
</style>

<div class="container-fluid p-0 app-container">
    <div class="app-header">
        <a href="index.php" style="color: #4a5568; font-size: 20px; text-decoration: none; line-height: 1;">
            <i class="ri-arrow-left-line"></i>
        </a>
        <h6 class="mb-0 fw-bold text-dark" style="font-size: 16px; letter-spacing: -0.3px;">Edit Product</h6>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="app-form-section">
            <div class="row g-3">
                <div class="col-6">
                    <label class="app-label">Brand Name</label>
                    <input type="text" name="brand" value="<?= htmlspecialchars($product['brand'] ?? 'ELDURATO') ?>" class="app-input" required>
                </div>
                <div class="col-6">
                    <label class="app-label">Product Title</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="app-input" required>
                </div>
            </div>
        </div>

        <div class="app-form-section">
            <div class="row g-3">
                <div class="col-4">
                    <label class="app-label">Price (₹)</label>
                    <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" class="app-input" required>
                </div>
                <div class="col-4">
                    <label class="app-label">MRP (₹)</label>
                    <input type="number" step="0.01" name="old_price" value="<?= $product['old_price'] ?? 0 ?>" class="app-input">
                </div>
                <div class="col-4">
                    <label class="app-label">Stock (Pcs)</label>
                    <input type="number" name="stock" value="<?= $product['stock'] ?? 0 ?>" class="app-input" required>
                </div>
            </div>
        </div>

        <div class="app-form-section">
            <div class="row g-3">
                <div class="col-4">
                    <label class="app-label">Material</label>
                    <input type="text" name="material" value="<?= htmlspecialchars($product['material'] ?? '') ?>" class="app-input">
                </div>
                <div class="col-4">
                    <label class="app-label">Color</label>
                    <input type="text" name="color" value="<?= htmlspecialchars($product['color'] ?? '') ?>" class="app-input">
                </div>
                <div class="col-4">
                    <label class="app-label">Warranty</label>
                    <input type="text" name="warranty" value="<?= htmlspecialchars($product['warranty'] ?? '') ?>" class="app-input">
                </div>
                <div class="col-12">
                    <label class="app-label">Description</label>
                    <textarea name="description" class="app-input" rows="3" style="resize: none;"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="app-form-section">
            <?php if (!empty($currentImages)): ?>
                <label class="app-label">Active Gallery</label>
                <div class="d-flex gap-2 flex-wrap mb-3">
                    <?php foreach($currentImages as $imgUrl): 
                        $actualUrl = is_array($imgUrl) ? ($imgUrl['url'] ?? '') : $imgUrl;
                        if(empty($actualUrl)) continue;
                    ?>
                        <img src="<?= $actualUrl ?>" class="app-gallery-thumb">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <label class="app-label">Replace Images</label>
            <input type="file" name="images[]" class="form-control form-control-sm border-0 bg-light p-2" style="font-size:13px; border-radius:6px;" accept="image/*" multiple>
        </div>

        <div class="p-3 bg-white">
            <button type="submit" class="app-btn-submit">Save Configurations</button>
        </div>
    </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

<?php include '../../includes/footer.php'; ?>