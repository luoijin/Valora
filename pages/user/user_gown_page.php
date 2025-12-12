<?php
// user_gown_page.php
session_start();
require_once '../../config.php';
require_once '../../dao/product_dao.php';

$productDAO = new ProductDAO($db);
$products = $productDAO->getAllProducts(true); // only active products

// Group products by category for better display
$productsByCategory = [
    'gown' => []
];

foreach ($products as $product) {
    if (isset($productsByCategory[$product['category']])) {
        $productsByCategory[$product['category']][] = $product;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Valora - Gowns</title>

  <link rel="shortcut icon" href="../../favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/shop.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="preload" as="image" href="../../assets/images/logo.png">
  <link rel="preload" as="image" href="../../assets/images/hero-banner-1.jpg">
</head>

<body id="top">

  <!-- Include Header/Navbar -->
  <?php include '../../includes/user_header.php'; ?>

  <!-- Include Mobile Sidebar -->
  <?php include '../../includes/user_sidebar.php'; ?>

  <main>
    <article>

      <!-- PAGE HEADER -->
      <section class="shop-header">
        <div class="container">
          <h1>Gowns</h1>
          <p>Discover our stunning collection of gowns for your special moments</p>
        </div>
      </section>

      <!-- PRODUCTS GRID SECTIONS -->
      <?php foreach ($productsByCategory as $category => $items): ?>
        <?php if (!empty($items)): ?>
        <section class="section">
          <div class="container">
            <div class="products-grid">
              <?php foreach ($items as $product): ?>
                <div class="product-card">
                  <a href="user_item.php?id=<?php echo $product['id']; ?>" class="product-image">
                    <?php 
                    $imagePath = $product['image_path'];
                    if ($imagePath) {
                        $imagePath = preg_replace('/^\.\.\/+/', '', $imagePath);
                        $imageUrl = '../../' . $imagePath;
                    }
                    ?>
                    <?php if ($imagePath && file_exists($imageUrl)): ?>
                      <img src="<?php echo htmlspecialchars($imageUrl); ?>" loading="lazy" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                      <div style="width:100%;height:100%;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);display:flex;align-items:center;justify-content:center;color:white;font-size:18px;">
                        No Image
                      </div>
                    <?php endif; ?>

                    <?php if ($product['stock_quantity'] <= 5): ?>
                      <span class="product-badge low-stock">Low Stock</span>
                    <?php elseif ($product['stock_quantity'] > 20): ?>
                      <span class="product-badge">In Stock</span>
                    <?php endif; ?>

              
                  </a>

                  <div class="product-info">
                    <div class="product-category"><?php echo strtoupper($product['category']); ?></div>
                    <h3 class="product-title">
                      <a href="user_item.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                    </h3>
                    <?php if (!empty($product['collection']) && $product['collection'] !== 'N/A'): ?>
                      <p class="product-collection"><?php echo htmlspecialchars($product['collection']); ?> Collection</p>
                    <?php endif; ?>
                    <div class="product-footer">
                      <span class="product-price">₱<?php echo number_format($product['price'], 2); ?></span>
                      <span class="product-stock"><?php echo $product['stock_quantity']; ?> in stock</span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if (empty($products)): ?>
      <section class="empty-state">
        <div class="container">
          <h3>No Products Available</h3>
          <p>Check back soon for new arrivals!</p>
        </div>
      </section>
      <?php endif; ?>

    </article>
  </main>

  <!-- Include Footer -->
  <?php include '../../includes/footer.php'; ?>

  <!-- BACK TO TOP -->
  <a href="#top" class="back-top-btn" aria-label="back to top" data-back-top-btn>
    <ion-icon name="arrow-up" aria-hidden="true"></ion-icon>
  </a>

  <script src="../../assets/js/script.js" defer></script>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <script src="../../assets/js/cart.js"></script>
</body>
</html>