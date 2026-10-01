<?php
require_once('auth_check.php');

$flash = "";
$flash_type = "ok";

// Handle Add Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? 'admin');

    if (strlen($password) < 6) {
        $flash = "Password must be at least 6 characters long.";
        $flash_type = "err";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $insert = mysqli_query($conn, "INSERT INTO admin (full_name, username, email, password, role) VALUES ('$full_name', '$username', '$email', '$hashed', '$role')");
        if ($insert) {
            $flash = "New administrator account created successfully!";
        } else {
            $flash = "Error creating admin: " . mysqli_error($conn);
            $flash_type = "err";
        }
    }
}

// Fetch all admins
$admins = mysqli_query($conn, "SELECT admin_id, username, email, full_name, role, created_at FROM admin ORDER BY admin_id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Accounts &bull; CastFlow Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include('menu.php'); ?>
    <div class="main" style="max-width: 1400px;">
        <div class="page-head">
            <div class="eyebrow">System Security &bull; Access Management</div>
            <h1>Administrator Accounts</h1>
            <p>Manage privileged users who have access to verification, requests, and database records.</p>
        </div>

        <?php if ($flash !== "") { ?>
            <div class="flash-msg <?php echo ($flash_type === 'ok') ? 'flash-ok' : 'flash-err'; ?>">
                <?php echo htmlspecialchars($flash); ?>
            </div>
        <?php } ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
            <!-- Admins List -->
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Username</th>
                            <th>Role Level</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($a = mysqli_fetch_assoc($admins)) { ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($a['full_name']); ?></strong>
                                <div style="font-size:12px; color:#777;"><?php echo htmlspecialchars($a['email']); ?></div>
                            </td>
                            <td>
                                <code>@<?php echo htmlspecialchars($a['username']); ?></code>
                            </td>
                            <td>
                                <span class="badge <?php echo ($a['role'] === 'superadmin') ? 'badge-superadmin' : ''; ?>">
                                    <?php echo ucfirst(htmlspecialchars($a['role'])); ?>
                                </span>
                            </td>
                            <td style="font-size:12.5px; color:#777;">
                                <?php echo date('M j, Y', strtotime($a['created_at'])); ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Admin Form -->
            <div class="panel" style="max-width:100%;">
                <h3 style="font-family:var(--font-serif); font-size:18px; margin-bottom:14px;">Add New Administrator</h3>
                <form method="POST" action="admins.php" class="form-grid">
                    <input type="hidden" name="add_admin" value="1">
                    
                    <div class="full">
                        <label>Full Name</label>
                        <input type="text" name="full_name" placeholder="John Doe" required>
                    </div>

                    <div class="full">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="johndoe" required>
                    </div>

                    <div class="full">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="john@castflow.com" required>
                    </div>

                    <div class="full">
                        <label>Password (min 6 characters)</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>

                    <div class="full">
                        <label>Role</label>
                        <select name="role">
                            <option value="admin">Standard Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-dark">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
