<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /projects/login_Page/");
    exit();
}
// Database connection
$conn = new mysqli("localhost", "root", "Raymond@WAMPP12345", "kiln_enterprise");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cart count (reusing your existing code)
$cartCount = 0;
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$cartCount = $row['total'] ?? 0;
$stmt->close();

// Fetch order history
$orders = [];
try {
    // Check if orders and order_items tables exist
    $ordersTableExists = $conn->query("SHOW TABLES LIKE 'orders'")->num_rows > 0;
    $orderItemsTableExists = $conn->query("SHOW TABLES LIKE 'order_items'")->num_rows > 0;

    if ($ordersTableExists && $orderItemsTableExists) {
        $orderQuery = $conn->prepare("
            SELECT o.id, SUM(oi.price * oi.quantity) AS total, o.order_date, o.status, 
                   COUNT(oi.id) AS item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.order_date DESC
        ");
        $orderQuery->bind_param("i", $userId);
        $orderQuery->execute();
        $result = $orderQuery->get_result();
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $orderQuery->close();
    } else {
        $orders = [];
    }
} catch (Exception $e) {
    $orders = [];
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Kiln Enterprise</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="logo1.ico" type="image/x-icon">
    <style>
        /* Order History specific styles */
        .order-history-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .order-history-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .order-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .order-id {
            font-weight: bold;
        }
        .order-date {
            color: #6c757d;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .status-processing {
            background: #fff3cd;
            color: #856404;
        }
        .status-shipped {
            background: #cce5ff;
            color: #004085;
        }
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .order-summary {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .order-items-count {
            color: #6c757d;
        }
        .order-total {
            font-weight: bold;
            color: #e74c3c;
        }
        .order-actions {
            padding: 15px;
            text-align: right;
        }
        .view-details-btn {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .empty-orders {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Reuse your existing header and menu structure -->
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
            <div style="display: flex; align-items: center;">
                <div class="cart-icon">
                    <a href="cart.php">
                        <img src="cart_icon.png" alt="Cart Icon">
                        <span class="cart-count"><?php echo htmlspecialchars($cartCount); ?></span>
                    </a>
                </div>
                <div class="user-icon-dropdown">
                    <div class="user-icon">
                        <img src="user.png" alt="User Icon">
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
        </nav>
    </header>
    <!-- Side Menu -->
    <aside class="side-menu" id="side-menu">
        <nav>
            <ul>
                <li><a href="/projects/landing_Page/">Dashboard</a></li>
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
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </aside>
    <!-- Order History Content -->
    <main class="order-history-container">
        <div class="order-history-header">
            <h1>Order History</h1>
            <p>Your past purchases with us</p>
        </div>
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <h2>You haven't placed any orders yet</h2>
                <p>Start shopping to see your order history here</p>
                <a href="/projects/new_Arrivals/" class="btn">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <span class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></span>
                                <span class="order-date">Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                            </div>
                            <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                            </span>
                        </div>
                        <div class="order-summary">
                            <div class="order-items-count">
                                <?php echo htmlspecialchars($order['item_count']); ?> item<?php echo $order['item_count'] != 1 ? 's' : ''; ?>
                            </div>
                            <div class="order-total">
                                $<?php echo number_format($order['total'], 2); ?>
                            </div>
                        </div>
                        <div class="order-actions">
                            <button class="view-details-btn" data-order-id="<?php echo $order['id']; ?>">
                                View Details
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <!-- Order Details Modal (hidden by default) -->
    <div id="orderDetailsModal" style="display: none;">
        <div class="modal-content">
            <h2>Order Details #<span id="modalOrderId"></span></h2>
            <div id="orderItemsContainer"></div>
            <div class="modal-footer">
                <button id="closeModalBtn">Close</button>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <h1>Need Help?</h1>
            <p>Our customer service is available 24/7</p>
            <p>Email: enterprisekiln@gmail.com</p>
            <div class="social-links">
                <a href="#" target="_blank">Facebook</a>
                <a href="#" target="_blank">Twitter</a>
                <a href="#" target="_blank">Instagram</a>
            </div>
        </div>
    </footer>
    <!-- JavaScript -->
    <script>
        // Toggle mobile menu (reuse your existing script)
        const menuIcon = document.getElementById('menu-icon');
        const sideMenu = document.getElementById('side-menu');
        const overlay = document.createElement('div');
        overlay.className = 'overlay';
        document.body.appendChild(overlay);
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

        // Order details modal functionality
        const modal = document.getElementById('orderDetailsModal');
        const modalOrderId = document.getElementById('modalOrderId');
        const orderItemsContainer = document.getElementById('orderItemsContainer');
        const closeModalBtn = document.getElementById('closeModalBtn');

        document.querySelectorAll('.view-details-btn').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                // Show loading state
                orderItemsContainer.innerHTML = '<p>Loading order details...</p>';
                modalOrderId.textContent = orderId;
                modal.style.display = 'block';

                // Fetch order details
                fetch('get_order_details.php?order_id=' + orderId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let html = `
                                <div class="order-info">
                                    <p><strong>Order Date:</strong> ${new Date(data.order_date).toLocaleDateString()}</p>
                                    <p><strong>Status:</strong> <span class="status-${data.status.toLowerCase()}">${data.status}</span></p>
                                    <p><strong>Total:</strong> $${data.total.toFixed(2)}</p>
                                </div>
                                <h3>Items:</h3>
                                <ul class="order-items-list">
                            `;
                            data.items.forEach(item => {
                                html += `
                                    <li class="order-item">
                                        <img src="${item.image_url}" alt="${item.name}" width="60">
                                        <div class="item-details">
                                            <h4>${item.name}</h4>
                                            <p>Quantity: ${item.quantity}</p>
                                            <p>Price: $${item.price.toFixed(2)}</p>
                                            <p>Subtotal: $${(item.price * item.quantity).toFixed(2)}</p>
                                        </div>
                                    </li>
                                `;
                            });
                            html += `</ul>`;
                            orderItemsContainer.innerHTML = html;
                        } else {
                            orderItemsContainer.innerHTML = `<p>Error loading order details: ${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        orderItemsContainer.innerHTML = '<p>Error loading order details. Please try again.</p>';
                        console.error('Error:', error);
                    });
            });
        });

        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>