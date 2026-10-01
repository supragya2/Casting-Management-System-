<?php
require_once('auth_check.php');

$search = trim($_GET['search'] ?? '');
$search_escaped = mysqli_real_escape_string($conn, $search);

$sql = "
    SELECT ma.*, m.model_name, m.email AS model_email, m.is_verified AS model_verified
    FROM model_availability ma
    LEFT JOIN model m ON ma.model_id = m.model_id
    WHERE 1=1
";

if ($search !== '') {
    $sql .= " AND (m.model_name LIKE '%$search_escaped%' OR ma.limitation LIKE '%$search_escaped%')";
}

$sql .= " ORDER BY ma.avail_date DESC";
$result = mysqli_query($conn, $sql);
$total_rows = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model Availability Schedule &bull; CastFlow Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include('menu.php'); ?>
    <div class="main" style="max-width: 1400px;">
        <div class="page-head">
            <div class="eyebrow">Database Entities &bull; Schedules</div>
            <h1>Model Availability Registry</h1>
            <p>Track dates, free/busy status, and specific runway or shoot limitations posted by models.</p>
        </div>

        <form method="GET" action="availability.php" class="filter-bar">
            <div class="filter-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by model or limitation note..." class="filter-input" style="min-width:300px;">
                <button type="submit" class="btn btn-sm btn-dark">Search</button>
                <?php if ($search !== '') { ?>
                    <a href="availability.php" class="btn btn-sm btn-ghost">Reset</a>
                <?php } ?>
            </div>
            <div style="font-size: 13px; color: #777;">
                Total <strong><?php echo $total_rows; ?></strong> availability slots
            </div>
        </form>

        <?php if ($total_rows == 0) { ?>
            <div class="empty-state">No availability schedules found.</div>
        <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Model</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Special Limitation / Booking Notes</th>
                            <th>Record ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { 
                            $status = strtolower($row['status']);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['model_name'] ?? 'Unknown'); ?></strong>
                                <?php if (!empty($row['model_verified'])) { ?>
                                    <span class="badge badge-verified" style="font-size:10px; margin-left:4px;">✓ Verified</span>
                                <?php } ?>
                                <div style="font-size:12px; color:#777;"><?php echo htmlspecialchars($row['model_email'] ?? ''); ?></div>
                            </td>
                            <td>
                                <strong><?php echo date('F j, Y', strtotime($row['avail_date'])); ?></strong>
                                <div style="font-size:11px; color:#888;"><?php echo date('l', strtotime($row['avail_date'])); ?></div>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo ($status === 'available') ? 'available' : 'unavailable'; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['limitation'] ?? 'None specified'); ?>
                            </td>
                            <td style="font-size:11px; color:#888;">
                                #<?php echo $row['availability_id']; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>
