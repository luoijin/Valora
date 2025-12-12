<!-- HEADER -->
<!-- includes/header.php -->
<header class="header"> 
  

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
          <li><a href="home.php" class="navbar-link has-after">Home</a></li>
          <li><a href="login.php" class="navbar-link has-after smooth-scroll">Collection</a></li>
          <li><a href="login.php" class="navbar-link has-after">Shop</a></li>
          <li><a href="login.php" class="navbar-link has-after smooth-scroll">Dress</a></li>
          <li><a href="login.php" class="navbar-link has-after smooth-scroll">Gown</a></li>
        </ul>
      </nav>

    </div>
  </div>

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
        <li><a href="home.php" class="navbar-link has-after">Home</a></li>
        <li><a href="login.php" class="navbar-link has-after smooth-scroll">Collection</a></li>
        <li><a href="login.php" class="navbar-link has-after">Shop</a></li>
        <li><a href="login.php" class="navbar-link has-after smooth-scroll">Dress</a></li>
        <li><a href="login.php" class="navbar-link has-after smooth-scroll">Gown</a></li>
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
            const headerOffset = 100; // Adjust this value based on your header height
            const elementPosition = targetSection.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            
            window.scrollTo({
              top: offsetPosition,
              behavior: 'smooth'
            });
          }
        }
        // If it's a different page, let the default behavior happen (navigate then scroll)
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