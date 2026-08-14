<?php $page = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar">
    <div>
        <div class="logo">CASTFLOW</div>
        <div class="role-tag">model portfolio</div>
    </div>
    <nav>
        <a href="dashboard.php" class="<?php if ($page=='dashboard.php') echo 'active'; ?>">Dashboard</a>
        <a href="portfolio.php" class="<?php if ($page=='portfolio.php') echo 'active'; ?>">My Portfolio</a>
        <a href="availability.php" class="<?php if ($page=='availability.php') echo 'active'; ?>">My Availability</a>
        <a href="browse_designers.php" class="<?php if ($page=='browse_designers.php' || $page=='designer_view.php') echo 'active'; ?>">Browse Designers</a>
        <a href="invitations.php" class="<?php if ($page=='invitations.php') echo 'active'; ?>">Invitations Received</a>
        <a href="requests.php" class="<?php if ($page=='requests.php') echo 'active'; ?>">Requests Sent</a>
        <a href="profile.php" class="<?php if ($page=='profile.php') echo 'active'; ?>">My Profile</a>
    </nav>
    <a href="../logout.php" class="logout-link">Log out &rarr;</a>
</div>
