<?php
// user_shop_page.php
session_start();
require_once '../../config.php';
require_once '../../dao/product_dao.php';

$productDAO = new ProductDAO($db);
$products = $productDAO->getAllProducts(true); // only active products

// Filter and organize products
$dressesAndGowns = [];
foreach ($products as $product) {
    if (in_array($product['category'], ['dress', 'gown'])) {
        $dressesAndGowns[] = $product;
    }
}

// Get filter parameters
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'all';
$selectedCollection = isset($_GET['collection']) ? $_GET['collection'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get unique collections
$collections = array_unique(array_map(function($p) {
    return $p['collection'];
}, $dressesAndGowns));
$collections = array_filter($collections, function($c) {
    return !empty($c) && $c !== 'N/A';
});

// Apply filters
$filteredProducts = $dressesAndGowns;

if ($selectedCategory !== 'all') {
    $filteredProducts = array_filter($filteredProducts, function($p) use ($selectedCategory) {
        return $p['category'] === $selectedCategory;
    });
}

if ($selectedCollection !== 'all') {
    $filteredProducts = array_filter($filteredProducts, function($p) use ($selectedCollection) {
        return $p['collection'] === $selectedCollection;
    });
}

// Apply sorting
usort($filteredProducts, function($a, $b) use ($sortBy) {
    switch ($sortBy) {
        case 'price_low':
            return $a['price'] - $b['price'];
        case 'price_high':
            return $b['price'] - $a['price'];
        case 'name':
            return strcmp($a['name'], $b['name']);
        default: // newest
            $idA = isset($a['product_id']) ? $a['product_id'] : (isset($a['id']) ? $a['id'] : 0);
            $idB = isset($b['product_id']) ? $b['product_id'] : (isset($b['id']) ? $b['id'] : 0);
            return $idB - $idA;
    }
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop - Valora</title>

  <link rel="shortcut icon" href="../../favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/shop.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">

 
</head>

<body id="top">

   <!-- Include Header/Navbar -->
  <?php include '../../includes/user_header.php'; ?>

  <!-- Include Mobile Sidebar -->
  <?php include '../../includes/user_sidebar.php'; ?>

  <main>
    <article>

      <!-- SHOP HEADER -->
      <section class="shop-header">
        <div class="container">
          <h1>Shop All Products</h1>
          <p>Discover our complete collection of elegant dresses and stunning gowns</p>
        </div>
      </section>

      <!-- SHOP CONTROLS -->
      <section class="shop-controls">
        <div class="container">
          <div class="controls-wrapper">
            <div class="filter-group">
              <form method="GET" action="" style="display: contents;">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                  <div>
                    <label for="category">Category:</label>
                    <select name="category" id="category" class="filter-select" onchange="this.form.submit()">
                      <option value="all" <?php echo $selectedCategory === 'all' ? 'selected' : ''; ?>>All</option>
                      <option value="dress" <?php echo $selectedCategory === 'dress' ? 'selected' : ''; ?>>Dress</option>
                      <option value="gown" <?php echo $selectedCategory === 'gown' ? 'selected' : ''; ?>>Gown</option>
                    </select>
                  </div>

                  <?php if (!empty($collections)): ?>
                  <div>
                    <label for="collection">Collection:</label>
                    <select name="collection" id="collection" class="filter-select" onchange="this.form.submit()">
                      <option value="all" <?php echo $selectedCollection === 'all' ? 'selected' : ''; ?>>All Collections</option>
                      <?php foreach ($collections as $collection): ?>
                        <option value="<?php echo htmlspecialchars($collection); ?>" <?php echo $selectedCollection === $collection ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($collection); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php endif; ?>

                  <div>
                    <label for="sort">Sort By:</label>
                    <select name="sort" id="sort" class="filter-select" onchange="this.form.submit()">
                      <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest</option>
                      <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                      <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                      <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name</option>
                    </select>
                  </div>
                </div>
              </form>
            </div>

            <div class="results-info">
              Showing <?php echo count($filteredProducts); ?> of <?php echo count($dressesAndGowns); ?> products
            </div>
          </div>
        </div>
      </section>

      <!-- PRODUCTS GRID -->
      <section class="section">
        <div class="container">
          <?php if (!empty($filteredProducts)): ?>
            <div class="products-grid">
              <?php foreach ($filteredProducts as $product): ?>
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
                    <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
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
          <?php else: ?>
            <div class="empty-state">
              <h3>No Products Found</h3>
              <p>Try adjusting your filters or check back later for new arrivals</p>
            </div>
          <?php endif; ?>
        </div>
      </section>

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