<?php
// home.php
session_start();
require_once '../config.php';
require_once '../dao/product_dao.php';

$productDAO = new ProductDAO($db);
$products = $productDAO->getAllProducts(true); // only active products

// Group products by category for better display
$productsByCategory = [
    'collection' => [],
    'dress' => [],
    'gown' => [],
];

foreach ($products as $product) {
    if (isset($productsByCategory[$product['category']])) {
        $productsByCategory[$product['category']][] = $product;
    }
}

// Limit to 4 products per category for home page
foreach ($productsByCategory as $category => $items) {
    $productsByCategory[$category] = array_slice($items, 0, 4);
}

// Get unique collections for the collection section
$collections = [];
foreach ($products as $product) {
    if (!empty($product['collection']) && $product['collection'] !== 'N/A') {
        $collectionName = $product['collection'];
        if (!isset($collections[$collectionName])) {
            $collections[$collectionName] = $product; // Store first product of each collection
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Valora - Home</title>

  <link rel="shortcut icon" href="../favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/shop.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="preload" as="image" href="../assets/images/logo.png">
  <link rel="preload" as="image" href="../assets/images/hero-banner-1.jpg">
  <link rel="preload" as="image" href="../assets/images/hero-banner-2.jpg">
  <link rel="preload" as="image" href="../assets/images/hero-banner-3.jpg">
</head>

<body id="top">

  <!-- HEADER -->
  <header class="header">
    <!-- <style>
    /* ================================================
   LOGO POSITION ADJUSTMENT - MOVE LEFT
   ================================================ */
/* If you want more control based on screen size */
@media (min-width: 992px) {
    .header-top .logo {
        position: relative;
        right: -50px; /* Adjust for larger screens */
    }
} -->

  </style>
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
              type="search" 
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
            <li><a href="#collection" class="navbar-link has-after">Collection</a></li>
            <li><a href="#dress" class="navbar-link has-after">Dress</a></li>
            <li><a href="#gown" class="navbar-link has-after">Gown</a></li>
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

      <ul class="navbar-list">
        <li><a href="#home" class="navbar-link" data-nav-link>Home</a></li>
        <li><a href="#collection" class="navbar-link" data-nav-link>Collection</a></li>
        <li><a href="#dress" class="navbar-link" data-nav-link>Dress</a></li>
        <li><a href="#gown" class="navbar-link" data-nav-link>Gown</a></li>
      </ul>
    </div>
    <div class="overlay" data-nav-toggler data-overlay></div>
  </div>

  <main>
    <article>

      <!-- HERO -->
      <section class="section hero" id="home" aria-label="hero" data-section>
        <div class="container">
          <ul class="has-scrollbar">
            <li class="scrollbar-item">
              <div class="hero-card has-bg-image" style="background-image: url('../assets/images/hero-banner-1.png')">
                <div class="card-content">
                  <h1 class="h1 hero-title">Your Dream Wedding <br>Starts Here</h1>
                  <p class="hero-text">Discover our exquisite collection of bridal gowns, bridesmaid dresses, and formal attire for your special day.</p>
                  <a href="login.php" class="btn btn-primary">Shop Now</a>
                </div>
              </div>
            </li>
      </section>

      <!-- COLLECTION BANNER -->
      <section class="section collection" id="collection" aria-label="collection" data-section>
        <div class="container">
          <ul class="collection-list">
            <li>
              <br><h2 class="h2 card-title">Fall Bridal</h2><br>
              <div class="collection-card has-before hover:shine">
                
                <p class="card-text"></p>
                <a href="login.php" class="btn-link">
                  <span class="span">Shop Now</span>
                  <ion-icon name="arrow-forward" aria-hidden="true"></ion-icon>
                </a>
                <div class="has-bg-image" style="background-image: url('../assets/images/collection/collection-1.png')"></div>
              </div>
            </li>

            <li>
              <br><h2 class="h2 card-title">Summer Bridal</h2><br>
              <div class="collection-card has-before hover:shine">
                
                <p class="card-text"></p>
                <a href="login.php" class="btn-link">
                  <span class="span">Discover Now</span>
                  <ion-icon name="arrow-forward" aria-hidden="true"></ion-icon>
                </a>
                <div class="has-bg-image" style="background-image: url('../assets/images/collection/collection-2.png')"></div>
              </div>
            </li>

            <li>
              <br><h2 class="h2 card-title">Bridal Studio</h2><br>
              <div class="collection-card has-before hover:shine">
                
                <p class="card-text"></p>
                <a href="login.php" class="btn-link">
                  <span class="span">Discover Now</span>
                  <ion-icon name="arrow-forward" aria-hidden="true"></ion-icon>
                </a>
                <div class="has-bg-image" style="background-image: url('../assets/images/collection/collection-3.png')"></div>
              </div>
            </li>
          </ul>
        </div>
      </section>

      <!-- DRESSES SECTION (GRID LAYOUT - 4 ITEMS) -->
      <?php if (!empty($productsByCategory['dress'])): ?>
      <section class="section shop" id="dress" aria-label="dresses" data-section>
        <div class="container">
          <div class="title-wrapper">
            <h2 class="h2 section-title">Dresses</h2>
            <a href="login.php" class="btn-link">
              <span class="span">View All</span>
              <ion-icon name="arrow-forward" aria-hidden="true"></ion-icon>
            </a>
          </div>

          <div class="products-grid">
            <?php foreach ($productsByCategory['dress'] as $product): ?>
              <div class="product-card">
                <div class="product-image">
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

                   
                  </a>
                </div>

                <div class="product-info">
                  <div class="product-category"><?php echo strtoupper($product['category']); ?></div>
                  <h3 class="product-title">
                    <a href="login.php"><?php echo htmlspecialchars($product['name']); ?></a>
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

      <!-- GOWNS SECTION (GRID LAYOUT - 4 ITEMS) -->
      <?php if (!empty($productsByCategory['gown'])): ?>
      <section class="section shop" id="gown" aria-label="gowns" data-section>
        <div class="container">
          <div class="title-wrapper">
            <h2 class="h2 section-title">Gowns</h2>
            <a href="login.php" class="btn-link">
              <span class="span">View All</span>
              <ion-icon name="arrow-forward" aria-hidden="true"></ion-icon>
            </a>
          </div>

          <div class="products-grid">
            <?php foreach ($productsByCategory['gown'] as $product): ?>
              <div class="product-card">
                <div class="product-image">
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
                  </a>
                </div>

                <div class="product-info">
                  <div class="product-category"><?php echo strtoupper($product['category']); ?></div>
                  <h3 class="product-title">
                    <a href="login.php"><?php echo htmlspecialchars($product['name']); ?></a>
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