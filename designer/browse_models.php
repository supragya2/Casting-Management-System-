<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}

$models = mysqli_query($conn, "SELECT * FROM model ORDER BY model_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Browse Models - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <div class="eyebrow">Casting talent</div>
        <h1>Browse Models</h1>
        <p>Find the right model for your next design.</p>
    </div>

    <?php if (mysqli_num_rows($models) == 0) { ?>
        <div class="empty-state">No models found.</div>
    <?php } else { ?>
        <div class="card-grid">
        <?php while ($m = mysqli_fetch_assoc($models)) { ?>
            <div class="item-card">
                <div class="thumb"><?php echo strtoupper(substr($m['model_name'],0,1)); ?></div>
                <div class="body">
                    <h3>
                        <?php echo $m['model_name']; ?>
                        <?php if (!empty($m['is_verified'])) { ?>
                            <span class="badge badge-verified" style="font-size:10px; margin-left:4px;">✓ Verified</span>
                        <?php } ?>
                    </h3>
                    <div class="meta"><?php echo $m['gender']; ?> &middot; Age <?php echo $m['age']; ?></div>
                    <div class="actions">
                        <a href="model_view.php?id=<?php echo $m['model_id']; ?>" class="btn btn-sm btn-dark">View Profile</a>
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
