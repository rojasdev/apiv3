<?php
include 'config.php'; // Database connection file

// Extract API key from headers or POST data
$api_key = $_POST['api_key'] ?? null;

if (!$api_key) {
    echo json_encode(['message' => 'API key missing']);
    exit();
}
// Verify the API key and retrieve the user ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE api_key = ?");
$stmt->execute([$api_key]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['message' => 'Invalid API key']);
    exit();
}

$user_id = $user['id'];

// Fetch orders for the authenticated user
$stmt = $pdo->prepare("SELECT id, customer_name, order_details, total_price, order_date FROM orders WHERE customer_name = ?");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the list of orders in JSON format
if ($orders) {
    echo json_encode(['orders' => $orders]);
} else {
    echo json_encode(['message' => 'No orders found']);
}
?>
