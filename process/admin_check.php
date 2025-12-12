<?php
// process/admin_check.php - Check if user is admin and redirect if not
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../pages/shop.php');
    exit();
}
?>
