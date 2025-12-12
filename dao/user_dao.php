<?php
// dao/user_dao.php - User Data Access Object

class UserDAO {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get all users
    public function getAllUsers() {
        $query = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user by ID
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get user by username
    public function getUserByUsername($username) {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get user by email
    public function getUserByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new user
    public function createUser($username, $email, $password, $first_name, $last_name, $role = 'customer') {
        $query = "INSERT INTO users (username, email, password, first_name, last_name, role, status) 
                  VALUES (:username, :email, :password, :first_name, :last_name, :role, 'active')";
        
        $stmt = $this->db->prepare($query);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':role', $role);
        
        return $stmt->execute();
    }

    // Update user
    public function updateUser($id, $email, $first_name, $last_name, $phone = null) {
        $query = "UPDATE users SET email = :email, first_name = :first_name, last_name = :last_name, phone = :phone WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        
        return $stmt->execute();
    }

    // Update user role
    public function updateUserRole($id, $role) {
        $query = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }

    // Deactivate user (soft delete)
    public function deactivateUser($id) {
        $query = "UPDATE users SET status = 'inactive' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Activate user
    public function activateUser($id) {
        $query = "UPDATE users SET status = 'active' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Delete user (hard delete - optional)
    public function deleteUser($id) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Check if username exists
    public function usernameExists($username) {
        $query = "SELECT COUNT(*) as count FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Get total user count (all users regardless of status)
    public function getTotalUserCount() {
        $query = "SELECT COUNT(*) as count FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Get active user count
    public function getActiveUserCount() {
        $query = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Get inactive user count
    public function getInactiveUserCount() {
        $query = "SELECT COUNT(*) as count FROM users WHERE status = 'inactive'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Legacy method - kept for backward compatibility (returns active users)
    public function getUserCount() {
        return $this->getActiveUserCount();
    }
}
?>