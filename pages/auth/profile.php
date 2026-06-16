<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';
require_once '../../config/cloudinary.php';
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
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) { header("Location: /pages/auth/logout.php"); exit; }
} catch (PDOException $e) { $error_msg = "Database fault."; }

$user_role = strtolower($user['role'] ?? 'user');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $mobile = trim($_POST['mobile']); 
    $profile_pic = $user['profile_pic'] ?? ''; 

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['profile_image']['name'];
        $filesize = $_FILES['profile_image']['size']; // फ़ाइल का साइज़ निकाला
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error_msg = "Invalid image format!";
        } elseif ($filesize > 102400) { // 100 KB से ज्यादा होने पर एरर
            $error_msg = "Image size must be less than 100KB!";
        } else {
            try {
                $cloudFolder = ($user_role === 'admin') ? 'belt_store/admin' : 'belt_store/users';
                $uploadResult = $cloudinary->uploadApi()->upload($_FILES['profile_image']['tmp_name'], [
                    'folder' => $cloudFolder,
                    'transformation' => [
                        ['width' => 250, 'height' => 250, 'crop' => 'fill', 'gravity' => 'face']
                    ]
                ]);
                $profile_pic = $uploadResult['secure_url'];
            } catch (Exception $e) {
                $error_msg = "Upload Error: " . $e->getMessage();
            }
        }
    }

    if (empty($error_msg)) {
        if (empty($name) || empty($mobile)) {
            $error_msg = "All fields are required!";
        } else {
            try {
                // Email check query hata di gayi hai kyunki email update hi nahi ho raha hai
                $update = $pdo->prepare("UPDATE users SET name = ?, mobile = ?, profile_pic = ? WHERE id = ?");
                if ($update->execute([$name, $mobile, $profile_pic, $user_id])) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_pic'] = $profile_pic; 
                    $_SESSION['success_msg'] = "Identity profile synced successfully!";
                    header("Location: profile.php");
                    exit;
                }
            } catch (PDOException $e) {
                $error_msg = "Update failed: " . $e->getMessage();
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error_msg = "All fields are required!";
    } elseif (strlen($new_pass) < 6) { 
        $error_msg = "New password must be at least 6 characters long!";
    } elseif ($new_pass !== $confirm_pass) {
        $error_msg = "New password and confirm password do not match!";
    } else {
        try {
            if (password_verify($current_pass, $user['password'])) {
                $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                $pass_update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($pass_update->execute([$hashed_new_pass, $user_id])) {
                    $_SESSION['success_msg'] = "Password changed successfully!";
                    header("Location: profile.php");
                    exit;
                }
            } else {
                $error_msg = "Incorrect current password!";
            }
        } catch (PDOException $e) {
            $error_msg = "Password update failed!";
        }
    }
}

$default_avatar = "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";
$final_avatar = !empty($user['profile_pic']) ? $user['profile_pic'] : $default_avatar;

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .google-card { background: #ffffff; border-radius: 16px; border: none !important; box-shadow: 0 8px 24px rgba(63, 81, 181, 0.03); overflow: hidden; }
    .card-ribbon { height: 4px; width: 100%; background: linear-gradient(90deg, #4f46e5, #6366f1); }
    .card-ribbon-rose { height: 4px; width: 100%; background: linear-gradient(90deg, #f43f5e, #ec4899); }
    .read-only-text { font-size: 14.5px; font-weight: 600; padding: 8px 0; display: block; color: #1e293b; }
    .d-none-ux { display: none !important; }
    .form-control { border: 1px solid #e2e8f0 !important; background-color: #f8fafc !important; border-radius: 10px !important; padding: 10px 14px; font-size: 14px; font-weight: 500; }
    .form-control:focus { background-color: #ffffff !important; border-color: #4f46e5 !important; outline: 0 !important; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12) !important; }
</style>

<div class="container py-3 py-md-4">

    <?php if(!empty($success_msg)): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-3 small py-2.5" role="alert">
            <i class="ri-checkbox-circle-fill me-1"></i> <?= htmlspecialchars($success_msg) ?><button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" style="padding: 0.85rem;"></button>
        </div>
    <?php endif; ?>
    <?php if(!empty($error_msg)): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-3 small py-2.5" role="alert">
            <i class="ri-error-warning-fill me-1"></i> <?= htmlspecialchars($error_msg) ?><button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" style="padding: 0.85rem;"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="m-0">
        <div class="row g-3">
            
            <div class="col-12 col-md-4 col-lg-3">
                <div class="google-card p-4 text-center h-100 d-flex flex-column align-items-center justify-content-center border">
                    <img src="<?= $final_avatar ?>" class="rounded-circle border shadow-sm mb-3" id="imgPreview" style="width: 100px; height: 100px; object-fit: cover;">
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Profile Image</h6>
                    <div class="w-100 d-none-ux editable-zone mt-2">
                  <input type="file" name="profile_image" class="form-control form-control-sm" accept="image/*" id="imageInput" style="font-size: 12px;">
            <div class="text-muted" style="font-size: 10px;">Max size: 100KB</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-8 col-lg-9">
                <div class="google-card h-100">
                    <div class="card-ribbon"></div>
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                            <h6 class="fw-bold m-0 text-dark"><i class="ri-profile-line text-primary me-1 fs-5"></i> Personal Details</h6>
                            <button type="button" class="btn btn-sm btn-light border fw-bold px-3 rounded-3 d-flex align-items-center gap-1" id="editActionBtn" onclick="activateUXEngine()" style="font-size:12px;">
                                <i class="ri-edit-box-line text-primary"></i> Edit Details
                            </button>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase">Full Name</label>
                                <span class="read-only-text" id="view_name"><?= htmlspecialchars($user['name'] ?? '') ?></span>
                                <input type="text" name="name" id="input_name" class="form-control d-none-ux editable-zone" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">Email Address</label>
                                <span class="read-only-text"><?= htmlspecialchars($user['email'] ?? '') ?></span>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">Phone Number</label>
                                <span class="read-only-text" id="view_mobile"><?= htmlspecialchars($user['mobile'] ?? '') ?></span>
                                <input type="tel" name="mobile" id="input_mobile" class="form-control d-none-ux editable-zone" value="<?= htmlspecialchars($user['mobile'] ?? '') ?>" pattern="[0-9]{10}" required>
                            </div>
                        </div>
                        
                        <div class="d-none-ux editable-zone gap-2 mt-4 pt-3 border-top justify-content-end" id="actionStrip">
                            <button type="button" class="btn btn-sm btn-light border rounded-3 px-3 fw-semibold text-secondary" style="font-size:12px;" onclick="window.location.reload();">Cancel</button>
                            <button type="submit" name="update_profile" class="btn btn-sm btn-primary rounded-3 px-4 fw-bold" style="font-size:12px; background-color: #4f46e5; border:none;">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <div class="google-card mt-4 mb-5">
        <div class="card-ribbon-rose"></div>
        <div class="p-3 px-4 d-flex justify-content-between align-items-center bg-white border-bottom">
            <span class="fw-bold text-dark small text-uppercase"><i class="ri-key-2-fill text-danger me-1 fs-5"></i> Security Credentials</span>
            <button class="btn btn-sm btn-light border fw-bold px-3 rounded-3 text-secondary" style="font-size:12px;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurityCore">
                Manage Password
            </button>
        </div>

        <div class="collapse" id="collapseSecurityCore">
            <div class="p-4 bg-white">
                <form method="POST" action="" class="m-0">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Current Password</label>
                            <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Min 6 chars" minlength="6" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" name="change_password" class="btn btn-sm btn-danger fw-bold px-4 rounded-3" style="font-size:12px; background-color: #f43f5e; border: none;">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function activateUXEngine() {
        document.querySelectorAll('#view_name, #view_mobile, #editActionBtn').forEach(el => el.classList.add('d-none-ux'));
        document.querySelectorAll('.editable-zone').forEach(el => {
            el.classList.remove('d-none-ux');
            if (el.id === 'actionStrip') el.style.display = 'flex';
        });
        document.getElementById('input_name').focus();
    }

   document.getElementById('imageInput').onchange = (e) => {
    const [file] = e.target.files;
    if (file) {
        if (file.size > 102400) { 
            alert("This image is too heavy! Please select an image under 100KB.");
            e.target.value = "";
            return;
        }
        document.getElementById('imgPreview').src = URL.createObjectURL(file);
    }
}

</script>
<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
