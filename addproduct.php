<?php
include 'config.php';  // Database connection

// Extract API key and other product data from POST
$api_key = $_POST['api_key'] ?? null;
$product_name = $_POST['name'] ?? null;
$product_description = $_POST['description'] ?? null;
$product_price = $_POST['price'] ?? null;

// Check for missing API key
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

// Check if required fields are present
if (!$product_name || !$product_price || !isset($_FILES['image'])) {
    echo json_encode(['message' => 'Required fields missing']);
    exit();
}

// Handle the image upload
$target_dir = "uploads/";  // Directory for image storage
$target_file = $target_dir . basename($_FILES["image"]["name"]);
$image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if the file is an image
$check = getimagesize($_FILES["image"]["tmp_name"]);
if ($check === false) {
    echo json_encode(['message' => 'File is not an image']);
    exit();
}

// Allow only certain file formats (e.g., jpg, png, jpeg, gif)
$allowed_formats = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($image_file_type, $allowed_formats)) {
    echo json_encode(['message' => 'Invalid image format']);
    exit();
}

// Move the uploaded file to the target directory
if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    echo json_encode(['message' => 'Image upload failed']);
    exit();
}

// Insert product into the database with the image URL
$stmt = $pdo->prepare("INSERT INTO store_products (name, description, price, image_url) VALUES (?, ?, ?, ?)");
$stmt->execute([$product_name, $product_description, $product_price, $target_file]);

echo json_encode([
    "message" => "Product added successfully",
    "product_id" => $pdo->lastInsertId()
]);
?>
