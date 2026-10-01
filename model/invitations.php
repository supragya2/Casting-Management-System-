<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'model') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $invite_id = $_POST['invite_id'];
    $action = $_POST['action'];
    if ($action == "accepted" || $action == "declined") {
        mysqli_query($conn, "UPDATE invitation SET status = '$action' WHERE invite_id = $invite_id AND model_id = $id");
    }
    header("Location: invitations.php");
    exit;
}

$invites = mysqli_query($conn, "
    SELECT i.*, ds.designer_name, ds.designer_id, d.design_name, d.image
    FROM invitation i, designer ds, design d
    WHERE i.designer_id = ds.designer_id AND i.design_id = d.design_id AND i.model_id = $id
    ORDER BY i.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Invitations Received - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <div class="eyebrow">Casting calls</div>
        <h1>Invitations Received</h1>
    </div>

    <?php if (mysqli_num_rows($invites) == 0) { ?>
        <div class="empty-state">No invitations yet.</div>
    <?php } else { ?>
        <div class="card-grid">
        <?php while ($i = mysqli_fetch_assoc($invites)) { ?>
            <div class="item-card">
                <div class="thumb">
                    <?php if ($i['image'] != "") { ?>
                        <img src="../uploads/designs/<?php echo $i['image']; ?>">
                    <?php } else { ?>
                        No image
                    <?php } ?>
                </div>
                <div class="body">
                    <h3><?php echo $i['design_name']; ?></h3>
                    <div class="meta">by <a href="designer_view.php?id=<?php echo $i['designer_id']; ?>"><?php echo $i['designer_name']; ?></a></div>
                    <div style="display:flex; gap:6px; align-items:center;">
                        <span class="badge badge-<?php echo $i['status']; ?>"><?php echo ucfirst($i['status']); ?></span>
                        <?php if (!empty($i['is_verified'])) { ?>
                            <span class="badge badge-verified">✓ Verified</span>
                        <?php } else { ?>
                            <span class="badge badge-unverified">Unverified</span>
                        <?php } ?>
                    </div>
                    <?php if ($i['status'] == 'pending') { ?>
                    <div class="actions">
                        <form method="POST">
                            <input type="hidden" name="invite_id" value="<?php echo $i['invite_id']; ?>">
                            <button name="action" value="accepted" class="btn btn-sm btn-accept">Accept</button>
                            <button name="action" value="declined" class="btn btn-sm btn-danger">Decline</button>
                        </form>
                    </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        </div>
    <?php } ?>
</div>
</div>
</body>
</html>
