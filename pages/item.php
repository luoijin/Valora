<?php
// pages/item.php
session_start();
require_once '../config.php';
require_once '../dao/product_dao.php';
require_once '../dao/variation_dao.php';
require_once '../dao/review_dao.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: home.php');
    exit();
}

$productDAO = new ProductDAO($db);
$variationDAO = new VariationDAO($db);
$reviewDAO = new ReviewDAO($db);
$product = $productDAO->getProductById($product_id);
$variations = $variationDAO->getVariationsForProduct($product_id);
$averageRating = $reviewDAO->getAverageRating($product_id);
$reviews = $reviewDAO->getProductReviews($product_id);
$totalReviews = count($reviews);

if (!$product) {
    header('Location: home.php');
    exit();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Calculate discount percentage if there's a sale
$discount = 0;
$originalPrice = $product['price'] * 1.2; // Example: 20% markup for display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Valora</title>
    
    <link rel="shortcut icon" href="../favicon.svg" type="image/svg+xml">
    
    <!-- Base Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Item Detail Specific Styles -->
    <link rel="stylesheet" href="../assets/css/item.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body id="top">
    <!-- Notification -->
    <div id="notification" class="item-notification">
        <span id="notificationText"></span>
    </div>

    <!-- Include Header/Navbar -->
    <?php include '../includes/header.php'; ?>

    <main>
      <article>
        <!-- Product Detail -->
        <section class="section">
          <div class="item-container">
            <div class="item-detail">
              <!-- Image Gallery -->
              <div class="item-gallery">
                <div class="item-main-image">
                  <?php 
                  $imagePath = $product['image_path'];
                  if ($imagePath) {
                      $imagePath = preg_replace('/^\.\.\/+/', '', $imagePath);
                      $imageUrl = '../' . $imagePath;
                  }
                  ?>
                  <?php if ($imagePath && file_exists($imageUrl)): ?>
                    <img id="mainImage" src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                  <?php else: ?>
                    <div class="item-no-image">No Image Available</div>
                  <?php endif; ?>
                </div>

                <!-- Thumbnails -->
                <div class="item-thumbnail-grid">
                  <?php if ($imagePath && file_exists($imageUrl)): ?>
                    <div class="item-thumbnail active">
                      <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="View 1">
                    </div>
                    <div class="item-thumbnail">
                      <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="View 2">
                    </div>
                    <div class="item-thumbnail">
                      <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="View 3">
                    </div>
                    <div class="item-thumbnail">
                      <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="View 4">
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Product Information -->
              <div class="item-info">
                <div class="item-header">
                  <div class="item-category"><?php echo strtoupper($product['category']); ?></div>
                  <h1 class="item-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                  <?php if (!empty($product['collection']) && $product['collection'] !== 'N/A'): ?>
                    <p class="item-collection"><?php echo htmlspecialchars($product['collection']); ?> Collection</p>
                  <?php endif; ?>
                  
                  <div class="item-rating-section">
                    <div class="item-stars">
                      <?php 
                        $avg = $averageRating ?? 0;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= floor($avg)) {
                                echo '<ion-icon name="star"></ion-icon>';
                            } elseif ($i - 0.5 <= $avg) {
                                echo '<ion-icon name="star-half"></ion-icon>';
                            } else {
                                echo '<ion-icon name="star-outline"></ion-icon>';
                            }
                        }
                      ?>
                    </div>
                    <span class="item-rating-text">
                      <?php 
                        if ($totalReviews > 0) {
                            echo number_format($avg, 1) . ' (' . $totalReviews . ' review' . ($totalReviews > 1 ? 's' : '') . ')';
                        } else {
                            echo 'No reviews yet';
                        }
                      ?>
                    </span>
                  </div>
                </div>

                <div class="item-price-section">
                  <div class="item-price">₱<?php echo number_format($product['price'], 2); ?></div>
                  <div class="item-original-price">₱<?php echo number_format($originalPrice, 2); ?></div>
                </div>

                <?php 
                  $defaultStock = $product['stock_quantity'];
                  if (!empty($variations)) {
                      $defaultVariation = $variations[0];
                      $defaultStock = $defaultVariation['stock_quantity'];
                  }
                ?>

                <div class="item-description">
                  <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>

                <!-- Color Variant -->
                <div class="item-variant-section">
                  <div class="item-variant-label">Select Color <span id="selected-color-label" style="font-size:12px;color:#555;margin-left:6px;"></span></div>
                  <div class="item-color-options">
                    <?php if (!empty($variations)): ?>
                        <?php 
                          $colors = [];
                          foreach ($variations as $variation) {
                            $colors[$variation['color']] = true;
                          }
                          $firstColor = array_key_first($colors);
                          foreach (array_keys($colors) as $color):
                            $bg = '#e5e7eb';
                            if (preg_match('/^#?[0-9a-fA-F]{3,6}$/', $color)) {
                                $bg = '#' . ltrim($color, '#');
                            } elseif (preg_match('/^[a-zA-Z]+$/', $color)) {
                                $bg = $color;
                            }
                        ?>
                          <div class="item-color-option <?php echo $color === $firstColor ? 'active' : ''; ?>" 
                               data-color="<?php echo htmlspecialchars($color); ?>" 
                               title="<?php echo htmlspecialchars($color); ?>"
                               style="background: <?php echo htmlspecialchars($bg); ?>;"></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                      <div class="item-color-option active" style="background: #FFFFF0;" data-color="Ivory White" title="Ivory White"></div>
                      <div class="item-color-option" style="background: #FFE4E1;" data-color="Blush Pink" title="Blush Pink"></div>
                      <div class="item-color-option" style="background: #FFFDD0;" data-color="Cream" title="Cream"></div>
                      <div class="item-color-option" style="background: linear-gradient(135deg, #FFD700, #FFA500);" data-color="Golden" title="Golden"></div>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Size Variant -->
                <div class="item-variant-section">
                  <div class="item-variant-label">Select Size</div>
                  <div class="item-variant-options">
                    <?php if (!empty($variations)): ?>
                      <?php 
                        $sizes = [];
                        foreach ($variations as $variation) {
                          $sizes[$variation['size']] = true;
                        }
                        $firstSize = array_key_first($sizes);
                      ?>
                      <?php foreach (array_keys($sizes) as $size): ?>
                        <div class="item-variant-option <?php echo $size === $firstSize ? 'active' : ''; ?>" data-size="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="item-variant-option" data-size="XS">XS</div>
                      <div class="item-variant-option active" data-size="S">S</div>
                      <div class="item-variant-option" data-size="M">M</div>
                      <div class="item-variant-option" data-size="L">L</div>
                      <div class="item-variant-option" data-size="XL">XL</div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="item-variant-section">
                  <div class="item-variant-label" id="variant-stock-label">Available stock: <?php echo isset($defaultStock) ? (int)$defaultStock : (int)$product['stock_quantity']; ?></div>
                </div>

                <!-- Quantity -->
                <div class="item-variant-section">
                  <div class="item-variant-label">Quantity</div>
                  <div class="item-quantity-section">
                    <div class="item-quantity-control">
                      <button class="item-qty-btn" id="decreaseQty">−</button>
                      <input type="number" id="quantity" class="item-qty-input" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" readonly>
                      <button class="item-qty-btn" id="increaseQty">+</button>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="item-action-buttons">
                  <!-- Add to Cart -->
                  <button class="item-btn item-btn-primary login-check"
                          id="addToCartBtn"
                          data-product-id="<?php echo $product['id']; ?>"
                          data-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>">
                    <ion-icon name="bag-handle-outline"></ion-icon>
                    Add to Cart
                  </button>

                  <!-- Add to Wishlist -->
                  <button class="item-btn item-btn-icon login-check"
                          id="addToWishlistBtn"
                          data-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>">
                    <ion-icon name="heart-outline"></ion-icon>
                  </button>
                </div>

                <!-- Product Details -->
                <div class="item-details-section">
                  <div class="item-variant-label">Product Details</div>
                  <div class="item-details-grid">
                    <div class="item-detail-item">
                      <div class="item-detail-label">Fabric</div>
                      <div class="item-detail-value">Premium Satin & Lace</div>
                    </div>
                    <div class="item-detail-item">
                      <div class="item-detail-label">Care Instructions</div>
                      <div class="item-detail-value">Dry Clean Only</div>
                    </div>
                    <div class="item-detail-item">
                      <div class="item-detail-label">Category</div>
                      <div class="item-detail-value"><?php echo ucfirst($product['category']); ?></div>
                    </div>
                    <div class="item-detail-item">
                      <div class="item-detail-label">Product ID</div>
                      <div class="item-detail-value">#VAL-<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></div>
                    </div>
                  </div>
                </div>

                <!-- Features -->
                <div class="item-features">
                  <div class="item-feature">
                    <div class="item-feature-icon">
                      <ion-icon name="gift-outline"></ion-icon>
                    </div>
                    <div class="item-feature-text">Free Shipping<br>over ₱5,000</div>
                  </div>
                  <div class="item-feature">
                    <div class="item-feature-icon">
                      <ion-icon name="shield-checkmark-outline"></ion-icon>
                    </div>
                    <div class="item-feature-text">Quality<br>Guaranteed</div>
                  </div>
                  <div class="item-feature">
                    <div class="item-feature-icon">
                      <ion-icon name="repeat-outline"></ion-icon>
                    </div>
                    <div class="item-feature-text">Easy Returns<br>within 30 days</div>
                  </div>
                </div>
              </div>
            </div>
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

    <!-- Scripts -->
    <script src="../assets/js/script.js" defer></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    
    <script>
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        
        // Quantity Controls
        const qtyInput = document.getElementById('quantity');
        const increaseBtn = document.getElementById('increaseQty');
        const decreaseBtn = document.getElementById('decreaseQty');
        const variationData = <?php echo json_encode($variations); ?>;
        const basePrice = <?php echo (float)$product['price']; ?>;
        const baseOriginal = <?php echo number_format($originalPrice, 2, '.', ''); ?>;
        const priceEl = document.querySelector('.item-price');
        const originalPriceEl = document.querySelector('.item-original-price');
        let selectedColor = (document.querySelector('.item-color-option.active') || {}).dataset?.color || null;
        let selectedSize = (document.querySelector('.item-variant-option.active') || {}).dataset?.size || null;

        const formatPrice = (num) => num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

        function resolvePrice() {
            if (variationData && variationData.length > 0 && selectedColor && selectedSize) {
                const match = variationData.find(v => v.color === selectedColor && v.size === selectedSize);
                if (match && match.price !== null && match.price !== undefined && match.price !== '') {
                    return parseFloat(match.price);
                }
            }
            return basePrice;
        }

        function updatePriceDisplay() {
            const price = resolvePrice();
            if (priceEl) {
                priceEl.textContent = `₱${formatPrice(price)}`;
            }
            if (originalPriceEl) {
                // keep original at 20% markup relative to current price
                const orig = price * 1.2;
                originalPriceEl.textContent = `₱${formatPrice(orig)}`;
            }
        }

        function resolveStock() {
            if (variationData && variationData.length > 0 && selectedColor && selectedSize) {
                const match = variationData.find(v => v.color === selectedColor && v.size === selectedSize);
                if (match) {
                    return parseInt(match.stock_quantity, 10) || 0;
                }
            }
            return <?php echo (int)$product['stock_quantity']; ?>;
        }

        function updateStockLabel() {
            const label = document.getElementById('variant-stock-label');
            const stock = resolveStock();
            const colorLabel = document.getElementById('selected-color-label');
            if (colorLabel) {
                colorLabel.textContent = selectedColor ? `(${selectedColor})` : '';
            }
            if (label) {
                label.textContent = `Available stock: ${stock}`;
            }
            qtyInput.max = stock;
            if (parseInt(qtyInput.value, 10) > stock) {
                qtyInput.value = stock || 1;
            }
            updatePriceDisplay();
            return stock;
        }

        let maxStock = updateStockLabel();

        increaseBtn.addEventListener('click', () => {
            let currentQty = parseInt(qtyInput.value);
            if (currentQty < maxStock) {
                qtyInput.value = currentQty + 1;
            } else {
                showNotification('Maximum stock reached', 'error');
            }
        });

        decreaseBtn.addEventListener('click', () => {
            let currentQty = parseInt(qtyInput.value);
            if (currentQty > 1) {
                qtyInput.value = currentQty - 1;
            }
        });

        // Color Selection
        document.querySelectorAll('.item-color-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.item-color-option').forEach(o => o.classList.remove('active'));
                this.classList.add('active');
                selectedColor = this.getAttribute('data-color');
                maxStock = updateStockLabel();
            });
        });

        // Size Selection
        document.querySelectorAll('.item-variant-option').forEach(option => {
            option.addEventListener('click', function() {
                const parent = this.parentElement;
                parent.querySelectorAll('.item-variant-option').forEach(o => o.classList.remove('active'));
                this.classList.add('active');
                selectedSize = this.getAttribute('data-size');
                maxStock = updateStockLabel();
            });
        });

        // Authentication Check Function
        function checkAuthAndRedirect(action) {
            if (!isLoggedIn) {
                showNotification('Please login to continue', 'error');
                return false;
            }
            return true;
        }

        // Add to Cart
        document.getElementById('addToCartBtn').addEventListener('click', function() {
            if (!checkAuthAndRedirect('cart')) {
                return;
            }

            const productId = this.getAttribute('data-product-id');
            const quantity = parseInt(qtyInput.value);
            
            if (quantity > maxStock) {
                showNotification('Not enough stock available', 'error');
                return;
            }
            
            if (quantity < 1) {
                showNotification('Please select at least 1 item', 'error');
                return;
            }

            // If logged in, proceed with cart functionality
            showNotification('Item added to cart!', 'success');
        });

        // Add to Wishlist
        document.getElementById('addToWishlistBtn').addEventListener('click', function() {
            if (!checkAuthAndRedirect('wishlist')) {
                return;
            }

            // If logged in, add to wishlist
            showNotification('Added to wishlist!', 'success');
        });

        // Notification Function
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            
            notificationText.textContent = message;
            notification.className = `item-notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Thumbnail click
        document.querySelectorAll('.item-thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                document.querySelectorAll('.item-thumbnail').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const img = this.querySelector('img');
                if (img) {
                    document.getElementById('mainImage').src = img.src;
                }
            });
        });
    </script>
</body>
</html>