<?php
require_once '../../database/auth/config.php';
session_start();
header('Content-Type: application/json');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $_GET['search'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$price = isset($_GET['price']) ? $_GET['price'] : null;
$brand = isset($_GET['brand']) ? $_GET['brand'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  
$results_per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
$offset = ($page - 1) * $results_per_page;
$filters = [];

if ($search) {
  $search = $conn->real_escape_string($search);
  $filters[] = "name LIKE '%$search%'";
}
if ($category) {
  $category = $conn->real_escape_string($category);
  $filters[] = "category='$category'";
}
if ($price) {
  $price = $conn->real_escape_string($price);
  switch ($price) {
    case '1':
      $filters[] = "price < 250000";
      break;
    case '2':
      $filters[] = "price BETWEEN 250000 AND 500000";
      break;
    case '3':
      $filters[] = "price BETWEEN 500000 AND 1000000";
      break;
    case '4':
      $filters[] = "price BETWEEN 1000000 AND 2000000";
      break;
    case '5':
      $filters[] = "price > 2000000";
      break;
  }
} 
if ($brand) {
  $brand = $conn->real_escape_string($brand);
  $filters[] = "brand='$brand'";
} 

$filters[] = "stock > 0";

$filter_query = implode(' AND ', $filters);

$sql = "SELECT * FROM product" . ($filter_query ? " WHERE $filter_query" : "") . " LIMIT $results_per_page OFFSET $offset";
$result = $conn->query($sql);
$total_results = $conn->query("SELECT COUNT(*) as total FROM product" . ($filter_query ? " WHERE $filter_query" : ""))->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);


if ($result->num_rows > 0) {
  $response = [
    'products' => [],
    'total_pages' => $total_pages,
    'current_page' => $page,
    'total_results' => $total_results,
  ];
  while ($row = $result->fetch_assoc()) {
    $product_id = $row['product_id'];
    $product_name = $row['name'];
    $product_image = $row['image'];
    $product_price = number_format($row['price'], 0, '', '.');
    $product_rating = $row['rating'];
    $product_category = $row['category'];
    $product_brand = $row['brand'];
    $product_stock = $row['stock'];
    $product_sold = $row['sold'];

    if ($product_image != null) {
      $image_src = "../database/api/get-product-image.php?id=" . $product_id;
    } else {
      $image_src = '../assets/product_image/placeholder.png';
    }

    $response['products'][] = [
      'id' => $product_id,
      'name' => $product_name,
      'image' => $image_src,
      'price' => "Rp. $product_price",
      'rating' => $product_rating,
      'category' => $product_category,
      'brand' => $product_brand,
      'stock' => $product_stock,
      'sold' => $product_sold,
    ];
  }
} else {
  $response = [
    'products' => [],
    'total_pages' => 0,
    'current_page' => $page,
    'total_results' => 0,
  ];
}
$conn->close();
echo json_encode($response);

exit;
?>