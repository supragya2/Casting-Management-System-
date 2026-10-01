<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM design WHERE designer_id = $id");
$row = mysqli_fetch_assoc($result);
$design_count = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM invitation WHERE designer_id = $id");
$row = mysqli_fetch_assoc($result);
$invite_count = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM request WHERE designer_id = $id AND status = 'pending'");
$row = mysqli_fetch_assoc($result);
$pending_count = $row['total'];

$recent = mysqli_query($conn, "
    SELECT r.*, m.model_name, d.design_name
    FROM request r, model m, design d
    WHERE r.model_id = m.model_id AND r.design_id = d.design_id AND r.designer_id = $id
    ORDER BY r.created_at DESC LIMIT 5
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
        <p>Here's what's happening with your designs and casting activity.</p>
    </div>

    <div class="stat-row">
        <div class="stat-card"><div class="num"><?php echo $design_count; ?></div><div class="label">Designs uploaded</div></div>
        <div class="stat-card"><div class="num"><?php echo $invite_count; ?></div><div class="label">Invitations sent</div></div>
        <div class="stat-card"><div class="num"><?php echo $pending_count; ?></div><div class="label">Requests awaiting reply</div></div>
    </div>

    <h3 style="font-family:var(--font-serif); font-size:20px; margin-bottom:14px;">Recent requests from models</h3>

    <?php if (mysqli_num_rows($recent) == 0) { ?>
        <div class="empty-state">No requests yet.</div>
    <?php } else { ?>
        <table class="data-table">
            <tr><th>Model</th><th>Design</th><th>Status</th><th>Date</th></tr>
            <?php while ($r = mysqli_fetch_assoc($recent)) { ?>
            <tr>
                <td><?php echo $r['model_name']; ?></td>
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
