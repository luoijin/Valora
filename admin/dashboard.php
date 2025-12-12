<?php
// admin/dashboard.php - Admin Dashboard with Responsive Sidebar
session_start();

// IMPORTANT: Only check session variables, never query database for status here
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../dao/user_dao.php';
require_once __DIR__ . '/../dao/product_dao.php';

$userDAO = new UserDAO($db);
$productDAO = new ProductDAO($db);

$user_count = $userDAO->getUserCount();
$product_count = $productDAO->getProductCount();
$low_stock_products = $productDAO->getLowStockProducts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Valora</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
           
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="number"><?php echo $user_count; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <div class="number"><?php echo $product_count; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Low Stock Items</h3>
                    <div class="number"><?php echo count($low_stock_products); ?></div>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <?php if (!empty($low_stock_products)): ?>
            <div class="content-section">
                <h2>⚠️ Low Stock Products</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td style="color: #e74c3c; font-weight: bold;"><?php echo $product['stock_quantity']; ?></td>
                            <td>
                                <a href="products/admin_product_edit.php?id=<?php echo $product['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
</body>
</html>