<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];
$editing = null;


if (isset($_GET['id'])) {
    $design_id = $_GET['id'];
    $result = mysqli_query($conn, "SELECT * FROM design WHERE design_id = $design_id AND designer_id = $id");
    $editing = mysqli_fetch_assoc($result);
}



if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $name = mysqli_real_escape_string($conn, $_POST['design_name']);
    $fabric = mysqli_real_escape_string($conn, $_POST['fabric']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);


    $image_name = "";
    if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {
        $image_name = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/designs/" . $image_name);
    }

    if ($editing) {
        if ($image_name != "") {
            $sql = "UPDATE design SET design_name='$name', fabric='$fabric', size='$size', type='$type', description='$description', image='$image_name' WHERE design_id = " . $editing['design_id'];
        } else {
            $sql = "UPDATE design SET design_name='$name', fabric='$fabric', size='$size', type='$type', description='$description' WHERE design_id = " . $editing['design_id'];
        }
        mysqli_query($conn, $sql);
    } else {
        $sql = "INSERT INTO design (designer_id, design_name, image, fabric, size, type, description)
                VALUES ($id, '$name', '$image_name', '$fabric', '$size', '$type', '$description')";
        mysqli_query($conn, $sql);
    }


    header("Location: designs.php");
    exit;
}
?>



<!DOCTYPE html>
<html>
<head>
<title>Add Design - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <h1><?php echo $editing ? 'Edit Design' : 'Add a New Design'; ?></h1>
    </div>

    <div class="panel">
        <form class="form-grid" method="POST" enctype="multipart/form-data">
            <div class="full">
                <label>Design Photo</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <div class="full">
                <label>Design Name</label>
                <input type="text" name="design_name" required value="<?php echo $editing ? $editing['design_name'] : ''; ?>">
            </div>
            <div>
                <label>Type</label>
                <input type="text" name="type" value="<?php echo $editing ? $editing['type'] : ''; ?>">
            </div>
            <div>
                <label>Fabric</label>
                <input type="text" name="fabric" value="<?php echo $editing ? $editing['fabric'] : ''; ?>">
            </div>
            <div>
                <label>Size</label>
                <input type="text" name="size" value="<?php echo $editing ? $editing['size'] : ''; ?>">
            </div>
            <div class="full">
                <label>Description</label>
                <textarea name="description"><?php echo $editing ? $editing['description'] : ''; ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-dark">Save</button>
                <a href="designs.php" class="btn btn-ghost">Cancel</a>

                
            </div>
        </form>
    </div>
</div>
</div>
</body>
</html>
