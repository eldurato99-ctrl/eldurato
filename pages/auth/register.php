<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $mobile = trim($_POST['mobile']);  
    $email = trim($_POST['email']);  
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $adminMobile = getenv('ADMIN_MOBILE') ?: '7267995307';
    $adminEmail = getenv('ADMIN_EMAIL') ?: 'hridesh027@gmail.com';
    if (empty($email)) {
        $error = "Email address is required!";
    } else {
        if ($mobile === $adminMobile || $email === $adminEmail) {
            $role = 'admin';
        } else {
            $role = 'user';
        }

        try {
            $check = $pdo->prepare("SELECT id FROM users WHERE mobile = ? OR email = ?");
            $check->execute([$mobile, $email]);
            if ($check->rowCount() > 0) {
                $error = "Mobile or Email already registered!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, mobile, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $mobile, $email, $password, $role]);
                header("Location: login.php?registered=1");
                exit;
            }
        } catch (PDOException $e) { 
            $error = "Registration failed!"; 
        }
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
    <title>Create Account</title>
    <link class="rounded-pill" rel="icon" type="image/x-icon" href="/belt/assets/images/logo.ico">
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
                    <h4 class="fw-bold text-center mb-4 text-dark">Create Account</h4>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger py-2 small border-0 text-center"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <input type="text" name="name" class="form-control auth-input" placeholder="Full Name" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control auth-input" placeholder="Email Address" required>
                        </div>
                        <div class="mb-3">
                            <input type="tel" name="mobile" pattern="[0-9]{10}" class="form-control auth-input" placeholder="10-digit Mobile Number" required>
                        </div>
                        <div class="mb-4">
                            <input type="password" name="password" class="form-control auth-input" placeholder="Password" required>
                        </div>
                        
                        <button class="btn btn-gradient text-white w-100 py-2 border-0 mb-3 fw-semibold shadow-sm">
                            Register
                        </button>
                    </form>
                    
                    <a href="google-login.php" class="btn btn-outline-danger w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold shadow-sm mb-3" style="border-radius: 8px;">
                        <i class="bi bi-google"></i> Continue with Google
                    </a>
                    
                    <div class="text-center mt-3 small text-secondary">
                        Already have an account? <a href="login.php" class="text-decoration-none fw-semibold text-primary">Login</a>
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