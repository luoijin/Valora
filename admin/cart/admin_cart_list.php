<?php
// admin/cart/admin_cart_list.php - View all shopping carts
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/user_dao.php';
require_once '../../dao/cart_dao.php';

$userDAO = new UserDAO($db);
$cartDAO = new CartDAO($db);

// Get all users with active carts
$query = "SELECT DISTINCT u.id, u.username, u.email, COUNT(c.id) as cart_items, SUM(p.price * c.quantity) as cart_total
          FROM users u
          LEFT JOIN cart c ON u.id = c.user_id
          LEFT JOIN products p ON c.product_id = p.id
          WHERE u.role = 'customer'
          GROUP BY u.id
          HAVING cart_items > 0
          ORDER BY u.username";

$stmt = $db->prepare($query);
$stmt->execute();
$customers_with_carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Manage Carts - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f5f5f5; 
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .sidebar h2 {
            font-size: 20px;
            white-space: nowrap;
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed h2 {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .hamburger {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }
        
        .hamburger:hover {
            transform: scale(1.1);
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        
        .sidebar ul li {
            margin: 15px 0;
        }
        
        .sidebar ul li a {
            color: #ecf0f1;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .sidebar ul li a:hover {
            background-color: #34495e;
        }
        
        .sidebar ul li a.active {
            background-color: #3498db;
        }
        
        .menu-icon {
            font-size: 20px;
            width: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .menu-text {
            margin-left: 15px;
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .menu-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: 80px;
        }
        
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
        }
        
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            background-color: #3498db; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            cursor: pointer; 
            border: none; 
        }
        
        .btn:hover { 
            background-color: #2980b9; 
        }
        
        .btn-danger { 
            background-color: #e74c3c; 
        }
        
        .btn-danger:hover { 
            background-color: #c0392b; 
        }
        
        .btn-sm { 
            padding: 5px 10px; 
            font-size: 12px; 
        }
        
        .content-section {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        
        th, td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid #ecf0f1; 
        }
        
        th { 
            background-color: #2c3e50; 
            color: white; 
        }
        
        tr:hover { 
            background-color: #f9f9f9; 
        }
        
        .message { 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 5px; 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        
        .price { 
            color: #27ae60; 
            font-weight: bold; 
        }
        
        .cart-items { 
            background-color: #e8f4f8; 
            padding: 3px 8px; 
            border-radius: 3px; 
        }
        
        .logout-btn {
            background-color: #e74c3c;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
                transform: translateX(-250px);
            }
            
            .sidebar.collapsed {
                transform: translateX(0);
                width: 80px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.collapsed ~ .main-content {
                margin-left: 0;
            }
            
            .mobile-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
            
            .mobile-overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Valora Admin</h2>
                <button class="hamburger" id="hamburgerBtn">
                    <span>☰</span>
                </button>
            </div>
            <ul>
                <li><a href="../dashboard.php"><span class="menu-icon">📊</span><span class="menu-text">Dashboard</span></a></li>
                <li><a href="../users/admin_user_list.php"><span class="menu-icon">👥</span><span class="menu-text">Users</span></a></li>
                <li><a href="../products/admin_product_list.php"><span class="menu-icon">📦</span><span class="menu-text">Products</span></a></li>
                <li><a href="../orders/admin_order_list.php"><span class="menu-icon">📋</span><span class="menu-text">Orders</span></a></li>
                <li><a href="admin_cart_list.php" class="active"><span class="menu-icon">🛒</span><span class="menu-text">Shopping Carts</span></a></li>
                
                <li><a href="../../process/logout.php" class="logout-btn"><span class="menu-icon">🚪</span><span class="menu-text">Logout</span></a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <?php if (!empty($message)): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="header">
                    <h1>Shopping Carts</h1>
                </div>
                
                <div class="content-section">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Items in Cart</th>
                                <th>Cart Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers_with_carts as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['username']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><span class="cart-items"><?php echo $customer['cart_items']; ?></span></td>
                                <td class="price">$<?php echo number_format($customer['cart_total'], 2); ?></td>
                                <td>
                                    <a href="admin_cart_view.php?user_id=<?php echo $customer['id']; ?>" class="btn btn-sm">View</a>
                                    <a href="admin_cart_clear.php?user_id=<?php echo $customer['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Clear this cart?');">Clear</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (empty($customers_with_carts)): ?>
                        <p style="text-align: center; padding: 30px; color: #7f8c8d;">No active carts found.</p>
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