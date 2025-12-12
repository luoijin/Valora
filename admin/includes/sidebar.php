<?php
// admin/includes/sidebar.php - Reusable Admin Sidebar Component

// Determine the current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Calculate the correct base path depending on where this file is included from
// If we're in admin root, base is current directory
// If we're in a subdirectory, base is ../
$base_path = ($current_dir === 'admin') ? '' : '../';
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Admin</h2>
        <button class="hamburger" id="hamburgerBtn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="sidebar-content">
        <ul class="sidebar-menu">
            <li>
                <a href="<?php echo $base_path; ?>dashboard.php" class="<?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                    <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>users/admin_user_list.php" class="<?php echo ($current_dir === 'users') ? 'active' : ''; ?>">
                    <span class="menu-icon"><i class="fas fa-users"></i></span>
                    <span class="menu-text">Users</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>products/admin_product_list.php" class="<?php echo ($current_dir === 'products') ? 'active' : ''; ?>">
                    <span class="menu-icon"><i class="fas fa-shopping-bag"></i></span>
                    <span class="menu-text">Products</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>orders/admin_order_list.php" class="<?php echo ($current_dir === 'orders') ? 'active' : ''; ?>">
                    <span class="menu-icon"><i class="fas fa-receipt"></i></span>
                    <span class="menu-text">Orders</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>analysis/admin_analysis_list.php" class="<?php echo ($current_dir === 'cart') ? 'active' : ''; ?>">
                    <span class="menu-icon"><i class="fas fa-chart-bar"></i></span>
                    <span class="menu-text">Analysis</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <ul>
                <li>
                    <a href="<?php echo $base_path; ?>../process/logout.php" class="logout-btn">
                        <span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span>
                        <span class="menu-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- JavaScript for Sidebar Toggle -->
<script>
    const sidebar = document.getElementById('sidebar');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileOverlay = document.getElementById('mobileOverlay');
    
    // Load saved state from localStorage
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
    }
    
    // Toggle sidebar
    hamburgerBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        
        // Handle mobile overlay
        if (window.innerWidth <= 768 && !sidebar.classList.contains('collapsed')) {
            mobileOverlay.classList.add('active');
        } else {
            mobileOverlay.classList.remove('active');
        }
    });
    
    // Close sidebar when clicking overlay (mobile)
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.add('collapsed');
            mobileOverlay.classList.remove('active');
            localStorage.setItem('sidebarCollapsed', 'true');
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && mobileOverlay) {
            mobileOverlay.classList.remove('active');
        }
    });
</script>