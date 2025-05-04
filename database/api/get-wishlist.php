<?php
session_start();
require_once '../customers.php';
require_once '../products.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['cust_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated', 'products' => []]);
    exit();
}

$cust_id = $_SESSION['cust_id'];

// Get product IDs
$likedProductIds = getCustomerLikedProducts($cust_id);

// If no products are liked, return an empty array
if (empty($likedProductIds)) {
    echo json_encode(['success' => true, 'products' => []]);
    exit();
}

// Get product details for each liked product ID
$productsData = getProductsByIds($likedProductIds);

if ($productsData === null) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve products', 'products' => []]);
    exit();
}

// Format products in the structure expected by the frontend
$formattedProducts = [];
foreach ($productsData as $product) {
  $product_id = $product['product_id'];
  $product_name = $product['name'];
  $product_price = number_format($product['price'], 0, '', '.');
  $product_rating = $product['rating'];
  $product_category = $product['category'];
  $product_brand = $product['brand'];
  $product_stock = $product['stock'];

  $image_src = "../database/api/get-product-image.php?id=" . $product_id;
  if (empty($product['image'])) {
      $image_src = '../assets/product_image/placeholder.png';
  }

  $formattedProducts[] = [
      'id' => $product_id,
      'name' => $product_name,
      'image' => $image_src,
      'price' => "Rp. $product_price",
      'rating' => $product_rating,
      'category' => $product_category,
      'brand' => $product_brand,
      'stock' => $product_stock,
  ];
}

echo json_encode(['success' => true, 'products' => $formattedProducts]);
exit();
?>