<?php
// admin/orders/admin_order_delete.php - Delete order
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/order_dao.php';

$orderDAO = new OrderDAO($db);

$order_id = $_GET['id'] ?? null;
if (!$order_id || !is_numeric($order_id)) {
    header('Location: admin_order_list.php');
    exit();
}

$order = $orderDAO->getOrderById($order_id);
if (!$order) {
    header('Location: admin_order_list.php');
    exit();
}

if ($orderDAO->deleteOrder($order_id)) {
    header('Location: admin_order_list.php?message=Order deleted successfully');
} else {
    header('Location: admin_order_list.php?message=Failed to delete order');
}
exit();
?>
