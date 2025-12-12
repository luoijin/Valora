<?php
// pages/user/user_search_results.php
session_start();
require_once '../../config.php';
require_once '../../dao/product_dao.php';

$productDAO = new ProductDAO($db);

// Get search query
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchResults = [];
$resultCount = 0;

if (!empty($searchQuery)) {
    $searchResults = $productDAO->searchProducts($searchQuery, true); // only active products
    $resultCount = count($searchResults);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results - Valora</title>

  <link rel="shortcut icon" href="../../favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/shop.css">
  <link rel="stylesheet" href="../../assets/css/search_page.css">

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

      <!-- SEARCH HEADER -->
      <section class="shop-header">
        <div class="container">
          <h1>Search Results</h1>
          <?php if (!empty($searchQuery)): ?>
            <p>Showing results for: <strong>"<?php echo htmlspecialchars($searchQuery); ?>"</strong></p>
            <p style="margin-top: 10px; color: var(--spanish-gray);">Found <?php echo $resultCount; ?> product<?php echo $resultCount !== 1 ? 's' : ''; ?></p>
          <?php else: ?>
            <p>Enter a search term to find products</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- SEARCH FORM -->
      <section class="shop-controls">
        <div class="container">
          <form method="GET" action="" class="search-form-main">
            <input 
              type="search" 
              name="q" 
              placeholder="Search for dresses, gowns, collections..." 
              class="search-field-main"
              value="<?php echo htmlspecialchars($searchQuery); ?>"
              required
            >
            <button type="submit" class="search-submit-main">
              <ion-icon name="search-outline"></ion-icon>
              <span>Search</span>
            </button>
          </form>
        </div>
      </section>

      <!-- SEARCH RESULTS -->
      <section class="section">
        <div class="container">
          <?php if (!empty($searchQuery) && !empty($searchResults)): ?>
            <div class="products-grid">
              <?php foreach ($searchResults as $product): ?>
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

                    <div class="product-actions">
                      <button onclick="event.preventDefault(); event.stopPropagation(); addToCart(<?php echo $product['id']; ?>)" class="action-btn" aria-label="add to cart">
                        <ion-icon name="bag-handle-outline" aria-hidden="true"></ion-icon>
                      </button>
                      <button onclick="event.preventDefault(); event.stopPropagation();" class="action-btn" aria-label="add to wishlist">
                        <ion-icon name="star-outline" aria-hidden="true"></ion-icon>
                      </button>
                      <button onclick="event.preventDefault(); event.stopPropagation();" class="action-btn" aria-label="quick view">
                        <ion-icon name="eye-outline" aria-hidden="true"></ion-icon>
                      </button>
                    </div>
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
          <?php elseif (!empty($searchQuery) && empty($searchResults)): ?>
            <div class="empty-state">
              <ion-icon name="search-outline" style="font-size: 80px; color: var(--spanish-gray); margin-bottom: 20px;"></ion-icon>
              <h3>No Products Found</h3>
              <p>We couldn't find any products matching "<?php echo htmlspecialchars($searchQuery); ?>"</p>
              <p style="margin-top: 15px;">Try different keywords or browse our collections</p>
              <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="user_shop_page.php" class="btn btn-primary">Browse All Products</a>
                <a href="user_home_page.php" class="btn btn-outline">Back to Home</a>
              </div>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <ion-icon name="search-outline" style="font-size: 80px; color: var(--spanish-gray); margin-bottom: 20px;"></ion-icon>
              <h3>Start Searching</h3>
              <p>Enter keywords to search for your perfect dress or gown</p>
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