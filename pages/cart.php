<?php
// ../pages/cart.php
include __DIR__ . '../includes/header.php';
?>

<section class="container cart-page">
  <h2>Shopping Cart</h2>
  <div class="cart-grid">
    <div id="cart-items" class="cart-items">
      <!-- JS renders cart items here -->
    </div>

    <aside class="order-summary" id="order-summary">
      <h3>Order Summary</h3>
      <div class="summary-row"><span>Items Subtotal</span><span id="summary-subtotal">₱0</span></div>
      <div class="summary-row"><span>Shipping Fee</span><span id="summary-shipping">₱120</span></div>
      <hr>
      <div class="summary-row total"><strong>Total</strong><strong id="summary-total">₱0</strong></div>
      <button class="btn btn-full" id="checkout-btn">Go to Checkout</button>
    </aside>
  </div>

  <section class="you-might container">
    <h3>You might also like</h3>
    <div id="recommendations" class="grid products-grid">
      <!-- JS inserts recommendations -->
    </div>
  </section>
</section>

<?php include __DIR__ . '../includes/footer.php'; ?>
