<!-- includes/footer.php - COMPLETE SEO FIXED VERSION -->
<?php
// ✅ DYNAMIC SEO DATA
$site_name = defined('SITE_NAME') ? SITE_NAME : 'ELDURATO';
$site_url = defined('SITE_URL') ? SITE_URL : 'https://eldurato.com';
$assets_url = defined('ASSETS_URL') ? ASSETS_URL : '../assets';
$current_year = date('Y');

// ✅ ORGANIZATION SCHEMA - CRITICAL FIX
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "<?php echo $site_name; ?>",
  "url": "<?php echo $site_url; ?>",
  "logo": "<?php echo $assets_url; ?>/images/logo.jpg",
  "description": "Premium genuine leather belts brand in India. Handcrafted formal, casual, and luxury belts with free shipping and COD.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "123, Leather Market",
    "addressLocality": "Lucknow",
    "addressRegion": "Uttar Pradesh",
    "postalCode": "226001",
    "addressCountry": "IN"
  },
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+91-7070759003",
    "contactType": "customer service",
    "availableLanguage": ["English", "Hindi"],
    "hoursAvailable": {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
      "opens": "09:00",
      "closes": "21:00"
    }
  },
  "sameAs": [
    "https://facebook.com/eldurato",
    "https://instagram.com/eldurato",
    "https://twitter.com/eldurato",
    "https://youtube.com/eldurato"
  ],
  "foundingDate": "2024",
  "founder": {
    "@type": "Person",
    "name": "Raj Sahni"
  }
}
</script>

<!-- ✅ LOCAL BUSINESS SCHEMA -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "<?php echo $site_name; ?>",
  "image": "<?php echo $assets_url; ?>/images/logo.jpg",
  "url": "<?php echo $site_url; ?>",
  "telephone": "+91-7070759003",
  "priceRange": "₹499 - ₹2999",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "123, Leather Market",
    "addressLocality": "Lucknow",
    "addressRegion": "Uttar Pradesh",
    "postalCode": "226001",
    "addressCountry": "IN"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "26.862131",
    "longitude": "80.999527"
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
      "opens": "09:00",
      "closes": "21:00"
    }
  ]
}
</script>

<!-- ✅ BREADCRUMB SCHEMA (Footer) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "<?php echo $site_url; ?>"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Products",
      "item": "<?php echo $site_url; ?>/pages/products/products.php"
    }
  ]
}
</script>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

<style>
    .site-footer {
        background-color: #0d0f12;
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
        color: #94a3b8 !important;
        text-decoration: none;
        transition: color 0.2s ease, transform 0.2s ease;
        display: inline-block;
    }
    .footer-link:hover {
        color: #ffc107 !important;
        transform: translateX(4px);
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
    .pay-icon {
        color: #64748b;
        transition: color 0.2s ease;
        font-size: 1.75rem;
    }
    .pay-icon-visa:hover { color: #1A1F71; }
    .pay-icon-mc:hover { color: #EB001B; }
    .pay-icon-up:hover { color: #008CFF; }
    .pay-icon-cod:hover { color: #10b981; }
    .color-inherit { color: inherit; }
    .color-inherit:hover { color: #ffc107; }
</style>

<footer class="site-footer text-white pt-5 pb-4" role="contentinfo">
    <div class="container">
        <div class="row g-4 text-md-start text-center">
            
            <!-- ✅ BRAND & DESCRIPTION -->
            <div class="col-lg-4 col-md-6" itemscope itemtype="https://schema.org/Organization">
                <meta itemprop="name" content="<?php echo $site_name; ?>">
                <meta itemprop="url" content="<?php echo $site_url; ?>">
                <meta itemprop="logo" content="<?php echo $assets_url; ?>/images/logo.jpg">
                
                <h5 class="fw-bold mb-3 d-flex align-items-center justify-content-center justify-content-md-start text-warning" style="letter-spacing: 1px; font-size: 1.2rem;">
                    <img src="<?php echo $assets_url; ?>/images/logo.jpg" 
                         class="me-2 rounded-1" 
                         alt="<?php echo $site_name; ?> - Premium Leather Belts Brand India" 
                         width="40" 
                         onerror="this.style.display='none'"
                         loading="lazy">
                    <?php echo $site_name; ?>
                </h5>
                
                <p class="small lh-lg text-secondary pe-lg-4" style="color: #94a3b8;">
                    Crafting premium quality leather belts designed for ultimate durability and timeless style. From formal sophistication to casual everyday essentials.
                </p>
                
                <!-- ✅ SOCIAL MEDIA LINKS - FIXED -->
                <div class="d-flex justify-content-center justify-content-md-start gap-2 mt-4">
                    <a href="https://facebook.com/eldurato" 
                       class="social-btn" 
                       aria-label="Follow Eldurato on Facebook" 
                       target="_blank" 
                       rel="noopener noreferrer nofollow">
                        <i class="ri-facebook-fill"></i>
                    </a>
                    <a href="https://instagram.com/eldurato" 
                       class="social-btn" 
                       aria-label="Follow Eldurato on Instagram" 
                       target="_blank" 
                       rel="noopener noreferrer nofollow">
                        <i class="ri-instagram-line"></i>
                    </a>
                    <a href="https://twitter.com/eldurato" 
                       class="social-btn" 
                       aria-label="Follow Eldurato on Twitter" 
                       target="_blank" 
                       rel="noopener noreferrer nofollow">
                        <i class="ri-twitter-x-fill"></i>
                    </a>
                    <a href="https://youtube.com/eldurato" 
                       class="social-btn" 
                       aria-label="Subscribe to Eldurato on YouTube" 
                       target="_blank" 
                       rel="noopener noreferrer nofollow">
                        <i class="ri-youtube-fill"></i>
                    </a>
                </div>
            </div>

            <!-- ✅ COLLECTIONS - FIXED with proper URLs -->
            <div class="col-lg-2 col-md-6 col-sm-6">
                <h6 class="footer-heading">Collections</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                    <li><a href="<?php echo $site_url; ?>/pages/products/products.php?q=formal" class="footer-link">Premium Formal</a></li>
                    <li><a href="<?php echo $site_url; ?>/pages/products/products.php?q=casual" class="footer-link">Casual Leather</a></li>
                    <li><a href="<?php echo $site_url; ?>/pages/products/products.php?q=luxury" class="footer-link">Luxury Edition</a></li>
                    <li><a href="<?php echo $site_url; ?>/pages/products/products.php?q=reversible" class="footer-link">Reversible 2-in-1</a></li>
                </ul>
            </div>

            <!-- ✅ QUICK LINKS -->
            <div class="col-lg-2 col-md-6 col-sm-6">
                <h6 class="footer-heading">Quick Links</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                    <li><a href="<?php echo $site_url; ?>/pages/about.php" class="footer-link">About Us</a></li>
                    <li><a href="<?php echo $site_url; ?>/pages/contact.php" class="footer-link">Contact Us</a></li>
                    <li><a href="<?php echo $site_url; ?>/pages/products/new-arrivals.php" class="footer-link">New Arrivals</a></li>
                    <li><a href="<?php echo $site_url; ?>/pages/products/products.php?filter=hot-sales" class="footer-link">Hot Sales</a></li>
                </ul>
            </div>

            <!-- ✅ CONTACT & PAYMENTS - FIXED with Schema -->
            <div class="col-lg-4 col-md-6" itemscope itemtype="https://schema.org/ContactPoint">
                <meta itemprop="contactType" content="customer service">
                <meta itemprop="telephone" content="+91-7070759003">
                <meta itemprop="email" content="eldurato99@gmail.com">
                
                <h6 class="footer-heading">Get In Touch</h6>
                <div class="small d-flex flex-column gap-2 mb-4" style="color: #94a3b8;">
                    
                    <!-- ✅ Address with Google Maps Link - FIXED -->
                    <p class="mb-0 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                        <i class="ri-map-pin-line text-warning"></i> 
                        <a href="https://maps.google.com/?q=26.862131,80.999527" 
                           target="_blank" 
                           rel="noopener noreferrer nofollow" 
                           class="text-decoration-none color-inherit" 
                           aria-label="Find Eldurato on Google Maps">
                            123, Leather Market, Lucknow, India
                        </a>
                    </p>
                    
                    <!-- ✅ Phone - FIXED -->
                    <p class="mb-0 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                        <i class="ri-phone-line text-warning"></i> 
                        <a href="tel:+917070759003" 
                           class="text-decoration-none color-inherit" 
                           aria-label="Call Eldurato at +91 7070759003">
                            +91 7070759003
                        </a>
                    </p>
                    
                    <!-- ✅ Email - FIXED -->
                    <p class="mb-0 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                        <i class="ri-mail-line text-warning"></i> 
                        <a href="mailto:eldurato99@gmail.com" 
                           class="text-decoration-none color-inherit" 
                           aria-label="Email Eldurato at eldurato99@gmail.com">
                            eldurato99@gmail.com
                        </a>
                    </p>
                </div>

                <!-- ✅ PAYMENT ICONS -->
                <h6 class="footer-heading mb-2" style="font-size: 10px; color: #64748b;">100% Safe Checkout</h6>
                <div class="d-flex justify-content-center justify-content-md-start gap-3">
                    <i class="ri-visa-line pay-icon pay-icon-visa" title="Visa accepted"></i>
                    <i class="ri-mastercard-line pay-icon pay-icon-mc" title="Mastercard accepted"></i>
                    <i class="ri-bank-card-line pay-icon pay-icon-up" title="UPI / RuPay accepted"></i>
                    <i class="ri-hand-coin-line pay-icon pay-icon-cod" title="Cash on Delivery available"></i>
                </div>
            </div>
        </div>

        <hr class="border-secondary opacity-10 my-4">

        <!-- ✅ COPYRIGHT - FIXED -->
        <div class="row align-items-center">
            <div class="col-md-12 text-center">
                <p class="mb-0 small" style="color: #64748b; font-size: 0.8rem;">
                    &copy; <?php echo $current_year; ?> 
                    <span class="text-white fw-medium"><?php echo $site_name; ?></span>. 
                    All Rights Reserved. 
                    Developed by <a href="https://www.awebgrow.com" 
                                     target="_blank" 
                                     rel="noopener noreferrer nofollow" 
                                     style="color: #94a3b8; text-decoration: none; transition: color 0.2s;">
                        AWebGrow
                    </a>
                </p>
            </div>
        </div>
    </div>
</footer>
