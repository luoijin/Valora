<?php
// actions/cancel_order.php
session_start();
require_once '../../config.php';
require_once '../../dao/order_dao.php';
require_once '../../dao/product_dao.php';

header('Content-Type: application/json');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit();
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $order_id = $input['order_id'] ?? null;

    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        exit();
    }

    $orderDAO = new OrderDAO($db);
    $productDAO = new ProductDAO($db);
    $user_id = $_SESSION['user_id'];

    // Get order and verify it belongs to the user
    $order = $orderDAO->getOrderById($order_id);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }

    if ($order['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    // Only allow cancellation of pending orders
    if ($order['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Only pending orders can be cancelled']);
        exit();
    }

    // Begin transaction
    $db->beginTransaction();

    try {
        // Get order items to restore stock
        $orderItems = $orderDAO->getOrderItems($order_id);
        
        // Restore stock for each item
        foreach ($orderItems as $item) {
            $productDAO->addStockTransaction($item['product_id'], $item['quantity']);
        }

        // Cancel the order
        $result = $orderDAO->updateOrderStatus($order_id, 'cancelled');

        if (!$result) {
            throw new Exception('Failed to update order status');
        }

        // Commit transaction
        $db->commit();

        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);

    } catch (Exception $e) {
        // Rollback on error
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Cancel Order Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>