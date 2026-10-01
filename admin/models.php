<?php
require_once('auth_check.php');

$flash = "";
$flash_type = "ok";

// Handle Verification Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_model_verify'])) {
    $model_id = intval($_POST['model_id'] ?? 0);
    $new_status = intval($_POST['new_status'] ?? 1);

    if ($model_id > 0) {
        if (mysqli_query($conn, "UPDATE model SET is_verified = $new_status WHERE model_id = $model_id")) {
            $status_word = ($new_status === 1) ? 'Verified' : 'Unverified';
            $flash = "Model successfully marked as $status_word!";
        } else {
            $flash = "Database error: " . mysqli_error($conn);
            $flash_type = "err";
        }
    }
}

// Filters
$search = trim($_GET['search'] ?? '');
$search_escaped = mysqli_real_escape_string($conn, $search);
$verify_filter = $_GET['verify'] ?? 'all';

$sql = "
    SELECT 
        m.*,
        (SELECT COUNT(*) FROM photos WHERE model_id = m.model_id) AS total_photos,
        (SELECT COUNT(*) FROM request WHERE model_id = m.model_id) AS total_requests,
        (SELECT COUNT(*) FROM invitation WHERE model_id = m.model_id) AS total_invites,
        (SELECT COUNT(*) FROM model_availability WHERE model_id = m.model_id) AS total_avail
    FROM model m
    WHERE 1=1
";

if ($verify_filter === '1') $sql .= " AND m.is_verified = 1";
if ($verify_filter === '0') $sql .= " AND m.is_verified = 0";
if ($search !== '') {
    $sql .= " AND (m.model_name LIKE '%$search_escaped%' OR m.email LIKE '%$search_escaped%' OR m.phone LIKE '%$search_escaped%')";
}

$sql .= " ORDER BY m.model_id DESC";
$result = mysqli_query($conn, $sql);
$total_rows = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Models Directory &bull; CastFlow Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include('menu.php'); ?>
    <div class="main" style="max-width: 1400px;">
        <div class="page-head">
            <div class="eyebrow">Database Entities &bull; Talent Pool</div>
            <h1>Models Registry</h1>
            <p>View, evaluate, and verify fashion models across physical measurements, portfolio, and casting activity.</p>
        </div>

        <?php if ($flash !== "") { ?>
            <div class="flash-msg <?php echo ($flash_type === 'ok') ? 'flash-ok' : 'flash-err'; ?>">
                <?php echo htmlspecialchars($flash); ?>
            </div>
        <?php } ?>

        <!-- Filter Bar -->
        <form method="GET" action="models.php" class="filter-bar">
            <div class="filter-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search model name, email, phone..." class="filter-input">
                
                <select name="verify" class="filter-select">
                    <option value="all" <?php if ($verify_filter==='all') echo 'selected'; ?>>All Models</option>
                    <option value="1" <?php if ($verify_filter==='1') echo 'selected'; ?>>✓ Verified Only</option>
                    <option value="0" <?php if ($verify_filter==='0') echo 'selected'; ?>>Pending / Unverified</option>
                </select>

                <button type="submit" class="btn btn-sm btn-dark">Search</button>
                <?php if ($search !== '' || $verify_filter !== 'all') { ?>
                    <a href="models.php" class="btn btn-sm btn-ghost">Reset</a>
                <?php } ?>
            </div>
            <div style="font-size: 13px; color: #777;">
                Found <strong><?php echo $total_rows; ?></strong> model records
            </div>
        </form>

        <?php if ($total_rows == 0) { ?>
            <div class="empty-state">No models found matching your criteria.</div>
        <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Model</th>
                            <th>Contact</th>
                            <th>Demographics</th>
                            <th>Measurements</th>
                            <th>Portfolio</th>
                            <th>Casting Activity</th>
                            <th>Verification</th>
                            <th style="text-align:right;">Admin Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($m = mysqli_fetch_assoc($result)) { 
                            $is_v = (int)($m['is_verified'] ?? 0);
                            $profile_img = $m['profile_image'];
                            $avatar_url = (!empty($profile_img) && file_exists(__DIR__ . '/../uploads/' . $profile_img)) 
                                ? '../uploads/' . htmlspecialchars($profile_img) : '';
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center;">
                                    <?php if ($avatar_url !== '') { ?>
                                        <img src="<?php echo $avatar_url; ?>" class="table-avatar" alt="Avatar">
                                    <?php } else { ?>
                                        <div class="avatar-placeholder"><?php echo strtoupper(substr($m['model_name'], 0, 1)); ?></div>
                                    <?php } ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($m['model_name']); ?></strong>
                                        <div style="font-size:11px; color:#888;">ID #<?php echo $m['model_id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($m['email']); ?></div>
                                <div style="font-size:12px; color:#777;"><?php echo htmlspecialchars($m['phone'] ?? 'N/A'); ?></div>
                            </td>
                            <td>
                                <div><strong>Gender:</strong> <?php echo htmlspecialchars($m['gender'] ?? 'N/A'); ?></div>
                                <div style="font-size:12px; color:#777;"><strong>Age:</strong> <?php echo htmlspecialchars($m['age'] ?? 'N/A'); ?></div>
                            </td>
                            <td style="font-size:12.5px;">
                                <div>H: <?php echo htmlspecialchars($m['height'] ?? '-'); ?></div>
                                <div>W: <?php echo htmlspecialchars($m['weight'] ?? '-'); ?></div>
                            </td>
                            <td>
                                <a href="photos.php?search=<?php echo urlencode($m['model_name']); ?>" style="font-weight:600; text-decoration:underline;">
                                    <?php echo $m['total_photos']; ?> photos
                                </a>
                                <div style="font-size:11px; color:#777;">
                                    <a href="availability.php?search=<?php echo urlencode($m['model_name']); ?>">
                                        <?php echo $m['total_avail']; ?> avail slots
                                    </a>
                                </div>
                            </td>
                            <td>
                                <a href="requests.php?search=<?php echo urlencode($m['model_name']); ?>" style="text-decoration:underline;">
                                    <?php echo $m['total_requests']; ?> sent / <?php echo $m['total_invites']; ?> inv
                                </a>
                            </td>
                            <td>
                                <?php if ($is_v === 1) { ?>
                                    <span class="badge badge-verified">✓ Verified</span>
                                <?php } else { ?>
                                    <span class="badge badge-unverified">&#9679; Unverified</span>
                                <?php } ?>
                            </td>
                            <td style="text-align:right;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="toggle_model_verify" value="1">
                                    <input type="hidden" name="model_id" value="<?php echo $m['model_id']; ?>">
                                    <?php if ($is_v === 1) { ?>
                                        <input type="hidden" name="new_status" value="0">
                                        <button type="submit" class="btn btn-sm btn-unverify" title="Revoke verified badge">Revoke</button>
                                    <?php } else { ?>
                                        <input type="hidden" name="new_status" value="1">
                                        <button type="submit" class="btn btn-sm btn-verify" title="Grant verified badge">✓ Verify</button>
                                    <?php } ?>
                                </form>
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
