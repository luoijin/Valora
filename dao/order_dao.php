<?php
// dao/order_dao.php - Order Data Access Object

class OrderDAO {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Create new order
    public function createOrder($user_id, $total_amount, $status = 'pending') {
        $query = "INSERT INTO orders (user_id, total_amount, status) 
                  VALUES (:user_id, :total_amount, :status)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':status', $status);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    // Add order item
    public function addOrderItem($order_id, $product_id, $quantity, $price, $variation_id = null, $color = null, $size = null) {
        $query = "INSERT INTO order_items (order_id, product_id, variation_id, color, size, quantity, price) 
                  VALUES (:order_id, :product_id, :variation_id, :color, :size, :quantity, :price)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':variation_id', $variation_id);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        
        return $stmt->execute();
    }

    // Get all orders
    public function getAllOrders($limit = 50, $offset = 0) {
        $query = "SELECT o.*, u.username, u.email, u.first_name, u.last_name 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  ORDER BY o.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get order by ID
    public function getOrderById($id) {
        $query = "SELECT o.*, u.username, u.email, u.first_name, u.last_name, u.phone 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get order items
    public function getOrderItems($order_id) {
        $query = "SELECT oi.*, p.name, p.image_path 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = :order_id 
                  ORDER BY oi.id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user orders
    public function getUserOrders($user_id) {
        $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update order status
    public function updateOrderStatus($id, $status) {
        $query = "UPDATE orders SET status = :status WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }

    // Delete order
    public function deleteOrder($id) {
        // Delete order items first
        $query = "DELETE FROM order_items WHERE order_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Delete order
        $query = "DELETE FROM orders WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Get order count
    public function getOrderCount() {
        $query = "SELECT COUNT(*) as count FROM orders";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    }

    // Get orders by status
    public function getOrdersByStatus($status) {
        $query = "SELECT o.*, u.username, u.email 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.status = :status 
                  ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get total revenue
    public function getTotalRevenue() {
        $query = "SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'completed'";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['revenue'] ?? 0;
    }

    // Get revenue by date range
    public function getRevenueByDateRange($start_date, $end_date) {
        $query = "SELECT SUM(total_amount) as revenue FROM orders 
                  WHERE status = 'completed' 
                  AND DATE(created_at) BETWEEN :start_date AND :end_date";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['revenue'] ?? 0;
    }

    // Get pending orders count
    public function getPendingOrdersCount() {
        $query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    }

    // Get order summary
    public function getOrderSummary() {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue
                  FROM orders";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Search orders
    public function searchOrders($keyword) {
        $query = "SELECT o.*, u.username, u.email 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.id LIKE :keyword 
                  OR u.username LIKE :keyword 
                  OR u.email LIKE :keyword 
                  ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get total units sold for a specific product
    public function getProductUnitsSold($product_id) {
        $query = "SELECT COALESCE(SUM(oi.quantity), 0) as units_sold 
                  FROM order_items oi
                  JOIN orders o ON oi.order_id = o.id
                  WHERE oi.product_id = :product_id 
                  AND o.status = 'completed'";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['units_sold'] ?? 0;
    }
}
?>