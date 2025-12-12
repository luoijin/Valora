<?php
// pages/search_results.php (Public version)
session_start();
require_once '../config.php';
require_once '../dao/product_dao.php';

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

  <link rel="shortcut icon" href="../favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/shop.css">
  <link rel="stylesheet" href="../assets/css/search_page.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body id="top">

  <!-- HEADER -->
  <header class="header">
    <div class="header-top" data-header>
      <div class="container">

        <button class="nav-open-btn" aria-label="open menu" data-nav-toggler>
          <span class="line line-1"></span>
          <span class="line line-2"></span>
          <span class="line line-3"></span>
        </button>

        <!-- FUNCTIONAL SEARCH BAR -->
      <div class="input-wrapper">
        <form action="search_results.php" method="GET" class="search-form">
          <input 
            type="text" 
            name="q" 
            placeholder="Search product" 
            class="search-field"
            required
          >
          <button type="submit" class="search-submit" aria-label="search">
            <ion-icon name="search-outline" aria-hidden="true"></ion-icon>
          </button>
        </form>
      </div>

        <a href="home.php" class="logo">
          <img src="../assets/images/logo/valora-logo-text.png" width="179" height="26" alt="Valora">
        </a>

        <div class="header-actions">

          <button class="header-action-btn" aria-label="cart item" onclick="window.location.href='login.php'">
            <ion-icon name="bag-handle-outline" aria-hidden="true"></ion-icon>
            <span class="btn-badge">0</span>
          </button>

          <button class="header-action-btn btn-valora" aria-label="sign in" onclick="window.location.href='login.php'">
            <span class="btn-text">Sign In</span>
            <ion-icon name="log-in-outline" aria-hidden="true"></ion-icon>
          </button>
        </div>

        <nav class="navbar">
          <ul class="navbar-list">
            <li><a href="home.php#home" class="navbar-link has-after">Home</a></li>
            <li><a href="home.php#collection" class="navbar-link has-after">Collection</a></li>
            <li><a href="home.php#dress" class="navbar-link has-after">Dress</a></li>
            <li><a href="home.php#gown" class="navbar-link has-after">Gown</a></li>
          </ul>
        </nav>

      </div>
    </div>
  </header>

  <!-- MOBILE NAVBAR -->
  <div class="sidebar">
    <div class="mobile-navbar" data-navbar>
      <div class="wrapper">
        <a href="home.php" class="logo">
          <img src="../assets/images/logo.png" width="179" height="26" alt="Valora">
        </a>
        <button class="nav-close-btn" aria-label="close menu" data-nav-toggler>
          <ion-icon name="close-outline" aria-hidden="true"></ion-icon>
        </button>
      </div>

      <!-- MOBILE SEARCH -->
      <div style="padding: 15px 20px;">
        <form action="search_results.php" method="GET" class="mobile-search-form">
          <input 
            type="search" 
            name="q" 
            placeholder="Search products..." 
            class="search-field"
            style="width: 100%; padding: 12px; border: 2px solid #e5e5e5; border-radius: 8px; font-size: 14px;"
            value="<?php echo htmlspecialchars($searchQuery); ?>"
            required
          >
        </form>
      </div>

      <ul class="navbar-list">
        <li><a href="home.php#home" class="navbar-link" data-nav-link>Home</a></li>
        <li><a href="home.php#collection" class="navbar-link" data-nav-link>Collection</a></li>
        <li><a href="home.php#dress" class="navbar-link" data-nav-link>Dress</a></li>
        <li><a href="home.php#gown" class="navbar-link" data-nav-link>Gown</a></li>
      </ul>
    </div>
    <div class="overlay" data-nav-toggler data-overlay></div>
  </div>

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
                  <a href="item.php?id=<?php echo $product['id']; ?>" class="product-image">
                    <?php 
                    $imagePath = $product['image_path'];
                    if ($imagePath) {
                        $imagePath = preg_replace('/^\.\.\/+/', '', $imagePath);
                        $imageUrl = '../' . $imagePath;
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
                      <button onclick="event.preventDefault(); event.stopPropagation(); alert('Please login to add items to cart');" class="action-btn" aria-label="add to cart">
                        <ion-icon name="bag-handle-outline" aria-hidden="true"></ion-icon>
                      </button>
                      <button onclick="event.preventDefault(); event.stopPropagation(); alert('Please login to add to wishlist');" class="action-btn" aria-label="add to wishlist">
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
                      <a href="item.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
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
                <a href="home.php" class="btn btn-primary">Back to Home</a>
                <a href="login.php" class="btn btn-outline">Login to Shop</a>
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
  <?php include '../includes/public_footer.php'; ?>

  <!-- BACK TO TOP -->
  <a href="#top" class="back-top-btn" aria-label="back to top" data-back-top-btn>
    <ion-icon name="arrow-up" aria-hidden="true"></ion-icon>
  </a>

  <script src="../assets/js/script.js" defer></script>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>