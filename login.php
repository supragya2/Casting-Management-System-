<?php
session_start();
include('config.php');

$role = "designer";
if (isset($_GET['role'])) {
    if ($_GET['role'] == "model") {
        $role = "model";
    } elseif ($_GET['role'] == "admin") {
        $role = "admin";
    }
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    if ($role == "model") {
        $result = mysqli_query($conn, "SELECT * FROM model WHERE email = '$email'");
    } elseif ($role == "admin") {
        $result = mysqli_query($conn, "SELECT * FROM admin WHERE email = '$email' OR username = '$email'");
    } else {
        $result = mysqli_query($conn, "SELECT * FROM designer WHERE email = '$email'");
    }

    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {

        if ($role == "model") {
            $_SESSION['user_id'] = $user['model_id'];
            $_SESSION['name'] = $user['model_name'];
            $_SESSION['role'] = "model";
            header("Location: model/dashboard.php");
            exit;
        } elseif ($role == "admin") {
            $_SESSION['user_id'] = $user['admin_id'];
            $_SESSION['name'] = $user['full_name'];
            $_SESSION['role'] = "admin";
            header("Location: admin/dashboard.php");
            exit;
        } else {
            $_SESSION['user_id'] = $user['designer_id'];
            $_SESSION['name'] = $user['designer_name'];
            $_SESSION['role'] = "designer";
            header("Location: designer/dashboard.php");
            exit;
        }

    } else {
        $error = "Invalid credentials. Please verify your details.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Log In - CastFlow</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-shell">
    <div class="brand-mark">CASTFLOW</div>
    <div class="auth-split">
        <div class="auth-form-side">
            <h1>Welcome back!</h1>
            <h2>Log in to your account</h2>

            <div class="role-pill-toggle">
                <a href="login.php?role=designer" class="<?php if ($role == 'designer') echo 'active'; ?>">Designer</a>
                <a href="login.php?role=model" class="<?php if ($role == 'model') echo 'active'; ?>">Model</a>
                <a href="login.php?role=admin" class="<?php if ($role == 'admin') echo 'active'; ?>">Admin</a>
            </div>

            <?php if ($role != 'admin') { ?>
                <p class="auth-switch">Don't have an account? <a href="signup.php?role=<?php echo $role; ?>">Sign up</a></p>
            <?php } else { ?>
                <p class="auth-switch">Admin control portal &bull; <a href="admin/login.php">Direct Admin Link</a></p>
            <?php } ?>

            <form class="auth-form" method="POST" action="login.php?role=<?php echo $role; ?>">

                <?php if ($error != "") { ?>
                    <div class="error-box full"><?php echo $error; ?></div>
                <?php } ?>

                <div class="field full"><label><?php echo ($role == 'admin') ? 'Email or Username' : 'Email'; ?></label>
                    <input type="text" name="email" id="loginEmail" placeholder="<?php echo ($role == 'admin') ? 'admin@castflow.com' : 'Email'; ?>" required></div>
                <div class="field full"><label>Password</label>
                    <input type="password" name="password" id="loginPassword" placeholder="Password" required></div>

                <button type="submit" class="btn-submit">Log In</button>
            </form>

            <?php if ($role == 'admin') { ?>
            <div class="demo-fill-card">
                <div>
                    <strong>Default Admin:</strong> admin@castflow.com / admin123
                </div>
                <button type="button" onclick="document.getElementById('loginEmail').value='admin@castflow.com'; document.getElementById('loginPassword').value='admin123';">Fill Demo</button>
            </div>
            <?php } ?>
        </div>




    <!-- <div class="right">

        <img src="top.jpg" class="top">

        <div class="bottom">
            <img src="left.jpg">
            <img src="right.jpg">
        </div>

    </div> -->



        <div class="auth-visual" style="background-image:url(login.jpg);"></div>
        
    </div>


</div>
</body>
</html>
