<?php
// pages/user/user_product_reviews.php
session_start();
require_once '../../config.php';
require_once '../../dao/product_dao.php';
require_once '../../dao/review_dao.php';

// Get product ID from URL
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;

if (!$product_id) {
    header('Location: user_shop_page.php');
    exit();
}

$productDAO = new ProductDAO($db);
$reviewDAO = new ReviewDAO($db);

// Get product details
$product = $productDAO->getProductById($product_id);

if (!$product) {
    header('Location: user_shop_page.php');
    exit();
}

// Get all reviews for this product
$reviews = $reviewDAO->getProductReviews($product_id);

// Calculate average rating
$averageRating = $reviewDAO->getAverageRating($product_id);
$totalReviews = count($reviews);

// Check if user has already reviewed this product
$userHasReviewed = false;
if (isset($_SESSION['user_id'])) {
    $userHasReviewed = $reviewDAO->hasUserReviewed($_SESSION['user_id'], $product_id);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reviews - <?php echo htmlspecialchars($product['name']); ?> - Valora</title>

  <link rel="shortcut icon" href="../../favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/user_product_reviews.css">

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

      <div class="reviews-container">
        <div class="page-header">
          <h1>Product Reviews</h1>
        </div>

        <!-- Product Info Card -->
        <div class="product-info-card">
          <?php 
          $imagePath = $product['image_path'];
          if ($imagePath) {
              $imagePath = preg_replace('/^\.\.\/+/', '', $imagePath);
              $imageUrl = '../../' . $imagePath;
          }
          ?>
          <?php if ($imagePath && file_exists($imageUrl)): ?>
            <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="product-image-review">
          <?php else: ?>
            <div class="product-image-review" style="background: var(--hoockers-green); display: flex; align-items: center; justify-content: center; color: white;">
              No Image
            </div>
          <?php endif; ?>
          
          <div class="product-details-review">
            <div class="product-category-review"><?php echo strtoupper($product['category']); ?></div>
            <h2 class="product-name-review"><?php echo htmlspecialchars($product['name']); ?></h2>
            <p style="color: var(--spanish-gray); margin-top: 10px;">
              <?php echo htmlspecialchars(substr($product['description'], 0, 150)) . '...'; ?>
            </p>
          </div>
        </div>

        <!-- Rating Summary -->
        <div class="rating-summary">
          <div class="average-rating">
            <?php echo number_format($averageRating, 1); ?>
          </div>
          <div class="stars-large live-stars">
            <?php 
            for ($i = 1; $i <= 5; $i++) {
              if ($i <= floor($averageRating)) {
                echo '<ion-icon name="star"></ion-icon>';
              } elseif ($i - 0.5 <= $averageRating) {
                echo '<ion-icon name="star-half"></ion-icon>';
              } else {
                echo '<ion-icon name="star-outline"></ion-icon>';
              }
            }
            ?>
          </div>
          <div class="total-reviews">Based on <?php echo $totalReviews; ?> review<?php echo $totalReviews != 1 ? 's' : ''; ?></div>
        </div>

        <!-- Review Form -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php if (!$userHasReviewed): ?>
            <div class="review-form-section">
              <h2>Write a Review</h2>
              <form id="reviewForm">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                
                <div class="form-group">
                  <label>Your Rating *</label>
                  <div class="rating-input">
                    <input type="radio" name="rating" id="star5" value="5" required>
                    <label for="star5"><ion-icon name="star"></ion-icon></label>
                    
                    <input type="radio" name="rating" id="star4" value="4">
                    <label for="star4"><ion-icon name="star"></ion-icon></label>
                    
                    <input type="radio" name="rating" id="star3" value="3">
                    <label for="star3"><ion-icon name="star"></ion-icon></label>
                    
                    <input type="radio" name="rating" id="star2" value="2">
                    <label for="star2"><ion-icon name="star"></ion-icon></label>
                    
                    <input type="radio" name="rating" id="star1" value="1">
                    <label for="star1"><ion-icon name="star"></ion-icon></label>
                  </div>
                </div>

                <div class="form-group">
                  <label for="reviewText">Your Review *</label>
                  <textarea 
                    class="form-control" 
                    id="reviewText" 
                    name="review_text" 
                    placeholder="Share your experience with this product..." 
                    required
                    minlength="10"
                    maxlength="1000"></textarea>
                </div>

                <button type="submit" class="btn-submit-review">Submit Review</button>
              </form>
            </div>
          <?php else: ?>
            <div class="already-reviewed">
              <p><strong>You've already reviewed this product.</strong></p>
              <p>Thank you for your feedback!</p>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="login-prompt">
            <p><strong>Please login to write a review</strong></p>
            <a href="../login.php" class="btn-login">Login Now</a>
          </div>
        <?php endif; ?>

        <!-- Reviews List -->
        <h2 style="font-size: 1.5rem; color: var(--gray-web); margin-bottom: 20px;">Customer Reviews</h2>
        
        <?php if (!empty($reviews)): ?>
          <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
              <div class="review-card">
                <div class="review-header">
                  <div class="reviewer-info">
                    <div class="reviewer-avatar">
                      <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                    </div>
                    <div class="reviewer-details">
                      <h3><?php echo htmlspecialchars($review['username']); ?></h3>
                      <div class="review-date">
                        <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                      </div>
                    </div>
                  </div>
                  <div class="review-rating">
                    <?php 
                    for ($i = 1; $i <= 5; $i++) {
                      if ($i <= $review['rating']) {
                        echo '<ion-icon name="star"></ion-icon>';
                      } else {
                        echo '<ion-icon name="star-outline"></ion-icon>';
                      }
                    }
                    ?>
                  </div>
                </div>
                <div class="review-content">
                  <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-reviews">
            <ion-icon name="chatbubbles-outline"></ion-icon>
            <h3>No Reviews Yet</h3>
            <p>Be the first to review this product!</p>
          </div>
        <?php endif; ?>

      </div>

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

  <script>
document.addEventListener('DOMContentLoaded', function() {
  const reviewForm = document.getElementById('reviewForm');
  
  if (!reviewForm) {
    console.log('Review form not found - user may not be logged in or already reviewed');
    return;
  }
  
  console.log('Review form found, attaching event listener');
  
  reviewForm.addEventListener('submit', function(e) {
    e.preventDefault();
    console.log('Form submit triggered');
    
    const formData = new FormData(this);
    
    // Debug: Show what's being sent
    console.log('=== Form Data ===');
    for (let pair of formData.entries()) {
      console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Validate rating is selected
    if (!formData.get('rating')) {
      alert('Please select a rating');
      return;
    }
    
    // Validate review text
    const reviewText = formData.get('review_text');
    if (!reviewText || reviewText.trim().length < 10) {
      alert('Please write a review of at least 10 characters');
      return;
    }
    
    console.log('Sending request to: ../../actions/submit_review.php');
    
    // Disable submit button to prevent double submission
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    fetch('../actions/submit_review.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      console.log('Response status:', response.status);
      console.log('Response type:', response.headers.get('content-type'));
      return response.text(); // Get as text first
    })
    .then(text => {
      console.log('=== Raw Response ===');
      console.log(text);
      
      // Try to parse as JSON
      let data;
      try {
        data = JSON.parse(text);
        console.log('=== Parsed JSON ===');
        console.log(data);
      } catch (e) {
        console.error('JSON Parse Error:', e);
        throw new Error('Server returned invalid JSON: ' + text.substring(0, 100));
      }
      
      // Re-enable button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
      
      if (data.success) {
        alert('Review submitted successfully!');
        location.reload();
      } else {
        alert('Failed to submit review: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('=== Error ===');
      console.error(error);
      
      // Re-enable button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
      
      alert('Failed to submit review. Please check console for details.\n\nError: ' + error.message);
    });
  });
});
</script>

</body>
</html>