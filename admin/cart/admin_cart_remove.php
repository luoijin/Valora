<?php
// admin/cart/admin_cart_remove.php - Remove item from cart
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/cart_dao.php';

$cartDAO = new CartDAO($db);

$user_id = $_GET['user_id'] ?? null;
$product_id = $_GET['product_id'] ?? null;

if (!$user_id || !is_numeric($user_id) || !$product_id || !is_numeric($product_id)) {
    header('Location: admin_cart_list.php');
    exit();
}

if ($cartDAO->removeFromCart($user_id, $product_id)) {
    header('Location: admin_cart_view.php?user_id=' . $user_id . '&message=Item removed successfully');
} else {
    header('Location: admin_cart_view.php?user_id=' . $user_id . '&message=Failed to remove item');
}
exit();
?>
