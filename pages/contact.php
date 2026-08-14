<?php
// ✅ SEO DATA - SIRF SEO KE LIYE
$page_title = "Contact Us - Eldurato | Premium Leather Belts Customer Support";
$page_description = "Contact Eldurato for premium leather belts inquiries. Customer support for orders, returns, and product questions. Reach us via WhatsApp or email.";
$page_keywords = "contact Eldurato, leather belts support, customer care, order inquiry, WhatsApp support";
$canonical_url = "https://eldurato.com/pages/contact.php";
$og_image = "https://eldurato.com/assets/images/logo.png";

// अगर फॉर्म सबमिट हुआ है
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = urlencode(trim($_POST['name']));
    $phone = urlencode(trim($_POST['phone']));
    $email = urlencode(trim($_POST['email']));
    $message = urlencode(trim($_POST['message']));

    // आपका व्हाट्सएप नंबर (कंट्री कोड के साथ, बिना '+' के)
    $my_whatsapp = "917070759003"; 

    // व्हाट्सएप पर भेजने के लिए मैसेज का फॉर्मेट
    $whatsapp_text = "🛍️ *New Customer Inquiry* 🛍️%0A%0A"
                   . "*Name:* " . $name . "%0A"
                   . "*Contact No:* " . $phone . "%0A"
                   . "*Email:* " . $email . "%0A"
                   . "*Message:* " . $message;

    // व्हाट्सएप API URL
    $whatsapp_url = "https://api.whatsapp.com/send?phone=" . $my_whatsapp . "&text=" . $whatsapp_text;

    // सीधे व्हाट्सएप पर भेजें
    header("Location: " . $whatsapp_url);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ✅ TITLE - SEO ADDED -->
    <title><?php echo $page_title; ?></title>
    
    <!-- ✅ META DESCRIPTION - SEO ADDED -->
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    <meta name="robots" content="index, follow">
    
    <!-- ✅ CANONICAL TAG - SEO ADDED -->
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    
    <!-- ✅ OPEN GRAPH TAGS - SEO ADDED -->
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
    
    <!-- ✅ CONTACT PAGE SCHEMA - SEO ADDED -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ContactPage",
      "name": "Contact Eldurato",
      "description": "<?php echo $page_description; ?>",
      "url": "<?php echo $canonical_url; ?>",
      "mainEntity": {
        "@type": "Organization",
        "name": "Eldurato",
        "contactPoint": {
          "@type": "ContactPoint",
          "telephone": "+91-7070759003",
          "email": "support@eldurato.com",
          "contactType": "customer service",
          "availableLanguage": ["English", "Hindi"]
        }
      }
    }
    </script>
    
    <!-- ✅ BREADCRUMB SCHEMA - SEO ADDED -->
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
          "name": "Contact Us",
          "item": "https://eldurato.com/pages/contact.php"
        }
      ]
    }
    </script>
    
    <link rel="icon" type="image/x-icon" href="<?php echo defined('ASSETS_URL') ? rtrim(ASSETS_URL, '/') . '/images/logo.ico' : '../assets/images/logo.ico'; ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<!-- ✅ H1 TAG - VISUALLY HIDDEN (Design par koi effect nahi) -->
<h1 class="visually-hidden">Contact Eldurato - Premium Leather Belts Customer Support | WhatsApp Inquiry</h1>

<!-- ✅ BREADCRUMB NAVIGATION - SEO ADDED (Design same) -->
<nav aria-label="breadcrumb" class="container mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="https://eldurato.com/"><i class="fas fa-home"></i> Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
    </ol>
</nav>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <!-- ✅ PAGE HEADER - SEO ADDED -->
            <div class="text-center mb-4">
                <h2 class="display-5 fw-bold text-dark">Get in Touch</h2>
                <p class="text-muted">We'd love to hear from you! Reach out for any inquiries about our premium leather belts.</p>
            </div>

            <div class="card shadow border-0 rounded-3">
                
                <!-- कार्ड हेडर -->
                <div class="card-header bg-success text-white text-center py-3">
                    <h4><i class="fab fa-whatsapp me-2"></i>Chat with Us on WhatsApp</h4>
                    <p class="mb-0 small">Quick response within 24 hours</p>
                </div>

                <div class="card-body p-4">
                    
                    <!-- ✅ CONTACT INFO - EMAIL UPDATED -->
                    <div class="row text-center mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <i class="fas fa-phone text-success fs-4"></i>
                                <p class="mb-0 fw-bold">+91 7070759003</p>
                                <small class="text-muted">Call or WhatsApp</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <i class="fas fa-envelope text-primary fs-4"></i>
                                <p class="mb-0 fw-bold">support@eldurato.com</p>
                                <small class="text-muted">Email us</small>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fas fa-user me-1"></i> Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fas fa-phone me-1"></i> Contact Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="Enter your mobile number" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fas fa-envelope me-1"></i> Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email (optional)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fas fa-comment me-1"></i> Your Message / Query</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="What would you like to know? (Product name, Order ID, etc.)" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                            <i class="fab fa-whatsapp me-2"></i> Send to WhatsApp
                        </button>

                    </form>
                </div>

                <div class="card-footer text-center bg-white border-0 pb-3">
                    <small class="text-muted">
                        <i class="fas fa-user-tie me-1"></i> Owner: Raj Sahni 
                        | <i class="fas fa-headset me-1"></i> Support: 7070759003
                        | <i class="fas fa-clock me-1"></i> Response: 24/7
                    </small>
                </div>

            </div>

            <!-- ✅ TRUST BADGES - Design Same -->
            <div class="row mt-4 g-3">
                <div class="col-md-4">
                    <div class="text-center p-3 bg-white rounded-3 shadow-sm">
                        <i class="fas fa-truck fs-2 text-warning"></i>
                        <p class="fw-bold mb-0 mt-2">Free Shipping</p>
                        <small class="text-muted">On all orders</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-white rounded-3 shadow-sm">
                        <i class="fas fa-undo fs-2 text-primary"></i>
                        <p class="fw-bold mb-0 mt-2">7-Day Return</p>
                        <small class="text-muted">Easy replacement</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-white rounded-3 shadow-sm">
                        <i class="fas fa-shield-alt fs-2 text-success"></i>
                        <p class="fw-bold mb-0 mt-2">100% Genuine</p>
                        <small class="text-muted">Pure leather</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
