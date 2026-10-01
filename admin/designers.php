<?php
require_once('auth_check.php');

$flash = "";
$flash_type = "ok";

// Handle Verification Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_designer_verify'])) {
    $designer_id = intval($_POST['designer_id'] ?? 0);
    $new_status = intval($_POST['new_status'] ?? 1);

    if ($designer_id > 0) {
        if (mysqli_query($conn, "UPDATE designer SET is_verified = $new_status WHERE designer_id = $designer_id")) {
            $status_word = ($new_status === 1) ? 'Verified' : 'Unverified';
            $flash = "Designer successfully marked as $status_word!";
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
        d.*,
        (SELECT COUNT(*) FROM design WHERE designer_id = d.designer_id) AS total_designs,
        (SELECT COUNT(*) FROM request WHERE designer_id = d.designer_id) AS total_requests,
        (SELECT COUNT(*) FROM invitation WHERE designer_id = d.designer_id) AS total_invites
    FROM designer d
    WHERE 1=1
";

if ($verify_filter === '1') $sql .= " AND d.is_verified = 1";
if ($verify_filter === '0') $sql .= " AND d.is_verified = 0";
if ($search !== '') {
    $sql .= " AND (d.designer_name LIKE '%$search_escaped%' OR d.email LIKE '%$search_escaped%' OR d.phone LIKE '%$search_escaped%')";
}

$sql .= " ORDER BY d.designer_id DESC";
$result = mysqli_query($conn, $sql);
$total_rows = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Designers Directory &bull; CastFlow Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include('menu.php'); ?>
    <div class="main" style="max-width: 1400px;">
        <div class="page-head">
            <div class="eyebrow">Database Entities &bull; Fashion Creators</div>
            <h1>Designers Registry</h1>
            <p>View, search, and manage all fashion designer accounts and official verification credentials.</p>
        </div>

        <?php if ($flash !== "") { ?>
            <div class="flash-msg <?php echo ($flash_type === 'ok') ? 'flash-ok' : 'flash-err'; ?>">
                <?php echo htmlspecialchars($flash); ?>
            </div>
        <?php } ?>

        <!-- Filter Bar -->
        <form method="GET" action="designers.php" class="filter-bar">
            <div class="filter-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search designer name, email, phone..." class="filter-input">
                
                <select name="verify" class="filter-select">
                    <option value="all" <?php if ($verify_filter==='all') echo 'selected'; ?>>All Designers</option>
                    <option value="1" <?php if ($verify_filter==='1') echo 'selected'; ?>>✓ Verified Only</option>
                    <option value="0" <?php if ($verify_filter==='0') echo 'selected'; ?>>Pending / Unverified</option>
                </select>

                <button type="submit" class="btn btn-sm btn-dark">Search</button>
                <?php if ($search !== '' || $verify_filter !== 'all') { ?>
                    <a href="designers.php" class="btn btn-sm btn-ghost">Reset</a>
                <?php } ?>
            </div>
            <div style="font-size: 13px; color: #777;">
                Found <strong><?php echo $total_rows; ?></strong> designer records
            </div>
        </form>

        <?php if ($total_rows == 0) { ?>
            <div class="empty-state">No designers found matching your criteria.</div>
        <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Designer</th>
                            <th>Contact</th>
                            <th>Experience</th>
                            <th>Designs</th>
                            <th>Requests</th>
                            <th>Verification</th>
                            <th>Registered</th>
                            <th style="text-align:right;">Admin Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($d = mysqli_fetch_assoc($result)) { 
                            $is_v = (int)($d['is_verified'] ?? 0);
                            $profile_img = $d['profile_image'];
                            $avatar_url = (!empty($profile_img) && file_exists(__DIR__ . '/../uploads/' . $profile_img)) 
                                ? '../uploads/' . htmlspecialchars($profile_img) : '';
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center;">
                                    <?php if ($avatar_url !== '') { ?>
                                        <img src="<?php echo $avatar_url; ?>" class="table-avatar" alt="Avatar">
                                    <?php } else { ?>
                                        <div class="avatar-placeholder"><?php echo strtoupper(substr($d['designer_name'], 0, 1)); ?></div>
                                    <?php } ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($d['designer_name']); ?></strong>
                                        <div style="font-size:11px; color:#888;">ID #<?php echo $d['designer_id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($d['email']); ?></div>
                                <div style="font-size:12px; color:#777;"><?php echo htmlspecialchars($d['phone'] ?? 'N/A'); ?></div>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($d['experience'] ?? '0'); ?></strong> yrs
                            </td>
                            <td>
                                <a href="designs.php?search=<?php echo urlencode($d['designer_name']); ?>" style="text-decoration:underline; font-weight:600;">
                                    <?php echo $d['total_designs']; ?> designs
                                </a>
                            </td>
                            <td>
                                <a href="requests.php?search=<?php echo urlencode($d['designer_name']); ?>" style="text-decoration:underline;">
                                    <?php echo $d['total_requests']; ?> rec / <?php echo $d['total_invites']; ?> sent
                                </a>
                            </td>
                            <td>
                                <?php if ($is_v === 1) { ?>
                                    <span class="badge badge-verified">✓ Verified</span>
                                <?php } else { ?>
                                    <span class="badge badge-unverified">&#9679; Unverified</span>
                                <?php } ?>
                            </td>
                            <td style="font-size:12px; color:#666;">
                                <?php echo !empty($d['created_at']) ? date('M j, Y', strtotime($d['created_at'])) : '&mdash;'; ?>
                            </td>
                            <td style="text-align:right;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="toggle_designer_verify" value="1">
                                    <input type="hidden" name="designer_id" value="<?php echo $d['designer_id']; ?>">
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
