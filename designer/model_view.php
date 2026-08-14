<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}

$designer_id = $_SESSION['user_id'];
$mid = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM model WHERE model_id = $mid");
$model = mysqli_fetch_assoc($result);

$photos = mysqli_query($conn, "SELECT * FROM photos WHERE model_id = $mid");

$my_designs = mysqli_query($conn, "SELECT * FROM design WHERE designer_id = $designer_id");

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $design_id = $_POST['design_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if ($design_id != "") {
        $sql = "INSERT INTO invitation (designer_id, model_id, design_id, description) VALUES ($designer_id, $mid, $design_id, '$message')";
        mysqli_query($conn, $sql);
        $msg = "Invitation sent to " . $model['model_name'] . ".";
    } else {
        $msg = "Please choose a design first.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $model['model_name']; ?> - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <?php if ($msg != "") { ?><div class="flash-msg flash-ok"><?php echo $msg; ?></div><?php } ?>

    <div class="profile-header">
        <div class="avatar"><?php echo strtoupper(substr($model['model_name'],0,1)); ?></div>
        <div>
            <h2><?php echo $model['model_name']; ?></h2>
            <div class="sub"><?php echo $model['gender']; ?> &middot; Age <?php echo $model['age']; ?> &middot; <?php echo $model['height']; ?>cm &middot; <?php echo $model['weight']; ?>kg</div>
        </div>
    </div>

    <h3 style="font-family:var(--font-serif); font-size:20px; margin-bottom:14px;">Portfolio</h3>
    <?php if (mysqli_num_rows($photos) == 0) { ?>
        <div class="empty-state" style="margin-bottom:36px;">No portfolio photos yet.</div>
    <?php } else { ?>
        <div class="card-grid" style="margin-bottom:36px;">
            <?php while ($p = mysqli_fetch_assoc($photos)) { ?>
                <div class="item-card"><div class="thumb"><img src="../uploads/photos/<?php echo $p['image']; ?>"></div></div>
            <?php } ?>
        </div>
    <?php } ?>

    <h3 style="font-family:var(--font-serif); font-size:20px; margin-bottom:14px;">Send an Invitation</h3>
    <div class="panel">
        <form class="form-grid" method="POST">
            <div class="full">
                <label>Which design?</label>
                <select name="design_id" required>
                    <option value="">Select a design</option>
                    <?php while ($d = mysqli_fetch_assoc($my_designs)) { ?>
                        <option value="<?php echo $d['design_id']; ?>"><?php echo $d['design_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="full">
                <label>Message</label>
                <textarea name="message" placeholder="Tell them about the shoot..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-dark">Send Invitation</button>
            </div>
        </form>
    </div>
</div>
</div>
</body>
</html>
