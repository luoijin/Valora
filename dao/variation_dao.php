<?php
// dao/variation_dao.php - Product variation (color/size) Data Access Object

class VariationDAO {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Return all variations for a product
    public function getVariationsForProduct($product_id) {
        $query = "SELECT * FROM product_variations WHERE product_id = :product_id ORDER BY color, size";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Return a specific variation by color/size
    public function getVariation($product_id, $color, $size) {
        $query = "SELECT * FROM product_variations 
                  WHERE product_id = :product_id AND color = :color AND size = :size
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':size', $size);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update variation stock to an absolute value
    public function updateStock($variation_id, $quantity) {
        $query = "UPDATE product_variations SET stock_quantity = :qty WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':qty', $quantity);
        $stmt->bindParam(':id', $variation_id);
        return $stmt->execute();
    }

    // Reduce stock by ordered quantity (guarded at zero)
    public function decrementStock($variation_id, $quantity) {
        $query = "UPDATE product_variations 
                  SET stock_quantity = GREATEST(0, stock_quantity - :qty) 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':qty', $quantity);
        $stmt->bindParam(':id', $variation_id);
        return $stmt->execute();
    }

    // Create a single variation row
    public function createVariation($product_id, $color, $size, $stock_quantity = 0, $price = null) {
        $query = "INSERT INTO product_variations (product_id, color, size, stock_quantity, price)
                  VALUES (:product_id, :color, :size, :stock_quantity, :price)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        $stmt->bindParam(':price', $price);
        return $stmt->execute();
    }

    // Delete all variations for a product
    public function deleteVariationsByProduct($product_id) {
        $stmt = $this->db->prepare("DELETE FROM product_variations WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id);
        return $stmt->execute();
    }
}
?>

