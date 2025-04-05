<?php
session_start();
require_once 'db_connect.php'; // Include the PDO connection script

// Initialize variables
$products = [];
$error = null;

// Get the search query from the URL
$query = isset($_GET['query']) ? trim($_GET['query']) : ''; // Default to an empty string

// Check if the query is empty
if ($query === '') {
    $error = "Invalid search query.";
} else {
    try {
        // Fetch products based on the search query
        $stmt = $pdo->prepare("SELECT id, name, price, image_url, description FROM products WHERE name LIKE ? OR type LIKE ? ORDER BY name");
        $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products)) {
            $error = "No products found matching your search.";
        }
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        $error = "An error occurred while fetching products. Please try again later.";
    }
}

// Close the connection (optional)
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results for: <?php echo htmlspecialchars($query); ?></title>
    <style>
        /* Add your styles here */
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Search Results for: <?php echo htmlspecialchars($query); ?></h1>

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
            <div class="error">No products available matching your search.</div>
        <?php endif; ?>
    </div>

    <script>
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
                    alert('Product added to cart successfully!');
                } else {
                    alert('Failed to add product to cart.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the product to the cart.');
            });
        }
    </script>
</body>
</html>