<?php
session_start();

// Include the database connection script
require_once 'db_connect.php';

// Check if the connection exists
if (!isset($pdo)) {
    die("Database connection is missing. Please check db_connect.php.");
}

// Get the category type from the URL
$type = isset($_GET['type']) ? trim($_GET['type']) : null;

// Initialize variables
$products = [];
$error = null;

if ($type) {
    try {
        // Fetch products based on the selected category type
        $stmt = $pdo->prepare("SELECT id, name, price, image_url, description, stock FROM products WHERE type = ? ORDER BY name");
        $stmt->execute([$type]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products)) {
            $error = "No products found for this category.";
        }
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        $error = "An error occurred while fetching products. Please try again later.";
    }
} else {
    $error = "Invalid category selection.";
}

// Close the connection (optional)
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category: <?php echo htmlspecialchars(ucfirst($type ?? '')); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .error {
            color: #e74c3c;
            text-align: center;
            padding: 20px;
        }
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .product-item {
            width: calc(33.33% - 20px);
            background-color: #f1f1f1;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .product-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-item h3 {
            margin: 10px 0;
            font-size: 1.2rem;
            color: #333;
        }
        .product-item p {
            margin: 5px 0;
            color: #666;
        }
        .product-item button {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #2ecc71;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        .product-item button:hover {
            background-color: #27ae60;
        }

        /* Notification Box Styles */
        #notification {
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 300px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }
        #notification.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        #notification.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        #notification p {
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Notification Box -->
    <div id="notification"></div>

    <div class="container">
        <h1>Category: <?php echo htmlspecialchars(ucfirst($type ?? '')); ?></h1>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!empty($products)): ?>
            <div class="product-list">
                <?php foreach ($products as $product): ?>
                    <div class="product-item">
                        <img src="../uploads/products/<?php echo htmlspecialchars($product['image_url'] ?? 'default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p>Price: $<?php echo number_format($product['price'], 2); ?></p>
                        <button onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="error">No products available in this category.</div>
        <?php endif; ?>
    </div>

    <script>
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.innerHTML = `<p>${message}</p>`;
            notification.className = `success`; // Default to success
            if (type === 'error') {
                notification.className = `error`;
            }
            notification.style.display = 'block';

            // Hide the notification after 3 seconds
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        function addToCart(productId) {
            fetch('/new_Arrivals/api/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Product added to cart successfully!', 'success');
                } else {
                    showNotification(data.message || 'Failed to add product to cart.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while adding the product to the cart.', 'error');
            });
        }
    </script>
</body>
</html>