<?php

$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "newcastflow";

$conn = null;

// Try default 3306 first
try {
    $conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, 3306);
} catch (Throwable $e) {}

// If port 3306 failed, try port 3307
if (!$conn) {
    try {
        $conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, 3307);
    } catch (Throwable $e) {}
}

// Fallback to localhost default socket/pipe
if (!$conn) {
    try {
        $conn = @mysqli_connect("localhost", $db_user, $db_pass, $db_name);
    } catch (Throwable $e) {}
}

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
