<?php
// admin/users/admin_user_status.php - Handle user activation/deactivation with authentication
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../../config.php';
require_once '../../dao/user_dao.php';

$userDAO = new UserDAO($db);

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$user_id = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? null;
$admin_username = trim($_POST['admin_username'] ?? '');
$admin_password = $_POST['admin_password'] ?? '';

// Validate input
if (!$user_id || !$action || !$admin_username || !$admin_password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Validate action
if (!in_array($action, ['activate', 'deactivate'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Authenticate admin credentials
try {
    $admin = $userDAO->getUserByUsername($admin_username);
    
    if (!$admin) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }
    
    // Verify password
    if (!password_verify($admin_password, $admin['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }
    
    // Verify the authenticated user is the same as logged-in user
    if ($admin['id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Authentication mismatch']);
        exit();
    }
    
    // Verify admin role
    if ($admin['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit();
    }
    
    // Get target user
    $target_user = $userDAO->getUserById($user_id);
    if (!$target_user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Prevent admin from deactivating themselves
    if ($action === 'deactivate' && $user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'You cannot deactivate yourself']);
        exit();
    }
    
    // Update user status
    $new_status = ($action === 'activate') ? 'active' : 'inactive';
    
    $query = "UPDATE users SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':id', $user_id);
    
    if ($stmt->execute()) {
        $message = ($action === 'activate') 
            ? 'User activated successfully' 
            : 'User deactivated successfully';
        
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>