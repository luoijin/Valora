<?php
// admin/cart/admin_cart_view.php - View customer cart
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

$user_id = $_GET['user_id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    header('Location: admin_cart_list.php');
    exit();
}

$user = $userDAO->getUserById($user_id);
if (!$user) {
    header('Location: admin_cart_list.php');
    exit();
}

$cart_items = $cartDAO->getCartByUserId($user_id);
$cart_total = $cartDAO->getCartTotal($user_id);

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
    <title>View Cart - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { margin-bottom: 30px; }
        .customer-info { background-color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; cursor: pointer; border: none; }
        .btn:hover { background-color: #2980b9; }
        .btn-danger { background-color: #e74c3c; }
        .btn-danger:hover { background-color: #c0392b; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; background-color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ecf0f1; }
        th { background-color: #2c3e50; color: white; }
        tr:hover { background-color: #f9f9f9; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .back-link { color: #3498db; text-decoration: none; margin-bottom: 20px; display: inline-block; }
        .price { color: #27ae60; font-weight: bold; }
        .total-section { background-color: white; padding: 20px; border-radius: 5px; text-align: right; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .total-amount { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .product-image { width: 80px; height: 80px; object-fit: cover; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_cart_list.php" class="back-link">← Back to Carts</a>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="header">
            <h1>Customer Cart</h1>
        </div>
        
        <div class="customer-info">
            <strong>Customer:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?><br>
            <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?><br>
            <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
        </div>
        
        <?php if (!empty($cart_items)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <?php if ($item['image_path']): ?>
                                <img src="../../<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                            <?php else: ?>
                                <span style="color: #999;">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td class="price">$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td class="price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        <td>
                            <a href="admin_cart_remove.php?user_id=<?php echo $user_id; ?>&product_id=<?php echo $item['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item?');">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total-section">
                <p>Cart Total: <span class="total-amount">$<?php echo number_format($cart_total, 2); ?></span></p>
                <a href="admin_cart_clear.php?user_id=<?php echo $user_id; ?>" class="btn btn-danger" onclick="return confirm('Clear entire cart?');">Clear Cart</a>
            </div>
        <?php else: ?>
            <p style="text-align: center; margin-top: 30px; color: #7f8c8d;">Cart is empty.</p>
        <?php endif; ?>
    </div>
</body>
</html>
