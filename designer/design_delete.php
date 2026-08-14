<?php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $design_id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM design WHERE design_id = $design_id AND designer_id = $id");
}

header("Location: designs.php");
exit;
?>
