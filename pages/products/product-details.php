<?php
require_once '../../config/database.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

if (!function_exists('url')) {
    function url($path) {
        return '/belt/' . ltrim($path, '/');
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM all_products_list WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<div class='container my-5 alert alert-warning rounded-3 border-0 shadow-sm mx-3'>The product has been relocated or expired. <a href='".url('pages/products/products.php')."' class='alert-link'>Return to Grid</a></div>";
    include '../../includes/footer.php';
    exit;
}

// 🚫 CHECK STOCK STATUS
$isOutOfStock = (isset($product['stock_status']) && $product['stock_status'] === 'out_of_stock');

$imagesData = !empty($product['images']) ? json_decode($product['images'], true) : [];

if (!empty($imagesData) && isset($imagesData[0]['url'])) {
    $firstImgUrl = $imagesData[0]['url'];
    $firstImgSizes = $imagesData[0]['sizes'] ?? '28,30,32,34,36,38,40';
} else {
    $firstImgUrl = !empty($product['images']) && !is_array(json_decode($product['images'])) ? $product['images'] : 'https://via.placeholder.com/500x400?text=No+Image';
    $firstImgSizes = '28,30,32,34,36,38,40';
}

$discount = ($product['old_price'] > 0) ? round((($product['old_price'] - $product['price']) / $product['old_price']) * 100) : 0;
?>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body { padding-bottom: <?= $isOutOfStock ? '20px' : '70px' ?> !important; }
    
    /* Native Size Chips Layout */
    .size-chip { 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        width: 45px; 
        height: 45px; 
        background: #fff; 
        border: 1px solid #e2e8f0; 
        border-radius: 50%; /* Pure circular android style design */
        font-size: 13px; 
        font-weight: 600; 
        color: #334155; 
        transition: all 0.15s ease;
    }
    .size-chip.active-size { 
        border-color: #1a202c !important; 
        color: #fff !important; 
        background-color: #1a202c !important; 
    }
    
    /* Carousel Overrides */
    .carousel-item img { height: 340px; object-fit: contain; }
    @media (min-width: 768px) {
        body { padding-bottom: 20px !important; }
        .carousel-item img { height: 440px; }
    }
</style>

<div class="container py-2 py-md-4">
    <div class="row g-3">
        
        <div class="col-12 col-md-6">
            <div class="bg-white rounded-4 border p-2 position-sticky shadow-sm" style="top: 20px;">
                <div id="productImagesCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
                    <div class="carousel-inner">
                        <?php if(!empty($imagesData) && is_array($imagesData)): ?>
                            <?php foreach($imagesData as $index => $imgItem): 
                                $imgUrl = isset($imgItem['url']) ? $imgItem['url'] : $imgItem;
                                $imgSizes = isset($imgItem['sizes']) ? $imgItem['sizes'] : '28,30,32,34,36,38,40';
                            ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" data-imgurl="<?= $imgUrl ?>" data-sizes="<?= htmlspecialchars($imgSizes) ?>">
                                    <img src="<?= $imgUrl; ?>" class="d-block w-100 p-2" style="<?= $isOutOfStock ? 'filter: grayscale(100%); opacity: 0.8;' : '' ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-item active" data-imgurl="<?= $firstImgUrl ?>" data-sizes="<?= $firstImgSizes ?>">
                                <img src="<?= $firstImgUrl; ?>" class="d-block w-100 p-2" style="<?= $isOutOfStock ? 'filter: grayscale(100%); opacity: 0.8;' : '' ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if(!empty($imagesData) && count($imagesData) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productImagesCarousel" data-bs-slide="prev" style="filter: invert(0.8); width: 8%;">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productImagesCarousel" data-bs-slide="next" style="filter: invert(0.8); width: 8%;">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>
                </div>
                
                <?php if(!empty($imagesData) && is_array($imagesData) && count($imagesData) > 1): ?>
                    <div class="d-flex justify-content-center gap-2 overflow-auto border-top pt-2 mt-1">
                        <?php foreach($imagesData as $index => $imgItem): 
                            $imgUrl = isset($imgItem['url']) ? $imgItem['url'] : $imgItem;
                        ?>
                            <div class="border rounded p-1 bg-light cursor-pointer thumb-box" data-bs-target="#productImagesCarousel" data-bs-slide-to="<?= $index ?>" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <img src="<?= $imgUrl; ?>" class="w-100 h-100 object-fit-contain" style="<?= $isOutOfStock ? 'filter: grayscale(100%);' : '' ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-12 col-md-6">
            <div class="d-flex flex-column gap-2">
                
                <div class="bg-white p-3 rounded-4 border shadow-sm">
                    <span class="text-uppercase text-muted fw-bold small tracking-wider" style="font-size: 10px;"><?= htmlspecialchars($product['brand']) ?></span>
                    <h5 class="fw-bold text-dark mt-1 mb-2" style="font-size: 17px; line-height: 1.4;"><?= htmlspecialchars($product['name']) ?></h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-2 px-2 py-1" style="font-size: 11px;"><?= $product['rating'] ?? '4.1' ?> ★</span>
                        <span class="text-muted small">(<?= number_format($product['total_reviews'] ?? 142) ?> Ratings)</span>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-4 border shadow-sm">
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fw-bold text-dark fs-3">₹<?= number_format($product['price']) ?></span>
                        <?php if ($product['old_price'] > 0): ?>
                            <span class="text-muted text-decoration-line-through small">₹<?= number_format($product['old_price']) ?></span>
                            <span class="badge bg-danger-subtle text-danger fw-bold rounded-2 px-2 py-1" style="font-size: 11px; border: 1px solid #fecaca;"><?= $discount ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;"><i class="ri-checkbox-circle-line text-success"></i> Free Delivery • Cash on Delivery Available</small>
                </div>

                <?php if ($isOutOfStock): ?>
                    <div class="alert alert-danger border-0 rounded-4 shadow-sm p-3 m-0 d-flex align-items-center gap-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                            <i class="ri-error-warning-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold m-0 text-danger" style="font-size: 14px;">Stock Not Available</h6>
                            <p class="small m-0 text-secondary" style="font-size: 12px;">This product is currently sold out. We will restock it soon.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="bg-white p-3 rounded-4 border shadow-sm">
                    <span class="fw-bold text-dark d-block small mb-2" style="font-size: 13px;">Select Waist Size:</span>
                    <div id="dynamicSizesRow" class="d-flex gap-2 flex-wrap"></div>
                </div>

                <div class="bg-white p-3 rounded-4 border shadow-sm">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 13px;">Product Highlights</h6>
                    <div class="row g-2 text-dark small" style="font-size: 12.5px;">
                        <div class="col-6"><span class="text-muted d-block" style="font-size: 10px;">Color</span><strong><?= htmlspecialchars($product['color'] ?? 'Black') ?></strong></div>
                        <div class="col-6"><span class="text-muted d-block" style="font-size: 10px;">Belt Width</span><strong><?= htmlspecialchars($product['belt_width'] ?? '1.5 inches') ?></strong></div>
                        <div class="col-6"><span class="text-muted d-block" style="font-size: 10px;">Material</span><strong><?= htmlspecialchars($product['material'] ?? 'Genuine Leather') ?></strong></div>
                        <div class="col-6"><span class="text-muted d-block" style="font-size: 10px;">Occasion</span><strong>Casual, Formal</strong></div>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-4 border shadow-sm mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 13px;">Specifications</h6>
                    <div class="table-responsive rounded-3 border overflow-hidden m-0">
                        <table class="table table-sm table-striped table-borderless small mb-0" style="font-size: 12px;">
                            <tbody>
                                <tr><td class="text-muted py-2 px-3 bg-light" style="width:35%;">Model Name</td><td class="text-dark py-2 px-3 fw-medium"><?= htmlspecialchars($product['model_name'] ?? 'Men Genuine Leather Belt') ?></td></tr>
                                <tr><td class="text-muted py-2 px-3 bg-light">Weight</td><td class="text-dark py-2 px-3 fw-medium"><?= htmlspecialchars($product['weight'] ?? '300 g') ?></td></tr>
                                <tr><td class="text-muted py-2 px-3 bg-light">Warranty</td><td class="text-dark py-2 px-3 fw-medium"><?= htmlspecialchars($product['warranty'] ?? '6 Months') ?></td></tr>
                                <tr><td class="text-muted py-2 px-3 bg-light" style="vertical-align: top;">Description</td><td class="text-dark py-2 px-3" style="line-height:1.4; text-align: justify;"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php if (!$isOutOfStock): ?>
    <div class="fixed-bottom bg-white border-top py-2 px-3 shadow-lg d-md-none" style="z-index: 1040;">
        <form action="<?php echo url('pages/products/cart.php'); ?>" method="POST" class="d-flex gap-2 mx-auto" style="max-width: 500px;">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="selected_image" id="cart_selected_image" value="<?php echo $firstImgUrl; ?>">
            <input type="hidden" name="size" id="cart_selected_size" value="">
            
            <button type="submit" name="add_to_cart" class="btn btn-outline-dark w-50 py-2 fw-bold d-flex align-items-center justify-content-center gap-1" style="font-size: 13px; border-radius: 8px; height: 42px;">
                <i class="ri-shopping-cart-2-line"></i> ADD TO CART
            </button>
            <button type="submit" name="buy_now" class="btn btn-dark w-50 py-2 fw-bold text-white d-flex align-items-center justify-content-center gap-1" style="background-color:#1a202c; border:none; font-size: 13px; border-radius: 8px; height: 42px;">
                <i class="ri-flash-line"></i> BUY NOW
            </button>
        </form>
    </div>
<?php endif; ?>

<?php if (!$isOutOfStock): ?>
    <div class="container d-none d-md-block mb-5">
        <div class="row">
            <div class="col-md-6 offset-md-6 px-1">
                <form action="<?php echo url('pages/products/cart.php'); ?>" method="POST" class="d-flex gap-3">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="selected_image" id="desktop_selected_image" value="<?php echo $firstImgUrl; ?>">
                    <input type="hidden" name="size" class="desktop-size-mirror" value="">

                    <button type="submit" name="add_to_cart" class="btn btn-outline-dark px-4 py-2.5 fw-bold" style="border-radius: 8px; font-size: 14px;"><i class="ri-shopping-cart-2-line me-1"></i> ADD TO CART</button>
                    <button type="submit" name="buy_now" class="btn btn-dark px-5 py-2.5 fw-bold text-white" style="background-color: #1a202c; border:none; border-radius: 8px; font-size: 14px;"><i class="ri-flash-line me-1"></i> BUY NOW</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
let currentSelectedSize = "";

const carouselEl = document.getElementById('productImagesCarousel');
if (carouselEl) {
    carouselEl.addEventListener('slide.bs.carousel', event => {
        const activeSlide = event.relatedTarget;
        const imgUrl = activeSlide.getAttribute('data-imgurl');
        const sizesString = activeSlide.getAttribute('data-sizes');
        
        if(document.getElementById('cart_selected_image')) document.getElementById('cart_selected_image').value = imgUrl;
        if(document.getElementById('desktop_selected_image')) document.getElementById('desktop_selected_image').value = imgUrl;
        
        renderSizes(sizesString);
    });
}

function renderSizes(sizesString) {
    const container = document.getElementById('dynamicSizesRow');
    container.innerHTML = ''; 
    
    const sizesArray = sizesString ? sizesString.split(',') : [];
    
    if(sizesArray.length > 0 && sizesArray[0].trim() !== "") {
        let matchedIndex = -1;
        
        sizesArray.forEach((size, idx) => {
            if(size.trim() === currentSelectedSize) {
                matchedIndex = idx;
            }
        });

        if (matchedIndex === -1) {
            currentSelectedSize = sizesArray[0].trim();
        }

        sizesArray.forEach((size, index) => {
            const cleanSize = size.trim();
            if(cleanSize) {
                const isSelected = (cleanSize === currentSelectedSize);
                
                if(isSelected) {
                    updateFormSizeValues(cleanSize);
                }

                const activeClass = isSelected ? 'active-size' : '';
                container.innerHTML += `<div class="size-chip ${activeClass}" style="cursor:pointer;" onclick="selectSize(this, '${cleanSize}')">${cleanSize}</div>`;
            }
        });
    } else {
        container.innerHTML = `<span class="badge bg-light text-secondary p-2 border" style="font-size:11px; border-radius:6px;">Free Size Available</span>`;
        updateFormSizeValues("Free Size");
    }
}

function selectSize(element, size) {
    document.querySelectorAll('.size-chip').forEach(chip => {
        chip.classList.remove('active-size');
    });
    
    element.classList.add('active-size');
    currentSelectedSize = size;
    updateFormSizeValues(size);
    console.log("Selected Size Updated to:", size);
}

function updateFormSizeValues(sizeValue) {
    const mobileInput = document.getElementById('cart_selected_size');
    if (mobileInput) {
        mobileInput.value = sizeValue;
    }
    
    const desktopInput = document.querySelector('.desktop-size-mirror');
    if (desktopInput) {
        desktopInput.value = sizeValue;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    renderSizes('<?= htmlspecialchars($firstImgSizes) ?>');
});
</script>

<?php include '../../includes/footer.php'; ?>