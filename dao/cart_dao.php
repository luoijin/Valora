<?php
// dao/cart_dao.php - Cart Data Access Object

class CartDAO {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get all cart items for a user (includes variation info when available)
    public function getCartByUserId($user_id) {
        $query = "SELECT c.*, 
                         p.name, 
                         p.price AS product_price, 
                         p.image_path, 
                         COALESCE(v.price, p.price) AS price,
                         COALESCE(v.stock_quantity, p.stock_quantity) AS stock_quantity,
                         v.color AS variation_color,
                         v.size AS variation_size
                  FROM cart c
                  JOIN products p ON c.product_id = p.id
                  LEFT JOIN product_variations v ON c.variation_id = v.id
                  WHERE c.user_id = :user_id
                  ORDER BY c.added_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get cart item by ID
    public function getCartItemById($id) {
        $query = "SELECT c.*, 
                         p.name, 
                         COALESCE(v.price, p.price) AS price, 
                         p.image_path,
                         COALESCE(v.stock_quantity, p.stock_quantity) AS stock_quantity,
                         v.color AS variation_color,
                         v.size AS variation_size
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  LEFT JOIN product_variations v ON c.variation_id = v.id
                  WHERE c.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add item to cart (checks same variation/color/size)
    public function addToCart($user_id, $product_id, $quantity = 1, $color = null, $size = null, $variation_id = null) {
        // Check if item with same variation already exists in cart
        $query = "SELECT id, quantity FROM cart 
                  WHERE user_id = :user_id 
                    AND product_id = :product_id
                    AND ((:color IS NULL AND color IS NULL) OR color = :color)
                    AND ((:size IS NULL AND size IS NULL) OR size = :size)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':size', $size);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Item exists, update quantity
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->updateQuantityById($existing['id'], $quantity + $existing['quantity']);
        } else {
            // Add new item
            $query = "INSERT INTO cart (user_id, product_id, variation_id, color, size, quantity) 
                      VALUES (:user_id, :product_id, :variation_id, :color, :size, :quantity)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':variation_id', $variation_id);
            $stmt->bindParam(':color', $color);
            $stmt->bindParam(':size', $size);
            $stmt->bindParam(':quantity', $quantity);
            
            return $stmt->execute();
        }
    }

    // Update item quantity using cart id
    public function updateQuantityById($cart_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCartById($cart_id);
        }
        
        $query = "UPDATE cart SET quantity = :quantity WHERE id = :cart_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cart_id);
        $stmt->bindParam(':quantity', $quantity);
        
        return $stmt->execute();
    }

    // Remove item from cart
    public function removeFromCartById($cart_id) {
        $query = "DELETE FROM cart WHERE id = :cart_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cart_id);
        
        return $stmt->execute();
    }

    // Backward compatible remove by product (removes all variations for that product and user)
    public function removeFromCart($user_id, $product_id) {
        $query = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        
        return $stmt->execute();
    }

    // Clear entire cart
    public function clearCart($user_id) {
        $query = "DELETE FROM cart WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }

    // Get cart total
    public function getCartTotal($user_id) {
        $query = "SELECT SUM(COALESCE(v.price, p.price) * c.quantity) as total 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  LEFT JOIN product_variations v ON c.variation_id = v.id
                  WHERE c.user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    // Get cart item count (total quantity of all items)
    public function getCartItemCount($user_id) {
        $query = "SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['count'];
    }

    // Get unique product count in cart
    public function getCartProductCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['count'];
    }

    // Check if product is in cart
    public function isInCart($user_id, $product_id) {
        $query = "SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    // Get cart with pagination
    public function getCartPaginated($user_id, $limit = 10, $offset = 0) {
        $query = "SELECT c.*, 
                         p.name, 
                         COALESCE(v.price, p.price) AS price, 
                         p.image_path, 
                         COALESCE(v.stock_quantity, p.stock_quantity) AS stock_quantity,
                         v.color AS variation_color,
                         v.size AS variation_size
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  LEFT JOIN product_variations v ON c.variation_id = v.id
                  WHERE c.user_id = :user_id 
                  ORDER BY c.added_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>