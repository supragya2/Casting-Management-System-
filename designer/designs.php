<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];
$designs = mysqli_query($conn, "SELECT * FROM design WHERE designer_id = $id ORDER BY design_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>My Designs - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head" style="display:flex; justify-content:space-between; align-items:flex-end;">
        <div>
            <div class="eyebrow">Your collection</div>
            <h1>My Designs</h1>
            <p>Upload garments so models can discover and request them.</p>
        </div>
        <a href="design_form.php" class="btn btn-dark">+ Add Design</a>
    </div>

    <?php if (mysqli_num_rows($designs) == 0) { ?>
        <div class="empty-state">You haven't added any designs yet.</div>
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
                    <div class="meta"><?php echo $d['type']; ?> &middot; <?php echo $d['fabric']; ?> &middot; Size <?php echo $d['size']; ?></div>
                    <div class="actions">
                        <a href="design_form.php?id=<?php echo $d['design_id']; ?>" class="btn btn-sm btn-ghost">Edit</a>
                        <a href="design_delete.php?id=<?php echo $d['design_id']; ?>" class="btn btn-sm btn-danger" data-confirm="Delete this design?">Delete</a>
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
    <?php } ?>
</div>
</div>
<script src="../js/script.js"></script>
</body>
</html>
