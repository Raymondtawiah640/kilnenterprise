<?php
session_start();
require 'db_connect.php';

// Initialize variables
$cartItems = [];
$totalPrice = 0;
$error = null;

// Only proceed if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_Page");
    exit();
}

// Handle quantity updates and item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Handle quantity updates
        if (isset($_POST['update_quantity'])) {
            $itemId = $_POST['cart_item_id'];
            $quantity = (int)$_POST['quantity'];

            // Verify product stock before updating
            $stmt = $pdo->prepare("
                SELECT p.stock 
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.id = ? AND c.user_id = ?
            ");
            $stmt->execute([$itemId, $_SESSION['user_id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item && $quantity <= $item['stock']) {
                if ($quantity > 0) {
                    $stmt = $pdo->prepare("
                        UPDATE cart
                        SET quantity = ?
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([$quantity, $itemId, $_SESSION['user_id']]);
                } else {
                    // Remove item if quantity is set to 0
                    $stmt = $pdo->prepare("
                        DELETE FROM cart 
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([$itemId, $_SESSION['user_id']]);
                }
            } else {
                throw new Exception("Not enough stock available");
            }
        }

        // Handle item removal
        if (isset($_POST['remove_item'])) {
            $itemId = $_POST['cart_item_id'];

            $stmt = $pdo->prepare("
                DELETE FROM cart 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$itemId, $_SESSION['user_id']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
        error_log("Cart update error: " . $e->getMessage());
    }
}

// Fetch cart items for the logged-in user
try {
    $stmt = $pdo->prepare("
        SELECT 
            c.id AS cart_item_id,
            c.quantity,
            p.id AS product_id,
            p.name, 
            p.price, 
            p.image_url AS image,
            p.description,
            p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY p.name
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total price
    foreach ($cartItems as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }

} catch (PDOException $e) {
    error_log("Cart error: " . $e->getMessage());
    $error = "An error occurred while loading your cart. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - Kiln Enterprise</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .cart-container {
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
            padding: 15px;
            background: #ffebee;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .empty-cart {
            text-align: center;
            padding: 40px 0;
        }
        .empty-cart a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .cart-item {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 30px;
            gap: 20px;
        }
        .cart-item img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .item-details {
            flex-grow: 1;
        }
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-control input {
            width: 80px;
            height: 40px;
            text-align: center;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .checkout-btn, .remove-btn {
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .checkout-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
        }
        .remove-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
        }
        .cart-total {
            text-align: right;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h1>Your Shopping Cart</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <p><a href="../new_Arrivals">Continue shopping</a></p>
            </div>
        <?php else: ?>
            <!-- Display cart items -->
            <form method="post" id="cart-form">
                <?php foreach ($cartItems as $item): 
                    $maxQuantity = $item['stock'];
                    $isLowStock = $item['quantity'] >= $maxQuantity;
                ?>
                    <div class="cart-item">
                        <img src="../uploads/products/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                            <?php if ($isLowStock): ?>
                                <p class="stock-warning">
                                    <?php if ($maxQuantity > 0): ?>
                                        Only <?php echo $maxQuantity; ?> available
                                    <?php else: ?>
                                        <span class="out-of-stock">Out of stock</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="item-actions">
                            <div class="quantity-control">
                                <input type="number" 
                                       name="quantity" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="<?php echo $maxQuantity; ?>"
                                       onchange="this.form.submit()">
                                <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                <input type="hidden" name="update_quantity" value="1">
                            </div>
                            <!-- Separate form for removing an item -->
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>

            <div class="cart-total">
                <h2>Total: $<?php echo number_format($totalPrice, 2); ?></h2>
                <form action="initiate_payment.php" method="POST">
                    <input type="hidden" name="total_amount" value="<?php echo $totalPrice; ?>">
                    <button class="checkout-btn">Proceed to Checkout</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>