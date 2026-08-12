<?php
session_start();
include('config.php');

$role = "designer";
if (isset($_GET['role']) && $_GET['role'] == "model") {
    $role = "model";
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if ($role == "model") {
        $result = mysqli_query($conn, "SELECT * FROM model WHERE email = '$email'");
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
        } else {
            $_SESSION['user_id'] = $user['designer_id'];
            $_SESSION['name'] = $user['designer_name'];
            $_SESSION['role'] = "designer";
            header("Location: designer/dashboard.php");
            exit;
        }

    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Log In - CastFlow</title>
<link rel="stylesheet" href="css/style.css">
<!-- <style>
    .right{
    width:700px;
}

.top{
    width:620npx;
    height:350px;
    object-fit:cover;
    margin-bottom:20px;
}

.bottom{
    display:flex;
    gap:20px;
}

.bottom img{
    width:300px;
    height:300px;
    object-fit:cover;
}
</style> -->
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
            </div>

            <p class="auth-switch">Don't have an account? <a href="signup.php?role=<?php echo $role; ?>">Sign up</a></p>

            <form class="auth-form" method="POST" action="login.php?role=<?php echo $role; ?>">

                <?php if ($error != "") { ?>
                    <div class="error-box full"><?php echo $error; ?></div>
                <?php } ?>

                <div class="field full"><label>Email</label>
                    <input type="email" name="email" placeholder="Email" required></div>
                <div class="field full"><label>Password</label>
                    <input type="password" name="password" placeholder="Password" required></div>

                <button type="submit" class="btn-submit">Log In</button>
            </form>
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
