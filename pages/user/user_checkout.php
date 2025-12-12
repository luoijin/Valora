<?php
session_start();
require_once '../../config.php';
require_once '../../dao/cart_dao.php';
require_once '../../dao/order_dao.php';
require_once '../../dao/product_dao.php';
require_once '../../dao/user_dao.php';
require_once '../../dao/variation_dao.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$cartDAO = new CartDAO($db);
$orderDAO = new OrderDAO($db);
$productDAO = new ProductDAO($db);
$userDAO = new UserDAO($db);
$variationDAO = new VariationDAO($db);

$user_id = $_SESSION['user_id'];
$user = $userDAO->getUserById($user_id);

// Get cart items
$cartItems = $cartDAO->getCartByUserId($user_id);

// Redirect if cart is empty
if (empty($cartItems)) {
    header('Location: user_cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal >= 5000 ? 0 : 500;
$total = $subtotal + $shipping;

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $zipCode = trim($_POST['zipCode'] ?? '');
    $paymentMethod = $_POST['paymentMethod'] ?? 'cod';
    
    // Validation
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($city)) $errors[] = 'City is required';
    if (empty($province)) $errors[] = 'Province is required';
    if (empty($zipCode)) $errors[] = 'Zip code is required';
    
    if (empty($errors)) {
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Create order
            $order_id = $orderDAO->createOrder($user_id, $total, 'pending');
            
            // Add order items and update stock
            foreach ($cartItems as $item) {
                $product = $productDAO->getProductById($item['product_id']);
                if (!$product) {
                    throw new Exception('Product not found while creating order.');
                }

                // Determine stock based on variation if present
                $availableStock = $item['stock_quantity'];
                if ($availableStock < $item['quantity']) {
                    throw new Exception('Insufficient stock for ' . $product['name']);
                }
                
                // Add order item with variation details
                $orderDAO->addOrderItem(
                    $order_id, 
                    $item['product_id'], 
                    $item['quantity'], 
                    $item['price'],
                    $item['variation_id'] ?? null,
                    $item['variation_color'] ?? $item['color'] ?? null,
                    $item['variation_size'] ?? $item['size'] ?? null
                );
                
                // Update stock: prefer variation stock when available
                if (!empty($item['variation_id'])) {
                    $variationDAO->decrementStock($item['variation_id'], $item['quantity']);
                } else {
                    $newStock = max(0, $product['stock_quantity'] - $item['quantity']);
                    $productDAO->updateStock($item['product_id'], $newStock);
                }
            }
            
            // Clear cart
            $cartDAO->clearCart($user_id);
            
            // Store order details in session for confirmation page
            $_SESSION['last_order'] = [
                'order_id' => $order_id,
                'customer' => [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                ],
                'shipping' => [
                    'address' => $address,
                    'city' => $city,
                    'province' => $province,
                    'zipCode' => $zipCode,
                ],
                'payment' => $paymentMethod,
                'total' => $total,
                    'items' => $cartItems
            ];
            
            // Commit transaction
            $db->commit();
            
            // Redirect to order confirmation
            header('Location: user_order.php?order=' . $order_id);
            exit();
            
        } catch (Exception $e) {
            // Rollback on error
            $db->rollBack();
            $errors[] = 'Order processing failed: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Valora</title>
    <link rel="stylesheet" href="../../assets/css/checkout.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <a href="user_home_page.php" class="logo">
          <img src="../../assets/images/logo/valora-logo-text.png" width="179" height="26" alt="Valora">
        </a>
    </header>

    <div class="container">
        <div class="checkout-header">
            <h1>Checkout</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="user_checkout.php" id="checkoutForm">
            <div class="checkout-layout">
                <div class="checkout-form">
                    <!-- Contact Information -->
                    <div class="form-section">
                        <h2>Contact Information</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number *</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="form-section">
                        <h2>Shipping Address</h2>
                        <div class="form-group">
                            <label>Street Address *</label>
                            <input type="text" name="address" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>City *</label>
                                <input type="text" name="city" required>
                            </div>
                            <div class="form-group">
                                <label>Province *</label>
                                <select name="province" required>
                                    <option value="">Select Province</option>
                                    <option value="Cebu">Cebu</option>
                                    <option value="Manila">Manila</option>
                                    <option value="Davao">Davao</option>
                                    <option value="Iloilo">Iloilo</option>
                                    <option value="Baguio">Baguio</option>
                                    <option value="Makati">Makati</option>
                                    <option value="Quezon City">Quezon City</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Zip Code *</label>
                            <input type="text" name="zipCode" pattern="[0-9]{4}" title="Please enter a valid 4-digit zip code" required>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-section">
                        <h2>Payment Method</h2>
                        <div class="payment-methods">
                            <label class="payment-option selected">
                                <input type="radio" name="paymentMethod" value="cod" checked>
                                <div>
                                    <strong>Cash on Delivery</strong>
                                    <p style="font-size: 13px; color: #888; margin-top: 5px;">Pay when you receive your items</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="order-summary">
                    <h2>Order Summary</h2>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <?php 
                        $imagePath = $item['image_path'];
                        if ($imagePath) {
                            $imagePath = preg_replace('/^\.\.\/+/', '', $imagePath);
                            $imageUrl = '../../' . $imagePath;
                        } else {
                            $imageUrl = '';
                        }
                        ?>
                        <?php if ($imagePath && file_exists($imageUrl)): ?>
                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                        <?php else: ?>
                            <div style="width:80px;height:80px;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);display:flex;align-items:center;justify-content:center;color:white;font-size:12px;border-radius:5px;">
                                No Image
                            </div>
                        <?php endif; ?>
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <?php 
                                $varColor = $item['variation_color'] ?? $item['color'] ?? '';
                                $varSize = $item['variation_size'] ?? $item['size'] ?? '';
                            ?>
                            <?php if (!empty($varColor) || !empty($varSize)): ?>
                                <p style="margin:4px 0;color:#555;font-size:13px;">
                                    Variation: <?php echo htmlspecialchars(trim(($varColor ? $varColor : '') . ' ' . ($varSize ? $varSize : ''))); ?>
                                </p>
                            <?php endif; ?>
                            <p>Qty: <?php echo $item['quantity']; ?></p>
                            <p class="item-price">₱<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span><?php echo $shipping === 0 ? 'FREE' : '₱' . number_format($shipping, 2); ?></span>
                    </div>
                    
                        <div class="summary-row total">
                            <span>Total</span>
                            <span class="price-row"><?php echo '₱' . number_format($total, 2); ?></span>
                        </div>
                    
                    <button type="submit" class="place-order-btn" id="placeOrderBtn">Place Order</button>
                    
                    <div class="secure-checkout">
                        🔒 Secure Checkout - Your information is protected
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Add visual feedback for selected payment method
        document.querySelectorAll('.payment-option').forEach(function(option) {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(function(o) {
                    o.classList.remove('selected');
                });
                this.classList.add('selected');
            });
        });

        // Form validation and submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('placeOrderBtn');
            btn.disabled = true;
            btn.textContent = 'Processing...';
            btn.style.opacity = '0.6';
        });
    </script>
</body>
</html>