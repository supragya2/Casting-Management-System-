<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'model') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM photos WHERE model_id = $id");
$photo_count = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM invitation WHERE model_id = $id AND status = 'pending'");
$invite_count = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM request WHERE model_id = $id");
$request_count = mysqli_fetch_assoc($result)['total'];

$recent = mysqli_query($conn, "
    SELECT i.*, ds.designer_name, d.design_name
    FROM invitation i, designer ds, design d
    WHERE i.designer_id = ds.designer_id AND i.design_id = d.design_id AND i.model_id = $id
    ORDER BY i.created_at DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <div class="eyebrow">Welcome back</div>
        <h1><?php echo $_SESSION['name']; ?></h1>
        <p>Here's what's happening with your bookings and portfolio.</p>
    </div>

    <div class="stat-row">
        <div class="stat-card"><div class="num"><?php echo $photo_count; ?></div><div class="label">Portfolio photos</div></div>
        <div class="stat-card"><div class="num"><?php echo $invite_count; ?></div><div class="label">Invitations awaiting reply</div></div>
        <div class="stat-card"><div class="num"><?php echo $request_count; ?></div><div class="label">Requests sent</div></div>
    </div>

    <h3 style="font-family:var(--font-serif); font-size:20px; margin-bottom:14px;">Recent invitations from designers</h3>
    <?php if (mysqli_num_rows($recent) == 0) { ?>
        <div class="empty-state">No invitations yet.</div>
    <?php } else { ?>
        <table class="data-table">
            <tr><th>Designer</th><th>Design</th><th>Status</th><th>Date</th></tr>
            <?php while ($i = mysqli_fetch_assoc($recent)) { ?>
            <tr>
                <td><?php echo $i['designer_name']; ?></td>
                <td><?php echo $i['design_name']; ?></td>
                <td><span class="badge badge-<?php echo $i['status']; ?>"><?php echo ucfirst($i['status']); ?></span></td>
                <td><?php echo $i['created_at']; ?></td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>
</div>
</body>
</html>
