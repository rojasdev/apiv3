<?php
include 'config.php';  // Database connection

// Extract API key from headers or POST data
$api_key = $_POST['api_key'] ?? null;

if (!$api_key) {
    echo json_encode(['message' => 'API key missing']);
    exit();
}

// Validate the API key and get the user ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE api_key = ?");
$stmt->execute([$api_key]);
$api_key_record = $stmt->fetch();

if (!$api_key_record) {
    echo json_encode(['message' => 'Invalid API key']);
    exit();
}

$user_id = $api_key_record['id'];

// Retrieve products for this user
$stmt = $pdo->prepare("SELECT id, name, description, price, image_url FROM store_products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output the result in JSON format
echo json_encode([
    'message' => 'Product list retrieved successfully',
    'products' => $products
]);
?>
