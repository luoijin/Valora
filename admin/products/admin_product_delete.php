<?php
// admin/products/admin_product_delete.php - Delete product
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../dao/product_dao.php';

$productDAO = new ProductDAO($db);

$product_id = $_GET['id'] ?? null;
if (!$product_id || !is_numeric($product_id)) {
    header('Location: admin_product_list.php');
    exit();
}

$product = $productDAO->getProductById($product_id);
if (!$product) {
    header('Location: admin_product_list.php');
    exit();
}

// Delete product image
if ($product['image_path'] && file_exists('../../' . $product['image_path'])) {
    unlink('../../' . $product['image_path']);
}

if ($productDAO->deleteProduct($product_id)) {
    header('Location: admin_product_list.php?message=Product deleted successfully');
} else {
    header('Location: admin_product_list.php?message=Failed to delete product');
}
exit();
?>
