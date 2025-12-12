<?php
// dao/review_dao.php - Review Data Access Object

class ReviewDAO {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Get all reviews for a product
    public function getProductReviews($product_id) {
        $query = "SELECT r.*, u.username, u.first_name, u.last_name
                  FROM reviews r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.product_id = :product_id
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get review by ID
    public function getReviewById($review_id) {
        $query = "SELECT r.*, u.username, u.first_name, u.last_name
                  FROM reviews r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $review_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Create new review
    public function createReview($user_id, $product_id, $rating, $review_text) {
        // Check if user already reviewed this product
        if ($this->hasUserReviewed($user_id, $product_id)) {
            return false;
        }
        
        $query = "INSERT INTO reviews (user_id, product_id, rating, review_text)
                  VALUES (:user_id, :product_id, :rating, :review_text)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':review_text', $review_text);
        
        return $stmt->execute();
    }
    
    // Check if user has already reviewed a product
    public function hasUserReviewed($user_id, $product_id) {
        $query = "SELECT COUNT(*) as count FROM reviews 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    // Get average rating for a product
    public function getAverageRating($product_id) {
        $query = "SELECT AVG(rating) as avg_rating FROM reviews 
                  WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
    }
    
    // Get review count for a product
    public function getReviewCount($product_id) {
        $query = "SELECT COUNT(*) as count FROM reviews 
                  WHERE product_id = :product_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    // Get all reviews by user
    public function getUserReviews($user_id) {
        $query = "SELECT r.*, p.name as product_name, p.image_path
                  FROM reviews r
                  JOIN products p ON r.product_id = p.id
                  WHERE r.user_id = :user_id
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update review
    public function updateReview($review_id, $rating, $review_text) {
        $query = "UPDATE reviews 
                  SET rating = :rating, review_text = :review_text, updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $review_id);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':review_text', $review_text);
        
        return $stmt->execute();
    }
    
    // Delete review
    public function deleteReview($review_id) {
        $query = "DELETE FROM reviews WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $review_id);
        
        return $stmt->execute();
    }
    
    // Get recent reviews (for admin dashboard)
    public function getRecentReviews($limit = 10) {
        $query = "SELECT r.*, u.username, p.name as product_name
                  FROM reviews r
                  JOIN users u ON r.user_id = u.id
                  JOIN products p ON r.product_id = p.id
                  ORDER BY r.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get reviews by rating
    public function getReviewsByRating($product_id, $rating) {
        $query = "SELECT r.*, u.username, u.first_name, u.last_name
                  FROM reviews r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.product_id = :product_id AND r.rating = :rating
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':rating', $rating);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get rating distribution for a product
    public function getRatingDistribution($product_id) {
        $query = "SELECT rating, COUNT(*) as count
                  FROM reviews
                  WHERE product_id = :product_id
                  GROUP BY rating
                  ORDER BY rating DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get total review count (all products)
    public function getTotalReviewCount() {
        $query = "SELECT COUNT(*) as count FROM reviews";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    }
}
?>