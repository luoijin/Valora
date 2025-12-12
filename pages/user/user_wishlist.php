<?php
// pages/user/user_wishlist.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - Valora</title>
    <link rel="shortcut icon" href="../../favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Urbanist', sans-serif;
        }

        main {
            min-height: calc(100vh - 200px);
            padding-top: 100px;
            padding-bottom: 60px;
        }

        .wishlist-container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 24px; 
        }

        .wishlist-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 36px;
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .wishlist-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #0d3b2e;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .wishlist-count {
            background: linear-gradient(135deg, #0d3b2e 0%, #1a5c47 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .wishlist-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 24px;
            margin-bottom: 40px;
        }

        .wishlist-card { 
            background: white;
            border-radius: 16px; 
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .wishlist-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }

        .wishlist-image-wrapper {
            position: relative;
            width: 100%;
            padding-top: 100%;
            background: linear-gradient(135deg, #f6f6f6 0%, #e9ecef 100%);
            overflow: hidden;
        }

        .wishlist-card img { 
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .wishlist-card:hover img {
            transform: scale(1.05);
        }

        .wishlist-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: #0d3b2e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .wishlist-info { 
            padding: 20px;
        }

        .wishlist-info h3 { 
            margin: 0 0 8px 0; 
            font-size: 18px; 
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.4;
        }

        .wishlist-category {
            display: inline-block;
            font-size: 12px;
            color: #0d3b2e;
            background: rgba(13, 59, 46, 0.1);
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .wishlist-actions { 
            display: flex; 
            gap: 8px; 
            margin-top: 16px;
        }

        .btn { 
            flex: 1;
            padding: 12px 20px; 
            border-radius: 10px; 
            border: none; 
            cursor: pointer; 
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-view { 
            background: linear-gradient(135deg, #0d3b2e 0%, #1a5c47 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(13, 59, 46, 0.3);
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 59, 46, 0.4);
        }

        .btn-remove { 
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #b91c1c;
        }

        .btn-remove:hover {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            transform: translateY(-2px);
        }

        .empty-state { 
            text-align: center; 
            padding: 80px 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 24px;
            opacity: 0.3;
        }

        .empty-state h2 {
            font-size: 28px;
            font-weight: 800;
            color: #1a1a1a;
            margin: 0 0 12px 0;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 16px;
            margin: 0 0 28px 0;
        }

        .btn-shop-now {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #0d3b2e 0%, #1a5c47 100%);
            color: white;
            padding: 14px 32px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(13, 59, 46, 0.3);
        }

        .btn-shop-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 59, 46, 0.4);
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 100px;
            right: 24px;
            background: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
            max-width: 350px;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            border-left: 4px solid #10b981;
        }

        .notification.error {
            border-left: 4px solid #ef4444;
        }

        .notification-icon {
            font-size: 24px;
        }

        .notification.success .notification-icon {
            color: #10b981;
        }

        .notification.error .notification-icon {
            color: #ef4444;
        }

        .notification-text {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            main {
                padding-top: 80px;
            }

            .wishlist-header {
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .wishlist-header h1 {
                font-size: 24px;
            }

            .wishlist-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .empty-state {
                padding: 60px 24px;
            }

            .empty-state-icon {
                font-size: 60px;
            }

            .empty-state h2 {
                font-size: 22px;
            }

            .notification {
                right: 12px;
                left: 12px;
                max-width: none;
            }
        }
    </style>
</head>
<body id="top">
    <!-- Notification -->
    <div id="notification" class="notification">
        <span class="notification-icon">✓</span>
        <span id="notificationText" class="notification-text"></span>
    </div>

    <?php include '../../includes/user_header.php'; ?>
    <?php include '../../includes/user_sidebar.php'; ?>
    
    <main>
        <div class="wishlist-container">
            <div class="wishlist-header">
                <h1>
                    <ion-icon name="heart"></ion-icon>
                    Wishlist
                </h1>
                <div class="wishlist-count" id="wishlistCount">
                    <ion-icon name="heart-outline"></ion-icon>
                    <span>0 items</span>
                </div>
            </div>
            <div id="wishlistContent"></div>
        </div>
    </main>

    <?php include '../../includes/footer.php'; ?>

    <!-- Back to Top -->
    <a href="#top" class="back-top-btn" aria-label="back to top" data-back-top-btn>
        <ion-icon name="arrow-up" aria-hidden="true"></ion-icon>
    </a>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="../../assets/js/script.js" defer></script>

    <script>
        const storageKey = 'wishlistItems';
        const loadWishlist = () => { 
            try { 
                return JSON.parse(localStorage.getItem(storageKey)) || []; 
            } catch(e) { 
                return []; 
            } 
        };
        const saveWishlist = (list) => localStorage.setItem(storageKey, JSON.stringify(list));

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            const icon = notification.querySelector('.notification-icon');
            
            notificationText.textContent = message;
            notification.className = `notification ${type} show`;
            icon.textContent = type === 'success' ? '✓' : '✕';
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        function updateWishlistCount() {
            const items = loadWishlist();
            const countEl = document.getElementById('wishlistCount');
            if (countEl) {
                const countText = items.length === 1 ? '1 item' : `${items.length} items`;
                countEl.querySelector('span').textContent = countText;
            }
        }

        function renderWishlist() {
            const container = document.getElementById('wishlistContent');
            const items = loadWishlist();
            
            updateWishlistCount();
            
            if (!items.length) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">💝</div>
                        <h2>Your Wishlist is Empty</h2>
                        <p>Start adding items you love to your wishlist and keep track of them here.</p>
                        <a href="user_shop_page.php" class="btn-shop-now">
                            <ion-icon name="storefront-outline"></ion-icon>
                            Start Shopping
                        </a>
                    </div>
                `;
                return;
            }

            const cards = items.map(item => `
                <div class="wishlist-card" data-id="${item.id}">
                    <div class="wishlist-image-wrapper">
                        <img src="${item.image || '../../assets/images/product-01.jpg'}" alt="${item.name || 'Product'}">
                    </div>
                    <div class="wishlist-info">
                        <div class="wishlist-category">Featured</div>
                        <h3>${item.name || 'Product'}</h3>
                        <div class="wishlist-actions">
                            <button class="btn btn-view" onclick="viewItem(${item.id})">
                                <ion-icon name="eye-outline"></ion-icon>
                                View
                            </button>
                            <button class="btn btn-remove" onclick="removeItem(${item.id})">
                                <ion-icon name="trash-outline"></ion-icon>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = `<div class="wishlist-grid">${cards}</div>`;
        }

        function removeItem(id) {
            const items = loadWishlist();
            const item = items.find(i => i.id == id);
            const updated = items.filter(i => i.id != id);
            saveWishlist(updated);
            renderWishlist();
            showNotification(`${item?.name || 'Item'} removed from wishlist`, 'success');
        }

        function viewItem(id) {
            window.location.href = 'user_item.php?id=' + id;
        }

        document.addEventListener('DOMContentLoaded', renderWishlist);
    </script>
</body>
</html>