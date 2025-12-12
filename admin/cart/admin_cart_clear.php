<?php
// admin/cart/admin_cart_clear.php - Clear entire cart
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/cart_dao.php';

$cartDAO = new CartDAO($db);

$user_id = $_GET['user_id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    header('Location: admin_cart_list.php');
    exit();
}

if ($cartDAO->clearCart($user_id)) {
    header('Location: admin_cart_view.php?user_id=' . $user_id . '&message=Cart cleared successfully');
} else {
    header('Location: admin_cart_view.php?user_id=' . $user_id . '&message=Failed to clear cart');
}
exit();
?>
