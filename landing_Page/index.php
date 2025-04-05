<?php
session_start();
require_once 'db_connect.php'; // Include the PDO connection script

// Redirect non-customers to appropriate dashboards
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: /projects/admin_panel/');
        exit();
    }
    if ($_SESSION['user_type'] === 'vendor') {
        header('Location: /projects/vendor_dashboard/');
        exit();
    }
}

// Fetch cart count for logged-in users
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        $cartCount = $row['total'] ?? 0;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Kiln Enterprise</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="logo1.ico" type="image/x-icon">
    <style>
        /* Customer-specific styles */
        .customer-welcome {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            margin-bottom: 30px;
        }
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 0 20px;
        }
        .dashboard-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .quick-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }
        .quick-action-btn {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        /* Dropdown styles */
        .user-icon-dropdown {
            position: relative; /* Position relative for dropdown */
        }
        .dropdown-content {
            display: none; /* Initially hidden */
            position: absolute; /* Position it absolutely */
            background-color: white;
            min-width: 160px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1; /* Ensure it appears above other elements */
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1; /* Highlight on hover */
        }
        /* Search Bar Styles */
        .search-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Align to the right */
            margin-top: 10px; /* Add some space above the search bar */
        }
        .search-bar input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
            margin-right: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .search-bar button {
            padding: 10px 15px;
            background: #2980b9;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .search-bar button:hover {
            background: #1c598a;
        }
    </style>
</head>
<body>
    <!-- Announcement header -->
    <header class="announcement-header">
        <p>Welcome to Kiln Enterprise - Your Premium Shopping Destination</p>
    </header>
    <!-- Main Header and Navigation -->
    <header class="main-header">
        <div class="menu-icon" id="menu-icon">
            <div class="icon-bar"></div>
            <div class="icon-bar"></div>
            <div class="icon-bar"></div>
        </div>
        <nav class="header-nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Customer is logged in -->
                <div style="display: flex; align-items: center;">
                    <!-- Cart Icon -->
                    <div class="cart-icon">
                        <a href="cart.php">
                            <img src="cart_icon.png" alt="Cart Icon">
                            <span class="cart-count"><?php echo htmlspecialchars($cartCount); ?></span>
                        </a>
                    </div>
                    <!-- Customer Account Dropdown -->
                    <div class="user-icon-dropdown">
                        <div class="user-icon">
                            <img src="user.png" alt="User  Icon">
                            <span>My Account</span>
                        </div>
                        <div class="dropdown-content">
                            <a href="profile.php">Profile</a>
                            <a href="order_history.php">Order History</a>
                            <a href="wishlist.php">Wishlist</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- User is not logged in -->
                <a href="/projects/login_Page/" class="sign-in-btn">Sign In</a>
                <a href="/projects/login_Page/Sign_up.php" class="sign-up-btn">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>
    <!-- Search Bar -->
    <!-- Search Bar -->
<div class="search-bar">
    <form action="search.php" method="GET">
        <input type="text" name="query" placeholder="Search products..." required>
        <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>
</div>
    <!-- Side Menu -->
    <aside class="side-menu" id="side-menu">
        <nav>
            <ul>
                <li><a href="../landing_Page">Dashboard</a></li>
                <li class="dropdown">
                    <a href="category.php">Shop by Category</a>
                    <ul class="dropdown-menu">
                        <li><a href="category.php?type=men">Men's Clothing</a></li>
                        <li><a href="category.php?type=women">Women's Clothing</a></li>
                        <li><a href="category.php?type=kids">Kids' Clothing</a></li>
                        <li><a href="category.php?type=accessories">Accessories</a></li>
                        <li><a href="category.php?type=electronics">Electronics</a></li>
                        <li><a href="category.php?type=home">Home & Kitchen</a></li>
                    </ul>
                </li>
                <li><a href="/projects/deals_Offers/">Deals & Offers</a></li>
                <li><a href="/projects/new_Arrivals/">New Arrivals</a></li>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="order_history.php">Order History</a></li>
                <li><a href="wishlist.php">Wishlist</a></li>
                <li><a href="/projects/trending_Now/">Trending Now</a></li>
                <li><a href="/projects/contact_Us/">Contact Us</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>
    <!-- Customer Dashboard Content -->
    <main>
        <section class="customer-welcome">
            <h1>Welcome Back, <?php echo isset($_SESSION['email']) ? htmlspecialchars(explode('@', $_SESSION['email'])[0]) : 'Customer'; ?>!</h1>
            <p>Ready to continue your shopping experience?</p>
            <div class="quick-actions">
                <a href="/projects/new_Arrivals/" class="quick-action-btn">New Arrivals</a>
                <a href="/projects/deals_Offers/" class="quick-action-btn">Special Deals</a>
                <a href="cart.php" class="quick-action-btn">View Cart (<?php echo $cartCount; ?>)</a>
            </div>
        </section>
        <div class="dashboard-sections">
            <div class="dashboard-card">
                <h2>Recent Orders</h2>
                <?php
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC LIMIT 3");
                        $stmt->execute(['user_id' => $userId]);
                        if ($stmt->rowCount() > 0) {
                            echo '<ul>';
                            while ($order = $stmt->fetch()) {
                                echo '<li>Order #' . htmlspecialchars($order['id']) . ' - $' . number_format($order['total'], 2) . '</li>';
                            }
                            echo '</ul>';
                            echo '<a href="order_history.php">View all orders</a>';
                        } else {
                            echo '<p>No recent orders found.</p>';
                            echo '<a href="/projects/new_Arrivals/">Start Shopping</a>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>Error fetching orders: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
                ?>
            </div>
            <div class="dashboard-card">
                <h2>Wishlist</h2>
                <?php
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                    try {
                        $stmt = $pdo->prepare("SELECT p.name FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = :user_id LIMIT 3");
                        $stmt->execute(['user_id' => $userId]);
                        if ($stmt->rowCount() > 0) {
                            echo '<ul>';
                            while ($item = $stmt->fetch()) {
                                echo '<li>' . htmlspecialchars($item['name']) . '</li>';
                            }
                            echo '</ul>';
                            echo '<a href="wishlist.php">View full wishlist</a>';
                        } else {
                            echo '<p>Your wishlist is empty.</p>';
                            echo '<a href="/projects/new_Arrivals/">Browse Products</a>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>Error fetching wishlist: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
                ?>
            </div>
            <div class="dashboard-card">
                <h2>Recommended For You</h2>
                <?php
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                    try {
                        $stmt = $pdo->prepare("SELECT p.name, p.id FROM products p JOIN browsing_history b ON p.id = b.product_id WHERE b.user_id = :user_id LIMIT 3");
                        $stmt->execute(['user_id' => $userId]);
                        if ($stmt->rowCount() > 0) {
                            echo '<ul>';
                            while ($product = $stmt->fetch()) {
                                echo '<li><a href="/products/' . htmlspecialchars($product['id']) . '">' . htmlspecialchars($product['name']) . '</a></li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>No recommendations available.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>Error fetching recommendations: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
                ?>
            </div>
        </div>
        <!-- Continue with your existing new arrivals and products sections -->
        <section class="new-arrivals">
            <div class="content-overlay">
                <h1>New Arrivals</h1>
                <h3>Discover what's trending now</h3>
                <button class="shop-now-btn"><a href="/projects/new_Arrivals/">Shop now</a></button>
            </div>
        </section>
        <section class="new-products">
            <div class="content-overlay">
                <h1>Seasonal Specials</h1>
                <h3>Perfect for the current weather</h3>
                <button class="shop-now-btn"><a href="/projects/deals_Offers/">View Offers</a></button>
            </div>
        </section>
    </main>
    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <h1>Need Help?</h1>
            <p>Our customer service is available 24/7</p>
            <p>Email: support@kilnenterprise.com</p>
            <div class="social-links">
                <a href="#" target="_blank">Facebook</a>
                <a href="#" target="_blank">Twitter</a>
                <a href="#" target="_blank">Instagram</a>
            </div>
        </div>
    </footer>
    <script>
        const menuIcon = document.getElementById('menu-icon');
        const sideMenu = document.getElementById('side-menu');
        const sideMenuLinks = sideMenu.querySelectorAll('a');
        const overlay = document.createElement('div');
        overlay.className = 'overlay';
        document.body.appendChild(overlay);
        // Toggle menu
        menuIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            sideMenu.classList.toggle('active');
            overlay.classList.toggle('active');
            menuIcon.classList.toggle('active');
        });
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!sideMenu.contains(e.target) && e.target !== menuIcon) {
                sideMenu.classList.remove('active');
                overlay.classList.remove('active');
                menuIcon.classList.remove('active');
            }
        });
        // Close menu when clicking menu items
        sideMenuLinks.forEach(link => {
            link.addEventListener('click', () => {
                sideMenu.classList.remove('active');
                overlay.classList.remove('active');
                menuIcon.classList.remove('active');
            });
        });
        // Prevent menu clicks from closing
        sideMenu.addEventListener('click', (e) => {
            e.stopPropagation();
        });
        // Optional: Add click-to-toggle functionality for user dropdown
        document.addEventListener('DOMContentLoaded', () => {
            const userIconDropdown = document.querySelector('.user-icon-dropdown');
            const dropdownContent = userIconDropdown.querySelector('.dropdown-content');
            if (dropdownContent) {
                userIconDropdown.addEventListener('click', (e) => {
                    e.stopPropagation(); // Prevent click from propagating to the document
                    dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
                });
                // Close dropdown when clicking outside
                document.addEventListener('click', () => {
                    dropdownContent.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>