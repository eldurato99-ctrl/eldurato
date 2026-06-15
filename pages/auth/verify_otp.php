<!-- pages\auth\verify_otp.php -->
<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['reset_mobile'])) {
    header("Location: forget_password.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $otp = trim($_POST['otp']);
    $mobile = $_SESSION['reset_mobile'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE mobile=?
        AND otp=?
        AND otp_expiry > NOW()
    ");

    $stmt->execute([$mobile,$otp]);

    if ($stmt->rowCount()) {

        $_SESSION['otp_verified'] = true;

        header("Location: reset_password.php");
        exit;

    } else {
        $error = "Invalid OTP";
    }
}
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify OTP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-2">
<div class="row justify-content-center">
<div class="col-md-5">

<div class="card">
<div class="card-body">

<h3>Verify OTP</h3>

<?php if(isset($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<input
type="text"
name="otp"
class="form-control mb-3"
placeholder="Enter OTP"
required
>

<button class="btn btn-success w-100">
Verify OTP
</button>

</form>

</div>
</div>

</div>
</div>
</div>
<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>