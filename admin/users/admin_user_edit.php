<?php
// admin/users/admin_user_edit.php - Edit user
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

$user_id = $_GET['id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    header('Location: admin_user_list.php');
    exit();
}

$user = $userDAO->getUserById($user_id);
if (!$user) {
    header('Location: admin_user_list.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'customer';
    
    // Validation
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($first_name)) $errors[] = 'First name is required';
    if (empty($last_name)) $errors[] = 'Last name is required';
    
    // Check if email is already used by another user
    $existing = $userDAO->getUserByEmail($email);
    if ($existing && $existing['id'] != $user_id) {
        $errors[] = 'Email already exists';
    }
    
    if (empty($errors)) {
        if ($userDAO->updateUser($user_id, $email, $first_name, $last_name, $phone)) {
            $userDAO->updateUserRole($user_id, $role);
            header('Location: admin_user_list.php?message=User updated successfully');
            exit();
        } else {
            $errors[] = 'Failed to update user';
        }
    }
    
    // Refresh user data
    $user = $userDAO->getUserById($user_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Valora Admin</title>
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
                <i class="fas fa-user-edit"></i> Edit User
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
                                Username
                            </label>
                            <input type="text" id="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" 
                                   disabled style="background-color: var(--gray-100); cursor: not-allowed;">
                            <span class="form-hint">
                                <i class="fas fa-lock"></i>
                                Username cannot be changed
                            </span>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope input-icon"></i>
                                Email <span class="required-star">*</span>
                            </label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   placeholder="user@example.com" required>
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Valid email address required
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
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" 
                                   placeholder="Enter first name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">
                                <i class="fas fa-user input-icon"></i>
                                Last Name <span class="required-star">*</span>
                            </label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" 
                                   placeholder="Enter last name" required>
                        </div>
                    </div>
                    
                    <div class="form-grid single" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone input-icon"></i>
                                Phone Number
                            </label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   placeholder="Enter phone number">
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Optional contact number
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
                                <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>
                                    Customer - Standard user access
                                </option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
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
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>