<?php
// admin/products/admin_product_create.php - Create new product
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/product_dao.php';

$productDAO = new ProductDAO($db);
$errors = [];

$collection = $_POST['collection'] ?? 'N/A';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $category = $_POST['category'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    
    // Validation
    if (empty($name)) $errors[] = 'Product name is required';
    if (empty($description)) $errors[] = 'Product description is required';
    if (empty($price)) $errors[] = 'Price is required';
    if (!is_numeric($price) || $price < 0) $errors[] = 'Price must be a valid number';
    if (empty($category)) $errors[] = 'Category is required';
    if (!is_numeric($stock_quantity) || $stock_quantity < 0) $errors[] = 'Stock must be a valid number';
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = 'assets/images/' . $category . '/';
        $full_target_dir = '../../' . $target_dir;
        
        if (!is_dir($full_target_dir)) {
            mkdir($full_target_dir, 0755, true);
        }
        
        $file_name = basename($_FILES['image']['name']);
        $unique_filename = uniqid() . '_' . $file_name;
        $target_file = $full_target_dir . $unique_filename;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        if (!in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = 'Only image files are allowed (jpg, jpeg, png, gif)';
        }
        
        if (empty($errors) && move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_dir . $unique_filename;
        } else if (empty($errors)) {
            $errors[] = 'Failed to upload image';
        }
    }
    
    if (empty($errors)) {
        if ($productDAO->createProduct($name, $description, $price, $category, $image_path, $stock_quantity, $collection)) {
            header('Location: admin_product_list.php?message=Product created successfully');
            exit();
        } else {
            $errors[] = 'Failed to create product';
        }
    }
}

$categories = ['dress', 'gown'];
$collections = ['Bridal Studio', 'Fall Bridal', 'Summer Bridal'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/admin_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="edit-container">
        <a href="admin_product_list.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
        
        <div class="page-header">
            <h1>
                <i class="fas fa-plus-circle"></i> Create New Product
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
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-card">
                <!-- Basic Information -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </h2>
                    <div class="form-grid single">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-tag input-icon"></i>
                                Product Name <span class="required-star">*</span>
                            </label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                   placeholder="Enter product name" required>
                        </div>
                    </div>
                    
                    <div class="form-grid single" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="description">
                                <i class="fas fa-align-left input-icon"></i>
                                Description <span class="required-star">*</span>
                            </label>
                            <textarea id="description" name="description" 
                                      placeholder="Enter detailed product description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Provide a comprehensive description of the product
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Pricing & Classification -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-peso-sign"></i> Pricing & Classification
                    </h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="price">
                                <i class="fas fa-money-bill-wave input-icon"></i>
                                Price (PHP) <span class="required-star">*</span>
                            </label>
                            <input type="number" id="price" name="price" step="0.01" min="0" 
                                   value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                   placeholder="0.00" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_quantity">
                                <i class="fas fa-boxes input-icon"></i>
                                Stock Quantity <span class="required-star">*</span>
                            </label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" 
                                value="<?php echo isset($_POST['stock_quantity']) && $_POST['stock_quantity'] !== '' ? htmlspecialchars($_POST['stock_quantity']) : ''; ?>" 
                                placeholder="0" required>
                            
                        </div>
                                            
                    <div class="form-grid" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="category">
                                <i class="fas fa-folder input-icon"></i>
                                Category <span class="required-star">*</span>
                            </label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($_POST['category'] ?? '') === $cat ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="collection">
                                <i class="fas fa-layer-group input-icon"></i>
                                Collection <span class="required-star">*</span>
                            </label>
                            <select id="collection" name="collection" required>
                                <option value="">Select Collection</option>
                                <?php foreach ($collections as $col): ?>
                                    <option value="<?php echo $col; ?>" <?php echo ($_POST['collection'] ?? '') === $col ? 'selected' : ''; ?>>
                                        <?php echo $col; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Product Image -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-image"></i> Product Image
                    </h2>
                    
                    <div class="image-upload-section" onclick="document.getElementById('image').click()">
                        <i class="fas fa-cloud-upload-alt upload-icon" style="font-size: 48px; color: var(--primary-green); margin-bottom: 12px;"></i>
                        <p class="upload-text" style="color: var(--gray-700); margin: 8px 0;">
                            <strong>Click anywhere to upload</strong><br>
                            <span style="font-size: 13px; color: var(--gray-500);">or drag and drop your image here</span>
                        </p>
                        <input type="file" id="image" name="image" accept="image/*" hidden>
                        <p id="file-name" style="margin-top:10px;font-size:14px;color:#6b7280;font-weight:500;"></p>
                        <span class="form-hint" style="justify-content: center; margin-top: 12px;">
                            <i class="fas fa-info-circle"></i>
                            Supported formats: JPG, JPEG, PNG, GIF (Max 5MB)
                        </span>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="admin_product_list.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus-circle"></i> Create Product
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        document.getElementById('image').addEventListener('change', function() {
            const fileName = this.files[0]?.name || '';
            document.getElementById('file-name').textContent = fileName ? `Selected: ${fileName}` : '';
        });
    </script>
</body>
</html>