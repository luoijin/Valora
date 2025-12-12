<?php
// admin/users/admin_user_delete.php - Delete user
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/user_dao.php';

$userDAO = new UserDAO($db);

$user_id = $_GET['id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    header('Location: admin_user_list.php');
    exit();
}

// Prevent deleting yourself
if ($user_id == $_SESSION['user_id']) {
    header('Location: admin_user_list.php?message=Cannot delete your own account');
    exit();
}

$user = $userDAO->getUserById($user_id);
if (!$user) {
    header('Location: admin_user_list.php');
    exit();
}

if ($userDAO->deleteUser($user_id)) {
    header('Location: admin_user_list.php?message=User deleted successfully');
} else {
    header('Location: admin_user_list.php?message=Failed to delete user');
}
exit();
?>
