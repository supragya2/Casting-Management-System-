<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'model') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

$requests = mysqli_query($conn, "
    SELECT r.*, ds.designer_name, ds.designer_id, d.design_name
    FROM request r, designer ds, design d
    WHERE r.designer_id = ds.designer_id AND r.design_id = d.design_id AND r.model_id = $id
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Requests Sent - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <div class="eyebrow">Your applications</div>
        <h1>Requests Sent</h1>
    </div>

    <?php if (mysqli_num_rows($requests) == 0) { ?>
        <div class="empty-state">You haven't requested any designs yet.</div>
    <?php } else { ?>
        <table class="data-table">
            <tr><th>Designer</th><th>Design</th><th>Status</th><th>Sent</th></tr>
            <?php while ($r = mysqli_fetch_assoc($requests)) { ?>
            <tr>
                <td><a href="designer_view.php?id=<?php echo $r['designer_id']; ?>"><?php echo $r['designer_name']; ?></a></td>
                <td><?php echo $r['design_name']; ?></td>
                <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                <td><?php echo $r['created_at']; ?></td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>
</div>
</body>
</html>
