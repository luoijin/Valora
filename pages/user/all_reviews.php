<?php
// pages/user/all_reviews.php
session_start();
require_once '../../config.php';
require_once '../../dao/product_dao.php';
require_once '../../dao/review_dao.php';

$productDAO = new ProductDAO($db);
$reviewDAO = new ReviewDAO($db);

// Get filter parameters
$filter_rating = isset($_GET['rating']) ? (int)$_GET['rating'] : null;
$filter_category = isset($_GET['category']) ? $_GET['category'] : null;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'recent'; // recent, highest, lowest

// Get all reviews with product information
$query = "SELECT r.*, u.username, u.first_name, u.last_name, 
          p.name as product_name, p.image_path, p.category, p.price
          FROM reviews r
          JOIN users u ON r.user_id = u.id
          JOIN products p ON r.product_id = p.id
          WHERE 1=1";

$params = [];

if ($filter_rating) {
    $query .= " AND r.rating = :rating";
    $params[':rating'] = $filter_rating;
}

if ($filter_category) {
    $query .= " AND p.category = :category";
    $params[':category'] = $filter_category;
}

// Sorting
switch ($sort_by) {
    case 'highest':
        $query .= " ORDER BY r.rating DESC, r.created_at DESC";
        break;
    case 'lowest':
        $query .= " ORDER BY r.rating ASC, r.created_at DESC";
        break;
    default: // recent
        $query .= " ORDER BY r.created_at DESC";
        break;
}

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$all_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get review statistics
$stats_query = "SELECT 
    COUNT(*) as total_reviews,
    AVG(rating) as avg_rating,
    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
    FROM reviews";
$stats = $db->query($stats_query)->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Reviews - Valora</title>

  <link rel="shortcut icon" href="../../favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/shop.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    .reviews-page-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .page-header {
      text-align: center;
      margin-bottom: 40px;
      padding-top: 40px;
    }

    .page-header h1 {
      font-size: 2.5rem;
      color: var(--gray-web);
      margin-bottom: 10px;
    }

    .page-header p {
      color: var(--spanish-gray);
      font-size: 1.1rem;
    }

    .reviews-stats {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 8px var(--black_10);
      margin-bottom: 40px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
    }

    .stat-card {
      text-align: center;
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--hoockers-green);
      margin-bottom: 5px;
    }

    .stat-label {
      color: var(--spanish-gray);
      font-size: 0.95rem;
    }

    .rating-breakdown {
      margin-top: 20px;
    }

    .rating-bar {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .rating-label {
      min-width: 80px;
      font-size: 0.9rem;
      color: var(--gray-web);
    }

    .bar-container {
      flex: 1;
      height: 8px;
      background: var(--cultured-1);
      border-radius: 4px;
      overflow: hidden;
    }

    .bar-fill {
      height: 100%;
      background: #fbbf24;
      transition: width 0.3s ease;
    }

    .rating-count {
      min-width: 40px;
      text-align: right;
      font-size: 0.9rem;
      color: var(--spanish-gray);
    }

    .filters-section {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 8px var(--black_10);
      margin-bottom: 30px;
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
    }

    .filter-group {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .filter-group label {
      font-weight: 600;
      color: var(--gray-web);
      white-space: nowrap;
    }

    .filter-select {
      padding: 8px 15px;
      border: 1px solid var(--light-gray);
      border-radius: 8px;
      font-size: 0.95rem;
      background: white;
      cursor: pointer;
      transition: border-color 0.3s;
    }

    .filter-select:focus {
      outline: none;
      border-color: var(--hoockers-green);
    }

    .filter-tags {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-left: auto;
    }

    .filter-tag {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      background: var(--hoockers-green);
      color: white;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }

    .filter-tag button {
      background: none;
      border: none;
      color: white;
      cursor: pointer;
      font-size: 1.2rem;
      line-height: 1;
      padding: 0;
    }

    .clear-filters {
      padding: 8px 15px;
      background: var(--spanish-gray);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.9rem;
      font-weight: 600;
      transition: opacity 0.3s;
    }

    .clear-filters:hover {
      opacity: 0.8;
    }

    .reviews-grid {
      display: grid;
      gap: 25px;
    }

    .review-item {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 8px var(--black_10);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      display: grid;
      grid-template-columns: 120px 1fr;
      gap: 20px;
    }

    .review-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px var(--black_15);
    }

    .product-thumbnail {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
    }

    .product-placeholder {
      width: 120px;
      height: 120px;
      background: var(--hoockers-green);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 0.8rem;
      text-align: center;
    }

    .review-content-area {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .review-header-info {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 15px;
    }

    .product-info {
      flex: 1;
    }

    .product-name-link {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--gray-web);
      text-decoration: none;
      display: inline-block;
      margin-bottom: 5px;
      transition: color 0.3s;
    }

    .product-name-link:hover {
      color: var(--hoockers-green);
    }

    .product-category-badge {
      display: inline-block;
      padding: 4px 10px;
      background: var(--cultured-1);
      color: var(--spanish-gray);
      border-radius: 4px;
      font-size: 0.75rem;
      text-transform: uppercase;
      font-weight: 600;
    }

    .review-rating-stars {
      display: flex;
      gap: 3px;
      color: #fbbf24;
      font-size: 1.3rem;
    }

    .reviewer-section {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 15px 0;
      border-top: 1px solid var(--cultured-1);
    }

    .reviewer-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: var(--hoockers-green);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      font-weight: 600;
    }

    .reviewer-info h4 {
      font-size: 1rem;
      font-weight: 600;
      color: var(--gray-web);
      margin-bottom: 3px;
    }

    .review-date {
      font-size: 0.85rem;
      color: var(--spanish-gray);
    }

    .review-text {
      color: var(--gray-web);
      line-height: 1.7;
      font-size: 0.95rem;
    }

    .empty-state {
      text-align: center;
      padding: 80px 20px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px var(--black_10);
    }

    .empty-state ion-icon {
      font-size: 80px;
      color: var(--spanish-gray);
      margin-bottom: 20px;
    }

    .empty-state h3 {
      font-size: 1.5rem;
      color: var(--gray-web);
      margin-bottom: 10px;
    }

    .empty-state p {
      color: var(--spanish-gray);
      margin-bottom: 20px;
    }

    .btn-shop {
      display: inline-block;
      padding: 12px 30px;
      background: var(--hoockers-green);
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-shop:hover {
      opacity: 0.9;
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .review-item {
        grid-template-columns: 1fr;
      }

      .product-thumbnail,
      .product-placeholder {
        width: 100%;
        max-width: 200px;
        margin: 0 auto;
      }

      .filters-section {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-group {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-tags {
        margin-left: 0;
      }

      .review-header-info {
        flex-direction: column;
      }
    }
  </style>
</head>

<body id="top">

  <?php include '../../includes/user_header.php'; ?>
  <?php include '../../includes/user_sidebar.php'; ?>

  <main>
    <article>
      <div class="reviews-page-container">
        
        <div class="page-header">
          <h1>Customer Reviews</h1>
          <p>See what our customers are saying about our products</p>
        </div>

        <!-- Statistics -->
        <div class="reviews-stats">
          <div class="stat-card">
            <div class="stat-number"><?php echo number_format($stats['total_reviews']); ?></div>
            <div class="stat-label">Total Reviews</div>
          </div>
          <div class="stat-card">
            <div class="stat-number"><?php echo number_format($stats['avg_rating'], 1); ?> ★</div>
            <div class="stat-label">Average Rating</div>
          </div>
          <div class="stat-card" style="grid-column: span 2;">
            <div class="stat-label" style="margin-bottom: 15px;">Rating Distribution</div>
            <div class="rating-breakdown">
              <?php 
              $ratings_data = [
                5 => $stats['five_star'],
                4 => $stats['four_star'],
                3 => $stats['three_star'],
                2 => $stats['two_star'],
                1 => $stats['one_star']
              ];
              foreach ($ratings_data as $rating => $count):
                $percentage = $stats['total_reviews'] > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
              ?>
              <div class="rating-bar">
                <div class="rating-label"><?php echo $rating; ?> stars</div>
                <div class="bar-container">
                  <div class="bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                </div>
                <div class="rating-count"><?php echo $count; ?></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
          <form method="GET" id="filterForm" style="display: flex; flex-wrap: wrap; gap: 15px; flex: 1;">
            <div class="filter-group">
              <label>Rating:</label>
              <select name="rating" class="filter-select" onchange="this.form.submit()">
                <option value="">All Ratings</option>
                <option value="5" <?php echo $filter_rating == 5 ? 'selected' : ''; ?>>5 Stars</option>
                <option value="4" <?php echo $filter_rating == 4 ? 'selected' : ''; ?>>4 Stars</option>
                <option value="3" <?php echo $filter_rating == 3 ? 'selected' : ''; ?>>3 Stars</option>
                <option value="2" <?php echo $filter_rating == 2 ? 'selected' : ''; ?>>2 Stars</option>
                <option value="1" <?php echo $filter_rating == 1 ? 'selected' : ''; ?>>1 Star</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Category:</label>
              <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="dress" <?php echo $filter_category == 'dress' ? 'selected' : ''; ?>>Dresses</option>
                <option value="gown" <?php echo $filter_category == 'gown' ? 'selected' : ''; ?>>Gowns</option>
                <option value="collection" <?php echo $filter_category == 'collection' ? 'selected' : ''; ?>>Collections</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Sort By:</label>
              <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="recent" <?php echo $sort_by == 'recent' ? 'selected' : ''; ?>>Most Recent</option>
                <option value="highest" <?php echo $sort_by == 'highest' ? 'selected' : ''; ?>>Highest Rated</option>
                <option value="lowest" <?php echo $sort_by == 'lowest' ? 'selected' : ''; ?>>Lowest Rated</option>
              </select>
            </div>
          </form>

          <?php if ($filter_rating || $filter_category): ?>
          <div class="filter-tags">
            <?php if ($filter_rating): ?>
              <div class="filter-tag">
                <?php echo $filter_rating; ?> Stars
                <button onclick="removeFilter('rating')">×</button>
              </div>
            <?php endif; ?>
            <?php if ($filter_category): ?>
              <div class="filter-tag">
                <?php echo ucfirst($filter_category); ?>
                <button onclick="removeFilter('category')">×</button>
              </div>
            <?php endif; ?>
            <button class="clear-filters" onclick="clearAllFilters()">Clear All</button>
          </div>
          <?php endif; ?>
        </div>

        <!-- Reviews List -->
        <?php if (!empty($all_reviews)): ?>
          <div class="reviews-grid">
            <?php foreach ($all_reviews as $review): ?>
              <div class="review-item">
                <?php 
                $imagePath = $review['image_path'];
                if ($imagePath) {
                    $imagePath = preg_replace('/^\.\.\/+/', '', $imagePath);
                    $imageUrl = '../../' . $imagePath;
                }
                ?>
                
                <div>
                  <?php if ($imagePath && file_exists($imageUrl)): ?>
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                         alt="<?php echo htmlspecialchars($review['product_name']); ?>" 
                         class="product-thumbnail">
                  <?php else: ?>
                    <div class="product-placeholder">No Image</div>
                  <?php endif; ?>
                </div>

                <div class="review-content-area">
                  <div class="review-header-info">
                    <div class="product-info">
                      <a href="user_item.php?id=<?php echo $review['product_id']; ?>" class="product-name-link">
                        <?php echo htmlspecialchars($review['product_name']); ?>
                      </a>
                      <div>
                        <span class="product-category-badge"><?php echo strtoupper($review['category']); ?></span>
                      </div>
                    </div>
                    <div class="review-rating-stars">
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

                  <div class="review-text">
                    <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                  </div>

                  <div class="reviewer-section">
                    <div class="reviewer-avatar">
                      <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                    </div>
                    <div class="reviewer-info">
                      <h4><?php echo htmlspecialchars($review['username']); ?></h4>
                      <div class="review-date">
                        <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <ion-icon name="chatbubbles-outline"></ion-icon>
            <h3>No Reviews Found</h3>
            <p>There are no reviews matching your filters.</p>
            <?php if ($filter_rating || $filter_category): ?>
              <button class="btn-shop" onclick="clearAllFilters()">Clear Filters</button>
            <?php else: ?>
              <a href="user_shop_page.php" class="btn-shop">Browse Products</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>
    </article>
  </main>

  <?php include '../../includes/footer.php'; ?>

  <a href="#top" class="back-top-btn" aria-label="back to top" data-back-top-btn>
    <ion-icon name="arrow-up" aria-hidden="true"></ion-icon>
  </a>

  <script src="../../assets/js/script.js" defer></script>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

  <script>
    function removeFilter(filterName) {
      const form = document.getElementById('filterForm');
      const input = form.querySelector(`[name="${filterName}"]`);
      if (input) {
        input.value = '';
        form.submit();
      }
    }

    function clearAllFilters() {
      window.location.href = 'all_reviews.php';
    }
  </script>

</body>
</html>