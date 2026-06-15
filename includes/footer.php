<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

<style>
    .site-footer {
        background-color: #0d0f12; /* Premium Slate Black */
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        letter-spacing: 0.3px;
    }
    .footer-heading {
        font-size: 0.85rem;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 1.25rem;
    }
    .footer-link {
        font-size: 0.9rem;
        color: #94a3b8 !important; /* Soft gray */
        text-decoration: none;
        transition: color 0.2s ease, transform 0.2s ease;
        display: inline-block;
    }
    .footer-link:hover {
        color: #ffc107 !important; /* Premium Gold/Yellow */
    }
    .social-btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.03);
        color: #94a3b8 !important;
        transition: all 0.2s ease;
    }
    .social-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff !important;
        transform: translateY(-2px);
    }
    /* Brand specific hover colors for payments */
    .pay-icon {
        color: #64748b;
        transition: color 0.2s ease;
        font-size: 1.75rem;
    }
    .pay-icon-visa:hover { color: #1A1F71; }
    .pay-icon-mc:hover { color: #EB001B; }
    .pay-icon-up:hover { color: #008CFF; }
    .pay-icon-cod:hover { color: #10b981; }
</style>

<footer class="site-footer text-white pt-5 pb-4">
    <div class="container">
        <div class="row g-4 text-md-start text-center">
            
            <!-- BRAND & DESCRIPTION -->
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-3 d-flex align-items-center justify-content-center justify-content-md-start text-warning" style="letter-spacing: 1px; font-size: 1.2rem;">
                    <!-- अगर लोगो इमेज लोड न हो तो भी टेक्स्ट दिखेगा -->
                    <img src="<?php echo defined('ASSETS_URL') ? ASSETS_URL : '../assets'; ?>/images/logo.jpg" class="me-2 rounded-1" alt="ELDURATO" width="40" onerror="this.style.display='none'">
                    <?php echo defined('SITE_NAME') ? SITE_NAME : 'ELDURATO'; ?>
                </h5>
                <!-- BUG FIXED: यहाँ स्पेस दिया है ताकि Bootstrap क्लासेस काम करें -->
                <p class="small lh-lg text-secondary pe-lg-4" style="color: #94a3b8;">
                    Crafting premium quality leather belts designed for ultimate durability and timeless style. From formal sophistication to casual everyday essentials.
                </p>
                
                <div class="d-flex justify-content-center justify-content-md-start gap-2 mt-4">
                    <a href="#" class="social-btn" aria-label="Facebook"><i class="ri-facebook-fill"></i></a>
                    <a href="#" class="social-btn" aria-label="Instagram"><i class="ri-instagram-line"></i></a>
                    <a href="#" class="social-btn" aria-label="Twitter"><i class="ri-twitter-x-fill"></i></a>
                    <a href="#" class="social-btn" aria-label="YouTube"><i class="ri-youtube-fill"></i></a>
                </div>
            </div>

            <!-- COLLECTIONS -->
            <div class="col-lg-2 col-md-6 col-sm-6">
                <h6 class="footer-heading">Collections</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                    <!-- PATH FIXED: सब-फ़ोल्डर से लिंक सही काम करने के लिए ../ लगाया गया है -->
                    <li><a href="../pages/products/products.php?q=formal" class="footer-link">Premium Formal</a></li>
                    <li><a href="../pages/products/products.php?q=casual" class="footer-link">Casual Leather</a></li>
                    <li><a href="../pages/products/products.php?q=luxury" class="footer-link">Luxury Edition</a></li>
                    <li><a href="../pages/products/products.php?q=reversible" class="footer-link">Reversible 2-in-1</a></li>
                </ul>
            </div>

            <!-- SUPPORT -->
            <div class="col-lg-2 col-md-6 col-sm-6">
                <h6 class="footer-heading">Support</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                    <li><a href="../track-order.php" class="footer-link">Track Order</a></li>
                    <li><a href="../return-policy.php" class="footer-link">7 Days Returns</a></li>
                    <li><a href="../size-guide.php" class="footer-link">Size Guide</a></li>
                    <li><a href="#faq" class="footer-link">FAQs</a></li>
                </ul>
            </div>

            <!-- CONTACT & PAYMENTS -->
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-heading">Get In Touch</h6>
                <div class="small d-flex flex-column gap-2 mb-4" style="color: #94a3b8;">
                    <p class="mb-0 d-flex align-items-center justify-content-center justify-content-md-start gap-2"><i class="ri-map-pin-line text-warning"></i> 123, Leather Market, India</p>
                    <p class="mb-0 d-flex align-items-center justify-content-center justify-content-md-start gap-2"><i class="ri-phone-line text-warning"></i> +91 7070759003</p>
                    <p class="mb-0 d-flex align-items-center justify-content-center justify-content-md-start gap-2"><i class="ri-mail-line text-warning"></i> support@<?php echo strtolower(defined('SITE_NAME') ? SITE_NAME : 'eldurato'); ?>.com</p>
                </div>
                
                <h6 class="footer-heading mb-2" style="font-size: 10px; color: #64748b;">100% Safe Checkout</h6>
                <div class="d-flex justify-content-center justify-content-md-start gap-3">
                    <i class="ri-visa-line pay-icon pay-icon-visa" title="Visa"></i>
                    <i class="ri-mastercard-line pay-icon pay-icon-mc" title="Mastercard"></i>
                    <i class="ri-bank-card-line pay-icon pay-icon-up" title="UPI / RuPay"></i>
                    <i class="ri-hand-coin-line pay-icon pay-icon-cod" title="Cash on Delivery"></i>
                </div>
            </div>

        </div>

        <hr class="border-secondary opacity-10 my-4">

        <!-- COPYRIGHT -->
        <div class="row align-items-center">
            <div class="col-md-12 text-center">
                <p class="mb-0 small" style="color: #64748b; font-size: 0.8rem;">
                    &copy; <?php echo date('Y'); ?> <span class="text-white fw-medium"><?php echo defined('SITE_NAME') ? SITE_NAME : 'ELDURATO'; ?></span>. All Rights Reserved.
                </p>
            </div>
        </div>
    </div>
</footer>