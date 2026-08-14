<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'model') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['model_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $age = $_POST['age'];
    $height = mysqli_real_escape_string($conn, $_POST['height']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);

    if ($age == "") { $age = 0; }

    mysqli_query($conn, "UPDATE model SET model_name='$name', phone='$phone', gender='$gender', age='$age', height='$height', weight='$weight', experience='$experience', bio='$bio' WHERE model_id = $id");
    $_SESSION['name'] = $name;
    header("Location: profile.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM model WHERE model_id = $id");
$model = mysqli_fetch_assoc($result);
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
            <div class="full"><label>Full Name</label>
                <input type="text" name="model_name" required value="<?php echo $model['model_name']; ?>"></div>
            <div><label>Email</label>
                <input type="text" value="<?php echo $model['email']; ?>" disabled></div>
            <div><label>Phone</label>
                <input type="text" name="phone" value="<?php echo $model['phone']; ?>"></div>
            <div><label>Gender</label>
                <select name="gender">
                    <option value="">Select</option>
                    <option value="Female" <?php if ($model['gender']=='Female') echo 'selected'; ?>>Female</option>
                    <option value="Male" <?php if ($model['gender']=='Male') echo 'selected'; ?>>Male</option>
                    <option value="Non-binary" <?php if ($model['gender']=='Non-binary') echo 'selected'; ?>>Non-binary</option>
                </select></div>
            <div><label>Age</label>
                <input type="number" name="age" value="<?php echo $model['age']; ?>"></div>
            <div><label>Height (cm)</label>
                <input type="text" name="height" value="<?php echo $model['height']; ?>"></div>
            <div><label>Weight (kg)</label>
                <input type="text" name="weight" value="<?php echo $model['weight']; ?>"></div>
            <div class="full"><label>Experience</label>
                <input type="text" name="experience" value="<?php echo $model['experience']; ?>"></div>
            <div class="full"><label>Bio</label>
                <textarea name="bio"><?php echo $model['bio']; ?></textarea></div>
            <div class="form-actions">
                <button type="submit" class="btn btn-dark">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</div>
</body>
</html>
