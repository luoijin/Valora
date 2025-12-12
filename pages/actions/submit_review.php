<?php
// actions/submit_review.php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header early
header('Content-Type: application/json');

try {
    // Check various possible paths for config.php
    $possible_paths = [
        __DIR__ . '/../config.php',
        __DIR__ . '/../../config.php',
        __DIR__ . '/../includes/config.php',
        dirname(dirname(__FILE__)) . '/config.php'
    ];
    
    $config_loaded = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $config_loaded = true;
            error_log("Config loaded from: $path");
            break;
        }
    }
    
    if (!$config_loaded) {
        throw new Exception("Config file not found. Searched paths: " . implode(', ', $possible_paths));
    }
    
    // Try loading review_dao.php
    $dao_paths = [
        __DIR__ . '/../dao/review_dao.php',
        __DIR__ . '/../../dao/review_dao.php',
        dirname(dirname(__FILE__)) . '/dao/review_dao.php'
    ];
    
    $dao_loaded = false;
    foreach ($dao_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $dao_loaded = true;
            error_log("DAO loaded from: $path");
            break;
        }
    }
    
    if (!$dao_loaded) {
        throw new Exception("ReviewDAO file not found. Searched paths: " . implode(', ', $dao_paths));
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit();
    }

    // Check if database connection exists
    if (!isset($db)) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }

    // Get form data
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

    // Validate input
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit();
    }

    if (!$rating || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Please select a rating between 1 and 5']);
        exit();
    }

    if (empty($review_text)) {
        echo json_encode(['success' => false, 'message' => 'Review text is required']);
        exit();
    }

    if (strlen($review_text) < 10) {
        echo json_encode(['success' => false, 'message' => 'Review must be at least 10 characters long']);
        exit();
    }

    if (strlen($review_text) > 1000) {
        echo json_encode(['success' => false, 'message' => 'Review must not exceed 1000 characters']);
        exit();
    }

    $reviewDAO = new ReviewDAO($db);
    $user_id = $_SESSION['user_id'];

    // Check if user already reviewed this product
    if ($reviewDAO->hasUserReviewed($user_id, $product_id)) {
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
        exit();
    }

    // Create the review
    $result = $reviewDAO->createReview($user_id, $product_id, $rating, $review_text);

    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Review submitted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to submit review. Please try again.'
        ]);
    }

} catch (Exception $e) {
    error_log("Error in submit_review: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>