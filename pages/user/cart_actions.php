<?php
// pages/user/cart_actions.php
session_start();
require_once '../../config.php';
require_once '../../dao/cart_dao.php';
require_once '../../dao/product_dao.php';
require_once '../../dao/variation_dao.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage cart']);
    exit;
}

$cartDAO = new CartDAO($db);
$productDAO = new ProductDAO($db);
$variationDAO = new VariationDAO($db);
$user_id = $_SESSION['user_id'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $product_id = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        $color = $_POST['color'] ?? null;
        $size = $_POST['size'] ?? null;
        
        // Validate product exists and has stock
        $product = $productDAO->getProductById($product_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        $variation = null;
        $variation_id = null;
        $availableStock = $product['stock_quantity'];

        if ($color !== null || $size !== null) {
            $variation = $variationDAO->getVariation($product_id, $color, $size);
            if ($variation) {
                $variation_id = $variation['id'];
                $availableStock = $variation['stock_quantity'];
            }
        }

        if ($availableStock < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
        
        if ($cartDAO->addToCart($user_id, $product_id, $quantity, $color, $size, $variation_id)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Item added to cart',
                'cart_count' => $cartDAO->getCartItemCount($user_id)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add item']);
        }
        break;
        
    case 'update':
        $cart_id = $_POST['cart_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 0;
        
        $cartItem = $cartDAO->getCartItemById($cart_id);
        if (!$cartItem) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            exit;
        }

        // Validate stock against variation or product
        if ($quantity > $cartItem['stock_quantity']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Only ' . $cartItem['stock_quantity'] . ' items available'
            ]);
            exit;
        }
        
        if ($cartDAO->updateQuantityById($cart_id, $quantity)) {
            $cartItems = $cartDAO->getCartByUserId($user_id);
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shipping = $subtotal >= 5000 ? 0 : 500;
            $total = $subtotal + $shipping;
            
            echo json_encode([
                'success' => true, 
                'message' => 'Cart updated',
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
                'cart_count' => $cartDAO->getCartItemCount($user_id) // FIXED: Use total quantity
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
        }
        break;
        
    case 'remove':
        $cart_id = $_POST['cart_id'] ?? 0;
        
        if ($cartDAO->removeFromCartById($cart_id)) {
            $cartItems = $cartDAO->getCartByUserId($user_id);
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shipping = $subtotal >= 5000 ? 0 : 500;
            $total = $subtotal + $shipping;
            
            echo json_encode([
                'success' => true, 
                'message' => 'Item removed from cart',
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
                'cart_count' => $cartDAO->getCartItemCount($user_id), // FIXED: Use total quantity
                'is_empty' => count($cartItems) === 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
        }
        break;
        
    case 'get':
        $cartItems = $cartDAO->getCartByUserId($user_id);
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping = $subtotal >= 5000 ? 0 : 500;
        $total = $subtotal + $shipping;
        
        echo json_encode([
            'success' => true,
            'items' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'cart_count' => $cartDAO->getCartItemCount($user_id) // FIXED: Use total quantity instead of count($cartItems)
        ]);
        break;
        
    case 'clear':
        if ($cartDAO->clearCart($user_id)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Cart cleared',
                'cart_count' => 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>