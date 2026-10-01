<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config.php');

// Verify admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Auto-check schema migrations once to ensure required tables/columns exist seamlessly
function ensureAdminSchema($conn) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    // Helper to add missing column
    $addCol = function($tbl, $col, $def) use ($conn) {
        $q = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl` LIKE '$col'");
        if ($q && mysqli_num_rows($q) == 0) {
            @mysqli_query($conn, "ALTER TABLE `$tbl` ADD `$col` $def");
        }
    };

    $addCol('model', 'is_verified', "TINYINT(1) NOT NULL DEFAULT 0");
    $addCol('designer', 'is_verified', "TINYINT(1) NOT NULL DEFAULT 0");
    $addCol('request', 'is_verified', "TINYINT(1) NOT NULL DEFAULT 0");
    $addCol('invitation', 'is_verified', "TINYINT(1) NOT NULL DEFAULT 0");
    $addCol('model', 'created_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    $addCol('designer', 'created_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

    // Ensure admin table exists
    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS admin (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

ensureAdminSchema($conn);
?>
