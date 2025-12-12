<?php
// admin/orders/admin_order_view.php - View order details
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

$order_items = $orderDAO->getOrderItems($order_id);

$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/admin_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles for order view - Add these to admin_form.css */
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
            border: 2px solid var(--gray-200);
        }
        
        .no-image {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-100);
            border-radius: var(--radius-sm);
            color: var(--gray-400);
            font-size: 12px;
            border: 2px dashed var(--gray-300);
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-light) 100%);
            color: var(--white);
            padding: 12px 24px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all var(--transition-base);
            box-shadow: 0 2px 8px rgba(13, 59, 46, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 59, 46, 0.35);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: var(--white);
            padding: 12px 24px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all var(--transition-base);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        .order-header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding: 16px;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            margin-top: 16px;
        }
        
        .order-header-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .order-header-item i {
            color: var(--primary-green);
        }
        
        .address-section {
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 20px;
            transition: all var(--transition-base);
        }
        
        .address-section:hover {
            border-color: var(--primary-green);
            background: rgba(13, 59, 46, 0.02);
        }
        
        .address-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: var(--primary-green);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid rgba(13, 59, 46, 0.1);
        }
        
        .address-header i {
            font-size: 16px;
        }
        
        .address-content {
            color: var(--gray-700);
            line-height: 1.6;
            font-size: 15px;
            padding-left: 26px;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-edit,
            .btn-delete {
                width: 100%;
                justify-content: center;
            }
            
            .address-content {
                padding-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <a href="admin_order_list.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        
        <div class="page-header">
            <h1>
                <i class="fas fa-receipt"></i> Order #<?php echo $order['id']; ?>
            </h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="success-alert">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>
        
        <div class="form-card">
            <!-- Order Header -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i> Order Summary
                </h2>
                <div class="order-header-info">
                    <div class="order-header-item">
                        <i class="fas fa-circle-notch"></i>
                        <div>
                            <span class="info-label">Status</span>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="order-header-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <span class="info-label">Order Date</span>
                            <span class="info-value"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="order-header-item">
                        <i class="fas fa-peso-sign"></i>
                        <div>
                            <span class="info-label">Total Amount</span>
                            <span class="info-value price-highlight">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Customer Information -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i> Customer Information
                </h2>
                <div class="order-info-grid">
                    <div class="info-item">
                        <i class="fas fa-user-circle"></i>
                        <div>
                            <span class="info-label">Name</span>
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
                        <i class="fas fa-user-tag"></i>
                        <div>
                            <span class="info-label">Username</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['username']); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <span class="info-label">Phone</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Address -->
                <?php if (!empty($order['address'])): ?>
                <div style="margin-top: 20px;">
                    <div class="address-section">
                        <div class="address-header">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Shipping Address</span>
                        </div>
                        <div class="address-content">
                            <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
                                <th><i class="fas fa-image"></i> Image</th>
                                <th><i class="fas fa-box"></i> Product</th>
                                <th><i class="fas fa-tag"></i> Unit Price</th>
                                <th><i class="fas fa-hashtag"></i> Quantity</th>
                                <th><i class="fas fa-calculator"></i> Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image_path']): ?>
                                        <img src="../../<?php echo htmlspecialchars($item['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="product-image">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="product-name">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                    <?php if (!empty($item['color']) || !empty($item['size'])): ?>
                                        <div style="font-size:12px;color:#4b5563;margin-top:4px;">
                                            Variation: <?php echo htmlspecialchars(trim(($item['color'] ?? '') . ' ' . ($item['size'] ?? ''))); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="price-cell">₱<?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="price-cell price-highlight">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                <td class="price-cell price-highlight"><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-cog"></i> Actions
                </h2>
                <div class="action-buttons">
                    <a href="admin_order_edit.php?id=<?php echo $order['id']; ?>" class="btn-edit">
                        <i class="fas fa-edit"></i> Edit Order
                    </a>
                    <a href="admin_order_delete.php?id=<?php echo $order['id']; ?>" 
                       class="btn-delete"
                       onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.');">
                        <i class="fas fa-trash-alt"></i> Delete Order
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>