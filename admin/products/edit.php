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
   
    // 1. Jo purani images delete nahi ki gayi hain unhe preserve karein
    $retainedImages = [];
    if (isset($_POST['existing_images']) && is_array($_POST['existing_images'])) {
        foreach ($_POST['existing_images'] as $jsonItem) {
            $decoded = json_decode($jsonItem, true);
            $retainedImages[] = ($decoded !== null) ? $decoded : $jsonItem;
        }
    }

    // 2. Nayi uploaded images process karein
    $newUploadedImages = [];
    if (!empty($_FILES["images"]["name"][0])) {
        try {
            foreach ($_FILES['images']['name'] as $key => $val) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $uploadResult = $cloudinary->uploadApi()->upload($_FILES['images']['tmp_name'][$key], [
                        'folder' => 'belt_store/products'
                    ]);
                    $newUploadedImages[] = $uploadResult['secure_url'];
                }
            }
        } catch (Exception $e) {
            echo "<script>alert('Upload Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }

    // 3. Purani aur Nayi images ko Merge karein
    $finalImages = array_merge($retainedImages, $newUploadedImages);
    $imagesJson = json_encode($finalImages);

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
    .app-gallery-thumb { width: 65px; height: 65px; object-fit: cover; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; }
    .app-btn-submit { background: #e61a61; color: #fff; border: none; width: 100%; padding: 12px; font-size: 14px; font-weight: 700; border-radius: 6px; letter-spacing: 0.3px; text-transform: uppercase; }
   
    .thumb-wrapper { position: relative; display: inline-block; }
    .btn-delete-img { position: absolute; top: -6px; right: -6px; background: #dc3545; color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .btn-delete-img:hover { background: #bb2d3b; }

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
            <!-- Active Gallery with Delete Buttons -->
            <label class="app-label">Active Gallery (Click 'X' to remove)</label>
            <div class="d-flex gap-3 flex-wrap mb-3" id="activeGalleryContainer">
                <?php if (!empty($currentImages)): ?>
                    <?php foreach($currentImages as $imgItem):
                        $actualUrl = is_array($imgItem) ? ($imgItem['url'] ?? '') : $imgItem;
                        if(empty($actualUrl)) continue;
                        $jsonVal = htmlspecialchars(json_encode($imgItem), ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="thumb-wrapper">
                            <input type="hidden" name="existing_images[]" value="<?= $jsonVal ?>">
                            <img src="<?= htmlspecialchars($actualUrl) ?>" class="app-gallery-thumb">
                            <button type="button" class="btn-delete-img" onclick="this.parentElement.remove()" title="Delete Image">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-muted small">No images available</span>
                <?php endif; ?>
            </div>

            <!-- New Image Upload and Live Preview -->
            <label class="app-label">Add / Replace Images</label>
            <input type="file" name="images[]" id="imageInput" class="form-control form-control-sm border-0 bg-light p-2" style="font-size:13px; border-radius:6px;" accept="image/*" multiple onchange="previewNewImages(this)">
           
            <!-- Live Preview Container for Newly Selected Images -->
            <div id="newImagesPreview" class="d-flex gap-2 flex-wrap mt-3"></div>
        </div>

        <div class="p-3 bg-white">
            <button type="submit" class="app-btn-submit">Save Configurations</button>
        </div>
    </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

<script>
function previewNewImages(input) {
    const previewContainer = document.getElementById('newImagesPreview');
    previewContainer.innerHTML = '';

    if (input.files && input.files.length > 0) {
        const titleLabel = document.createElement('div');
        titleLabel.className = 'w-100 text-uppercase fw-semibold text-muted small mb-1';
        titleLabel.style.fontSize = '10px';
        titleLabel.innerText = 'New Selected Images Preview:';
        previewContainer.appendChild(titleLabel);

        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'app-gallery-thumb';
                img.style.border = '2px solid #e61a61';
                previewContainer.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    }
}
</script>

<?php include '../../includes/footer.php'; ?>