<?php
// dao/product_dao.php - Product Data Access Object

class ProductDAO {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Get all products
    public function getAllProducts($active_only = false) {
        $query = "SELECT * FROM products";
        if ($active_only) {
            $query .= " WHERE is_active = 1";
        }
        $query .= " ORDER BY id ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get product by ID
    public function getProductById($id) {
        $query = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get products by category
    public function getProductsByCategory($category, $active_only = false) {
        $query = "SELECT * FROM products WHERE category = :category";
        if ($active_only) {
            $query .= " AND is_active = 1";
        }
        $query .= " ORDER BY id ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get products by collection
    public function getProductsByCollection($collection, $active_only = true) {
        $query = "SELECT * FROM products WHERE collection = :collection";
        if ($active_only) {
            $query .= " AND is_active = 1";
        }
        $query .= " ORDER BY created_at DESC, id ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':collection', $collection);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create new product
    public function createProduct($name, $description, $price, $category, $image_path, $stock_quantity = 0, $collection = 'N/A') {
        $query = "INSERT INTO products (name, description, price, category, collection, image_path, stock_quantity) 
                  VALUES (:name, :description, :price, :category, :collection, :image_path, :stock_quantity)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':collection', $collection);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        
        return $stmt->execute();
    }
    
    // Update product
    public function updateProduct($id, $name, $description, $price, $category, $image_path = null, $stock_quantity = null, $collection = 'N/A') {
        $query = "UPDATE products SET name = :name, description = :description, price = :price, category = :category, collection = :collection";
        
        if ($image_path !== null) {
            $query .= ", image_path = :image_path";
        }
        if ($stock_quantity !== null) {
            $query .= ", stock_quantity = :stock_quantity";
        }
        $query .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':collection', $collection);
        
        if ($image_path !== null) {
            $stmt->bindParam(':image_path', $image_path);
        }
        if ($stock_quantity !== null) {
            $stmt->bindParam(':stock_quantity', $stock_quantity);
        }
        
        return $stmt->execute();
    }
    
    // Update product stock
    public function updateStock($id, $quantity) {
        $query = "UPDATE products SET stock_quantity = :stock_quantity WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':stock_quantity', $quantity);
        return $stmt->execute();
    }
    
    // Toggle product active status
    public function toggleProductStatus($id) {
        $query = "UPDATE products SET is_active = NOT is_active WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Delete product
    public function deleteProduct($id) {
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Get total product count
    public function getProductCount($active_only = false) {
        $query = "SELECT COUNT(*) as count FROM products";
        if ($active_only) {
            $query .= " WHERE is_active = 1";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    // Search products
    public function searchProducts($keyword, $active_only = false) {
        $query = "SELECT * FROM products WHERE (name LIKE :keyword OR description LIKE :keyword)";
        if ($active_only) {
            $query .= " AND is_active = 1";
        }
        $query .= " ORDER BY id ASC";
        
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get products with low stock
    public function getLowStockProducts($threshold = 5) {
        $query = "SELECT * FROM products WHERE stock_quantity <= :threshold AND is_active = 1 ORDER BY stock_quantity ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':threshold', $threshold);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add stock transaction
    public function addStockTransaction($product_id, $quantity, $type = 'purchase') {
        // type can be 'purchase', 'sale', 'adjustment'
        $product = $this->getProductById($product_id);
        if (!$product) return false;
        
        $new_quantity = $product['stock_quantity'] + $quantity;
        
        $query = "UPDATE products SET stock_quantity = :stock_quantity WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':stock_quantity', $new_quantity);
        $stmt->bindParam(':id', $product_id);
        
        return $stmt->execute();
    }

    // Record purchase/sale
    public function recordStockMovement($product_id, $quantity, $movement_type) {
        // movement_type: 'purchase' (add) or 'sale' (subtract)
        $product = $this->getProductById($product_id);
        if (!$product) return false;
        
        if ($movement_type === 'sale') {
            $new_quantity = max(0, $product['stock_quantity'] - $quantity);
        } else {
            $new_quantity = $product['stock_quantity'] + $quantity;
        }
        
        $query = "UPDATE products SET stock_quantity = :stock_quantity WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':stock_quantity', $new_quantity);
        $stmt->bindParam(':id', $product_id);
        
        return $stmt->execute();
    }
}
?>