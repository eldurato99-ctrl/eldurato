<?php
require_once '../../config/functions.php'; 
require_once '../../config/database.php';
if (isset($_SESSION['user_id'])) {
    $role = strtolower($_SESSION['user_role'] ?? 'user');
    if ($role === 'admin') {
        header("Location: " . SITE_URL . "/admin/index.php");
    } else {
        header("Location: " . SITE_URL . "/pages/account/dashboard.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = trim($_POST['login_input']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE mobile = ? OR email = ?");
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_pic']  = $user['profile_pic'] ?? '';
            
            $role = strtolower($user['role'] ?? 'user');

            // बेहतर सुरक्षा और रोल चेकिंग
            if ($role === 'admin') {
                header("Location: " . SITE_URL . "/admin/index.php");
            } else {
                header("Location: " . SITE_URL . "/pages/account/dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid Credentials!";
        }
    } catch (PDOException $e) { 
        $error = "Login reference error."; 
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
    <title>Login</title>
    <link class="rounded-pill" rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>/images/logo.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="themes.css" rel="stylesheet">
</head>
<body class="auth-body">

<div class="container d-flex flex-column justify-content-center align-items-center flex-grow-1 my-5">
    <div class="row justify-content-center w-100">
        <div class="col-md-5 col-lg-4">
            <div class="card auth-card p-3 border-0">
                <div class="card-body">
                    <h4 class="fw-bold text-center mb-4 text-dark">Login</h4>
                    
                    <?php if(isset($_GET['registered'])): ?>
                        <div class="alert alert-success py-2 small border-0 text-center">Registration successful! Please login.</div>
                    <?php endif; ?>
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger py-2 small border-0 text-center"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <input type="text" name="login_input" class="form-control auth-input" placeholder="Mobile Number or Email" required>
                        </div>
                        <div class="mb-4">
                            <input type="password" name="password" class="form-control auth-input" placeholder="Password" required>
                        </div>
                        
                        <button class="btn btn-gradient text-white w-100 py-2 border-0 mb-3 fw-semibold shadow-sm">
                            Login
                        </button>
                    </form>
                    
                    <a href="google-login.php" class="btn btn-outline-danger w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold shadow-sm mb-3" style="border-radius: 8px;">
                        <i class="bi bi-google"></i> Continue with Google
                    </a>
                    
                    <div class="d-flex justify-content-between mt-3 small">
                        <a href="forgot_password.php" class="text-decoration-none text-secondary">Forgot Password?</a>
                        <a href="register.php" class="text-decoration-none fw-semibold text-primary">Create Account</a>
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