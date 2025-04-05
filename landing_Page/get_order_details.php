<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require_once 'db_connection.php';

$orderId = $_GET['order_id'];
$userId = $_SESSION['user_id'];

// Verify the order belongs to the user
$verifyStmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
$verifyStmt->bind_param("ii", $orderId, $userId);
$verifyStmt->execute();
$verifyResult = $verifyStmt->get_result();

if ($verifyResult->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

// Get order details
$orderStmt = $conn->prepare("
    SELECT o.id, o.total, o.order_date, o.status
    FROM orders o
    WHERE o.id = ?
");
$orderStmt->bind_param("i", $orderId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$order = $orderResult->fetch_assoc();

// Get order items
$itemsStmt = $conn->prepare("
    SELECT oi.product_id, p.name, p.price, oi.quantity, p.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'id' => $order['id'],
    'total' => $order['total'],
    'order_date' => $order['order_date'],
    'status' => $order['status'],
    'items' => $items
]);

$verifyStmt->close();
$orderStmt->close();
$itemsStmt->close();
$conn->close();
?>