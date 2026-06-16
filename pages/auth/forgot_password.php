<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';
require_once  '../../config/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND email != ''");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expire = date("Y-m-d H:i:s", strtotime("+1 hour"));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, token_expire = ? WHERE email = ?");
        $stmt->execute([$token, $expire, $email]);
        $resetLink = SITE_URL . "/pages/auth/reset_password.php?token=" . $token;
        $mail = new PHPMailer(true);

        try {






            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username = $_ENV['SMTP_EMAIL'] ?? '';
            $mail->Password = $_ENV['SMTP_PASSWORD'] ?? '';      
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
           $mail->Port = (int)($_ENV[''] ?? 587);
            $mail->setFrom($_ENV['SMTP_EMAIL'] ?? '',$_ENV['SITE_NAME'] ?? 'ELDURATO');
            $mail->addAddress($email, $user['name']);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            
            $mail->Body    = "
                <h3>Hello {$user['name']},</h3>
                <p>You requested a password reset for your account.</p>
                <p>Click the link below to reset your password. This link is valid for 1 hour.</p>
                <p><a href='{$resetLink}' style='background: #0d6efd; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>
                <br>
                <p>If you didn't request this, please ignore this email.</p>
            ";

            $mail->send();
            $success = "A password reset link has been sent to your email address.";
        } catch (Exception $e) {
            $error = "Mail could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $error = "This email address is not registered with us!";
    }
}
include '../../includes/header.php';
include '../../includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link class="rounded-pill" rel="icon" type="image/x-icon" href="/assets/images/logo.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="themes.css" rel="stylesheet">
</head>
<body class="auth-body">

<div class="container d-flex flex-column justify-content-center align-items-center flex-grow-1 my-5">
    <div class="row justify-content-center w-100">
        <div class="col-md-5 col-lg-4">
            <div class="card auth-card border-0 p-3">
                <div class="card-body">
                    <h4 class="fw-bold text-center mb-4 text-dark">Forgot Password</h4>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger py-2 small border-0 text-center"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success py-2 small border-0 text-center"><?= $success ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" autocomplete="off">
                        <div class="mb-4">
                            <input type="email" name="email" class="form-control auth-input" placeholder="Enter Your Registered Email" required>
                        </div>
                        
                        <button class="btn btn-gradient text-white w-100 py-2 border-0 mb-2 fw-semibold shadow-sm">
                            Send Reset Link
                        </button>
                    </form>
                    
                    <div class="text-center mt-3 small">
                        <a href="login.php" class="text-decoration-none fw-semibold text-primary">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
