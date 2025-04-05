<?php
session_start(); // Start the session to check if the user is logged in

// Connect to the database
$conn = new mysqli("localhost", "root", "Raymond@WAMPP12345", "kiln_enterprise");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all products from the database
$query = "SELECT product_id, name, price, image_url FROM products";
$result = $conn->query($query);

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Now - Kiln Enterprise</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="logo1.ico" type="image/x-icon">
</head>
<body>
    <!-- Announcement header -->
    <header class="announcement-header">
        <p>Quality products sold here</p>
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
                <div class="cart-icon">
                    <a href="cart.php">
                        <img src="cart_icon.png" alt="Cart Icon">
                        <span class="cart-count">0</span>
                    </a>
                </div>
                <div class="user-icon-dropdown">
                    <div class="user-icon">
                        <img src="user.png" alt="User Icon">
                    </div>
                    <div class="dropdown-content">
                        <a href="profile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="../login_Page" class="sign-in-btn">Sign In</a>
                <a href="http://localhost/projects/login_Page/Sign_up.php" class="sign-up-btn">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Product Grid -->
    <section class="product-grid">
        <h1>Shop Now</h1>
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>$<?php echo number_format($product['price'], 2); ?></p>
                    <button class="add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>">Add to Cart</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products available.</p>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <h1>Contact Us</h1>
            <p>Location: Koforidua</p>
            <p>Email: enterprisekiln@gmail.com</p>
            <div class="social-links">
                <a href="#" target="_blank">Facebook</a>
                <a href="#" target="_blank">Twitter</a>
                <a href="#" target="_blank">Instagram</a>
            </div>
        </div>
    </footer>

    <!-- JavaScript for Add to Cart Functionality -->
    <script>
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.getAttribute('data-product-id');

                fetch('/api/add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Product added to cart!');
                        const cartCountElement = document.querySelector('.cart-count');
                        if (cartCountElement) {
                            cartCountElement.textContent = parseInt(cartCountElement.textContent || 0) + 1;
                        }
                    } else {
                        alert('Failed to add product to cart.');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>