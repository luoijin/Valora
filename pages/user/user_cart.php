<?php
// pages/user/user_cart
session_start();
require_once '../../config.php';
require_once '../../dao/cart_dao.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$cartDAO = new CartDAO($db);
$user_id = $_SESSION['user_id'];

// Get cart items from database
$cartItems = $cartDAO->getCartByUserId($user_id);

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal >= 5000 ? 0 : 500;
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Valora</title>
    <link rel="stylesheet" href="../../assets/css/user_cart.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <a href="user_home_page.php" class="logo">
          <img src="../../assets/images/logo/valora-logo-text.png" width="179" height="26" alt="Valora">
        </a>
        <nav>
            <a href="user_home_page.php">Home</a>
            <a href="user_home_page.php#collection">Collection</a>
            <a href="user_shop_page.php">Shop</a>
            <a href="user_home_page.php#dress">Dress</a>
            <a href="user_home_page.php#gown">Gown</a>
        </nav>
    </header>

    <div class="container">
        <h1>Shopping Cart</h1>
        <div class="breadcrumb">Home / Cart</div>

        <div id="cart-content">
            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <h2>Your cart is empty</h2>
                    <a href="user_shop_page.php" class="checkout-btn" style="display: inline-block; width: auto; padding: 16px 40px;">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>" data-item-price="<?php echo $item['price']; ?>">
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
                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div class="item-image-placeholder" style="width:150px;height:150px;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);display:<?php echo (!$imagePath || !file_exists($imageUrl)) ? 'flex' : 'none'; ?>;align-items:center;justify-content:center;color:white;font-size:14px;border-radius:8px;">
                                    No Image
                                </div>
                                
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <?php 
                                        $varColor = $item['variation_color'] ?? $item['color'] ?? '';
                                        $varSize = $item['variation_size'] ?? $item['size'] ?? '';
                                    ?>
                                    <?php if (!empty($varColor) || !empty($varSize)): ?>
                                        <p style="margin:4px 0;color:#444;font-size:13px;">
                                            Variation: 
                                            <?php echo htmlspecialchars(trim(($varColor ? $varColor : '') . ' ' . ($varSize ? $varSize : ''))); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="item-price price-row"><span class="currency">₱</span><span class="item-subtotal"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span></p>
                                    <p class="item-unit-price price-row" style="font-size: 12px; color: #888;"><span class="currency">₱</span><span><?php echo number_format($item['price'], 2); ?></span> each</p>
                                    <p class="stock-info">Stock: <?php echo $item['stock_quantity']; ?> available</p>
                                </div>
                                
                                <div class="item-actions">
                                    <div class="quantity-control">
                                        <button type="button" class="qty-btn qty-minus" data-cart-id="<?php echo $item['id']; ?>">−</button>
                                        <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                        <button type="button" class="qty-btn qty-plus" data-cart-id="<?php echo $item['id']; ?>" data-max-stock="<?php echo $item['stock_quantity']; ?>">+</button>
                                    </div>
                                    <br>
                                    <a href="#" class="remove-btn" data-cart-id="<?php echo $item['id']; ?>">Remove</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <a href="user_shop_page.php" class="continue-shopping">← Continue Shopping</a>
                    </div>

                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        <div class="order-summary-items">
                            <?php foreach ($cartItems as $item): 
                                $varColor = $item['variation_color'] ?? $item['color'] ?? '';
                                $varSize = $item['variation_size'] ?? $item['size'] ?? '';
                            ?>
                                <div class="summary-item-row">
                                    <div class="summary-item-text">
                                        <div class="summary-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <?php if (!empty($varColor) || !empty($varSize)): ?>
                                            <div class="summary-variation">Variation: <?php echo htmlspecialchars(trim(($varColor ? $varColor : '') . ' ' . ($varSize ? $varSize : ''))); ?></div>
                                        <?php endif; ?>
                                        <div class="summary-qty">Qty: <?php echo (int)$item['quantity']; ?></div>
                                    </div>
                                    <div class="summary-price price-row"><span class="currency">₱</span><span><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal" class="price-row"><span class="currency">₱</span><span><?php echo number_format($subtotal, 2); ?></span></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span id="shipping" class="price-row"><?php echo $shipping === 0 ? 'FREE' : '<span class="currency">₱</span><span>' . number_format($shipping, 2) . '</span>'; ?></span>
                        </div>
                        
                        <div id="shipping-note">
                            <?php if ($shipping === 0): ?>
                                <div class="shipping-note">
                                    🎉 You've qualified for free shipping!
                                </div>
                            <?php else: ?>
                                <div class="shipping-note">
                                    Add ₱<?php echo number_format(5000 - $subtotal, 2); ?> more for free shipping
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span id="total" class="price-row total-row"><span class="currency">₱</span><span><?php echo number_format($total, 2); ?></span></span>
                        </div>
                        
                        <a href="user_checkout.php"><button type="button" class="checkout-btn">Proceed to Checkout</button></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cart page loaded');
    
    const qtyPlusButtons = document.querySelectorAll('.qty-plus');
    const qtyMinusButtons = document.querySelectorAll('.qty-minus');
    const removeButtons = document.querySelectorAll('.remove-btn');
    
    console.log('Found buttons:', {
        plus: qtyPlusButtons.length,
        minus: qtyMinusButtons.length,
        remove: removeButtons.length
    });
    
    qtyPlusButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const cartId = this.getAttribute('data-cart-id');
            const maxStock = parseInt(this.getAttribute('data-max-stock'));
            const cartItem = this.closest('.cart-item');
            const qtyDisplay = cartItem.querySelector('.quantity-display');
            const currentQty = parseInt(qtyDisplay.textContent);
            
            console.log('Plus clicked:', cartId, 'Current:', currentQty, 'Max:', maxStock);
            
            if (currentQty >= maxStock) {
                showNotification('Only ' + maxStock + ' items available in stock', 'error');
                return;
            }
            
            updateQuantity(cartId, currentQty + 1, cartItem);
        });
    });
    
    qtyMinusButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const cartId = this.getAttribute('data-cart-id');
            const cartItem = this.closest('.cart-item');
            const qtyDisplay = cartItem.querySelector('.quantity-display');
            const currentQty = parseInt(qtyDisplay.textContent);
            
            console.log('Minus clicked:', cartId, 'Current:', currentQty);
            
            if (currentQty <= 1) {
                showNotification('Quantity cannot be less than 1', 'error');
                return;
            }
            
            updateQuantity(cartId, currentQty - 1, cartItem);
        });
    });
    
    removeButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const cartId = this.getAttribute('data-cart-id');
            console.log('Remove clicked:', cartId);
            removeItem(cartId);
        });
    });
});

function updateQuantity(cartId, newQuantity, cartItem) {
    console.log('Updating quantity:', cartId, newQuantity);
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('cart_id', cartId);
    formData.append('quantity', newQuantity);

    fetch('cart_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        console.log('Response received');
        return response.json();
    })
    .then(function(data) {
        console.log('Data:', data);
        if (data.success) {
            const qtyDisplay = cartItem.querySelector('.quantity-display');
            qtyDisplay.textContent = newQuantity;
            
            const itemPrice = parseFloat(cartItem.getAttribute('data-item-price'));
            const itemSubtotal = cartItem.querySelector('.item-subtotal');
            const newSubtotal = (itemPrice * newQuantity).toFixed(2);
            itemSubtotal.textContent = newSubtotal.replace(/\d(?=(\d{3})+\.)/g, '$&,');
            
            updateCartSummary(data);
            showNotification('Cart updated successfully');
        } else {
            showNotification(data.message || 'Failed to update', 'error');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showNotification('Failed to update cart', 'error');
    });
}

function removeItem(cartId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    console.log('Removing item:', cartId);
    
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('cart_id', cartId);

    fetch('cart_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        console.log('Remove response:', data);
        if (data.success) {
            const cartItem = document.querySelector('[data-cart-id="' + cartId + '"]');
            cartItem.style.animation = 'fadeOut 0.3s ease';
            
            setTimeout(function() {
                cartItem.remove();
                
                if (data.is_empty) {
                    location.reload();
                } else {
                    updateCartSummary(data);
                }
            }, 300);
            
            showNotification('Item removed from cart');
        } else {
            showNotification(data.message || 'Failed to remove item', 'error');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showNotification('Failed to remove item', 'error');
    });
}

function updateCartSummary(data) {
    const subtotalEl = document.getElementById('subtotal');
    const shippingEl = document.getElementById('shipping');
    const totalEl = document.getElementById('total');
    const shippingNote = document.getElementById('shipping-note');
    
    subtotalEl.innerHTML = '<span class="currency">₱</span><span>' + data.subtotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</span>';
    shippingEl.innerHTML = data.shipping === 0 ? 'FREE' : '<span class="currency">₱</span><span>' + data.shipping.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</span>';
    totalEl.innerHTML = '<span class="currency">₱</span><span>' + data.total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + '</span>';
    
    if (data.shipping === 0) {
        shippingNote.innerHTML = '<div class="shipping-note">🎉 You\'ve qualified for free shipping!</div>';
    } else {
        const remaining = 5000 - data.subtotal;
        shippingNote.innerHTML = '<div class="shipping-note">Add ₱' + remaining.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + ' more for free shipping</div>';
    }
}

function showNotification(message, type) {
    type = type || 'success';
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.textContent = message;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; padding: 15px 25px; background: ' + (type === 'success' ? '#4CAF50' : '#f44336') + '; color: white; border-radius: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 10000; animation: slideIn 0.3s ease;';
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
}

const style = document.createElement('style');
style.textContent = '@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } } @keyframes fadeOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.95); } } .stock-info { font-size: 12px; color: #666; margin-top: 5px; } .qty-btn { cursor: pointer; transition: all 0.2s ease; background: white; border: 1px solid #ddd; padding: 5px 12px; border-radius: 4px; } .qty-btn:hover { background-color: #f0f0f0; transform: scale(1.05); } .qty-btn:active { transform: scale(0.95); } .item-image { object-fit: cover; border-radius: 8px; } .price-row { display: inline-flex; align-items: baseline; gap: 4px; } .currency { font-weight: 700; } .order-summary-items { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; margin-bottom: 12px; max-height: 220px; overflow-y: auto; background:#fafafa; } .summary-item-row { display:flex; justify-content: space-between; align-items:flex-start; padding:8px 6px; border-bottom:1px solid #e5e7eb; gap:10px; } .summary-item-row:last-child { border-bottom:none; } .summary-name { font-weight:600; color:#111; } .summary-variation { font-size:12px; color:#4b5563; } .summary-qty { font-size:12px; color:#4b5563; } .summary-price { font-weight:600; color:#111; }';
document.head.appendChild(style);
</script>
</body>
</html>