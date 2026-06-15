<?php
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

    // व्हाट्सएप API URL (यह यूजर को व्हाट्सएप पर रीडायरेक्ट करेगा)
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
    <title>Contact Us - E-Commerce</title>
    <link rel="icon" type="image/x-icon" href="<?php echo defined('ASSETS_URL') ? rtrim(ASSETS_URL, '/') . '/images/logo.ico' : '../assets/images/logo.ico'; ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons for WhatsApp style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 rounded-3">
                
                <!-- कार्ड हेडर (व्हाट्सएप लुक) -->
                <div class="card-header bg-success text-white text-center py-3">
                    <h4><i class="fab fa-whatsapp me-2"></i>Contact Our Support</h4>
                    <p class="mb-0 small">हमसे संपर्क करें.</p>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="">
                        
                        <!-- नाम -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="अपना नाम लिखें" required>
                        </div>

                        <!-- मोबाइल नंबर -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Contact Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="अपना मोबाइल नंबर लिखें" required>
                        </div>

                        <!-- ईमेल -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="अपना ईमेल लिखें (वैकल्पिक)">
                        </div>

                        <!-- मैसेज -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your Message / Query</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="आप क्या पूछना चाहते हैं? (Product Name, Order ID आदि)" required></textarea>
                        </div>

                        <!-- सबमिट बटन -->
                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                            <i class="fab fa-whatsapp me-2"></i> Send to WhatsApp
                        </button>

                    </form>
                </div>

                <div class="card-footer text-center bg-white border-0 pb-3">
                    <small class="text-muted">Owner: Raj Sahni | Support: 7070759003</small>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>