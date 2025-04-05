<?php
session_start();
require 'db_connect.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_Page');
    exit;
}

// Initialize variables
$user_id = $_SESSION['user_id'];
$wishlist_items = [];
$errors = [];

// Fetch wishlist items
try {
    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('wishlist', $tables)) {
        $errors[] = "Wishlist feature is not available yet.";
    } elseif (!in_array('products', $tables)) {
        $errors[] = "Product catalog is not available.";
    } else {
        // Fetch wishlist items with proper error handling
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price, p.image_url, p.slug, 
                   p.stock_status, p.discount_price
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $wishlist_items = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log("Wishlist error: " . $e->getMessage());
    $errors[] = "Failed to load wishlist. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - Kiln Enterprise</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="logo1.ico" type="image/x-icon">
    <style>
        /* Your existing CSS styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .wishlist-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .wishlist-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .wishlist-header h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .wishlist-items {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .wishlist-item {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        .wishlist-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .wishlist-item img {
            max-width: 100%;
            height: 180px;
            object-fit: contain;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .wishlist-item h3 {
            margin: 10px 0;
            font-size: 1.2rem;
            color: #2c3e50;
            height: 3em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
        }
        .price-container {
            margin: 10px 0;
        }
        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9em;
        }
        .discount-price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 1.1em;
        }
        .stock-status {
            font-size: 0.9em;
            margin: 5px 0;
        }
        .in-stock {
            color: #27ae60;
        }
        .out-of-stock {
            color: #e74c3c;
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 15px;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .view-btn {
            background: #3498db;
            color: white;
        }
        .view-btn:hover {
            background: #2980b9;
        }
        .add-to-cart-btn {
            background: #2ecc71;
            color: white;
        }
        .add-to-cart-btn:hover {
            background: #27ae60;
        }
        .add-to-cart-btn.disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
        }
        .remove-btn:hover {
            background: #c0392b;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        .empty-state p {
            margin-bottom: 20px;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            vertical-align: middle;
            margin-left: 8px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: #2ecc71;
            color: white;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(200%);
            transition: transform 0.3s ease-out;
            z-index: 1000;
        }
        .notification.show {
            transform: translateX(0);
        }
        .notification.error {
            background: #e74c3c;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <!-- Your existing header content -->
    </header>
    
    <!-- Notification Area -->
    <div id="notification" class="notification" style="display: none;"></div>
    
    <!-- Wishlist Content -->
    <main>
        <div class="wishlist-container">
            <div class="wishlist-header">
                <h1>Your Wishlist</h1>
                <?php if (!empty($wishlist_items)): ?>
                    <p id="wishlist-count"><?php echo count($wishlist_items); ?> item(s) in your wishlist</p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="empty-state">
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                    <a href="/" class="btn view-btn">Return to Home</a>
                </div>
            <?php elseif (empty($wishlist_items)): ?>
                <div class="empty-state">
                    <p>Your wishlist is empty.</p>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <a href="/projects/new_Arrivals/" class="btn view-btn">Browse New Arrivals</a>
                        <a href="/products/" class="btn add-to-cart-btn">View All Products</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="wishlist-items" id="wishlist-items-container">
                    <?php foreach ($wishlist_items as $item): ?>
                        <div class="wishlist-item" data-product-id="<?php echo $item['id']; ?>">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 onerror="this.src='images/placeholder.jpg'">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            
                            <div class="price-container">
                                <?php if ($item['discount_price'] && $item['discount_price'] < $item['price']): ?>
                                    <span class="original-price">$<?php echo number_format($item['price'], 2); ?></span>
                                    <span class="discount-price">$<?php echo number_format($item['discount_price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="discount-price">$<?php echo number_format($item['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="stock-status <?php echo ($item['stock_status'] === 'in_stock') ? 'in-stock' : 'out-of-stock'; ?>">
                                <?php echo ($item['stock_status'] === 'in_stock') ? 'In Stock' : 'Out of Stock'; ?>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="/product/<?php echo htmlspecialchars($item['slug'] ?? $item['id']); ?>" 
                                   class="btn view-btn">View Product</a>
                                   
                                <button class="btn add-to-cart-btn <?php echo ($item['stock_status'] !== 'in_stock') ? 'disabled' : ''; ?>" 
                                        data-product-id="<?php echo $item['id']; ?>"
                                        <?php echo ($item['stock_status'] !== 'in_stock') ? 'disabled' : ''; ?>>
                                    Add to Cart
                                </button>
                                
                                <button class="btn remove-btn" data-product-id="<?php echo $item['id']; ?>">
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="site-footer">
        <!-- Your existing footer content -->
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Menu toggle functionality
            const menuIcon = document.getElementById('menu-icon');
            if (menuIcon) {
                menuIcon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.getElementById('side-menu').classList.toggle('active');
                    document.querySelector('.overlay').classList.toggle('active');
                    this.classList.toggle('active');
                });
            }

            // Notification system
            function showNotification(message, isError = false) {
                const notification = document.getElementById('notification');
                notification.textContent = message;
                notification.className = isError ? 'notification error show' : 'notification show';
                notification.style.display = 'block';
                
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 300);
                }, 3000);
            }

            // Remove item from wishlist
            document.querySelectorAll('.remove-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    const wishlistItem = this.closest('.wishlist-item');
                    const originalText = this.textContent;
                    
                    // Show loading state
                    this.disabled = true;
                    this.innerHTML = 'Removing... <span class="loading"></span>';
                    
                    // AJAX request to remove item
                    fetch('wishlist_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=remove&product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove item from DOM with animation
                            wishlistItem.style.opacity = '0';
                            wishlistItem.style.transform = 'translateX(-100%)';
                            setTimeout(() => {
                                wishlistItem.remove();
                                
                                // Update item count
                                const remainingItems = document.querySelectorAll('.wishlist-item').length;
                                const countDisplay = document.getElementById('wishlist-count');
                                if (countDisplay) {
                                    countDisplay.textContent = `${remainingItems} item(s) in your wishlist`;
                                }
                                
                                // Show empty state if no items left
                                if (remainingItems === 0) {
                                    document.getElementById('wishlist-items-container').innerHTML = `
                                        <div class="empty-state">
                                            <p>Your wishlist is empty.</p>
                                            <div style="display: flex; gap: 10px; justify-content: center;">
                                                <a href="/projects/new_Arrivals/" class="btn view-btn">Browse New Arrivals</a>
                                                <a href="/products/" class="btn add-to-cart-btn">View All Products</a>
                                            </div>
                                        </div>
                                    `;
                                }
                                
                                showNotification('Item removed from wishlist');
                            }, 300);
                        } else {
                            showNotification(data.message || 'Failed to remove item', true);
                            this.disabled = false;
                            this.textContent = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred. Please try again.', true);
                        this.disabled = false;
                        this.textContent = originalText;
                    });
                });
            });

            // Add to cart functionality
            document.querySelectorAll('.add-to-cart-btn:not(.disabled)').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    const originalText = this.textContent;
                    
                    // Show loading state
                    this.disabled = true;
                    this.innerHTML = 'Adding... <span class="loading"></span>';
                    
                    // AJAX request to add to cart
                    fetch('cart_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=add&product_id=${productId}&quantity=1`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Item added to cart!');
                            
                            // Update cart count in header if exists
                            const cartCountElement = document.querySelector('.cart-count');
                            if (cartCountElement) {
                                const currentCount = parseInt(cartCountElement.textContent) || 0;
                                cartCountElement.textContent = currentCount + 1;
                            }
                        } else {
                            showNotification(data.message || 'Failed to add item to cart', true);
                        }
                        this.disabled = false;
                        this.textContent = originalText;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred. Please try again.', true);
                        this.disabled = false;
                        this.textContent = originalText;
                    });
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                const sideMenu = document.getElementById('side-menu');
                if (sideMenu && !sideMenu.contains(e.target)) {
                    sideMenu.classList.remove('active');
                    document.querySelector('.overlay').classList.remove('active');
                    if (menuIcon) menuIcon.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>