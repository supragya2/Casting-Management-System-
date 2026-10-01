<?php
$page = basename($_SERVER['PHP_SELF']);

// Live counts for sidebar badges
$count_unverified_req = 0;
$count_designers = 0;
$count_models = 0;
$count_designs = 0;

if (isset($conn)) {
    // Unverified requests or invitations
    $r1 = @mysqli_query($conn, "SELECT COUNT(*) FROM request WHERE is_verified = 0");
    $c1 = ($r1) ? mysqli_fetch_row($r1)[0] : 0;
    $r2 = @mysqli_query($conn, "SELECT COUNT(*) FROM invitation WHERE is_verified = 0");
    $c2 = ($r2) ? mysqli_fetch_row($r2)[0] : 0;
    $count_unverified_req = $c1 + $c2;

    $res = @mysqli_query($conn, "SELECT COUNT(*) FROM designer");
    $count_designers = ($res) ? mysqli_fetch_row($res)[0] : 0;

    $res = @mysqli_query($conn, "SELECT COUNT(*) FROM model");
    $count_models = ($res) ? mysqli_fetch_row($res)[0] : 0;

    $res = @mysqli_query($conn, "SELECT COUNT(*) FROM design");
    $count_designs = ($res) ? mysqli_fetch_row($res)[0] : 0;
}
?>
<div class="sidebar" style="width: 260px;">
    <div>
        <div class="logo">CASTFLOW</div>
        <div class="admin-role-tag">Admin Console</div>
        <div style="font-size: 12px; color: var(--muted); margin-top: 6px;">
            Logged in: <strong style="color:var(--white);"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></strong>
        </div>
    </div>
    <nav style="overflow-y: auto; max-height: calc(100vh - 200px); padding-right: 4px;">
        <a href="dashboard.php" class="<?php if ($page=='dashboard.php') echo 'active'; ?>">
            <span>Overview Dashboard</span>
        </a>
        <a href="requests.php" class="<?php if ($page=='requests.php') echo 'active'; ?>" title="View Model and Designer requests and verification">
            <span>Requests & Invites</span>
            <?php if ($count_unverified_req > 0) { ?>
                <span class="nav-count alert" title="<?php echo $count_unverified_req; ?> unverified"><?php echo $count_unverified_req; ?></span>
            <?php } ?>
        </a>
        <a href="designers.php" class="<?php if ($page=='designers.php') echo 'active'; ?>">
            <span>Designers</span>
            <span class="nav-count"><?php echo $count_designers; ?></span>
        </a>
        <a href="models.php" class="<?php if ($page=='models.php') echo 'active'; ?>">
            <span>Models</span>
            <span class="nav-count"><?php echo $count_models; ?></span>
        </a>
        <a href="designs.php" class="<?php if ($page=='designs.php') echo 'active'; ?>">
            <span>Designs Catalog</span>
            <span class="nav-count"><?php echo $count_designs; ?></span>
        </a>
        <a href="photos.php" class="<?php if ($page=='photos.php') echo 'active'; ?>">
            <span>Portfolio Photos</span>
        </a>
        <a href="availability.php" class="<?php if ($page=='availability.php') echo 'active'; ?>">
            <span>Model Availability</span>
        </a>
        <a href="database.php" class="<?php if ($page=='database.php') echo 'active'; ?>" style="border-top: 1px solid #2a2a2a; margin-top: 6px; padding-top: 12px;">
            <span>Database Explorer</span>
            <span class="nav-count" style="background:var(--gold); color:var(--black); font-weight:700;">SQL</span>
        </a>
        <a href="admins.php" class="<?php if ($page=='admins.php') echo 'active'; ?>">
            <span>Admin Users</span>
        </a>
    </nav>
    <div style="border-top: 1px solid #2a2a2a; padding-top: 14px; display: flex; flex-direction: column; gap: 8px;">
        <a href="../index.php" target="_blank" style="font-size: 12.5px; color: var(--muted); text-decoration: none; display: flex; align-items: center; justify-content: space-between;">
            <span>Public Platform</span>
            <span>&nearr;</span>
        </a>
        <a href="logout.php" class="logout-link" style="border: none; padding: 0;">Log out &rarr;</a>
    </div>
</div>
