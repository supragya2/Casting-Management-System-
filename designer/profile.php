<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['designer_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);

    mysqli_query($conn, "UPDATE designer SET designer_name='$name', phone='$phone', experience='$experience', bio='$bio' WHERE designer_id = $id");
    $_SESSION['name'] = $name;
    header("Location: profile.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM designer WHERE designer_id = $id");
$designer = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
<title>My Profile - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <h1>My Profile</h1>
    </div>

    <div class="panel">
        <form class="form-grid" method="POST">
            <div class="full"><label>Studio / Designer Name</label>
                <input type="text" name="designer_name" required value="<?php echo $designer['designer_name']; ?>"></div>
            <div><label>Email</label>
                <input type="text" value="<?php echo $designer['email']; ?>" disabled></div>
            <div><label>Phone</label>
                <input type="text" name="phone" value="<?php echo $designer['phone']; ?>"></div>
            <div class="full"><label>Experience</label>
                <input type="text" name="experience" value="<?php echo $designer['experience']; ?>"></div>
            <div class="full"><label>Bio</label>
                <textarea name="bio"><?php echo $designer['bio']; ?></textarea></div>
            <div class="form-actions">
                <button type="submit" class="btn btn-dark">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</div>
</body>
</html>
