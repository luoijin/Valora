<?php
// admin/orders/admin_order_edit.php - Edit order status
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/order_dao.php';

$orderDAO = new OrderDAO($db);
$errors = [];

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

$order_items = $orderDAO->getOrderItems($order_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    
    if (empty($status)) {
        $errors[] = 'Status is required';
    } elseif (!in_array($status, ['pending', 'completed', 'cancelled'])) {
        $errors[] = 'Invalid status';
    }
    
    if (empty($errors)) {
        if ($orderDAO->updateOrderStatus($order_id, $status)) {
            header('Location: admin_order_view.php?id=' . $order_id . '&message=Order status updated successfully');
            exit();
        } else {
            $errors[] = 'Failed to update order status';
        }
    }
    
    // Refresh order data
    $order = $orderDAO->getOrderById($order_id);
}

$statuses = ['pending', 'completed', 'cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/admin_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="edit-container">
        <a href="admin_order_list.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        
        <div class="page-header">
            <h1>
                <i class="fas fa-edit"></i> Edit Order #<?php echo $order['id']; ?>
            </h1>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="errors-alert">
                <div class="errors-alert-header">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Please fix the following errors:</span>
                </div>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="form-card">
            <!-- Order Information -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i> Order Information
                </h2>
                <div class="order-info-grid">
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <span class="info-label">Customer</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-peso-sign"></i>
                        <div>
                            <span class="info-label">Total Amount</span>
                            <span class="info-value price-highlight"><?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-circle-notch"></i>
                        <div>
                            <span class="info-label">Current Status</span>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-shopping-cart"></i> Order Items
                </h2>
                <div class="order-items-table">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-box"></i> Product</th>
                                <th><i class="fas fa-hashtag"></i> Quantity</th>
                                <th><i class="fas fa-tag"></i> Unit Price</th>
                                <th><i class="fas fa-calculator"></i> Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td class="product-name"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="price-cell">₱<?php echo number_format($item['price'], 2); ?></td>
                                <td class="price-cell price-highlight">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                <td class="price-cell price-highlight"><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Update Status -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-sync-alt"></i> Update Order Status
                </h2>
                <form method="POST">
                    <div class="form-grid single">
                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-toggle-on input-icon"></i>
                                Order Status <span class="required-star">*</span>
                            </label>
                            <select id="status" name="status" required>
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($s); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Select the new status for this order
                            </span>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions" style="margin-top: 24px; padding: 0; background: transparent;">
                        <a href="admin_order_list.php" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>