<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'model') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {
        $image_name = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/photos/" . $image_name);
        mysqli_query($conn, "INSERT INTO photos (model_id, image, description) VALUES ($id, '$image_name', '$description')");
    }
    header("Location: portfolio.php");
    exit;
}

if (isset($_GET['delete'])) {
    $pid = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM photos WHERE photo_id = $pid AND model_id = $id");
    header("Location: portfolio.php");
    exit;
}

$photos = mysqli_query($conn, "SELECT * FROM photos WHERE model_id = $id ORDER BY photo_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>My Portfolio - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <div class="eyebrow">Showcase</div>
        <h1>My Portfolio</h1>
    </div>

    <div class="panel" style="margin-bottom:36px;">
        <form class="form-grid" method="POST" enctype="multipart/form-data">
            <div class="full">
                <label>Photo</label>
                <input type="file" name="image" accept="image/*" required>
            </div>
            <div class="full">
                <label>Caption (optional)</label>
                <input type="text" name="description">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-dark">Add Photo</button>
            </div>
        </form>
    </div>

    <?php if (mysqli_num_rows($photos) == 0) { ?>
        <div class="empty-state">Your portfolio is empty.</div>
    <?php } else { ?>
        <div class="card-grid">
        <?php while ($p = mysqli_fetch_assoc($photos)) { ?>
            <div class="item-card">
                <div class="thumb"><img src="../uploads/photos/<?php echo $p['image']; ?>"></div>
                <div class="body">
                    <div class="meta"><?php echo $p['description']; ?></div>
                    <div class="actions">
                        <a href="portfolio.php?delete=<?php echo $p['photo_id']; ?>" class="btn btn-sm btn-danger" data-confirm="Remove this photo?">Delete</a>
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
