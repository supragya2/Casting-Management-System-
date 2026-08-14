<?php $page = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar">
    <div>
        <div class="logo">CASTFLOW</div>
        <div class="role-tag">designer studio</div>
    </div>
    <nav>
        <a href="dashboard.php" class="<?php if ($page=='dashboard.php') echo 'active'; ?>">Dashboard</a>
        <a href="designs.php" class="<?php if ($page=='designs.php' || $page=='design_form.php') echo 'active'; ?>">My Designs</a>
        <a href="browse_models.php" class="<?php if ($page=='browse_models.php' || $page=='model_view.php') echo 'active'; ?>">Browse Models</a>
        <a href="invitations.php" class="<?php if ($page=='invitations.php') echo 'active'; ?>">Invitations Sent</a>
        <a href="requests.php" class="<?php if ($page=='requests.php') echo 'active'; ?>">Requests Received</a>
        <a href="profile.php" class="<?php if ($page=='profile.php') echo 'active'; ?>">My Profile</a>
    </nav>
    <a href="../logout.php" class="logout-link">Log out &rarr;</a>
</div>
