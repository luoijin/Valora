<?php
// admin/orders/admin_order_list.php - List all orders
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/order_dao.php';

$orderDAO = new OrderDAO($db);

$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$orders = $orderDAO->getAllOrders($limit, $offset);
$summary = $orderDAO->getOrderSummary();

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
    <title>Manage Orders - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <?php if (!empty($message)): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="header">
                    <h1>Order Management</h1>
                </div>
                
                <!-- Statistics -->
                <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr); gap: 16px;">
                    <div class="stat-card" style="padding: 16px;">
                        <h3 style="font-size: 11px; margin-bottom: 8px;">Total Orders</h3>
                        <div class="number" style="font-size: 28px; margin: 0;"><?php echo $summary['total_orders']; ?></div>
                    </div>
                    <div class="stat-card" style="border-left-color: var(--success); padding: 16px;">
                        <h3 style="font-size: 11px; margin-bottom: 8px;">Completed</h3>
                        <div class="number" style="color: var(--success); font-size: 28px; margin: 0;"><?php echo $summary['completed_orders']; ?></div>
                    </div>
                    <div class="stat-card" style="border-left-color: var(--warning); padding: 16px;">
                        <h3 style="font-size: 11px; margin-bottom: 8px;">Pending</h3>
                        <div class="number" style="color: var(--warning); font-size: 28px; margin: 0;"><?php echo $summary['pending_orders']; ?></div>
                    </div>
                    <div class="stat-card" style="border-left-color: var(--danger); padding: 16px;">
                        <h3 style="font-size: 11px; margin-bottom: 8px;">Cancelled</h3>
                        <div class="number" style="color: var(--danger); font-size: 28px; margin: 0;"><?php echo $summary['cancelled_orders'] ?? 0; ?></div>
                    </div>
                    <div class="stat-card" style="border-left-color: var(--primary-green); padding: 16px;">
                        <h3 style="font-size: 11px; margin-bottom: 8px;">Total Revenue</h3>
                        <div class="number" style="color: var(--primary-green); font-size: 24px; margin: 0;">₱<?php echo number_format($summary['total_revenue'], 2); ?></div>
                    </div>
                </div>
                
                <div class="content-section">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td class="price">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="admin_order_view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">View</a>
                                    <a href="admin_order_edit.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">Edit</a>
                                    <a href="admin_order_delete.php?id=<?php echo $order['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (empty($orders)): ?>
                        <p style="text-align: center; padding: 30px; color: #7f8c8d;">No orders found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const sidebar = document.getElementById('sidebar');
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileOverlay = document.getElementById('mobileOverlay');
        
        // Load saved state from localStorage
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
        }
        
        // Toggle sidebar
        hamburgerBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            
            // Handle mobile overlay
            if (window.innerWidth <= 768 && !sidebar.classList.contains('collapsed')) {
                mobileOverlay.classList.add('active');
            } else {
                mobileOverlay.classList.remove('active');
            }
        });
        
        // Close sidebar when clicking overlay (mobile)
        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.add('collapsed');
            mobileOverlay.classList.remove('active');
            localStorage.setItem('sidebarCollapsed', 'true');
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                mobileOverlay.classList.remove('active');
            }
        });
    </script>
</body>
</html>