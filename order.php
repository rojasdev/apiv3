<?php
include 'config.php';  // Database connection

// Set the content type to application/json
header("Content-Type: application/json");

// Receive the JSON data from the request body
$json_data = file_get_contents('php://input');

// First, decode once to remove outer quotes and get the internal JSON string
$decoded_once = json_decode($json_data);

// Now decode again to get a PHP array
$data = json_decode($decoded_once, true);

// Check for the API key and validate it
$api_key = $data['api_key'] ?? null;

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

// Check for order items and total price
if (!isset($data['items']) || !isset($data['total_price'])) {
    echo json_encode(["message" => "Invalid input, items or total price missing"]);
    exit();
}

$items_json = json_encode($data['items']);  // Convert items to JSON for storage
$total_price = $data['total_price'];

// Insert the order into the database
$stmt = $pdo->prepare("INSERT INTO orders (customer_name, order_details, total_price) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $items_json, $total_price]);

// Return success message with the order ID
echo json_encode([
    "message" => "Order placed successfully",
    "order_id" => $pdo->lastInsertId()
]);



/*
// Check if json_decode was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    
    echo json_encode(['message' => 'Invalid JSON input: ' . json_last_error_msg()]);
    exit; // Stop further execution

}

// Step 3: Check if the 'total_price' key exists in the data array
if (isset($data['total_price'])) {
    // Process the order and respond with success
    echo json_encode(['message' => $data['total_price'] . ' Order placed successfully']);
} else {
    // Handle the case where 'total_price' is not set
    echo json_encode(['message' => 'total_price not provided']);
}*/
