

<?php
class crudDAO {

    private $conn;

    public function __construct() {
        // Use the global PDO connection from config.php
        global $db;
        $this->conn = $db;
    }

    // CREATE USER (Sign Up)
    // Parameters follow the current database schema: first_name, last_name
    public function create($first_name, $last_name, $username, $password, $email, $role = 'customer') {
        try {
            // Check if email or username already exists
            $check = $this->conn->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
            $check->execute([
                'email' => $email,
                'username' => $username
            ]);

            if ($check->rowCount() > 0) {
                // prevent duplicate account creation
                return false;
            }

            // Securely hash password
            $hashpass = password_hash($password, PASSWORD_BCRYPT);

            // Insert new record using the current users schema (first_name, last_name, role)
            $sql = $this->conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role) 
                VALUES (:username, :email, :password, :first_name, :last_name, :role)");
            $sql->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashpass,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => $role
            ]);

            return true;

        } catch (PDOException $e) {
            // Display database error for debugging
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }

    // LOGIN USER (Authentication)
    public function login($username, $password) {
        try {
            $sql = $this->conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $sql->execute(['username' => $username]);
            $user = $sql->fetch(PDO::FETCH_ASSOC);

            // Verify password if user exists
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            } else {
                return false;
            }

        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }
}
?>
