<?php
require_once('auth_check.php');

$flash = "";
$flash_type = "ok";

// Handle Actions (Verification Toggle or Status Change)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';
    $target_table = $_POST['target_table'] ?? '';
    $record_id = intval($_POST['record_id'] ?? 0);

    if ($record_id > 0 && in_array($target_table, ['request', 'invitation'])) {
        $id_column = ($target_table === 'request') ? 'request_id' : 'invite_id';

        if ($action_type === 'toggle_verify') {
            $new_val = intval($_POST['new_verify_val'] ?? 1);
            $update = mysqli_query($conn, "UPDATE `$target_table` SET is_verified = $new_val WHERE `$id_column` = $record_id");
            if ($update) {
                $status_txt = ($new_val === 1) ? 'Verified' : 'Unverified';
                $flash = "Successfully marked interaction as $status_txt!";
            } else {
                $flash = "Error updating verification: " . mysqli_error($conn);
                $flash_type = "err";
            }
        } elseif ($action_type === 'update_status') {
            $new_status = mysqli_real_escape_string($conn, $_POST['new_status'] ?? 'pending');
            if (in_array($new_status, ['pending', 'accepted', 'declined'])) {
                $update = mysqli_query($conn, "UPDATE `$target_table` SET status = '$new_status' WHERE `$id_column` = $record_id");
                if ($update) {
                    $flash = "Status updated to " . ucfirst($new_status) . "!";
                } else {
                    $flash = "Error updating status: " . mysqli_error($conn);
                    $flash_type = "err";
                }
            }
        }
    }
}

// Current Filter Parameters
$tab = $_GET['tab'] ?? 'all'; // all, requests, invitations
$verify_filter = $_GET['verify'] ?? 'all'; // all, 1, 0
$status_filter = $_GET['status'] ?? 'all'; // all, pending, accepted, declined
$search = trim($_GET['search'] ?? '');
$search_escaped = mysqli_real_escape_string($conn, $search);

// Build queries
$items = [];

// 1. Model -> Designer Requests Query
if ($tab === 'all' || $tab === 'requests') {
    $sql_req = "
        SELECT 
            'request' AS source_type,
            r.request_id AS record_id,
            r.status,
            r.is_verified,
            r.created_at,
            '' AS description,
            m.model_id,
            m.model_name,
            m.email AS model_email,
            m.profile_image AS model_image,
            m.is_verified AS model_verified,
            ds.designer_id,
            ds.designer_name,
            ds.email AS designer_email,
            ds.profile_image AS designer_image,
            ds.is_verified AS designer_verified,
            d.design_id,
            d.design_name,
            d.image AS design_image,
            d.type AS design_type
        FROM request r
        LEFT JOIN model m ON r.model_id = m.model_id
        LEFT JOIN designer ds ON r.designer_id = ds.designer_id
        LEFT JOIN design d ON r.design_id = d.design_id
        WHERE 1=1
    ";

    if ($verify_filter === '1') $sql_req .= " AND r.is_verified = 1";
    if ($verify_filter === '0') $sql_req .= " AND r.is_verified = 0";
    if ($status_filter !== 'all') $sql_req .= " AND r.status = '$status_filter'";
    if ($search !== '') {
        $sql_req .= " AND (m.model_name LIKE '%$search_escaped%' OR ds.designer_name LIKE '%$search_escaped%' OR d.design_name LIKE '%$search_escaped%')";
    }

    $res_req = mysqli_query($conn, $sql_req);
    if ($res_req) {
        while ($row = mysqli_fetch_assoc($res_req)) {
            $items[] = $row;
        }
    }
}

// 2. Designer -> Model Invitations Query
if ($tab === 'all' || $tab === 'invitations') {
    $sql_inv = "
        SELECT 
            'invitation' AS source_type,
            i.invite_id AS record_id,
            i.status,
            i.is_verified,
            i.created_at,
            i.description,
            m.model_id,
            m.model_name,
            m.email AS model_email,
            m.profile_image AS model_image,
            m.is_verified AS model_verified,
            ds.designer_id,
            ds.designer_name,
            ds.email AS designer_email,
            ds.profile_image AS designer_image,
            ds.is_verified AS designer_verified,
            d.design_id,
            d.design_name,
            d.image AS design_image,
            d.type AS design_type
        FROM invitation i
        LEFT JOIN model m ON i.model_id = m.model_id
        LEFT JOIN designer ds ON i.designer_id = ds.designer_id
        LEFT JOIN design d ON i.design_id = d.design_id
        WHERE 1=1
    ";

    if ($verify_filter === '1') $sql_inv .= " AND i.is_verified = 1";
    if ($verify_filter === '0') $sql_inv .= " AND i.is_verified = 0";
    if ($status_filter !== 'all') $sql_inv .= " AND i.status = '$status_filter'";
    if ($search !== '') {
        $sql_inv .= " AND (m.model_name LIKE '%$search_escaped%' OR ds.designer_name LIKE '%$search_escaped%' OR d.design_name LIKE '%$search_escaped%')";
    }

    $res_inv = mysqli_query($conn, $sql_inv);
    if ($res_inv) {
        while ($row = mysqli_fetch_assoc($res_inv)) {
            $items[] = $row;
        }
    }
}

// Sort combined items by created_at DESC
usort($items, function($a, $b) {
    return strtotime($b['created_at'] ?? '1970-01-01') - strtotime($a['created_at'] ?? '1970-01-01');
});

// Summary Counts
$tot_req = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM request"))[0] ?? 0;
$tot_inv = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM invitation"))[0] ?? 0;
$verified_req = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM request WHERE is_verified = 1"))[0] ?? 0;
$verified_inv = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM invitation WHERE is_verified = 1"))[0] ?? 0;
$tot_verified = $verified_req + $verified_inv;
$tot_all = $tot_req + $tot_inv;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model &amp; Designer Requests &bull; CastFlow Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include('menu.php'); ?>
    <div class="main" style="max-width: 1400px;">
        <div class="page-head">
            <div class="eyebrow">Collaboration Intelligence</div>
            <h1>Model &amp; Designer Requests</h1>
            <p>Monitor all interactive requests and casting calls sent between models and designers, with instant verification control.</p>
        </div>

        <?php if ($flash !== "") { ?>
            <div class="flash-msg <?php echo ($flash_type === 'ok') ? 'flash-ok' : 'flash-err'; ?>">
                <?php echo htmlspecialchars($flash); ?>
            </div>
        <?php } ?>

        <!-- Stat Row -->
        <div class="stat-row">
            <div class="stat-card">
                <div class="num"><?php echo $tot_all; ?></div>
                <div class="label">Total Interactions</div>
            </div>
            <div class="stat-card">
                <div class="num" style="color:#4527a0;"><?php echo $tot_req; ?></div>
                <div class="label">Model &rarr; Designer Requests</div>
            </div>
            <div class="stat-card">
                <div class="num" style="color:#880e4f;"><?php echo $tot_inv; ?></div>
                <div class="label">Designer &rarr; Model Invites</div>
            </div>
            <div class="stat-card">
                <div class="num" style="color:#1b5e20;"><?php echo $tot_verified; ?></div>
                <div class="label">Verified Collaborations</div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-container">
            <a href="requests.php?tab=all&verify=<?php echo urlencode($verify_filter); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" class="tab-btn <?php if ($tab==='all') echo 'active'; ?>">
                All Collaborations (<?php echo $tot_all; ?>)
            </a>
            <a href="requests.php?tab=requests&verify=<?php echo urlencode($verify_filter); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" class="tab-btn <?php if ($tab==='requests') echo 'active'; ?>">
                Model Requests &rarr; (<?php echo $tot_req; ?>)
            </a>
            <a href="requests.php?tab=invitations&verify=<?php echo urlencode($verify_filter); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" class="tab-btn <?php if ($tab==='invitations') echo 'active'; ?>">
                &larr; Designer Invitations (<?php echo $tot_inv; ?>)
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <form method="GET" action="requests.php" class="filter-bar">
            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
            <div class="filter-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search model, designer, or design..." class="filter-input">
                
                <select name="verify" class="filter-select">
                    <option value="all" <?php if ($verify_filter==='all') echo 'selected'; ?>>Verification: All</option>
                    <option value="1" <?php if ($verify_filter==='1') echo 'selected'; ?>>✓ Verified Only</option>
                    <option value="0" <?php if ($verify_filter==='0') echo 'selected'; ?>>Pending / Unverified</option>
                </select>

                <select name="status" class="filter-select">
                    <option value="all" <?php if ($status_filter==='all') echo 'selected'; ?>>Status: All</option>
                    <option value="pending" <?php if ($status_filter==='pending') echo 'selected'; ?>>Pending</option>
                    <option value="accepted" <?php if ($status_filter==='accepted') echo 'selected'; ?>>Accepted</option>
                    <option value="declined" <?php if ($status_filter==='declined') echo 'selected'; ?>>Declined</option>
                </select>

                <button type="submit" class="btn btn-sm btn-dark">Filter</button>
                <?php if ($search !== '' || $verify_filter !== 'all' || $status_filter !== 'all') { ?>
                    <a href="requests.php?tab=<?php echo urlencode($tab); ?>" class="btn btn-sm btn-ghost">Reset</a>
                <?php } ?>
            </div>
            <div style="font-size: 13px; color: #777;">
                Showing <strong><?php echo count($items); ?></strong> records
            </div>
        </form>

        <!-- Structured Data Table -->
        <?php if (empty($items)) { ?>
            <div class="empty-state">
                No matching requests or invitations found with the current filter.
            </div>
        <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Sender &bull; Receiver</th>
                            <th>Target Design</th>
                            <th>Status</th>
                            <th>Verification</th>
                            <th>Timestamp</th>
                            <th style="text-align: right;">Admin Controls</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item) { 
                            $is_verified = (int)($item['is_verified'] ?? 0);
                            $source = $item['source_type'];
                            $rec_id = $item['record_id'];
                        ?>
                        <tr>
                            <!-- Type Badge -->
                            <td>
                                <?php if ($source === 'request') { ?>
                                    <span class="badge badge-model-req" title="Model requested this design">
                                        Model &rarr; Designer
                                    </span>
                                <?php } else { ?>
                                    <span class="badge badge-designer-inv" title="Designer invited this model">
                                        Designer &rarr; Model
                                    </span>
                                <?php } ?>
                                <div style="font-size:11px; color:#888; margin-top:4px;">ID #<?php echo $rec_id; ?></div>
                            </td>

                            <!-- Model & Designer -->
                            <td>
                                <div style="margin-bottom: 6px;">
                                    <span style="font-size: 11px; color:#888; text-transform:uppercase; letter-spacing:0.5px;">Model:</span>
                                    <strong><?php echo htmlspecialchars($item['model_name'] ?? 'Unknown'); ?></strong>
                                    <?php if (!empty($item['model_verified'])) { ?>
                                        <span title="Verified Model" style="color:#2e7d32; font-weight:bold; font-size:12px;">✓</span>
                                    <?php } ?>
                                    <span style="color:#888; font-size:12px;">(<?php echo htmlspecialchars($item['model_email'] ?? ''); ?>)</span>
                                </div>
                                <div>
                                    <span style="font-size: 11px; color:#888; text-transform:uppercase; letter-spacing:0.5px;">Designer:</span>
                                    <strong><?php echo htmlspecialchars($item['designer_name'] ?? 'Unknown'); ?></strong>
                                    <?php if (!empty($item['designer_verified'])) { ?>
                                        <span title="Verified Designer" style="color:#2e7d32; font-weight:bold; font-size:12px;">✓</span>
                                    <?php } ?>
                                    <span style="color:#888; font-size:12px;">(<?php echo htmlspecialchars($item['designer_email'] ?? ''); ?>)</span>
                                </div>
                            </td>

                            <!-- Design -->
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <?php if (!empty($item['design_image']) && file_exists(__DIR__ . '/../uploads/designs/' . $item['design_image'])) { ?>
                                        <img src="../uploads/designs/<?php echo htmlspecialchars($item['design_image']); ?>" class="table-thumb" alt="Design">
                                    <?php } else { ?>
                                        <div class="table-thumb" style="display:flex; align-items:center; justify-content:center; font-size:10px; color:#999;">No img</div>
                                    <?php } ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['design_name'] ?? 'General Collaboration'); ?></strong>
                                        <div style="font-size:12px; color:#777;"><?php echo htmlspecialchars($item['design_type'] ?? ''); ?></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars($item['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($item['status'])); ?>
                                </span>
                            </td>

                            <!-- Verification Badge -->
                            <td>
                                <?php if ($is_verified === 1) { ?>
                                    <span class="badge badge-verified" title="Verified by Administration">
                                        ✓ Verified
                                    </span>
                                <?php } else { ?>
                                    <span class="badge badge-unverified" title="Pending verification approval">
                                        &#9679; Unverified
                                    </span>
                                <?php } ?>
                            </td>

                            <!-- Date -->
                            <td style="font-size: 12.5px; color:#666; white-space:nowrap;">
                                <?php echo date('M j, Y', strtotime($item['created_at'])); ?><br>
                                <span style="font-size: 11px; color:#999;"><?php echo date('H:i', strtotime($item['created_at'])); ?></span>
                            </td>

                            <!-- Actions -->
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    <!-- Toggle Verification -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action_type" value="toggle_verify">
                                        <input type="hidden" name="target_table" value="<?php echo $source; ?>">
                                        <input type="hidden" name="record_id" value="<?php echo $rec_id; ?>">
                                        <?php if ($is_verified === 1) { ?>
                                            <input type="hidden" name="new_verify_val" value="0">
                                            <button type="submit" class="btn btn-sm btn-unverify" title="Revoke verification badge">
                                                Unverify
                                            </button>
                                        <?php } else { ?>
                                            <input type="hidden" name="new_verify_val" value="1">
                                            <button type="submit" class="btn btn-sm btn-verify" title="Verify this collaboration">
                                                ✓ Verify
                                            </button>
                                        <?php } ?>
                                    </form>

                                    <!-- Quick Status Change Dropdown Form -->
                                    <form method="POST" style="display:inline;" onchange="this.submit();">
                                        <input type="hidden" name="action_type" value="update_status">
                                        <input type="hidden" name="target_table" value="<?php echo $source; ?>">
                                        <input type="hidden" name="record_id" value="<?php echo $rec_id; ?>">
                                        <select name="new_status" class="filter-select" style="padding: 5px 8px; font-size:11px;">
                                            <option value="pending" <?php if ($item['status']==='pending') echo 'selected'; ?>>Pending</option>
                                            <option value="accepted" <?php if ($item['status']==='accepted') echo 'selected'; ?>>Accepted</option>
                                            <option value="declined" <?php if ($item['status']==='declined') echo 'selected'; ?>>Declined</option>
                                        </select>
                                    </form>
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
