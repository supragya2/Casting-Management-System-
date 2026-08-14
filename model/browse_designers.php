<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'model') {
    header("Location: ../login.php");
    exit;
}

$designers = mysqli_query($conn, "SELECT * FROM designer ORDER BY designer_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Browse Designers - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <div class="eyebrow">Discover</div>
        <h1>Browse Designers</h1>
    </div>

    <?php if (mysqli_num_rows($designers) == 0) { ?>
        <div class="empty-state">No designers found.</div>
    <?php } else { ?>
        <div class="card-grid">
        <?php while ($d = mysqli_fetch_assoc($designers)) { ?>
            <div class="item-card">
                <div class="thumb"><?php echo strtoupper(substr($d['designer_name'],0,1)); ?></div>
                <div class="body">
                    <h3><?php echo $d['designer_name']; ?></h3>
                    <div class="meta"><?php echo $d['experience']; ?></div>
                    <div class="actions">
                        <a href="designer_view.php?id=<?php echo $d['designer_id']; ?>" class="btn btn-sm btn-dark">View Designs</a>
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
