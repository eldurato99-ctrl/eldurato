<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

<section class="hero-carousel-section mb-4" data-aos="fade-down" data-aos-duration="1000">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
        
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <picture>
                    <img src="/assets/images/hero-slide-1.webp" class="d-block w-100" alt="Premium Leather Belts">
                </picture>
            </div>

            <div class="carousel-item">
                <picture>
                    <img src="/assets/images/hero-slide-3.webp" class="d-block w-100" alt="Mega Sale Banner">
                </picture>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true" style="background-color: rgba(0,0,0,0.5); padding: 20px; border-radius: 50%;"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true" style="background-color: rgba(0,0,0,0.5); padding: 20px; border-radius: 50%;"></span>
        </button>
    </div>
</section>

<main class="container-fluid homeBar px-md-1">

    <style>
        /* Smooth Custom Scrollbar */
        .csv-scroll::-webkit-scrollbar { height: 4px; }
        .csv-scroll::-webkit-scrollbar-thumb { background: #cbd5e1;}

        /* Main Container */
        .quick-cat-section {
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            padding: 20px 10px;
        }

        /* DRY Base class for items */
        .cat-item {
            min-width: 110px;
            position: relative;
            transition: transform 0.2s ease-in-out;
        }
        .cat-item:hover {
            transform: scale(1.05);
        }

        /* Uniform Circle Wrapper */
        .circle-wrapper {
            position: relative;
            width: 86px;
            height: 86px;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: radial-gradient(circle, #ffffff 62%, var(--bg-shade) 100%);
            border: 2px solid var(--bg-shade);
        }

        /* Core Image Style with Solid Vibrant Borders */
        .circle-wrapper img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            z-index: 2;
            border: 3px solid var(--theme-color);
        }

        /* --- Clean Vector Accent Overlays --- */
        .cat-item::after {
            position: absolute;
            top: 2px;
            right: 18px;
            font-size: 14px;
            z-index: 3;
        }
        
        .cat-item[data-cat="formal"]::after { content: '✨'; opacity: 0.9; }
        .cat-item[data-cat="casual"]::after { content: '⚡'; opacity: 0.9; }
        .cat-item[data-cat="luxury"]::after { content: '✨'; }
        .cat-item[data-cat="leather"]::after { content: '🍃'; }
        .cat-item[data-cat="trending"]::after { content: '↗'; font-size: 16px; font-weight: bold; color: var(--theme-color); top: 0px; }

        /* Unified Styled Text */
        .cat-title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
            color: var(--theme-color);
        }
    </style>

  <section class="my-4 quick-cat-section text-center" data-aos="zoom-up" data-aos-delay="100">
    <div class="d-flex justify-content-around flex-nowrap csv-scroll" style="overflow-x: auto; white-space: nowrap; padding-bottom: 5px;">
        
        <a href="<?php echo SITE_URL; ?>/pages/products/products.php?q=office" class="p-2 d-inline-block cat-item text-decoration-none" data-cat="formal" style="--theme-color: #0284c7; --bg-shade: #e0f2fe;">
            <div class="circle-wrapper">
                <img src="/assets/images/formal-belt.jpg" alt="Formal">
            </div>
            <p class="cat-title">Office<br>Belts</p>
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/products/products.php?q=casual" class="p-2 d-inline-block cat-item text-decoration-none" data-cat="casual" style="--theme-color: #ea580c; --bg-shade: #ffedd5;">
            <div class="circle-wrapper">
                <img src="/assets/images/casual-belt.jpg" alt="Casual">
            </div>
            <p class="cat-title">Casual<br>Belts</p>
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/products/products.php?q=luxury" class="p-2 d-inline-block cat-item text-decoration-none" data-cat="luxury" style="--theme-color: #7c3aed; --bg-shade: #f3e8ff;">
            <div class="circle-wrapper">
                <img src="/assets/images/premium.jpg" alt="Luxury">
            </div>
            <p class="cat-title">Premium<br>Luxury</p>
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/products/products.php?q=leather" class="p-2 d-inline-block cat-item text-decoration-none" data-cat="leather" style="--theme-color: #16a34a; --bg-shade: #dcfce7;">
            <div class="circle-wrapper">
                <img src="/assets/images/leather-belt.webp" alt="Leather">
            </div>
            <p class="cat-title">100% Pure<br>Leather</p>
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/products/products.php?q=trending" class="p-2 d-inline-block cat-item text-decoration-none" data-cat="trending" style="--theme-color: #ca8a04; --bg-shade: #fef9c3;">
            <div class="circle-wrapper">
                <img src="/assets/images/black-clothing.jpg" alt="Trending">
            </div>
            <p class="cat-title">Trending<br>Now</p>
        </a>

    </div>
</section>

    <div data-aos="fade-up">
        <?php include __DIR__ . '/../pages/products/RandProduct.php'; ?>
    </div>

    <div class="row text-center bg-white py-3 my-4 mx-0  shadow-sm border-bottom border-primary border-3 g-0" data-aos="fade-up" data-aos-delay="150">
        <div class="col-6 col-md-3 border-end border-primary mb-2 mb-md-0">
            <img src="https://cdn-icons-png.flaticon.com/512/2920/2920331.png" width="45" class="mb-1" alt="free shipping">
            <p class="mb-0 small fw-bold">Free Shipping</p>
        </div>
        <div class="col-6 col-md-3 border-md-end border-primary mb-2 mb-md-0">
            <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" width="45" class="mb-1" alt="7 days">
            <p class="mb-0 small fw-bold">7 Days Replacement</p>
        </div>
        <div class="col-6 col-md-3 border-end border-primary">
            <img src="https://cdn-icons-png.flaticon.com/512/7549/7549293.png" width="45" class="mb-1" alt="100% leather">
            <p class="mb-0 small fw-bold">100% Original Leather</p>
        </div>
        <div class="col-6 col-md-3">
            <img src="https://cdn-icons-png.flaticon.com/512/2331/2331966.png" width="45" class="mb-1" alt="cod">
            <p class="mb-0 small fw-bold">Cash on Delivery (COD)</p>
        </div>
    </div>

    <div class="bg-gradient bg-danger text-white p-3 rounded-0 mb-4 d-flex justify-content-between align-items-center flex-wrap" data-aos="zoom-in-up">
        <div>
            <span class="badge bg-warning text-dark fs-6">🔥 DEAL OF THE DAY 🔥</span>
            <h4 class="mt-2 mb-0">Flat 50% Off + Extra 10%</h4>
            <small>On premium leather belts & luxury gift boxes</small>
        </div>
        <div class="mt-2 mt-sm-0">
            <div class="bg-white text-dark rounded-0 px-3 py-2 fw-bold d-flex gap-3">
                <span>12 <small>Hrs</small></span> : <span>45 <small>Mins</small></span> : <span>22 <small>Secs</small></span>
            </div>
        </div>
    </div>

    <div class="row my-4 g-3">
        <div class="col-md-6" data-aos="fade-right" data-aos-delay="100">
            <div class="position-relative overflow-hidden rounded-0 bg-dark text-white shadow style-combo" style="height: 200px;">
                <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&fit=crop" class="w-100 h-100 object-fit-cover opacity-50" alt="Combo">
                <div class="position-absolute top-50 start-0 translate-middle-y ps-4">
                    <h3 class="fw-bold mb-1">Buy 1 Get 1 Free</h3>
                    <p class="mb-2 text-warning fw-semibold">On Casual Belt Collections</p>
                </div>
            </div>
        </div>
        <div class="col-md-6" data-aos="fade-left" data-aos-delay="200">
            <div class="position-relative overflow-hidden rounded-0 bg-primary text-white shadow style-gift" style="height: 200px;">
                <img src="https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&fit=crop" class="w-100 h-100 object-fit-cover opacity-50" alt="Gift Box">
                <div class="position-absolute top-50 start-0 translate-middle-y ps-4">
                    <h3 class="fw-bold mb-1">Luxury Gift Box Packs</h3>
                    <p class="mb-2 text-light fw-semibold">Perfect Gift for Corporate & Grooms</p>
                </div>
            </div>
        </div>
    </div>

    <section class="p-2 my-3" data-aos="fade-up" data-aos-duration="800">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">Products For You</h5>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Latest Premium Collection</small>
            </div>
            <a href="<?php echo SITE_URL; ?>/pages/products/products.php" class="btn btn-sm btn-light border fw-bold text-primary text-uppercase px-3 rounded-0-1" style="font-size: 0.75rem;">
                View All
            </a>
        </div>
        <img src="/assets/images/home-belt.webp" class="d-block w-100 mb-4 rounded-0 shadow-sm" alt="Premium Leather Belts" data-aos="zoom-in" data-aos-delay="100">

        <?php 
        define('INCLUDED_IN_HERO', true);
        include __DIR__ . '/../pages/products/products.php'; 
        ?>
    </section>

    <section class="container my-5 py-3">
        <div class="text-center mb-5" data-aos="fade-down">
            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle rounded-0 px-3 py-2 mb-2 text-uppercase tracking-wider small fw-bold" style="color: #db2777 !important; background-color: #fce7f3 !important; border-color: #fbcfe8 !important;">Reviews</span>
            <h2 class="fw-bold text-dark">What Our Happy Customers Say</h2>
            <p class="text-muted small">Real experiences from verified buyers across India</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="h-100 bg-white p-4 rounded-0 shadow-sm border d-flex flex-column justify-content-between" style="border-color: #e0f2fe !important;">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="small" style="color: #0284c7;">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-0 px-2 py-1 small" style="font-size: 0.75rem;">
                                <i class="bi bi-patch-check-fill me-1"></i> Verified Buyer
                            </span>
                        </div>
                        <p class="text-secondary mb-4 small" style="line-height: 1.6; font-style: italic;">
                            "The leather quality is exactly like high-end brands. Perfect stiffness and smooth texture. Worth every rupee!"
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3 pt-3 border-top border-light">
                        <div class="fw-bold rounded-0-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; min-width: 45px; font-size: 0.95rem; background-color: #e0f2fe; color: #0369a1;">RK</div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Rajesh K.</h6>
                            <small class="text-muted" style="font-size: 0.8rem;"><i class="bi bi-geo-alt-fill me-1" style="color: #0284c7;"></i>Delhi</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="h-100 bg-white p-4 rounded-0 shadow-sm border d-flex flex-column justify-content-between" style="border-color: #fce7f3 !important;">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="small" style="color: #db2777;">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-0 px-2 py-1 small" style="font-size: 0.75rem;">
                                <i class="bi bi-patch-check-fill me-1"></i> Verified Buyer
                            </span>
                        </div>
                        <p class="text-secondary mb-4 small" style="line-height: 1.6; font-style: italic;">
                            "Awesome product. I ordered the auto-lock buckle belt and it looks very luxury with formals. Fast delivery by the store."
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3 pt-3 border-top border-light">
                        <div class="fw-bold rounded-0-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; min-width: 45px; font-size: 0.95rem; background-color: #fce7f3; color: #b5179e;">AV</div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Amit Verma</h6>
                            <small class="text-muted" style="font-size: 0.8rem;"><i class="bi bi-geo-alt-fill me-1" style="color: #db2777;"></i>Mumbai</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="h-100 bg-white p-4 rounded-0 shadow-sm border d-flex flex-column justify-content-between" style="border-color: #e0f2fe !important;">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="small" style="color: #0284c7;">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-0 px-2 py-1 small" style="font-size: 0.75rem;">
                                <i class="bi bi-patch-check-fill me-1"></i> Verified Buyer
                            </span>
                        </div>
                        <p class="text-secondary mb-4 small" style="line-height: 1.6; font-style: italic;">
                            "Affordable price and Flipkart like delivery speed. 100% original leather checked. Fully satisfied."
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3 pt-3 border-top border-light">
                        <div class="fw-bold rounded-0-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; min-width: 45px; font-size: 0.95rem; background-color: #e0f2fe; color: #0369a1;">VS</div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Vikram S.</h6>
                            <small class="text-muted" style="font-size: 0.8rem;"><i class="bi bi-geo-alt-fill me-1" style="color: #0284c7;"></i>Bangalore</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="faq" class="faq-section my-5 py-4" data-aos="fade-up" data-aos-duration="800">
        <div class="container-fluid px-3 px-md-5">
            <div class="text-center mb-5">
                <h4 class="fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 3vw, 2.2rem); letter-spacing: 0.5px;">Frequently Asked Questions</h4>
                <p class="text-muted small mb-3">Got questions? We have got the answers.</p>
                <div class="mx-auto" style="width: 50px; height: 3px; background: #db2777; border-radius: 2px;"></div>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="p-4 h-100 rounded-0 bg-white shadow-sm faq-modern-card faq-blue border d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="faq-icon-shape d-flex align-items-center justify-content-center" style="--icon-bg: rgba(2, 132, 199, 0.1); --icon-color: #0284c7;"><i class="fa-solid fa-shield-halved fs-5"></i></div>
                            <h6 class="fw-bold text-dark mb-0 style-faq-q">Is the leather genuine?</h6>
                        </div>
                        <p class="small text-secondary mb-0 flex-grow-1" style="line-height: 1.6; padding-left: 3px;">Yes, 100% genuine pure full-grain leather. Every Eldurato belt comes with an official certificate of authenticity.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="150">
                    <div class="p-4 h-100 rounded-0 bg-white shadow-sm faq-modern-card faq-pink border d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="faq-icon-shape d-flex align-items-center justify-content-center" style="--icon-bg: rgba(219, 39, 119, 0.1); --icon-color: #db2777;"><i class="fa-solid fa-rotate-left fs-5"></i></div>
                            <h6 class="fw-bold text-dark mb-0 style-faq-q">What is the return policy?</h6>
                        </div>
                        <p class="small text-secondary mb-0 flex-grow-1" style="line-height: 1.6; padding-left: 3px;">We offer a 7-day easy replacement or return policy on all unworn items in original packaging, no questions asked.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="p-4 h-100 rounded-0 bg-white shadow-sm faq-modern-card faq-blue border d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="faq-icon-shape d-flex align-items-center justify-content-center" style="--icon-bg: rgba(2, 132, 199, 0.1); --icon-color: #0284c7;"><i class="fa-solid fa-wallet fs-5"></i></div>
                            <h6 class="fw-bold text-dark mb-0 style-faq-q">Do you offer COD?</h6>
                        </div>
                        <p class="small text-secondary mb-0 flex-grow-1" style="line-height: 1.6; padding-left: 3px;">Yes, Cash on Delivery (COD) option is fully available for thousands of pin codes across India with zero hidden fees.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="250">
                    <div class="p-4 h-100 rounded-0 bg-white shadow-sm faq-modern-card faq-pink border d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="faq-icon-shape d-flex align-items-center justify-content-center" style="--icon-bg: rgba(219, 39, 119, 0.1); --icon-color: #db2777;"><i class="fa-solid fa-truck-ramp-box fs-5"></i></div>
                            <h6 class="fw-bold text-dark mb-0 style-faq-q">How long does it take?</h6>
                        </div>
                        <p class="small text-secondary mb-0 flex-grow-1" style="line-height: 1.6; padding-left: 3px;">Standard shipping takes about 3-5 business days to safely reach your doorstep anywhere across India.</p>
                    </div>
                </div>
            </div>
        </div>

        <style>
            html { scroll-behavior: smooth; }
            .faq-modern-card { border-color: rgba(0, 0, 0, 0.05) !important; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
            .faq-modern-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08) !important; }
            .faq-modern-card.faq-blue:hover { border-color: #0284c7 !important; }
            .faq-modern-card.faq-pink:hover { border-color: #db2777 !important; }
            .faq-icon-shape { width: 44px; height: 44px; min-width: 44px; border-radius: 12px; background-color: var(--icon-bg); color: var(--icon-color); transition: transform 0.3s ease; }
            .faq-modern-card:hover .faq-icon-shape { transform: scale(1.1) rotate(5deg); }
            .style-faq-q { font-size: 0.98rem; line-height: 1.3; letter-spacing: -0.2px; }
        </style>
    </section>
</main>

<?php include 'perfume.php'; ?>

<style>
    body { background-color: #f1f3f6; font-family: Roboto, Arial, sans-serif; overflow-x: hidden; }
    .carousel-item img { object-fit: cover; width: 100%; }
    .hover-zoom:hover { transform: scale(1.03); transition: transform 0.2s ease-in-out; box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important; }
    .product-card:hover { border-color: #2874f0 !important; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .csv-scroll::-webkit-scrollbar { display: none; }
    .csv-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes pulse { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }
    .animate-pulse { animation: pulse 1.5s infinite; }
</style>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
    AOS.init({ duration: 700, once: true, offset: 80 });
</script>
