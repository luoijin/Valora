<!-- HEADER -->
 <!-- includes/user_header -->
<header class="header">
  <style>
    /* ================================================
   LOGO POSITION ADJUSTMENT - MOVE LEFT
   ================================================ */
/* If you want more control based on screen size */
@media (min-width: 992px) {
    .header-top .logo {
        position: relative;
        right: -90px; /* Adjust for larger screens */
    }
}

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
        <form action="user_search_results.php" method="GET" class="search-form">
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

      <a href="user_home_page.php" class="logo">
        <img src="../../assets/images/logo/valora-logo-text.png" width="179" height="26" alt="Valora">
      </a>
      

      <div class="header-actions">
        <a href="all_reviews.php">
          <button class="header-action-btn" aria-label="favourite item">
            <ion-icon name="star-outline" aria-hidden="true"></ion-icon>
            
          </button>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="user_order_tracker.php">
            <button class="header-action-btn" aria-label="my orders">
              <ion-icon name="receipt-outline" aria-hidden="true"></ion-icon>
             
            </button>
          </a>
        <?php endif; ?>

        <a href="user_cart.php">
          <button class="header-action-btn" aria-label="cart item">
            <ion-icon name="bag-handle-outline" aria-hidden="true"></ion-icon>
            <span class="btn-badge">0</span>
          </button>
        </a>

        <a href="user_wishlist.php">
          <button class="header-action-btn" aria-label="wishlist">
            <ion-icon name="heart-outline" aria-hidden="true"></ion-icon>
          </button>
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
          <button class="header-action-btn btn-valora" aria-label="sign out" onclick="window.location.href='../../process/logout.php'">
            <span class="btn-text">Logout</span>
            <ion-icon name="log-out-outline" aria-hidden="true"></ion-icon>
          </button>
        <?php else: ?>
          <button class="header-action-btn btn-valora" aria-label="sign in" onclick="window.location.href='login.php'">
            <span class="btn-text">Login</span>
            <ion-icon name="log-in-outline" aria-hidden="true"></ion-icon>
          </button>
        <?php endif; ?>
      </div>

      <nav class="navbar">
        <ul class="navbar-list">
          <li><a href="user_home_page.php" class="navbar-link has-after">Home</a></li>
          <li><a href="user_home_page.php#collection" class="navbar-link has-after smooth-scroll">Collection</a></li>
          <li><a href="user_shop_page.php" class="navbar-link has-after">Shop</a></li>
          <li><a href="user_home_page.php#dress" class="navbar-link has-after smooth-scroll">Dress</a></li>
          <li><a href="user_home_page.php#gown" class="navbar-link has-after smooth-scroll">Gown</a></li>
          
        </ul>
      </nav>

    </div>
  </div>

  <!-- MOBILE NAVBAR -->
  <div class="sidebar">
    <div class="mobile-navbar" data-navbar>
      <div class="wrapper">
        <a href="user_home_page.php" class="logo">
          <img src="../../assets/images/logo.png" width="179" height="26" alt="Valora">
        </a>
        <button class="nav-close-btn" aria-label="close menu" data-nav-toggler>
          <ion-icon name="close-outline" aria-hidden="true"></ion-icon>
        </button>
      </div>

      <!-- MOBILE SEARCH -->
      <div style="padding: 15px 20px;">
        <form action="user_search_results.php" method="GET" class="mobile-search-form">
          <input 
            type="search" 
            name="q" 
            placeholder="Search products..." 
            class="search-field"
            style="width: 100%; padding: 12px; border: 2px solid #e5e5e5; border-radius: 8px; font-size: 14px;"
            required
          >
        </form>
      </div>

      <ul class="navbar-list">
        <li><a href="user_home_page.php" class="navbar-link" data-nav-link>Home</a></li>
        <li><a href="user_home_page.php#collection" class="navbar-link smooth-scroll" data-nav-link>Collection</a></li>
        <li><a href="user_shop_page.php" class="navbar-link" data-nav-link>Shop</a></li>
        <li><a href="user_home_page.php#dress" class="navbar-link smooth-scroll" data-nav-link>Dress</a></li>
        <li><a href="user_home_page.php#gown" class="navbar-link smooth-scroll" data-nav-link>Gown</a></li>
       
      </ul>
    </div>
    <div class="overlay" data-nav-toggler data-overlay></div>
  </div>
</header>

<!-- Smooth Scroll Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Get all smooth scroll links
  const smoothScrollLinks = document.querySelectorAll('.smooth-scroll');
  
  smoothScrollLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      
      // Check if the link contains a hash (anchor)
      if (href.includes('#')) {
        const [page, hash] = href.split('#');
        const currentPage = window.location.pathname;
        
        // If we're on the same page or the link is to the current page
        if (!page || currentPage.includes(page.split('/').pop())) {
          e.preventDefault();
          
          // Find the target section
          const targetSection = document.getElementById(hash);
          
          if (targetSection) {
            // Close mobile menu if open
            const navbar = document.querySelector('[data-navbar]');
            const overlay = document.querySelector('[data-overlay]');
            if (navbar && navbar.classList.contains('active')) {
              navbar.classList.remove('active');
              if (overlay) overlay.classList.remove('active');
              document.body.classList.remove('nav-active');
            }
            
            // Smooth scroll to the section
            const headerOffset = 100;
            const elementPosition = targetSection.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            
            window.scrollTo({
              top: offsetPosition,
              behavior: 'smooth'
            });
          }
        }
      }
    });
  });
  
  // Handle hash in URL on page load
  if (window.location.hash) {
    setTimeout(() => {
      const hash = window.location.hash.substring(1);
      const targetSection = document.getElementById(hash);
      
      if (targetSection) {
        const headerOffset = 100;
        const elementPosition = targetSection.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
        
        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
      }
    }, 100);
  }
});
</script>