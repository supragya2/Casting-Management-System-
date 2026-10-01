<?php
require_once('auth_check.php');

$search = trim($_GET['search'] ?? '');
$search_escaped = mysqli_real_escape_string($conn, $search);

$sql = "
    SELECT 
        d.*,
        ds.designer_name,
        ds.email AS designer_email,
        (SELECT COUNT(*) FROM request WHERE design_id = d.design_id) AS total_requests,
        (SELECT COUNT(*) FROM invitation WHERE design_id = d.design_id) AS total_invites
    FROM design d
    LEFT JOIN designer ds ON d.designer_id = ds.designer_id
    WHERE 1=1
";

if ($search !== '') {
    $sql .= " AND (d.design_name LIKE '%$search_escaped%' OR ds.designer_name LIKE '%$search_escaped%' OR d.type LIKE '%$search_escaped%' OR d.fabric LIKE '%$search_escaped%')";
}

$sql .= " ORDER BY d.design_id DESC";
$result = mysqli_query($conn, $sql);
$total_rows = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Designs Catalog &bull; CastFlow Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include('menu.php'); ?>
    <div class="main" style="max-width: 1400px;">
        <div class="page-head">
            <div class="eyebrow">Database Entities &bull; Creations</div>
            <h1>Designs Catalog</h1>
            <p>Every fashion garment, dress, and apparel piece uploaded by designers for model casting.</p>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="designs.php" class="filter-bar">
            <div class="filter-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search design, designer, fabric, or type..." class="filter-input" style="min-width:300px;">
                <button type="submit" class="btn btn-sm btn-dark">Search</button>
                <?php if ($search !== '') { ?>
                    <a href="designs.php" class="btn btn-sm btn-ghost">Clear</a>
                <?php } ?>
            </div>
            <div style="font-size: 13px; color: #777;">
                Total <strong><?php echo $total_rows; ?></strong> designs listed
            </div>
        </form>

        <?php if ($total_rows == 0) { ?>
            <div class="empty-state">No designs found.</div>
        <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Designer</th>
                            <th>Garment Specs</th>
                            <th>Description</th>
                            <th>Model Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { 
                            $img_name = $row['image'];
                            $img_path = (!empty($img_name) && file_exists(__DIR__ . '/../uploads/designs/' . $img_name))
                                ? '../uploads/designs/' . htmlspecialchars($img_name) : '';
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <?php if ($img_path !== '') { ?>
                                        <img src="<?php echo $img_path; ?>" class="table-thumb" alt="Design Photo" style="width:60px; height:60px;">
                                    <?php } else { ?>
                                        <div class="table-thumb" style="width:60px; height:60px; display:flex; align-items:center; justify-content:center; font-size:11px; color:#999;">No img</div>
                                    <?php } ?>
                                    <div>
                                        <strong style="font-size:15px;"><?php echo htmlspecialchars($row['design_name']); ?></strong>
                                        <div style="font-size:11px; color:#888;">Design ID #<?php echo $row['design_id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['designer_name'] ?? 'Unknown Designer'); ?></strong>
                                <div style="font-size:12px; color:#777;"><?php echo htmlspecialchars($row['designer_email'] ?? ''); ?></div>
                            </td>
                            <td>
                                <div><strong>Type:</strong> <?php echo htmlspecialchars($row['type'] ?? 'N/A'); ?></div>
                                <div style="font-size:12.5px; color:#555;"><strong>Fabric:</strong> <?php echo htmlspecialchars($row['fabric'] ?? 'N/A'); ?></div>
                                <div style="font-size:12px; color:#777;"><strong>Size:</strong> <?php echo htmlspecialchars($row['size'] ?? 'N/A'); ?></div>
                            </td>
                            <td style="max-width:320px; font-size:13px; color:#555;">
                                <?php echo htmlspecialchars($row['description'] ?? 'No description provided.'); ?>
                            </td>
                            <td>
                                <div>
                                    <a href="requests.php?search=<?php echo urlencode($row['design_name']); ?>" style="text-decoration:underline; font-weight:600;">
                                        <?php echo $row['total_requests']; ?> requests
                                    </a>
                                </div>
                                <div style="font-size:12px; color:#777;">
                                    <?php echo $row['total_invites']; ?> invitations
                                </div>
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
