<?php
session_start();
require_once('../config.php');

// If already logged in as admin, go to dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = mysqli_real_escape_string($conn, trim($_POST['identifier']));
    $password = $_POST['password'];

    // Check admin credentials
    $stmt = mysqli_query($conn, "SELECT * FROM admin WHERE email = '$identifier' OR username = '$identifier' LIMIT 1");
    if ($stmt && $admin = mysqli_fetch_assoc($stmt)) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['name'] = $admin['full_name'];
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_level'] = $admin['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid password. Please check your credentials.";
        }
    } else {
        $error = "No admin account found with that email or username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - CastFlow Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-login-badge {
            display: inline-block;
            background: rgba(201, 161, 59, 0.15);
            color: var(--gold);
            border: 1px solid var(--gold);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="brand-mark">CASTFLOW <span style="font-size:12px; color:var(--gold); font-family:var(--font-body); letter-spacing:2px; vertical-align:middle; margin-left:6px;">ADMIN CONSOLE</span></div>
    <div class="auth-split">
        <div class="auth-form-side">
            <div class="admin-login-badge">Restricted Access</div>
            <h1>Administrator Portal</h1>
            <h2>Sign in to oversee casting requests, users, and verification</h2>

            <form class="auth-form" method="POST" action="login.php">
                <?php if ($error != "") { ?>
                    <div class="error-box full"><?php echo htmlspecialchars($error); ?></div>
                <?php } ?>

                <div class="field full">
                    <label>Admin Email or Username</label>
                    <input type="text" name="identifier" id="adminId" placeholder="admin@castflow.com" required autofocus>
                </div>
                <div class="field full">
                    <label>Master Password</label>
                    <input type="password" name="password" id="adminPass" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-submit">Enter Admin Console</button>
            </form>

            <div class="demo-fill-card">
              
                <button type="button" onclick="document.getElementById('adminId').value='admin@castflow.com'; document.getElementById('adminPass').value='admin123';">Fill Demo</button>
            </div>

            <p class="auth-switch" style="margin-top: 24px;">
                <a href="../login.php" style="color:var(--muted); text-decoration:none;">&larr; Return to Model & Designer Login</a>
            </p>
        </div>
        <div class="auth-visual" style="background-image:url(../login.jpg);"></div>
    </div>
</div>
</body>
</html>
