<?php require_once __DIR__ . '/../config/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ✅ TITLE - Dynamic & Keyword Rich -->
    <title><?php echo SITE_NAME; ?> - Premium Genuine Leather Belts for Men & Women | Free Shipping</title>
    
    <!-- ✅ META DESCRIPTION -->
    <meta name="description" content="Shop premium genuine leather belts at Eldurato. Formal, casual, and luxury belts for men & women. Free shipping, COD, and 7-day replacement. Best prices in India.">
    <meta name="keywords" content="leather belts, men belts, women belts, formal belts, casual belts, premium leather, Eldurato, buy belts online">
    <meta name="robots" content="index, follow">
    
    <!-- ✅ CANONICAL TAG -->
    <link rel="canonical" href="https://eldurato.com<?php echo $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>/images/logo.ico">
    
    <!-- Google Analytics (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-8L6MVCN6LH"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-8L6MVCN6LH');
    </script>
    
    <!-- ✅ OPEN GRAPH TAGS -->
    <meta property="og:title" content="Eldurato - Premium Genuine Leather Belts Store India">
    <meta property="og:description" content="Shop premium leather belts for men & women. Free shipping, COD, and 7-day replacement available.">
    <meta property="og:image" content="https://eldurato.com/assets/images/logo.png">
    <meta property="og:url" content="https://eldurato.com">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Eldurato">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Eldurato - Premium Leather Belts">
    <meta name="twitter:description" content="Shop genuine leather belts at best prices in India. Free shipping & COD.">
    <meta name="twitter:image" content="https://eldurato.com/assets/images/logo.png">
    
    <!-- ✅ SCHEMA MARKUP -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Eldurato",
      "url": "https://eldurato.com",
      "description": "Premium genuine leather belts store in India",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://eldurato.com/pages/products/products.php?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Eldurato",
      "url": "https://eldurato.com",
      "logo": "https://eldurato.com/assets/images/logo.png",
      "description": "India's premium destination for genuine leather belts",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+91-7070759003",
        "email": "support@eldurato.com",
        "contactType": "customer service",
        "availableLanguage": ["English", "Hindi"]
      },
      "sameAs": [
        "https://facebook.com/eldurato",
        "https://instagram.com/eldurato"
      ]
    }
    </script>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
