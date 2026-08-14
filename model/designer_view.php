<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'model') {
    header("Location: ../login.php");
    exit;
}

$model_id = $_SESSION['user_id'];
$did = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM designer WHERE designer_id = $did");
$designer = mysqli_fetch_assoc($result);

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $design_id = $_POST['design_id'];

    $check = mysqli_query($conn, "SELECT * FROM request WHERE model_id = $model_id AND design_id = $design_id AND status = 'pending'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "You already have a pending request for this design.";
    } else {
        mysqli_query($conn, "INSERT INTO request (model_id, designer_id, design_id) VALUES ($model_id, $did, $design_id)");
        $msg = "Request sent to " . $designer['designer_name'] . ".";
    }
}

$designs = mysqli_query($conn, "SELECT * FROM design WHERE designer_id = $did ORDER BY design_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $designer['designer_name']; ?> - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <?php if ($msg != "") { ?><div class="flash-msg flash-ok"><?php echo $msg; ?></div><?php } ?>

    <div class="profile-header">
        <div class="avatar"><?php echo strtoupper(substr($designer['designer_name'],0,1)); ?></div>
        <div>
            <h2><?php echo $designer['designer_name']; ?></h2>
            <div class="sub"><?php echo $designer['experience']; ?></div>
        </div>
    </div>

    <h3 style="font-family:var(--font-serif); font-size:20px; margin-bottom:14px;">Designs</h3>
    <?php if (mysqli_num_rows($designs) == 0) { ?>
        <div class="empty-state">No designs yet.</div>
    <?php } else { ?>
        <div class="card-grid">
        <?php while ($d = mysqli_fetch_assoc($designs)) { ?>
            <div class="item-card">
                <div class="thumb">
                    <?php if ($d['image'] != "") { ?>
                        <img src="../uploads/designs/<?php echo $d['image']; ?>">
                    <?php } else { ?>
                        No image
                    <?php } ?>
                </div>
                <div class="body">
                    <h3><?php echo $d['design_name']; ?></h3>
                    <div class="meta"><?php echo $d['type']; ?> &middot; <?php echo $d['fabric']; ?></div>
                    <div class="actions">
                        <form method="POST">
                            <input type="hidden" name="design_id" value="<?php echo $d['design_id']; ?>">
                            <button type="submit" class="btn btn-sm btn-dark">Request to Model</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
    <?php } ?>
</div>
</div>
</body>
</html>
