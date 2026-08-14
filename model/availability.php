<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'model') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $date = $_POST['date'];
    $status = $_POST['status'];
    $limitation = mysqli_real_escape_string($conn, $_POST['limitation']);
    mysqli_query($conn, "INSERT INTO model_availability (model_id, avail_date, status, limitation) VALUES ($id, '$date', '$status', '$limitation')");
    header("Location: availability.php");
    exit;
}

if (isset($_GET['delete'])) {
    $aid = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM model_availability WHERE availability_id = $aid AND model_id = $id");
    header("Location: availability.php");
    exit;
}

$rows = mysqli_query($conn, "SELECT * FROM model_availability WHERE model_id = $id ORDER BY avail_date ASC");
?>
<!DOCTYPE html>
<html>
<head>
<title>My Availability - CastFlow</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
<?php include('menu.php'); ?>
<div class="main">
    <div class="page-head">
        <h1>My Availability</h1>
    </div>

    <div class="panel" style="margin-bottom:36px; max-width:560px;">
        <form class="form-grid" method="POST">
            <div><label>Date</label><input type="date" name="date" required></div>
            <div><label>Status</label>
                <select name="status">
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>
            <div class="full"><label>Notes</label>
                <input type="text" name="limitation"></div>
            <div class="form-actions">
                <button type="submit" class="btn btn-dark">Add Date</button>
            </div>
        </form>
    </div>

    <?php if (mysqli_num_rows($rows) == 0) { ?>
        <div class="empty-state">No dates added yet.</div>
    <?php } else { ?>
        <table class="data-table">
            <tr><th>Date</th><th>Status</th><th>Notes</th><th></th></tr>
            <?php while ($r = mysqli_fetch_assoc($rows)) { ?>
            <tr>
                <td><?php echo $r['avail_date']; ?></td>
                <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                <td><?php echo $r['limitation']; ?></td>
                <td><a href="availability.php?delete=<?php echo $r['availability_id']; ?>" class="btn btn-sm btn-danger" data-confirm="Remove this date?">Delete</a></td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>
</div>
<script src="../js/script.js"></script>
</body>
</html>
