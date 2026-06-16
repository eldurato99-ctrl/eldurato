<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';

$show_form = false;
$error = "";
$success = "";
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $error = "Invalid request! Token is missing.";
} else {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND token_expire > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "This reset link is invalid or has expired! Please request a new one.";
    } else {
        $show_form = true;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $show_form) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        $new_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expire = NULL WHERE id = ?");
        $stmt->execute([$new_password, $user['id']]);
        $success = "Password reset successfully! Redirecting to login page...";
        $show_form = false; // फॉर्म छुपा दें
        header("refresh:3;url=login.php");
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
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-2">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4 text-dark fw-bold">Reset Password</h3>
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger py-2 small"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success py-2 small"><?= $success ?></div>
                    <?php endif; ?>

                    <?php if($show_form): ?>
                        <form method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label small text-secondary">New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter New Password" required minlength="6">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small text-secondary">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password" required minlength="6">
                            </div>
                            <button class="btn btn-success w-100 py-2 fw-semibold">Update Password</button>
                        </form>
                    <?php else: ?>
                        <div class="text-center mt-3">
                            <a href="forgot_password.php" class="btn btn-outline-primary btn-sm">Request New Link</a>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
