<?php
session_start();
require_once '../config/database.php';
require_once '../config/cloudinary.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /pages/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = $_SESSION['success_msg'] ?? "";
$error_msg = $_SESSION['error_msg'] ?? "";
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$admin) { header("Location: /pages/auth/logout.php"); exit; }
} catch (PDOException $e) {
    $error_msg = "DB Error: " . $e->getMessage();
}

function redirectWithAlert($status, $msg) {
    $_SESSION[$status] = $msg;
    header("Location: profile.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $mobile = trim($_POST['mobile']);
        $email = trim($_POST['email']);
        $profile_pic = $admin['profile_pic'] ?? '';

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                try {
                    $upload = $cloudinary->uploadApi()->upload($_FILES['profile_image']['tmp_name'], [
                        'folder' => 'belt_store/admin',
                        'transformation' => [['width' => 250, 'height' => 250, 'crop' => 'fill', 'gravity' => 'face']]
                    ]);
                    $profile_pic = $upload['secure_url'];
                } catch (Exception $e) { $error_msg = "Upload Error: " . $e->getMessage(); }
            } else { $error_msg = "Invalid image format!"; }
        }

        if (empty($error_msg)) {
            try {
                $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $chk->execute([$email, $user_id]);
                if ($chk->rowCount() > 0) {
                    $error_msg = "Email already in use!";
                } else {
                    $up = $pdo->prepare("UPDATE users SET name = ?, mobile = ?, email = ?, profile_pic = ? WHERE id = ?");
                    if ($up->execute([$name, $mobile, $email, $profile_pic, $user_id])) {
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_pic']  = $profile_pic;
                        redirectWithAlert('success_msg', "Profile updated successfully!");
                    }
                }
            } catch (PDOException $e) { $error_msg = "Update failed!"; }
        }
    }

    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        if ($new !== $_POST['confirm_password']) {
            $error_msg = "Passwords do not match!";
        } else {
            try {
                if (password_verify($current, $admin['password'])) {
                    $pass_up = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($pass_up->execute([password_hash($new, PASSWORD_DEFAULT), $user_id])) {
                        redirectWithAlert('success_msg', "Password changed successfully!");
                    }
                } else { $error_msg = "Incorrect current password!"; }
            } catch (PDOException $e) { $error_msg = "Password update failed!"; }
        }
    }
}

$final_avatar = !empty($admin['profile_pic']) ? $admin['profile_pic'] : "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELDURATO - Admin Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet"> 
</head>
<body>

<div class="container-fluid">
    <div class="row">
     
        <?php include 'adminSidebar.php'; ?>

        <div class="col-lg-10 p-2 offset-lg-2">
            
            <div class="bg-primary bg-gradient p-3 text-white shadow-sm d-flex justify-content-between align-items-center mb-4 rounded-3">
                <div>
                    <h5 class="fw-bold m-0 fs-6">Admin Identity Console</h5>
                    <div class="opacity-75 small"><?= date('M d, Y') ?></div>
                </div>
                <a href="../index.php" class="nav-link-custom m-0 text-white d-flex align-items-center gap-1 small"><i class="ri-store-2-line"></i>View Shop</a>
            </div>

            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <?= htmlspecialchars($success_msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <?= htmlspecialchars($error_msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
                <div class="row g-3">
                    
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center h-100 bg-white rounded-3">
                            <div class="bg-light p-2 px-3 fw-bold border-bottom text-secondary text-start small">
                                <span><i class="ri-image-line me-1"></i> Avatar Configuration</span>
                            </div>
                            <div class="p-4 d-flex flex-column justify-content-center align-items-center my-auto">
                                <img src="<?= $final_avatar ?>" class="shadow-sm rounded-circle mb-3 object-fit-cover" id="imgPreview" alt="Avatar" style="width:120px; height:120px;">
                                <div class="mb-2 d-none editable-zone w-100">
                                    <input type="file" name="profile_image" class="form-control form-control-sm" accept="image/*" id="imageInput">
                                </div>
                                <span class="badge bg-warning text-dark text-uppercase px-2.5 py-1.5 fw-bold mt-2" style="font-size:11px;"><?= htmlspecialchars($admin['role'] ?? 'Admin') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100 bg-white rounded-3">
                            <div class="bg-light p-2 px-3 fw-bold border-bottom d-flex justify-content-between align-items-center">
                                <span class="text-secondary small"><i class="ri-profile-line me-1"></i> Identity Details</span>
                                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold px-3 py-1" id="editActionBtn" onclick="activateUXEngine()" style="font-size:12px;">
                                    <i class="ri-edit-line me-1"></i> Edit Details
                                </button>
                            </div>
                            
                            <div class="p-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label text-muted fw-bold small mb-1">Admin Display Name</label>
                                        <div class="p-2 bg-light border rounded text-dark fw-medium view-zone" id="view_name"><?= htmlspecialchars($admin['name'] ?? '') ?></div>
                                        <input type="text" name="name" id="input_name" class="form-control d-none editable-zone" value="<?= htmlspecialchars($admin['name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted fw-bold small mb-1">Secure Email Routing</label>
                                        <div class="p-2 bg-light border rounded text-dark fw-medium view-zone" id="view_email"><?= htmlspecialchars($admin['email'] ?? '') ?></div>
                                        <input type="email" name="email" id="input_email" class="form-control d-none editable-zone" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted fw-bold small mb-1">Contact Number</label>
                                        <div class="p-2 bg-light border rounded text-dark fw-medium view-zone" id="view_mobile"><?= htmlspecialchars($admin['mobile'] ?? '') ?></div>
                                        <input type="tel" name="mobile" id="input_mobile" class="form-control d-none editable-zone" value="<?= htmlspecialchars($admin['mobile'] ?? '') ?>" pattern="[0-9]{10}" required>
                                    </div>
                                </div>
                                
                                <div class="d-none editable-zone gap-2 mt-4 pt-3 border-top justify-content-end" id="actionStrip">
                                    <button type="button" class="btn btn-sm btn-light border text-secondary px-3" onclick="window.location.reload();">Cancel</button>
                                    <button type="submit" name="update_profile" class="btn btn-sm btn-primary px-4 bg-gradient border-0" style="background-color: #4f46e5;">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

            <div class="card border-0 shadow-sm mt-4 bg-white rounded-3 overflow-hidden">
                <div class="bg-danger bg-gradient text-white p-2 px-3 fw-bold d-flex justify-content-between align-items-center">
                    <span class="small"><i class="ri-key-2-line me-1"></i> Security Crypt Access</span>
                    <button class="btn btn-sm btn-light fw-bold text-danger px-3 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurityCore" style="font-size:12px;">
                        Manage Password
                    </button>
                </div>

                <div class="collapse" id="collapseSecurityCore">
                    <div class="p-4">
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold small mb-1">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold small mb-1">New Password</label>
                                    <input type="password" name="new_password" class="form-control" placeholder="Min 6 chars" minlength="6" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold small mb-1">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                                </div>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-sm btn-danger fw-bold px-4 mt-3">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function activateUXEngine() {
        // Hiding read-only fields using Bootstrap 'd-none'
        document.querySelectorAll('.view-zone, #editActionBtn').forEach(el => el.classList.add('d-none'));
        
        // Showing inputs and control strip
        document.querySelectorAll('.editable-zone').forEach(el => {
            el.classList.remove('d-none');
            if (el.id === 'actionStrip') el.classList.add('d-flex');
        });
        document.getElementById('input_name').focus();
    }

    document.getElementById('imageInput').onchange = () => {
        const [file] = document.getElementById('imageInput').files;
        if (file) document.getElementById('imgPreview').src = URL.createObjectURL(file);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
