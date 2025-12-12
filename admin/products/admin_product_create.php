<?php
// admin/products/admin_product_create.php - Create new product
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

$collection = $_POST['collection'] ?? 'N/A';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $category = $_POST['category'] ?? '';
    $collection = $_POST['collection'] ?? '';
    $colors = $_POST['var_color'] ?? [];
    $sizes = $_POST['var_size'] ?? [];
    $stocks = $_POST['var_stock'] ?? [];
    $prices = $_POST['var_price'] ?? [];
    
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
    
    // Calculate stock quantity from variations
    $stock_quantity = $variationStockTotal;
    
    // Validation
    if (empty($name)) $errors[] = 'Product name is required';
    if (empty($description)) $errors[] = 'Product description is required';
    if (empty($price)) $errors[] = 'Price is required';
    if (!is_numeric($price) || $price < 0) $errors[] = 'Price must be a valid number';
    if (empty($category)) $errors[] = 'Category is required';
    if (empty($collection)) $errors[] = 'Collection is required';
    if (empty($variationRows)) $errors[] = 'At least one variation is required';
    
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
        try {
            $db->beginTransaction();

            if (!$productDAO->createProduct($name, $description, $price, $category, $image_path, $stock_quantity, $collection)) {
                throw new Exception('Failed to create product');
            }

            $product_id = $db->lastInsertId();

            // Create variations
            foreach ($variationRows as $row) {
                $variationDAO->createVariation($product_id, $row['color'], $row['size'], $row['stock'], $row['price']);
            }

            $db->commit();
            header('Location: admin_product_list.php?message=Product created successfully');
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = $e->getMessage();
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
    <style>
        .stock-display {
            background: #f3f4f6;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            color: #374151;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stock-display .stock-value {
            color: #0d3b2e;
            font-size: 18px;
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
                            <label>
                                <i class="fas fa-boxes input-icon"></i>
                                Total Stock Quantity
                            </label>
                            <div class="stock-display">
                                <i class="fas fa-calculator"></i>
                                <span>Auto-calculated:</span>
                                <span class="stock-value" id="total-stock-display">0</span>
                                <span>units</span>
                            </div>
                            <span class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Automatically calculated from variation stocks below
                            </span>
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

                <!-- Variations -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-swatchbook"></i> Variations (Color / Size) <span class="required-star">*</span>
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
                                            $rowsToShow = !empty($variationRows) ? $variationRows : [['color'=>'','size'=>'','stock'=>'','price'=>'']];
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
                                                <input type="number" min="0" name="var_stock[]" value="<?php echo htmlspecialchars($row['stock'] ?? ''); ?>" placeholder="0" class="variation-stock-input" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
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
                            <span class="form-hint" style="margin-top:10px;display:block;"><i class="fas fa-info-circle"></i> Product stock will auto-calculate from variation stocks.</span>
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

        // Calculate total stock from variations
        function updateTotalStock() {
            const stockInputs = document.querySelectorAll('.variation-stock-input');
            let total = 0;
            stockInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                total += Math.max(0, value);
            });
            document.getElementById('total-stock-display').textContent = total;
        }

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
                    <input type="number" min="0" name="var_stock[]" placeholder="0" class="variation-stock-input" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                </td>
                <td style="padding:8px;">
                    <input type="number" min="0" step="0.01" name="var_price[]" placeholder="Use base price" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                </td>
                <td style="text-align:center;padding:8px;">
                    <button type="button" class="btn-remove-row" style="border:none;background:#fee2e2;color:#b91c1c;padding:8px 12px;border-radius:6px;cursor:pointer;">✕</button>
                </td>
            `;
            variationBody.appendChild(tr);
            updateTotalStock();
        });

        variationBody.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-remove-row')) {
                const row = e.target.closest('tr');
                if (row && variationBody.children.length > 1) {
                    row.remove();
                    updateTotalStock();
                }
            }
        });

        // Update total stock on input change
        variationBody.addEventListener('input', (e) => {
            if (e.target.classList.contains('variation-stock-input')) {
                updateTotalStock();
            }
        });

        // Initialize total stock display
        updateTotalStock();
    </script>
</body>
</html>