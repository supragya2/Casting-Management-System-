<?php
session_start();
// if already logged in, send to the right dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'designer') {
        header("Location: designer/dashboard.php");
        exit;
    } else {
        header("Location: model/dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>CastFlow</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-shell">
    <div class="brand-mark">CASTFLOW</div>
    <div class="landing">
        <h1>Welcome to CastFlow!</h1>
        <div class="tagline">Choose Your Role</div>

        <div class="role-grid">
            <div class="role-card" style="background-image:url('https://images.unsplash.com/photo-1558769132-cb1aea458c5e?q=80&w=1200');">
                <div class="role-copy">Find models for your design and bring your designs to life</div>
                <a href="signup.php?role=designer" class="role-btn">Designer</a>
            </div>
            <div class="role-card" style="background-image:url('https://images.unsplash.com/photo-1512310604669-443f26c35f52?q=80&w=1200');">
                <div class="role-copy">Find your work as a model for fashion shows and runway events</div>
                <a href="signup.php?role=model" class="role-btn">Model</a>
            </div>
        </div>

        <p style="margin-top:40px; font-family:var(--font-serif); color:var(--muted);">
            Already have an account? <a href="login.php" style="color:#7db8ff; text-decoration:underline;">Log in</a>
        </p>
    </div>
</div>
</body>
</html>
