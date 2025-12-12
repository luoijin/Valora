<?php
// admin/products/admin_product_edit.php - Edit product
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/product_dao.php';
require_once '../../dao/variation_dao.php';

$productDAO = new ProductDAO($db);
$variationDAO = new VariationDAO($db);
$errors = [];
$variationRows = [];
$variationStockTotal = 0;

$product_id = $_GET['id'] ?? null;
if (!$product_id || !is_numeric($product_id)) {
    header('Location: admin_product_list.php');
    exit();
}

$product = $productDAO->getProductById($product_id);
$existingVariations = $variationDAO->getVariationsForProduct($product_id);
if (!$product) {
    header('Location: admin_product_list.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $category = $_POST['category'] ?? '';
    $collection = $_POST['collection'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? null;
    $colors = $_POST['var_color'] ?? [];
    $sizes = $_POST['var_size'] ?? [];
    $stocks = $_POST['var_stock'] ?? [];
    $prices = $_POST['var_price'] ?? [];
    $variationRows = [];
    $variationStockTotal = 0;

    // Build variation rows (only keep rows with either color/size/stock)
    foreach ($colors as $idx => $color) {
        $colorVal = trim($color ?? '');
        $sizeVal = trim($sizes[$idx] ?? '');
        $stockVal = isset($stocks[$idx]) && $stocks[$idx] !== '' ? (int)$stocks[$idx] : 0;
        $priceVal = isset($prices[$idx]) && $prices[$idx] !== '' ? $prices[$idx] : null;

        if ($colorVal === '' && $sizeVal === '' && $stockVal <= 0 && ($priceVal === null || $priceVal === '')) {
            continue;
        }

        if ($stockVal < 0) {
            $errors[] = "Variation stock must be 0 or more (row " . ($idx + 1) . ")";
        }
        if ($priceVal !== null && $priceVal !== '' && (!is_numeric($priceVal) || $priceVal < 0)) {
            $errors[] = "Variation price must be a valid number (row " . ($idx + 1) . ")";
        }

        $variationRows[] = [
            'color' => $colorVal ?: null,
            'size' => $sizeVal ?: null,
            'stock' => $stockVal,
            'price' => $priceVal === '' ? null : $priceVal
        ];
        $variationStockTotal += max(0, $stockVal);
    }
    
    // Validation
    if (empty($name)) $errors[] = 'Product name is required';
    if (empty($description)) $errors[] = 'Product description is required';
    if (empty($price)) $errors[] = 'Price is required';
    if (!is_numeric($price) || $price < 0) $errors[] = 'Price must be a valid number';
    if (empty($category)) $errors[] = 'Category is required';
    if (empty($collection)) $errors[] = 'Collection is required';
    if (!is_numeric($stock_quantity) || $stock_quantity < 0) $errors[] = 'Stock must be a valid number';

    // If variations provided, override product stock with sum of variation stocks
    if (!empty($variationRows)) {
        $stock_quantity = $variationStockTotal;
    }
    
    // Handle image deletion
    if (isset($_GET['delete_image']) && $_GET['delete_image'] == '1') {
        if ($product['image_path'] && file_exists('../../' . $product['image_path'])) {
            unlink('../../' . $product['image_path']);
        }
        $productDAO->updateProduct($product_id, $product['name'], $product['description'], $product['price'], 
                                $product['category'], null, $product['stock_quantity'], $product['collection']);
        $product = $productDAO->getProductById($product_id);
        $message = 'Image deleted successfully';
    }

    $image_path = $product['image_path'];
    
    // Handle image upload
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
            if ($product['image_path'] && file_exists('../../' . $product['image_path'])) {
                unlink('../../' . $product['image_path']);
            }
            $image_path = $target_dir . $unique_filename;
        } else if (empty($errors)) {
            $errors[] = 'Failed to upload image';
        }
    }
    
    if (empty($errors)) {
        try {
            $db->beginTransaction();

            if (!$productDAO->updateProduct($product_id, $name, $description, $price, $category, $image_path, $stock_quantity, $collection)) {
                throw new Exception('Failed to update product');
            }

            // Replace variations if any submitted, otherwise keep existing
            if (!empty($variationRows)) {
                $variationDAO->deleteVariationsByProduct($product_id);
                foreach ($variationRows as $row) {
                    $variationDAO->createVariation($product_id, $row['color'], $row['size'], $row['stock'], $row['price']);
                }
            }

            $db->commit();
            header('Location: admin_product_list.php?message=Product updated successfully');
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = $e->getMessage();
        }
    }
    
$product = $productDAO->getProductById($product_id);
$existingVariations = $variationDAO->getVariationsForProduct($product_id);
}

$categories = ['dress', 'gown'];
$collections = ['Bridal Studio', 'Fall Bridal', 'Summer Bridal'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/admin_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modern Edit Product Styles - Uses admin.css variables for consistency */
        body {
            background-color: var(--gray-50);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .edit-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--white) 0%, var(--gray-50) 100%);
            padding: 28px 32px;
            border-radius: var(--radius-lg);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .page-header h1 {
            color: var(--primary-green);
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .back-link {
            color: var(--primary-green);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: var(--radius-sm);
            transition: all var(--transition-base);
            background: var(--white);
            border: 2px solid var(--gray-200);
        }
        
        .back-link:hover {
            background-color: rgba(13, 59, 46, 0.08);
            border-color: var(--primary-green);
            transform: translateX(-3px);
        }
        
        .form-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        
        .form-section {
            padding: 32px;
            border-bottom: 2px solid var(--gray-100);
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .section-title {
            color: var(--primary-green);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 2px solid rgba(13, 59, 46, 0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-grid.single {
            grid-template-columns: 1fr;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            color: var(--gray-700);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .required-star {
            color: var(--danger);
            font-weight: 700;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 15px;
            background-color: var(--white);
            color: var(--gray-900);
            transition: all var(--transition-base);
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.6;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(13, 59, 46, 0.1);
        }
        
        .form-hint {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .image-upload-section {
            background: var(--gray-50);
            padding: 24px;
            border-radius: var(--radius-md);
            border: 2px dashed var(--gray-300);
            text-align: center;
            transition: all var(--transition-base);
        }
        
        .image-upload-section:hover {
            border-color: var(--primary-green);
            background: rgba(13, 59, 46, 0.02);
        }
        
        .image-upload-section input[type="file"] {
            border: none;
            padding: 0;
            background: transparent;
            cursor: pointer;
        }
        
        .current-image-preview {
            margin-top: 20px;
            padding: 20px;
            background: var(--white);
            border-radius: var(--radius-md);
            border: 2px solid var(--gray-200);
            position: relative;
        }
        
        .current-image-preview img {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
        }
        
        .delete-image-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: var(--white);
            padding: 10px 18px;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
            transition: all var(--transition-base);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .delete-image-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        .form-actions {
            padding: 24px 32px;
            background: var(--gray-50);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-light) 100%);
            color: var(--white);
            padding: 14px 32px;
            border-radius: var(--radius-md);
            border: none;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: 0 2px 8px rgba(13, 59, 46, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 59, 46, 0.35);
        }
        
        .btn-cancel {
            background: var(--white);
            color: var(--gray-700);
            padding: 14px 32px;
            border-radius: var(--radius-md);
            border: 2px solid var(--gray-300);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all var(--transition-base);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cancel:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
        }
        
        .errors-alert {
            background: linear-gradient(135deg, var(--danger-light) 0%, #fef2f2 100%);
            border: 2px solid #fecaca;
            border-left: 5px solid var(--danger);
            padding: 20px 24px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
        }
        
        .errors-alert-header {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--danger-dark);
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 16px;
        }
        
        .errors-alert ul {
            margin: 0;
            padding-left: 28px;
            color: var(--danger-dark);
        }
        
        .errors-alert li {
            margin: 6px 0;
            font-weight: 500;
        }
        
        .success-alert {
            background: linear-gradient(135deg, var(--success-light) 0%, #ecfdf5 100%);
            border: 2px solid var(--accent-green-light);
            border-left: 5px solid var(--success);
            padding: 16px 24px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--success-dark);
            font-weight: 600;
        }
        
        .input-icon {
            color: var(--gray-400);
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .edit-container {
                padding: 0 15px;
                margin: 20px auto;
            }
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
                padding: 20px;
            }
            
            .page-header h1 {
                font-size: 22px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-section {
                padding: 24px 20px;
            }
            
            .form-actions {
                flex-direction: column-reverse;
                padding: 20px;
            }
            
            .btn-submit,
            .btn-cancel {
                width: 100%;
                justify-content: center;
            }
            
            .delete-image-btn {
                position: static;
                margin-top: 12px;
                display: inline-flex;
            }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <a href="admin_product_list.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
        
        <div class="page-header">
            <h1>
                <i class="fas fa-edit"></i> Edit Product
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
        
        <?php if (!empty($message)): ?>
            <div class="success-alert">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
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
                                   value="<?php echo htmlspecialchars($product['name']); ?>" 
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
                                      placeholder="Enter detailed product description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
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
                                   value="<?php echo htmlspecialchars($product['price']); ?>" 
                                   placeholder="0.00" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_quantity">
                                <i class="fas fa-boxes input-icon"></i>
                                Stock Quantity
                            </label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" 
                                   value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" 
                                   placeholder="0">
                        </div>
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
                                    <option value="<?php echo $cat; ?>" <?php echo ($product['category'] ?? '') === $cat ? 'selected' : ''; ?>>
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
                                    <option value="<?php echo $col; ?>" <?php echo ($product['collection'] ?? '') === $col ? 'selected' : ''; ?>>
                                        <?php echo $col; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Variations -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-swatchbook"></i> Variations (Color / Size)
                    </h2>
                    <p style="margin:0 0 12px;color:#4b5563;">Add rows for each color/size with its own stock and optional price (leave price blank to use base price).</p>
                    <div class="form-grid single">
                        <div class="form-group">
                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background:#f9fafb;">
                                            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Color</th>
                                            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Size</th>
                                            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Stock</th>
                                            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Price (optional)</th>
                                            <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variation-rows">
                                        <?php 
                                            $rowsToShow = !empty($variationRows) ? $variationRows : $existingVariations;
                                            if (empty($rowsToShow)) {
                                                $rowsToShow = [['color'=>'','size'=>'','stock'=>'','price'=>'']];
                                            }
                                            foreach ($rowsToShow as $idx => $row): 
                                        ?>
                                        <tr>
                                            <td style="padding:8px;">
                                                <input type="text" name="var_color[]" value="<?php echo htmlspecialchars($row['color'] ?? ''); ?>" placeholder="e.g. Ivory" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                                            </td>
                                            <td style="padding:8px;">
                                                <input type="text" name="var_size[]" value="<?php echo htmlspecialchars($row['size'] ?? ''); ?>" placeholder="e.g. S" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                                            </td>
                                            <td style="padding:8px;">
                                                <input type="number" min="0" name="var_stock[]" value="<?php echo htmlspecialchars($row['stock'] ?? $row['stock_quantity'] ?? ''); ?>" placeholder="0" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                                            </td>
                                            <td style="padding:8px;">
                                                <input type="number" min="0" step="0.01" name="var_price[]" value="<?php echo htmlspecialchars($row['price'] ?? ''); ?>" placeholder="Use base price" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                                            </td>
                                            <td style="text-align:center;padding:8px;">
                                                <button type="button" class="btn-remove-row" style="border:none;background:#fee2e2;color:#b91c1c;padding:8px 12px;border-radius:6px;cursor:pointer;">✕</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="add-variation-row" style="margin-top:12px;background:#0d3b2e;color:#fff;border:none;padding:10px 14px;border-radius:6px;cursor:pointer;">
                                + Add Variation
                            </button>
                            <span class="form-hint" style="margin-top:10px;display:block;"><i class="fas fa-info-circle"></i> Product stock will auto-sum from variation stocks.</span>
                        </div>
                    </div>
                </div>
                
                <!-- Product Image -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-image"></i> Product Image
                    </h2>
                    
                    <div class="image-upload-section" onclick="document.getElementById('image').click()">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>

                        <p class="upload-text">
                            <strong>Click anywhere to upload</strong><br>
                        </p>

                        <input type="file" id="image" name="image" accept="image/*" hidden>
                        <p id="file-name" style="margin-top:10px;font-size:14px;color:#6b7280;"></p>

                        <span class="form-hint">
                            <i class="fas fa-info-circle"></i>
                            Supported formats: JPG, JPEG, PNG, GIF (Max 5MB)
                        </span>
                    </div>

                    
                    <?php if ($product['image_path']): ?>
                        <div class="current-image-preview">
                            <h3 style="color: var(--gray-700); font-size: 14px; font-weight: 600; margin-bottom: 12px; text-align: left;">
                                <i class="fas fa-image"></i> Current Image
                            </h3>
                            <img src="../../<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <a href="?id=<?php echo $product_id; ?>&delete_image=1" 
                               class="delete-image-btn"
                               onclick="return confirm('Are you sure you want to delete this image?')">
                                <i class="fas fa-trash-alt"></i> Delete Image
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="admin_product_list.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                </div>
            </div>
        </form>
    </div>
    <script>
    document.getElementById('image').addEventListener('change', function () {
        const fileName = this.files[0]?.name || '';
        document.getElementById('file-name').textContent = fileName ? `Selected: ${fileName}` : '';
    });

    // Variation rows
    const variationBody = document.getElementById('variation-rows');
    document.getElementById('add-variation-row').addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="padding:8px;">
                <input type="text" name="var_color[]" placeholder="e.g. Ivory" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
            </td>
            <td style="padding:8px;">
                <input type="text" name="var_size[]" placeholder="e.g. S" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
            </td>
            <td style="padding:8px;">
                <input type="number" min="0" name="var_stock[]" placeholder="0" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
            </td>
            <td style="padding:8px;">
                <input type="number" min="0" step="0.01" name="var_price[]" placeholder="Use base price" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
            </td>
            <td style="text-align:center;padding:8px;">
                <button type="button" class="btn-remove-row" style="border:none;background:#fee2e2;color:#b91c1c;padding:8px 12px;border-radius:6px;cursor:pointer;">✕</button>
            </td>
        `;
        variationBody.appendChild(tr);
    });

    variationBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-remove-row')) {
            const row = e.target.closest('tr');
            if (row && variationBody.children.length > 1) {
                row.remove();
            }
        }
    });
    </script>

</body>
</html>