<?php
// admin/products/admin_product_view.php - View and manage product inventory
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/product_dao.php';
require_once '../../dao/order_dao.php';
require_once '../../dao/variation_dao.php';

$productDAO = new ProductDAO($db);
$orderDAO = new OrderDAO($db);
$variationDAO = new VariationDAO($db);

$product_id = $_GET['id'] ?? null;
if (!$product_id || !is_numeric($product_id)) {
    header('Location: admin_product_list.php');
    exit();
}

$product = $productDAO->getProductById($product_id);
if (!$product) {
    header('Location: admin_product_list.php?message=Product not found');
    exit();
}

$message = '';
$error = '';

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $quantity = intval($_POST['quantity'] ?? 0);
    
    if ($quantity > 0) {
        if ($action === 'add_stock') {
            if ($productDAO->addStockTransaction($product_id, $quantity)) {
                $message = "Successfully added $quantity units to inventory";
            } else {
                $error = "Failed to add stock";
            }
        } elseif ($action === 'remove_stock') {
            if ($productDAO->recordStockMovement($product_id, $quantity, 'sale')) {
                $message = "Successfully recorded sale of $quantity units";
            } else {
                $error = "Failed to record sale";
            }
        }
        
        // Refresh product data
        $product = $productDAO->getProductById($product_id);
    } else {
        $error = "Quantity must be greater than 0";
    }
}

// Get units sold from order_items
$unitsSold = $orderDAO->getProductUnitsSold($product_id);

// Get product variations
$variations = $variationDAO->getVariationsForProduct($product_id);

$availableStock = $product['stock_quantity'];
$totalStock = $availableStock + $unitsSold; // Total = Available + Sold
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container">
                <a href="admin_product_list.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                
                <?php if (!empty($message)): ?>
                    <div class="message" style="margin: 16px 0;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="message" style="margin: 16px 0; background-color: var(--danger-light); color: var(--danger-dark); border-color: #fecaca;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="header">
                    <h1><i class="fas fa-box"></i> Product Details</h1>
                    <div style="display: flex; gap: 12px;">
                        <a href="admin_product_edit.php?id=<?php echo $product_id; ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Product
                        </a>
                        <a href="admin_product_delete.php?id=<?php echo $product_id; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this product?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
                
                <!-- Product Overview -->
                <div class="product-view-container">
                    <!-- Image Section -->
                    <div class="product-image-section">
                        <?php if ($product['image_path']): ?>
                            <img src="../../<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-main-image">
                        <?php else: ?>
                            <div class="no-image-placeholder">
                                <i class="fas fa-image"></i>
                                <p>No image available</p>
                            </div>
                        <?php endif; ?>
                        
                        <div style="text-align: center; color: var(--gray-500); font-size: 13px;">
                            Product ID: #<?php echo $product_id; ?>
                        </div>
                    </div>
                    
                    <!-- Info Section -->
                    <div class="product-info-section">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="product-meta">
                            <span class="category-badge" style="padding: 6px 14px; font-size: 13px;">
                                <i class="fas fa-tag"></i> <?php echo ucfirst($product['category']); ?>
                            </span>
                            <span class="collection-badge" style="padding: 6px 14px; font-size: 13px;">
                                <i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($product['collection'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        
                        <div class="price-display" style="margin: 24px 0;">
                            ₱<?php echo number_format($product['price'], 2); ?>
                        </div>
                        
                        <div style="margin-top: 24px;">
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-calendar-plus"></i> Created</span>
                                <span class="info-value"><?php echo date('F j, Y', strtotime($product['created_at'])); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fas fa-calendar-check"></i> Last Updated</span>
                                <span class="info-value"><?php echo date('F j, Y', strtotime($product['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <?php if ($product['description']): ?>
                <div class="description-section">
                    <h2><i class="fas fa-align-left"></i> Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Inventory Management -->
                <div class="inventory-section">
                    <h2><i class="fas fa-warehouse"></i> Inventory </h2>
                    
                    <!-- Stock Statistics -->
                    <div class="inventory-stats">
                        <div class="stat-box total">
                            <div class="stat-box-label">Total Stock</div>
                            <div class="stat-box-value"><?php echo $totalStock; ?></div>
                        </div>
                        <div class="stat-box available <?php echo ($availableStock <= 5) ? 'low-stock' : ''; ?>">
                            <div class="stat-box-label">
                                <?php echo ($availableStock <= 5) ? 'Available (LOW!)' : 'Available'; ?>
                            </div>
                            <div class="stat-box-value"><?php echo $availableStock; ?></div>
                        </div>
                        <div class="stat-box sold">
                            <div class="stat-box-label">Units Sold</div>
                            <div class="stat-box-value"><?php echo $unitsSold; ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($variations)): ?>
                    <!-- Product Variations -->
                    <div style="margin-top: 32px;">
                        <h3 style="color: var(--gray-700); font-size: 16px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-swatchbook"></i> Product Variations
                        </h3>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--radius-md); overflow: hidden;">
                                <thead>
                                    <tr style="background: var(--gray-50); border-bottom: 2px solid var(--gray-200);">
                                        <th style="text-align: left; padding: 12px 16px; font-weight: 600; color: var(--gray-700); font-size: 14px;">
                                            <i class="fas fa-palette"></i> Color
                                        </th>
                                        <th style="text-align: left; padding: 12px 16px; font-weight: 600; color: var(--gray-700); font-size: 14px;">
                                            <i class="fas fa-ruler"></i> Size
                                        </th>
                                        <th style="text-align: left; padding: 12px 16px; font-weight: 600; color: var(--gray-700); font-size: 14px;">
                                            <i class="fas fa-boxes"></i> Stock
                                        </th>
                                        <th style="text-align: left; padding: 12px 16px; font-weight: 600; color: var(--gray-700); font-size: 14px;">
                                            <i class="fas fa-tag"></i> Price
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($variations as $variation): ?>
                                    <tr style="border-bottom: 1px solid var(--gray-100);">
                                        <td style="padding: 14px 16px; color: var(--gray-800); font-size: 14px;">
                                            <?php if ($variation['color']): ?>
                                                <span style="background: var(--gray-100); padding: 4px 10px; border-radius: var(--radius-sm); display: inline-block;">
                                                    <?php echo htmlspecialchars($variation['color']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--gray-400); font-style: italic;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 14px 16px; color: var(--gray-800); font-size: 14px;">
                                            <?php if ($variation['size']): ?>
                                                <span style="background: var(--gray-100); padding: 4px 10px; border-radius: var(--radius-sm); display: inline-block;">
                                                    <?php echo htmlspecialchars($variation['size']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--gray-400); font-style: italic;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 14px 16px; color: var(--gray-800); font-size: 14px; font-weight: 600;">
                                            <?php 
                                            $varStock = $variation['stock_quantity'] ?? 0;
                                            $stockColor = $varStock <= 5 ? 'var(--danger)' : 'var(--success)';
                                            ?>
                                            <span style="color: <?php echo $stockColor; ?>;">
                                                <?php echo $varStock; ?> units
                                            </span>
                                        </td>
                                        <td style="padding: 14px 16px; color: var(--gray-800); font-size: 14px; font-weight: 600;">
                                            <?php if ($variation['price'] !== null && $variation['price'] !== ''): ?>
                                                ₱<?php echo number_format($variation['price'], 2); ?>
                                            <?php else: ?>
                                                <span style="color: var(--gray-500); font-style: italic; font-weight: 400;">
                                                    Base price (₱<?php echo number_format($product['price'], 2); ?>)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 12px; padding: 12px; background: var(--gray-50); border-radius: var(--radius-sm); color: var(--gray-600); font-size: 13px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i>
                            <span>Variations can be added or removed from the <a href="admin_product_edit.php?id=<?php echo $product_id; ?>" style="color: var(--primary-green); font-weight: 600; text-decoration: none;">Edit Product</a></span>
                        </div>
                    </div>
                    <?php endif; ?>
                
                </div>
            </div>
        </div>
    </div>
    
    <script>
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
    </script>
</body>
</html>