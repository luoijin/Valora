<?php
session_start();
require_once '../config.php';
require_once '../dao/crudDAO.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$crud = new crudDAO();
$user = $crud->login($username, $password);

if ($user) {
    // Check if user account is active
    $status = $user['status'] ?? 'active'; // Default to active if column doesn't exist
    
    if ($status === 'inactive') {
        // User account is deactivated
        header('Location: ../pages/login.php?error=deactivated');
        exit();
    }
    
    // Set session values using current users schema
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['first_name'] = $user['first_name'] ?? '';
    $_SESSION['last_name'] = $user['last_name'] ?? '';
    $_SESSION['role'] = $user['role'] ?? 'customer';

    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../pages/user/user_home_page.php');
    }
    exit();
} else {
    header('Location: ../pages/login.php?error=invalid');
    exit();
}
?>