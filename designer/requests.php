<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    if ($action == "accepted" || $action == "declined") {
        mysqli_query($conn, "UPDATE request SET status = '$action' WHERE request_id = $request_id AND designer_id = $id");
    }
    header("Location: requests.php");
    exit;
}

$requests = mysqli_query($conn, "
    SELECT r.*, m.model_name, m.model_id, d.design_name
    FROM request r, model m, design d
    WHERE r.model_id = m.model_id AND r.design_id = d.design_id AND r.designer_id = $id
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Requests Received - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <div class="eyebrow">Model interest</div>
        <h1>Requests Received</h1>
    </div>

    <?php if (mysqli_num_rows($requests) == 0) { ?>
        <div class="empty-state">No requests yet.</div>
    <?php } else { ?>
        <table class="data-table">
            <tr><th>Model</th><th>Design</th><th>Status</th><th>Verification</th><th>Received</th><th></th></tr>
            <?php while ($r = mysqli_fetch_assoc($requests)) { ?>
            <tr>
                <td><a href="model_view.php?id=<?php echo $r['model_id']; ?>"><?php echo $r['model_name']; ?></a></td>
                <td><?php echo $r['design_name']; ?></td>
                <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                <td>
                    <?php if (!empty($r['is_verified'])) { ?>
                        <span class="badge badge-verified">✓ Verified</span>
                    <?php } else { ?>
                        <span class="badge badge-unverified">Unverified</span>
                    <?php } ?>
                </td>
                <td><?php echo $r['created_at']; ?></td>
                <td>
                    <?php if ($r['status'] == 'pending') { ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?php echo $r['request_id']; ?>">
                        <button name="action" value="accepted" class="btn btn-sm btn-accept">Accept</button>
                        <button name="action" value="declined" class="btn btn-sm btn-danger">Decline</button>
                    </form>
                    <?php } else { echo "&mdash;"; } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>
</div>
</body>
</html>
