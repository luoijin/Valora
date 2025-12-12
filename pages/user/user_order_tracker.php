<?php
// pages/user/user_orders_page.php
session_start();
require_once '../../config.php';
require_once '../../dao/order_dao.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$orderDAO = new OrderDAO($db);
$user_id = $_SESSION['user_id'];

// Get all orders for the user using existing method
$orders = $orderDAO->getUserOrders($user_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders - Valora</title>

  <link rel="shortcut icon" href="../../favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="../../assets/css/order_tracker_page.css">

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

      <div class="orders-container">
        <div class="page-header">
          <h1>My Orders</h1>
          <p>Track and manage your orders</p>
        </div>

        <?php if (!empty($orders)): ?>
          <div class="orders-list">
            <?php foreach ($orders as $order): ?>
              <div class="order-card">
                <div class="order-header">
                  <div>
                    <div class="order-id">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                    <div class="order-date">Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?></div>
                  </div>
                  <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                    <?php echo ucfirst($order['status']); ?>
                  </div>
                </div>

                <div class="order-body">
                  <div class="order-items">
                    <?php 
                    $orderItems = $orderDAO->getOrderItems($order['id']);
                    foreach ($orderItems as $item): 
                      $imagePath = $item['image_path'] ?? '';
                      if ($imagePath) {
                          $imagePath = preg_replace('/^\.\.\/+/', '', $imagePath);
                          $imageUrl = '../../' . $imagePath;
                      } else {
                          $imageUrl = '';
                      }
                    ?>
                      <div class="order-item">
                        <?php if ($imageUrl && file_exists($imageUrl)): ?>
                          <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                               alt="<?php echo htmlspecialchars($item['name']); ?>" 
                               class="item-image">
                        <?php else: ?>
                          <div class="item-image-placeholder">No Image</div>
                        <?php endif; ?>
                        
                        <div class="item-details">
                          <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                          <?php if (!empty($item['color']) || !empty($item['size'])): ?>
                            <div class="item-quantity">Variation: <?php echo htmlspecialchars(trim(($item['color'] ?? '') . ' ' . ($item['size'] ?? ''))); ?></div>
                          <?php endif; ?>
                          <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?> × ₱<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="item-price">
                          ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <!-- Tracking Timeline -->
                  <div class="tracking-timeline">
                    <h3 style="margin-bottom: 20px; font-size: 1.1rem;">Track Order</h3>
                    
                    <div class="timeline-item">
                      <div class="timeline-dot active">
                        <ion-icon name="checkmark"></ion-icon>
                      </div>
                      <div class="timeline-content">
                        <h4>Order Placed</h4>
                        <p><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                      </div>
                    </div>

                    <div class="timeline-item">
                      <div class="timeline-dot <?php echo in_array($order['status'], ['processing', 'shipped', 'completed', 'delivered']) ? 'active' : ''; ?>">
                        <ion-icon name="<?php echo in_array($order['status'], ['processing', 'shipped', 'completed', 'delivered']) ? 'checkmark' : 'ellipse'; ?>"></ion-icon>
                      </div>
                      <div class="timeline-content">
                        <h4>Processing</h4>
                        <p><?php echo in_array($order['status'], ['processing', 'shipped', 'completed', 'delivered']) ? 'Order is being prepared' : 'Waiting for confirmation'; ?></p>
                      </div>
                    </div>

                    <div class="timeline-item">
                      <div class="timeline-dot <?php echo in_array($order['status'], ['shipped', 'completed', 'delivered']) ? 'active' : ''; ?>">
                        <ion-icon name="<?php echo in_array($order['status'], ['shipped', 'completed', 'delivered']) ? 'checkmark' : 'ellipse'; ?>"></ion-icon>
                      </div>
                      <div class="timeline-content">
                        <h4>Shipped</h4>
                        <p><?php echo in_array($order['status'], ['shipped', 'completed', 'delivered']) ? 'Order is on the way' : 'Not shipped yet'; ?></p>
                      </div>
                    </div>

                    <div class="timeline-item">
                      <div class="timeline-dot <?php echo in_array($order['status'], ['completed', 'delivered']) ? 'active' : ''; ?>">
                        <ion-icon name="<?php echo in_array($order['status'], ['completed', 'delivered']) ? 'checkmark' : 'ellipse'; ?>"></ion-icon>
                      </div>
                      <div class="timeline-content">
                        <h4>Delivered</h4>
                        <p><?php echo in_array($order['status'], ['completed', 'delivered']) ? 'Order has been delivered' : 'Not delivered yet'; ?></p>
                      </div>
                    </div>
                  </div>

                  <div class="order-footer">
                    <div class="order-total">
                      Total: ₱<?php echo number_format($order['total_amount'], 2); ?>
                    </div>
                    <div class="order-actions">
                     
                      
                      <?php if ($order['status'] === 'pending'): ?>
                        <button onclick="cancelOrder(<?php echo $order['id']; ?>)" class="btn-cancel">
                          Cancel Order
                        </button>
                      <?php endif; ?>
                      
                      <?php if (in_array($order['status'], ['completed', 'delivered'])): ?>
                        <?php 
                        // Show review button for each product in the order
                        $orderItems = $orderDAO->getOrderItems($order['id']);
                        if (!empty($orderItems)):
                          $firstItem = $orderItems[0]; // Get first item for review
                        ?>
                          <a href="user_product_reviews.php?product_id=<?php echo $firstItem['product_id']; ?>" class="btn-track" style="background: #fbbf24; color: #000;">
                            
                            Review Product
                          </a>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

        <?php else: ?>
          <div class="empty-state">
            <ion-icon name="receipt-outline"></ion-icon>
            <h3>No Orders Yet</h3>
            <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
            <a href="user_shop_page.php" class="btn btn-primary">Start Shopping</a>
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
  <script src="../../assets/js/cart.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

  <script>
    function cancelOrder(orderId) {
      if (confirm('Are you sure you want to cancel this order?')) {
        fetch('../actions/cancel_order.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Order cancelled successfully!');
            location.reload();
          } else {
            alert('Failed to cancel order: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Failed to cancel order. Please try again.');
        });
      }
    }
  </script>

</body>
</html>