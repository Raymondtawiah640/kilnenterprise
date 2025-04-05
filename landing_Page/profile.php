<?php
session_start();
require 'db_connect.php'; // Include the database connection

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_Page');
    exit;
}

// Initialize variables
$user = null;
$orders = [];
$error = null;

try {
    // Fetch user data
    $user_id = $_SESSION['user_id'];
    $query = "SELECT id, email, name, phone FROM users WHERE id = ?";
    
    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    if (!$stmt) {
        throw new Exception("Database connection error");
    }
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Use PDO::FETCH_ASSOC for associative array

    if (!$user) {
        throw new Exception("User data not found");
    }

    // Fetch recent orders
    $order_query = "
        SELECT 
            o.id AS order_id, 
            o.order_date, 
            o.total_amount, 
            o.status,
            COUNT(oi.id) AS item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.order_date DESC 
        LIMIT 5
    ";
    $order_stmt = $pdo->prepare($order_query);

    if ($order_stmt) {
        $order_stmt->execute([$user_id]);
        $orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC); // Use PDO::FETCH_ASSOC for consistency
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Profile Error: " . $error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Kiln Enterprise</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="assets/images/logo1.ico" type="image/x-icon">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --error-color: #e74c3c;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .profile-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .profile-info p {
            margin: 0.8rem 0;
            font-size: 1.1rem;
            color: var(--text-color);
        }
        .info-label {
            font-weight: 600;
            display: inline-block;
            width: 100px;
            color: #555;
        }
        .edit-profile-btn {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: background 0.3s;
        }
        .edit-profile-btn:hover {
            background: #2980b9;
        }
        .section-title {
            font-size: 1.5rem;
            margin: 1.5rem 0 1rem;
            color: var(--text-color);
            border-bottom: 2px solid var(--light-gray);
            padding-bottom: 0.5rem;
        }
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .order-card {
            border: 1px solid #eee;
            border-radius: var(--border-radius);
            padding: 1.2rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
        }
        .order-id {
            font-weight: 600;
            color: var(--primary-color);
        }
        .order-status {
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .order-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        .order-status.completed {
            background: #d4edda;
            color: #155724;
        }
        .order-status.shipped {
            background: #cce5ff;
            color: #004085;
        }
        .view-order-btn {
            display: inline-block;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .no-orders {
            text-align: center;
            padding: 2rem;
            background: var(--light-gray);
            border-radius: var(--border-radius);
            margin-top: 1.5rem;
        }
        .shop-now-btn {
            display: inline-block;
            background: var(--secondary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            margin-top: 1rem;
            transition: background 0.3s;
        }
        .shop-now-btn:hover {
            background: #27ae60;
        }
        .error-message {
            background: #ffebee;
            border-left: 4px solid var(--error-color);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: var(--border-radius);
        }
        .error-message p {
            color: var(--error-color);
            margin: 0.5rem 0;
        }
        @media (max-width: 768px) {
            .profile-container {
                padding: 0 0.5rem;
            }
            .profile-card {
                padding: 1rem;
            }
            .orders-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <h2>My Profile</h2>
                <a href="edit_profile.php" class="edit-profile-btn">Edit Profile</a>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <p>We encountered an error loading your profile:</p>
                    <p><?php echo htmlspecialchars($error); ?></p>
                    <a href="profile.php" class="retry-btn">Try Again</a>
                    <p>or <a href="../login_Page/">log in again</a></p>
                </div>
            <?php elseif ($user): ?>
                <div class="profile-info">
                    <p><span class="info-label">Name:</span> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><span class="info-label">Email:</span> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><span class="info-label">Phone:</span> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                </div>
                <h3 class="section-title">Recent Orders</h3>
                <?php if (!empty($orders)): ?>
                    <div class="orders-grid">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <span class="order-id">Order #<?php echo htmlspecialchars($order['order_id']); ?></span>
                                    <span class="order-status <?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </div>
                                <div class="order-details">
                                    <p><?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                                    <p><?php echo $order['item_count']; ?> item<?php echo $order['item_count'] != 1 ? 's' : ''; ?></p>
                                    <p><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></p>
                                </div>
                                <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="view-order-btn">
                                    View Details &rarr;
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="order_history.php" class="view-all-orders">View All Orders &rarr;</a>
                <?php else: ?>
                    <div class="no-orders">
                        <img src="assets/images/empty-cart.svg" alt="No orders yet">
                        <h3>You haven't placed any orders yet</h3>
                        <p>Discover our latest products and special offers</p>
                        <a href="../new_arrivals/" class="shop-now-btn">Start Shopping</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="error-message">
                    <p>Your profile information could not be loaded.</p>
                    <a href="profile.php" class="retry-btn">Try Again</a>
                    <p>or <a href="logout.php">log in again</a></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        // Mobile menu toggle
        document.getElementById('menu-icon')?.addEventListener('click', function() {
            document.getElementById('side-menu').classList.toggle('active');
        });

        // User dropdown functionality
        document.querySelector('.user-icon-dropdown')?.addEventListener('click', function(e) {
            e.stopPropagation();
            this.querySelector('.dropdown-content').classList.toggle('show');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        });
    </script>
</body>
</html>