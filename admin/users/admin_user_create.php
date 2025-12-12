<?php
// admin/users/admin_user_create.php - Create new user
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/user_dao.php';

$userDAO = new UserDAO($db);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $role = $_POST['role'] ?? 'customer';
    
    // Validation
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($password)) $errors[] = 'Password is required';
    if ($password !== $password_confirm) $errors[] = 'Passwords do not match';
    if (empty($first_name)) $errors[] = 'First name is required';
    if (empty($last_name)) $errors[] = 'Last name is required';
    if ($userDAO->usernameExists($username)) $errors[] = 'Username already exists';
    if ($userDAO->emailExists($email)) $errors[] = 'Email already exists';
    
    if (empty($errors)) {
        if ($userDAO->createUser($username, $email, $password, $first_name, $last_name, $role)) {
            header('Location: admin_user_list.php?message=User created successfully');
            exit();
        } else {
            $errors[] = 'Failed to create user';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/admin_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="edit-container">
        <a href="admin_user_list.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
        
        <div class="page-header">
            <h1>
                <i class="fas fa-user-plus"></i> Create New User
            </h1>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="errors-alert">
                <div class="errors-alert-header">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Please fix the following errors:</span>
                </div>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-card">
                <!-- Account Information -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-user-circle"></i> Account Information
                    </h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-user input-icon"></i>
                                Username <span class="required-star">*</span>
                            </label>
                            <input type="text" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                   placeholder="Enter username" required>
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Unique username for login
                            </span>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope input-icon"></i>
                                Email <span class="required-star">*</span>
                            </label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   placeholder="user@example.com" required>
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Valid email address required
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-grid" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock input-icon"></i>
                                Password <span class="required-star">*</span>
                            </label>
                            <input type="password" id="password" name="password" 
                                   placeholder="Enter password" required>
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Minimum 8 characters recommended
                            </span>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirm">
                                <i class="fas fa-lock input-icon"></i>
                                Confirm Password <span class="required-star">*</span>
                            </label>
                            <input type="password" id="password_confirm" name="password_confirm" 
                                   placeholder="Re-enter password" required>
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Must match password above
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-id-card"></i> Personal Information
                    </h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">
                            <i class="fas fa-user input-icon"></i>
                            First Name <span class="required-star">*</span>
                        </label>
                        <input type="text" id="first_name" name="first_name" 
                            value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" 
                            placeholder="Enter first name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">
                            <i class="fas fa-user input-icon"></i>
                            Last Name <span class="required-star">*</span>
                        </label>
                        <input type="text" id="last_name" name="last_name" 
                            value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" 
                            placeholder="Enter last name" required>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Role & Permissions -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-user-shield"></i> Role & Permissions
                    </h2>
                    
                    <div class="form-grid single">
                        <div class="form-group">
                            <label for="role">
                                <i class="fas fa-user-tag input-icon"></i>
                                User Role <span class="required-star">*</span>
                            </label>
                            <select id="role" name="role" required>
                                <option value="customer" <?php echo ($_POST['role'] ?? '') === 'customer' ? 'selected' : ''; ?>>
                                    Customer - Standard user access
                                </option>
                                <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                                    Admin - Full system access
                                </option>
                            </select>
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Determines user permissions and access level
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="admin_user_list.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i> Create User
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>