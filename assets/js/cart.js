// assets/js/cart.js - Enhanced Cart Functionality with Badge Updates and Alerts

// Add to cart function
function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    // Path is relative to where the HTML page is located (pages/user/)
    fetch('cart_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show browser alert
            alert('Item successfully added to cart!');
            
            // Also show the styled notification
            showNotification('Item added to cart!');
            updateCartBadge(data.cart_count);
        } else {
            alert('Failed to add item: ' + (data.message || 'Unknown error'));
            showNotification(data.message || 'Failed to add item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add item to cart. Please try again.');
        showNotification('Failed to add item to cart', 'error');
    });
}

// Show notification function
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#4CAF50' : '#f44336'};
        color: white;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Update cart badge count with animation
function updateCartBadge(count) {
    // Update all cart badges (desktop and mobile)
    const cartBadges = document.querySelectorAll('.header-action-btn[aria-label="cart item"] .btn-badge');
    
    cartBadges.forEach(badge => {
        badge.textContent = count;
        
        // Show/hide badge based on count
        if (count > 0) {
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'flex'; // Keep visible but show 0
        }
        
        // Add scale animation for visual feedback
        badge.style.transform = 'scale(1.3)';
        badge.style.transition = 'transform 0.2s ease';
        
        setTimeout(() => {
            badge.style.transform = 'scale(1)';
        }, 200);
    });
    
    // Also update legacy .cart-badge selector if exists
    const legacyBadge = document.querySelector('.cart-badge');
    if (legacyBadge) {
        legacyBadge.textContent = count;
        if (count > 0) {
            legacyBadge.style.display = 'block';
        }
    }
}

// Fetch current cart count from server
function fetchCartCount() {
    fetch('cart_actions.php?action=get', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.cart_count !== undefined) {
            updateCartBadge(data.cart_count);
        }
    })
    .catch(error => {
        console.error('Error fetching cart count:', error);
    });
}

// Initialize cart functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Fetch and display current cart count on page load
    fetchCartCount();
    
    // Add transition styles to all badges
    const allBadges = document.querySelectorAll('.btn-badge, .cart-badge');
    allBadges.forEach(badge => {
        badge.style.transition = 'transform 0.2s ease';
    });
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    /* Badge pulse animation on update */
    @keyframes badgePulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
    
    /* Ensure badges are visible */
    .btn-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
    }
`;
document.head.appendChild(style);

// Export functions for use in other scripts (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { 
        addToCart, 
        updateCartBadge, 
        fetchCartCount, 
        showNotification 
    };
}