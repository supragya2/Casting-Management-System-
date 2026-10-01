<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

$invites = mysqli_query($conn, "
    SELECT i.*, m.model_name, m.model_id, d.design_name
    FROM invitation i, model m, design d
    WHERE i.model_id = m.model_id AND i.design_id = d.design_id AND i.designer_id = $id
    ORDER BY i.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Invitations Sent - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <div class="eyebrow">Casting calls</div>
        <h1>Invitations Sent</h1>
    </div>

    <?php if (mysqli_num_rows($invites) == 0) { ?>
        <div class="empty-state">You haven't invited any models yet.</div>
    <?php } else { ?>
        <table class="data-table">
            <tr><th>Model</th><th>Design</th><th>Status</th><th>Verification</th><th>Sent</th></tr>
            <?php while ($i = mysqli_fetch_assoc($invites)) { ?>
            <tr>
                <td><a href="model_view.php?id=<?php echo $i['model_id']; ?>"><?php echo $i['model_name']; ?></a></td>
                <td><?php echo $i['design_name']; ?></td>
                <td><span class="badge badge-<?php echo $i['status']; ?>"><?php echo ucfirst($i['status']); ?></span></td>
                <td>
                    <?php if (!empty($i['is_verified'])) { ?>
                        <span class="badge badge-verified">✓ Verified</span>
                    <?php } else { ?>
                        <span class="badge badge-unverified">Unverified</span>
                    <?php } ?>
                </td>
                <td><?php echo $i['created_at']; ?></td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>
</div>
</body>
</html>
