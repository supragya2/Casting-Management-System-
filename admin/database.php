<?php
require_once('auth_check.php');

// Permitted tables
$allowed_tables = [
    'request' => 'Requests (Model &rarr; Designer)',
    'invitation' => 'Invitations (Designer &rarr; Model)',
    'designer' => 'Designers Registry',
    'model' => 'Models Registry',
    'design' => 'Designs Catalog',
    'photos' => 'Portfolio Photos',
    'model_availability' => 'Model Availability',
    'admin' => 'Admin Accounts'
];

$active_table = $_GET['table'] ?? 'request';
if (!array_key_exists($active_table, $allowed_tables)) {
    $active_table = 'request';
}

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=castflow_' . $active_table . '_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');

    // Get columns
    $col_res = mysqli_query($conn, "SHOW COLUMNS FROM `$active_table`");
    $headers = [];
    while ($c = mysqli_fetch_assoc($col_res)) {
        $headers[] = $c['Field'];
    }
    fputcsv($output, $headers);

    // Get rows
    $rows_res = mysqli_query($conn, "SELECT * FROM `$active_table`");
    while ($row = mysqli_fetch_assoc($rows_res)) {
        // Mask passwords
        if (isset($row['password'])) {
            $row['password'] = '[PROTECTED_HASH]';
        }
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Get columns definition
$col_query = mysqli_query($conn, "SHOW COLUMNS FROM `$active_table`");
$columns = [];
while ($c = mysqli_fetch_assoc($col_query)) {
    $columns[] = $c;
}

// Search
$search = trim($_GET['search'] ?? '');
$search_escaped = mysqli_real_escape_string($conn, $search);

$where = "WHERE 1=1";
if ($search !== '') {
    $or_clauses = [];
    foreach ($columns as $c) {
        $field = $c['Field'];
        $or_clauses[] = "`$field` LIKE '%$search_escaped%'";
    }
    if (!empty($or_clauses)) {
        $where .= " AND (" . implode(" OR ", $or_clauses) . ")";
    }
}

// Fetch total count and rows
$count_res = mysqli_query($conn, "SELECT COUNT(*) FROM `$active_table` $where");
$total_matching = ($count_res) ? mysqli_fetch_row($count_res)[0] : 0;

$data_query = mysqli_query($conn, "SELECT * FROM `$active_table` $where LIMIT 100");
$rows = [];
if ($data_query) {
    while ($r = mysqli_fetch_assoc($data_query)) {
        $rows[] = $r;
    }
}

// Counts for each table in tab navigation
$table_counts = [];
foreach ($allowed_tables as $tbl => $label) {
    $cnt_r = @mysqli_query($conn, "SELECT COUNT(*) FROM `$tbl`");
    $table_counts[$tbl] = ($cnt_r) ? mysqli_fetch_row($cnt_r)[0] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Content Explorer &bull; CastFlow Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .table-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 24px;
        }
        .table-pill {
            background: var(--white);
            border: 1px solid #e6e3db;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .table-pill:hover {
            border-color: #333;
            color: var(--black);
        }
        .table-pill.active {
            background: var(--black);
            color: var(--white);
            border-color: var(--black);
        }
        .table-pill .count {
            background: #eee;
            color: #333;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 10px;
        }
        .table-pill.active .count {
            background: var(--gold);
            color: var(--black);
            font-weight: 700;
        }
        .schema-collapsible {
            background: var(--white);
            border: 1px solid #e6e3db;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .schema-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .schema-item {
            background: #fdfdfc;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="app-shell">
    <?php include('menu.php'); ?>
    <div class="main" style="max-width: 1500px;">
        <div class="page-head">
            <div class="eyebrow">Database Engine &bull; Structured Content Browser</div>
            <h1>Database Explorer</h1>
            <p>Direct structured inspection of every table, column schema, and data record in the system.</p>
        </div>

        <!-- Table Selector Tabs -->
        <div class="table-pills">
            <?php foreach ($allowed_tables as $tbl => $label) { ?>
                <a href="database.php?table=<?php echo urlencode($tbl); ?>" class="table-pill <?php if ($active_table === $tbl) echo 'active'; ?>">
                    <span><?php echo $tbl; ?></span>
                    <span class="count"><?php echo $table_counts[$tbl]; ?></span>
                </a>
            <?php } ?>
        </div>

        <!-- Schema Overview Card -->
        <div class="schema-collapsible">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong style="font-size:14px; text-transform:uppercase; letter-spacing:1px; color:#555;">
                        Schema for <code style="color:var(--gold); font-size:15px;"><?php echo htmlspecialchars($active_table); ?></code>
                    </strong>
                    <span style="font-size:12px; color:#777; margin-left:10px;">(<?php echo count($columns); ?> columns defined)</span>
                </div>
                <a href="database.php?table=<?php echo urlencode($active_table); ?>&export=csv" class="btn btn-sm btn-dark" title="Download raw table as CSV">
                    &darr; Export to CSV
                </a>
            </div>
            <div class="schema-grid">
                <?php foreach ($columns as $c) { ?>
                    <div class="schema-item">
                        <div style="font-weight:700; color:var(--black);"><?php echo htmlspecialchars($c['Field']); ?></div>
                        <div style="color:#666; font-family:monospace; font-size:11px; margin-top:2px;">
                            <?php echo htmlspecialchars($c['Type']); ?>
                            <?php if ($c['Key'] === 'PRI') echo '<span style="color:#c9a13b; font-weight:bold;"> [PK]</span>'; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Search & Filter -->
        <form method="GET" action="database.php" class="filter-bar">
            <input type="hidden" name="table" value="<?php echo htmlspecialchars($active_table); ?>">
            <div class="filter-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search across all fields in <?php echo $active_table; ?>..." class="filter-input" style="min-width:320px;">
                <button type="submit" class="btn btn-sm btn-dark">Search</button>
                <?php if ($search !== '') { ?>
                    <a href="database.php?table=<?php echo urlencode($active_table); ?>" class="btn btn-sm btn-ghost">Reset</a>
                <?php } ?>
            </div>
            <div style="font-size: 13px; color: #777;">
                Showing <strong><?php echo count($rows); ?></strong> of <strong><?php echo $total_matching; ?></strong> rows
            </div>
        </form>

        <!-- Records Table -->
        <?php if (empty($rows)) { ?>
            <div class="empty-state">No records found in table <strong><?php echo htmlspecialchars($active_table); ?></strong>.</div>
        <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $c) { ?>
                                <th><?php echo htmlspecialchars($c['Field']); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r) { ?>
                        <tr>
                            <?php foreach ($columns as $c) { 
                                $field = $c['Field'];
                                $val = $r[$field] ?? null;
                            ?>
                            <td>
                                <?php 
                                if ($val === null) {
                                    echo '<span style="color:#bbb; font-style:italic;">NULL</span>';
                                } elseif ($field === 'is_verified') {
                                    if ((int)$val === 1) {
                                        echo '<span class="badge badge-verified">✓ 1 (Verified)</span>';
                                    } else {
                                        echo '<span class="badge badge-unverified">0 (Unverified)</span>';
                                    }
                                } elseif ($field === 'status') {
                                    echo '<span class="badge badge-' . htmlspecialchars(strtolower($val)) . '">' . htmlspecialchars(ucfirst($val)) . '</span>';
                                } elseif ($field === 'password') {
                                    echo '<span style="color:#999; font-family:monospace; font-size:11px;">••••••••••••</span>';
                                } elseif (in_array($field, ['image', 'profile_image']) && !empty($val)) {
                                    $subfolder = ($active_table === 'design') ? 'designs/' : (($active_table === 'photos') ? 'photos/' : '');
                                    $img_src = '../uploads/' . $subfolder . htmlspecialchars($val);
                                    echo '<div style="display:flex; align-items:center; gap:8px;">';
                                    echo '<img src="' . $img_src . '" class="table-thumb" style="width:36px; height:36px;" onerror="this.style.display=\'none\';">';
                                    echo '<span class="code-tag">' . htmlspecialchars($val) . '</span>';
                                    echo '</div>';
                                } elseif (strlen((string)$val) > 70) {
                                    echo htmlspecialchars(substr((string)$val, 0, 70)) . '&hellip;';
                                } else {
                                    echo htmlspecialchars((string)$val);
                                }
                                ?>
                            </td>
                            <?php } ?>
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
