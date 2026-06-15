<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /belt/pages/auth/login.php");
    exit;
}

try {
    // profile_pic column adds to dynamic dataset
    $users = $pdo->query("SELECT id, name, email, mobile, role, profile_pic, created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
}

$default_avatar = "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELDURATO - User Base</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        /* Mobile optimization for text and padding */
        @media (max-width: 768px) {
            .table-mobile-compact th, 
            .table-mobile-compact td {
                padding: 0.5rem 0.4rem !important; /* Padding kam ki taaki horizontal scroll kam ho */
                font-size: 11px !important;       /* Pure table ka font size mobile par chota kiya */
            }
            .avatar-img {
                width: 28px !important;           /* Mobile par image size choti */
                height: 28px !important;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <?php include '../adminSidebar.php'; ?>

        <div class="col-lg-10 p-2 offset-lg-2">
            <div class="bg-primary bg-gradient p-3 text-white shadow-sm d-flex justify-content-between align-items-center mb-4 rounded-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-light d-lg-none px-2 py-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <i class="ri-menu-2-line fs-6 m-0 align-middle"></i>
                    </button>
                    <div>
                        <h5 class="fw-bold m-0 fs-6">User Base Control</h5>
                        <div style="font-size: 11px;" class="opacity-75"><?= date('M d, Y') ?></div>
                    </div>
                </div>
                <a href="../../index.php" class="nav-link-custom m-0 text-white d-flex align-items-center gap-1 sidebar-text-sm" style="font-size: 12px;"><i class="ri-store-2-line"></i>View Shop</a>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-4 rounded-3 bg-white">
                <div class="bg-primary bg-gradient text-white p-3 fw-bold d-flex justify-content-between align-items-center">
                    <span class="m-0 small"><i class="ri-group-line me-1"></i> Registered Accounts Flow</span>
                    <span class="badge bg-white text-dark rounded-pill fw-bold px-2 py-1" style="font-size: 10px;">Total: <?= count($users) ?></span>
                </div>
                
                <div class="p-1 p-md-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-mobile-compact">
                            <thead class="table-light text-muted text-uppercase" style="font-size: 11px;">
                                <tr>
                                    <th>UID</th>
                                    <th>Avatar</th>
                                    <th>Client Identity</th>
                                    <th>Secure Mail</th>
                                    <th>Contact</th>
                                    <th>Access level</th>
                                    <th>Joined Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($users)): foreach($users as $u): 
                                    $role = strtolower($u['role'] ?? 'user');
                                    $roleBadge = ($role === 'admin') ? 'bg-danger text-white' : 'bg-light text-dark border';
                                    $user_pic = !empty($u['profile_pic']) ? $u['profile_pic'] : $default_avatar;
                                ?>
                                <tr>
                                    <td class="fw-bold text-primary">#ELD-<?= $u['id'] ?></td>
                                    <td>
                                        <img src="<?= $user_pic ?>" class="shadow-sm rounded-circle object-fit-cover avatar-img" alt="Pic" style="width: 34px; height: 34px; border: 2px solid #e2e8f0;">
                                    </td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($u['name']) ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="font-monospace text-secondary"><?= !empty($u['mobile']) ? htmlspecialchars($u['mobile']) : '—' ?></td>
                                    <td>
                                        <span class="badge <?= $roleBadge ?> text-uppercase rounded-pill px-2 py-0.5" style="font-size: 9px; font-weight:700;"><?= $role ?></span>
                                    </td>
                                    <td class="text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted fw-bold small">No entries found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>