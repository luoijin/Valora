<?php
session_start();
require_once '../../config.php';
require_once '../../dao/order_dao.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$orderDAO = new OrderDAO($db);

// Get order ID from URL or session
$order_id = $_GET['order'] ?? null;

if (!$order_id) {
    header('Location: user_shop_page.php');
    exit();
}

// Get order details from database
$order = $orderDAO->getOrderById($order_id);
$order_items = $orderDAO->getOrderItems($order_id);

// Verify order belongs to logged-in user
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: user_shop_page.php');
    exit();
}

// Get order data from session (for additional details)
$orderData = $_SESSION['last_order'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/order.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Valora</title>
    
</head>
<body>
    <div class="top-banner">
        THANK YOU FOR YOUR ORDER!
    </div>

    <header>
        <a href="user_home_page.php" class="logo">
        <img src="../../assets/images/logo/valora-logo-text.png" width="179" height="26" alt="Valora">
      </a>
    </header>

    <div class="container">
        <div class="confirmation-card">
            <div class="success-icon">✓</div>
            
            <h1>Order Confirmed!</h1>
            <p class="order-number">Order Number: <strong>#<?php echo $order['id']; ?></strong></p>
            
            <div class="confirmation-message">
                <p>
                    Thank you for your order! We're thrilled to be part of your special day. 
                    A confirmation email has been sent to <strong><?php echo htmlspecialchars($order['email']); ?></strong>
                </p>
            </div>

            <div class="order-details">
                <h2>Order Details</h2>
                
                <?php if ($orderData): ?>
                <div class="detail-section">
                    <h3>Shipping Address</h3>
                    <p><?php echo htmlspecialchars($orderData['customer']['firstName'] . ' ' . $orderData['customer']['lastName']); ?></p>
                    <p><?php echo htmlspecialchars($orderData['shipping']['address']); ?></p>
                    <p><?php echo htmlspecialchars($orderData['shipping']['city'] . ', ' . $orderData['shipping']['province'] . ' ' . $orderData['shipping']['zipCode']); ?></p>
                    <p><?php echo htmlspecialchars($orderData['customer']['phone']); ?></p>
                </div>

                <div class="detail-section">
                    <h3>Payment Method</h3>
                    <p>
                        <?php 
                        $paymentMethods = [
                            'cod' => 'Cash on Delivery',
                            'gcash' => 'GCash',
                            'bank' => 'Bank Transfer',
                            'card' => 'Credit/Debit Card'
                        ];
                        echo $paymentMethods[$orderData['payment']] ?? 'Cash on Delivery';
                        ?>
                    </p>
                </div>
                <?php endif; ?>

                <div class="detail-section">
                    <h3>Order Items</h3>
                    <div class="order-items">
                        <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div>
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <?php if (!empty($item['color']) || !empty($item['size'])): ?>
                                    <div class="item-details">Variation: <?php echo htmlspecialchars(trim(($item['color'] ?? '') . ' ' . ($item['size'] ?? ''))); ?></div>
                                <?php endif; ?>
                                <div class="item-details">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-name">₱<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>What's Next?</h3>
                    <p>📧 You'll receive an email confirmation shortly</p>
                    <p>📦 Your order will be prepared within 2-3 business days</p>
                    <p>🚚 Estimated delivery: 5-7 business days</p>
                    <p>📱 Track your order status in your account</p>
                </div>

                <div class="detail-section">
                    <h3>Order Status</h3>
                    <p><strong><?php echo ucfirst($order['status']); ?></strong></p>
                    <p>Placed on: <?php echo date('F d, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                </div>
            </div>

            <div class="action-buttons">
                <a href="user_shop_page.php" class="btn btn-secondary">Continue Shopping</a>
            </div>

            <div class="contact-info">
                <p>Need help with your order?</p>
                <p>Contact us at <a href="mailto:valoragowns@gmail.com">valoragowns@gmail.com</a> or call <a href="tel:+639497987581">+63 9497 987 581</a></p>
            </div>
        </div>
    </div>
</body>
</html>