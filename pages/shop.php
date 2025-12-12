<?php
// pages.shop.php
include __DIR__ . '../includes/products.php';
include __DIR__ . '../includes/header.php';
?>
<section class="container shop-intro">
  <div class="shop-left">
    <h2>SHOP ALL</h2>
    <p>Find the perfect wedding dress for any unforgettable moment.</p>

    <aside class="filters">
      <h4>Category</h4>
      <label><input type="checkbox" checked disabled> Bridal Gown</label><br>
      <label><input type="checkbox" checked disabled> Wedding Dress</label>

      <h4>Price Range</h4>
      <label><input type="radio" name="pr" disabled> ₱50,000 Below</label><br>
      <label><input type="radio" name="pr" disabled> ₱50,000 Above</label>

      <h4>Color</h4>
      <div class="swatches">
        <span class="swatch" style="background:#f8e9e6"></span>
        <span class="swatch" style="background:#fff"></span>
        <span class="swatch" style="background:#f6e6da"></span>
      </div>
    </aside>
  </div>

  <div class="shop-right">
    <div class="grid products-grid">
      <?php foreach ($products as $p): ?>
        <article class="product-card">
          <a href="/item.php?id=<?php echo $p['id']; ?>">
            <img src="/<?php echo $p['image']; ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
            <div class="card-body">
              <h3><?php echo htmlspecialchars($p['title']); ?></h3>
              <div class="meta">
                <span class="price"><?php echo $p['currency'] . number_format($p['price'], 0); ?></span>
                <span class="rating">★ <?php echo $p['rating']; ?></span>
              </div>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '../includes/footer.php'; ?>
