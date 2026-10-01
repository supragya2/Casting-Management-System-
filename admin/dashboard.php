<?php
require_once('auth_check.php');

$flash = "";
$flash_type = "ok";

// Handle quick verification toggle from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_verify_toggle'])) {
    $tbl = $_POST['table'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $new_val = intval($_POST['val'] ?? 1);

    $id_col_map = [
        'request' => 'request_id',
        'invitation' => 'invite_id',
        'designer' => 'designer_id',
        'model' => 'model_id'
    ];

    if (isset($id_col_map[$tbl]) && $id > 0) {
        $col = $id_col_map[$tbl];
        if (mysqli_query($conn, "UPDATE `$tbl` SET is_verified = $new_val WHERE `$col` = $id")) {
            $flash = "Updated verification status successfully!";
        } else {
            $flash = "Error updating: " . mysqli_error($conn);
            $flash_type = "err";
        }
    }
}

// Global Metrics
$count_designers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM designer"))[0] ?? 0;
$count_models = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM model"))[0] ?? 0;
$count_designs = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM design"))[0] ?? 0;
$count_photos = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM photos"))[0] ?? 0;
$count_requests = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM request"))[0] ?? 0;
$count_invitations = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM invitation"))[0] ?? 0;

$verified_models = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM model WHERE is_verified = 1"))[0] ?? 0;
$verified_designers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM designer WHERE is_verified = 1"))[0] ?? 0;
$verified_requests = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM request WHERE is_verified = 1"))[0] ?? 0;
$verified_invitations = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM invitation WHERE is_verified = 1"))[0] ?? 0;

$pending_requests = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM request WHERE status = 'pending'"))[0] ?? 0;
$pending_invitations = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM invitation WHERE status = 'pending'"))[0] ?? 0;

// Fetch Recent Interactions (Both requests and invitations)
$recent_interactions = [];
$res1 = mysqli_query($conn, "
    SELECT 'request' AS type, r.request_id AS id, r.status, r.is_verified, r.created_at,
           m.model_name, m.email AS model_email, ds.designer_name, ds.email AS designer_email,
           d.design_name, d.image AS design_image
    FROM request r
    LEFT JOIN model m ON r.model_id = m.model_id
    LEFT JOIN designer ds ON r.designer_id = ds.designer_id
    LEFT JOIN design d ON r.design_id = d.design_id
    ORDER BY r.created_at DESC LIMIT 5
");
if ($res1) {
    while ($r = mysqli_fetch_assoc($res1)) $recent_interactions[] = $r;
}

$res2 = mysqli_query($conn, "
    SELECT 'invitation' AS type, i.invite_id AS id, i.status, i.is_verified, i.created_at,
           m.model_name, m.email AS model_email, ds.designer_name, ds.email AS designer_email,
           d.design_name, d.image AS design_image
    FROM invitation i
    LEFT JOIN model m ON i.model_id = m.model_id
    LEFT JOIN designer ds ON i.designer_id = ds.designer_id
    LEFT JOIN design d ON i.design_id = d.design_id
    ORDER BY i.created_at DESC LIMIT 5
");
if ($res2) {
    while ($r = mysqli_fetch_assoc($res2)) $recent_interactions[] = $r;
}

usort($recent_interactions, function($a, $b) {
    return strtotime($b['created_at'] ?? '1970-01-01') - strtotime($a['created_at'] ?? '1970-01-01');
});
$recent_interactions = array_slice($recent_interactions, 0, 6);

// Recent Designers & Models
$recent_designers = mysqli_query($conn, "SELECT * FROM designer ORDER BY designer_id DESC LIMIT 4");
$recent_models = mysqli_query($conn, "SELECT * FROM model ORDER BY model_id DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard &bull; CastFlow Management</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-shell">
    <?php include('menu.php'); ?>
    <div class="main" style="max-width: 1400px;">
        <div class="page-head">
            <div class="eyebrow">Executive Intelligence &bull; CastFlow Control</div>
            <h1>System Overview</h1>
            <p>Complete control center for platform activity, verification statuses, and casting collaborations.</p>
        </div>

        <?php if ($flash !== "") { ?>
            <div class="flash-msg <?php echo ($flash_type === 'ok') ? 'flash-ok' : 'flash-err'; ?>">
                <?php echo htmlspecialchars($flash); ?>
            </div>
        <?php } ?>

        <!-- Stat Row -->
        <div class="stat-row">
            <div class="stat-card">
                <div class="num"><?php echo $count_models; ?></div>
                <div class="label">Total Models (<?php echo $verified_models; ?> verified)</div>
                <a href="models.php" style="font-size:12px; color:var(--gold); display:inline-block; margin-top:8px;">Manage Models &rarr;</a>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $count_designers; ?></div>
                <div class="label">Total Designers (<?php echo $verified_designers; ?> verified)</div>
                <a href="designers.php" style="font-size:12px; color:var(--gold); display:inline-block; margin-top:8px;">Manage Designers &rarr;</a>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $count_designs; ?></div>
                <div class="label">Total Designs Uploaded</div>
                <a href="designs.php" style="font-size:12px; color:var(--gold); display:inline-block; margin-top:8px;">Browse Catalog &rarr;</a>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo ($count_requests + $count_invitations); ?></div>
                <div class="label">Collaborations (<?php echo ($verified_requests + $verified_invitations); ?> verified)</div>
                <a href="requests.php" style="font-size:12px; color:var(--gold); display:inline-block; margin-top:8px;">Review All Requests &rarr;</a>
            </div>
            <div class="stat-card">
                <div class="num" style="color: #d97706;"><?php echo ($pending_requests + $pending_invitations); ?></div>
                <div class="label">Pending Decisions</div>
                <a href="requests.php?status=pending" style="font-size:12px; color:var(--gold); display:inline-block; margin-top:8px;">View Pending &rarr;</a>
            </div>
        </div>

        <!-- Central Showcase: Model & Designer Collaborations -->
        <div style="margin-bottom: 40px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:16px;">
                <div>
                    <h2 style="font-family:var(--font-serif); font-size:22px;">Recent Model &amp; Designer Requests</h2>
                    <p style="color:#666; font-size:13.5px; margin-top:4px;">Direct requests and invitations between models and designers with verification status.</p>
                </div>
                <a href="requests.php" class="btn btn-sm btn-dark">View Full Request Center &rarr;</a>
            </div>

            <?php if (empty($recent_interactions)) { ?>
                <div class="empty-state">No casting requests or invitations have been recorded yet.</div>
            <?php } else { ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Model &harr; Designer</th>
                                <th>Design</th>
                                <th>Status</th>
                                <th>Verified?</th>
                                <th>Date</th>
                                <th style="text-align:right;">Verification Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_interactions as $item) { 
                                $is_ver = (int)$item['is_verified'];
                                $tbl_name = $item['type'];
                                $item_id = $item['id'];
                            ?>
                            <tr>
                                <td>
                                    <?php if ($tbl_name === 'request') { ?>
                                        <span class="badge badge-model-req">Model Request</span>
                                    <?php } else { ?>
                                        <span class="badge badge-designer-inv">Designer Invite</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div><strong>Model:</strong> <?php echo htmlspecialchars($item['model_name'] ?? 'N/A'); ?></div>
                                    <div style="color:#666; font-size:12.5px;"><strong>Designer:</strong> <?php echo htmlspecialchars($item['designer_name'] ?? 'N/A'); ?></div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['design_name'] ?? 'General Portfolio'); ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($item['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($item['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($is_ver === 1) { ?>
                                        <span class="badge badge-verified">✓ Verified</span>
                                    <?php } else { ?>
                                        <span class="badge badge-unverified">&#9679; Unverified</span>
                                    <?php } ?>
                                </td>
                                <td style="font-size:12.5px; color:#666;">
                                    <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                                </td>
                                <td style="text-align:right;">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="quick_verify_toggle" value="1">
                                        <input type="hidden" name="table" value="<?php echo $tbl_name; ?>">
                                        <input type="hidden" name="id" value="<?php echo $item_id; ?>">
                                        <?php if ($is_ver === 1) { ?>
                                            <input type="hidden" name="val" value="0">
                                            <button type="submit" class="btn btn-sm btn-unverify" title="Revoke verification">Unverify</button>
                                        <?php } else { ?>
                                            <input type="hidden" name="val" value="1">
                                            <button type="submit" class="btn btn-sm btn-verify" title="Verify interaction">✓ Verify</button>
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

        <!-- Two Column Grid: Designers and Models Verification Status -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 40px;">
            <!-- Designers Column -->
            <div class="panel" style="max-width: 100%;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h3 style="font-family:var(--font-serif); font-size:18px;">Designers Registered</h3>
                    <a href="designers.php" style="font-size:12px; color:var(--gold);">View All &rarr;</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr><th>Designer</th><th>Exp</th><th>Verified</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($ds = mysqli_fetch_assoc($recent_designers)) { 
                            $is_v = (int)($ds['is_verified'] ?? 0);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($ds['designer_name']); ?></strong>
                                <div style="font-size:11px; color:#777;"><?php echo htmlspecialchars($ds['email']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($ds['experience']); ?> yrs</td>
                            <td>
                                <?php if ($is_v === 1) { ?>
                                    <span class="badge badge-verified">✓ Verified</span>
                                <?php } else { ?>
                                    <span class="badge badge-unverified">Pending</span>
                                <?php } ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="quick_verify_toggle" value="1">
                                    <input type="hidden" name="table" value="designer">
                                    <input type="hidden" name="id" value="<?php echo $ds['designer_id']; ?>">
                                    <input type="hidden" name="val" value="<?php echo ($is_v === 1) ? 0 : 1; ?>">
                                    <button type="submit" class="btn btn-sm <?php echo ($is_v === 1) ? 'btn-unverify' : 'btn-verify'; ?>">
                                        <?php echo ($is_v === 1) ? 'Revoke' : 'Verify'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Models Column -->
            <div class="panel" style="max-width: 100%;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h3 style="font-family:var(--font-serif); font-size:18px;">Models Registered</h3>
                    <a href="models.php" style="font-size:12px; color:var(--gold);">View All &rarr;</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr><th>Model</th><th>Gender/Age</th><th>Verified</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($md = mysqli_fetch_assoc($recent_models)) { 
                            $is_v = (int)($md['is_verified'] ?? 0);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($md['model_name']); ?></strong>
                                <div style="font-size:11px; color:#777;"><?php echo htmlspecialchars($md['email']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($md['gender'] ?? 'N/A'); ?>, <?php echo htmlspecialchars($md['age'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($is_v === 1) { ?>
                                    <span class="badge badge-verified">✓ Verified</span>
                                <?php } else { ?>
                                    <span class="badge badge-unverified">Pending</span>
                                <?php } ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="quick_verify_toggle" value="1">
                                    <input type="hidden" name="table" value="model">
                                    <input type="hidden" name="id" value="<?php echo $md['model_id']; ?>">
                                    <input type="hidden" name="val" value="<?php echo ($is_v === 1) ? 0 : 1; ?>">
                                    <button type="submit" class="btn btn-sm <?php echo ($is_v === 1) ? 'btn-unverify' : 'btn-verify'; ?>">
                                        <?php echo ($is_v === 1) ? 'Revoke' : 'Verify'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Database Health Banner -->
        <div style="background:var(--white); border:1px solid #e6e3db; border-radius:14px; padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <strong style="font-size:15px;">Database Explorer &amp; Structured Raw Content</strong>
                <p style="color:#666; font-size:13px; margin-top:2px;">Inspect schema definitions, raw records, and export CSV tables across all 8 system entities.</p>
            </div>
            <a href="database.php" class="btn btn-dark">Open Database Inspector &rarr;</a>
        </div>
    </div>
</div>
</body>
</html>
