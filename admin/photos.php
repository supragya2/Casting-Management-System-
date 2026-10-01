<?php
require_once('auth_check.php');

$search = trim($_GET['search'] ?? '');
$search_escaped = mysqli_real_escape_string($conn, $search);

$sql = "
    SELECT p.*, m.model_name, m.email AS model_email, m.is_verified AS model_verified
    FROM photos p
    LEFT JOIN model m ON p.model_id = m.model_id
    WHERE 1=1
";

if ($search !== '') {
    $sql .= " AND (m.model_name LIKE '%$search_escaped%' OR p.description LIKE '%$search_escaped%')";
}

$sql .= " ORDER BY p.photo_id DESC";
$result = mysqli_query($conn, $sql);
$total_rows = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model Portfolios &bull; CastFlow Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }
        .photo-card {
            background: var(--white);
            border: 1px solid #e6e3db;
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .photo-img-box {
            aspect-ratio: 4 / 5;
            background: #f0eee6;
            overflow: hidden;
        }
        .photo-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .photo-img-box:hover img {
            transform: scale(1.04);
        }
        .photo-info {
            padding: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
    </style>
</head>
<body>
<div class="app-shell">
    <?php include('menu.php'); ?>
    <div class="main" style="max-width: 1400px;">
        <div class="page-head">
            <div class="eyebrow">Database Entities &bull; Visual Assets</div>
            <h1>Model Portfolio Photos</h1>
            <p>Review high-fashion portfolio shots and lookbooks uploaded by models across the platform.</p>
        </div>

        <form method="GET" action="photos.php" class="filter-bar">
            <div class="filter-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by model name or photo description..." class="filter-input" style="min-width:300px;">
                <button type="submit" class="btn btn-sm btn-dark">Search</button>
                <?php if ($search !== '') { ?>
                    <a href="photos.php" class="btn btn-sm btn-ghost">Reset</a>
                <?php } ?>
            </div>
            <div style="font-size: 13px; color: #777;">
                Total <strong><?php echo $total_rows; ?></strong> portfolio photos
            </div>
        </form>

        <?php if ($total_rows == 0) { ?>
            <div class="empty-state">No portfolio photos found.</div>
        <?php } else { ?>
            <div class="photo-gallery">
                <?php while ($p = mysqli_fetch_assoc($result)) { 
                    $img = $p['image'];
                    $img_src = (!empty($img) && file_exists(__DIR__ . '/../uploads/photos/' . $img))
                        ? '../uploads/photos/' . htmlspecialchars($img) : '';
                ?>
                <div class="photo-card">
                    <div class="photo-img-box">
                        <?php if ($img_src !== '') { ?>
                            <img src="<?php echo $img_src; ?>" alt="Portfolio Photo">
                        <?php } else { ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#999; font-size:13px;">No file image</div>
                        <?php } ?>
                    </div>
                    <div class="photo-info">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <strong><?php echo htmlspecialchars($p['model_name'] ?? 'Unknown Model'); ?></strong>
                            <?php if (!empty($p['model_verified'])) { ?>
                                <span class="badge badge-verified" style="font-size:10px;">✓ Verified</span>
                            <?php } ?>
                        </div>
                        <div style="font-size:12px; color:#777;"><?php echo htmlspecialchars($p['model_email'] ?? ''); ?></div>
                        <div style="font-size:13px; color:#444; margin-top:6px; flex:1;">
                            <?php echo htmlspecialchars($p['description'] ?? 'Portfolio look'); ?>
                        </div>
                        <div style="font-size:11px; color:#999; border-top:1px solid #eee; padding-top:8px; margin-top:8px;">
                            Photo ID #<?php echo $p['photo_id']; ?> &bull; Model ID #<?php echo $p['model_id']; ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>
