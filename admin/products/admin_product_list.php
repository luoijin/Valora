<?php
// admin/products/admin_product_list.php - List all products with inventory
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/product_dao.php';
require_once '../../dao/order_dao.php';

$productDAO = new ProductDAO($db);
$orderDAO = new OrderDAO($db);
$products = $productDAO->getAllProducts();

// Sort products by ID in ascending order
usort($products, function($a, $b) {
    return $a['id'] - $b['id'];
});

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
    <title>Product Inventory - Valora Admin</title>
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
                
                <!-- Check for low stock products -->
                <?php
                $lowStockProducts = array_filter($products, function($p) {
                    $availableStock = $p['stock_quantity'];
                    return $availableStock <= 5;
                });
                ?>
                
                <?php if (count($lowStockProducts) > 0): ?>
                    <div class="stock-warning-banner">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="warning-text">
                            <strong>Low Stock Alert:</strong> <?php echo count($lowStockProducts); ?> product(s) have low inventory levels and need restocking.
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="header">
                    <h1><i class="fas fa-boxes"></i> Product Inventory Management</h1>
                    <a href="admin_product_create.php" class="btn"><i class="fas fa-plus"></i> Add New Product</a>
                </div>
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <input type="text" id="searchInput" placeholder="Search products..." style="flex: 1; min-width: 200px;">
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="dress">Dress</option>
                        <option value="gown">Gown</option>
                    </select>
                    <select id="stockFilter">
                        <option value="">All Stock Levels</option>
                        <option value="low">Low Stock (≤5)</option>
                        <option value="medium">Medium Stock (6-20)</option>
                        <option value="high">High Stock (>20)</option>
                    </select>
                </div>
                
                <div class="content-section">
                    <table id="productsTable">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Collection</th>
                                <th>Price</th>
                                <th>Inventory</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <?php
                                // Get available stock from database
                                $availableStock = $product['stock_quantity'];
                                // Get units sold from order_items
                                $unitsSold = $orderDAO->getProductUnitsSold($product['id']);
                                // Calculate total stock (Available + Sold)
                                $totalStock = $availableStock + $unitsSold;
                                $isLowStock = $availableStock <= 5;
                            ?>
                            <tr 
                                data-category="<?php echo htmlspecialchars($product['category']); ?>" 
                                data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>"
                                data-collection="<?php echo htmlspecialchars(strtolower($product['collection'] ?? '')); ?>"
                                data-stock-level="<?php echo htmlspecialchars($availableStock); ?>">
                                <td class="product-image-cell">
                                    <?php if ($product['image_path']): ?>
                                        <img src="../../<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: var(--gray-200); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--gray-500);">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $product['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                                <td><span class="category-badge"><?php echo ucfirst($product['category']); ?></span></td>
                                <td><span class="collection-badge"><?php echo htmlspecialchars($product['collection'] ?? 'N/A'); ?></span></td>
                                <td class="price"><strong>₱<?php echo number_format($product['price'], 2); ?></strong></td>
                                <td>
                                    <div class="stock-details">
                                        <span class="inventory-badge total">
                                            <i class="fas fa-layer-group"></i> Total: <?php echo $totalStock; ?>
                                        </span>
                                        <span class="inventory-badge available <?php echo $isLowStock ? 'critical' : ''; ?>">
                                            <i class="fas fa-box-open"></i> Available: <?php echo $availableStock; ?>
                                        </span>
                                        <span class="inventory-badge purchased">
                                            <i class="fas fa-shopping-cart"></i> Sold: <?php echo $unitsSold; ?>
                                        </span>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin_product_view.php?id=<?php echo $product['id']; ?>" class="view-btn">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (empty($products)): ?>
                        <p style="text-align: center; padding: 30px; color: #7f8c8d;">No products found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar functionality
            const sidebar = document.getElementById('sidebar');
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const mobileOverlay = document.getElementById('mobileOverlay');
            
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebar.classList.add('collapsed');
            }
            
            hamburgerBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                
                if (window.innerWidth <= 768 && !sidebar.classList.contains('collapsed')) {
                    mobileOverlay.classList.add('active');
                } else {
                    mobileOverlay.classList.remove('active');
                }
            });
            
            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.add('collapsed');
                mobileOverlay.classList.remove('active');
                localStorage.setItem('sidebarCollapsed', 'true');
            });
            
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    mobileOverlay.classList.remove('active');
                }
            });
            
            // Filter functionality
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const stockFilter = document.getElementById('stockFilter');
            
            function filterTable() {
                const rows = document.querySelectorAll('#productsTable tbody tr');
                const searchTerm = searchInput.value.trim().toLowerCase();
                const categoryValue = categoryFilter.value.toLowerCase();
                const stockValue = stockFilter.value;
                
                rows.forEach(row => {
                    const name = row.dataset.name || '';
                    const category = row.dataset.category ? row.dataset.category.toLowerCase() : '';
                    const collection = row.dataset.collection || '';
                    const stockLevel = parseInt(row.dataset.stockLevel || '0', 10);
                    
                    let showRow = true;
                    
                    // Search by name or collection
                    if (searchTerm && !(name.includes(searchTerm) || collection.includes(searchTerm))) {
                        showRow = false;
                    }
                    
                    // Category filter
                    if (categoryValue && category !== categoryValue) {
                        showRow = false;
                    }
                    
                    // Stock filter
                    if (stockValue === 'low' && stockLevel > 5) {
                        showRow = false;
                    } else if (stockValue === 'medium' && (stockLevel <= 5 || stockLevel > 20)) {
                        showRow = false;
                    } else if (stockValue === 'high' && stockLevel <= 20) {
                        showRow = false;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                });
            }
                    
            searchInput.addEventListener('input', filterTable);
            categoryFilter.addEventListener('change', filterTable);
            stockFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html>