<?php
require_once 'auth/config.php';

/**
 * Get all products with optional filtering and pagination
 * 
 * @param array $filters Optional filters like category, price range, etc.
 * @param int $page Current page number
 * @param int $limit Number of products per page
 * @return array Product data and pagination info
 */
function getProducts($filters = array(), $page = 1, $limit = 12) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Calculate offset for pagination
  $offset = ($page - 1) * $limit;
  
  // Start building the query
  $query = "SELECT * FROM product WHERE 1=1";
  $countQuery = "SELECT COUNT(*) as total FROM product WHERE 1=1";
  
  // Add filters if provided
  if (!empty($filters)) {
      if (isset($filters['category']) && !empty($filters['category'])) {
          $category = $conn->real_escape_string($filters['category']);
          $query .= " AND category = '$category'";
          $countQuery .= " AND category = '$category'";
      }
      
      if (isset($filters['min_price']) && isset($filters['max_price'])) {
          $minPrice = (float)$filters['min_price'];
          $maxPrice = (float)$filters['max_price'];
          $query .= " AND price BETWEEN $minPrice AND $maxPrice";
          $countQuery .= " AND price BETWEEN $minPrice AND $maxPrice";
      }
      
      if (isset($filters['search']) && !empty($filters['search'])) {
          $search = $conn->real_escape_string($filters['search']);
          $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
          $countQuery .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
      }

      if (isset($filters['brand']) && !empty($filters['brand'])) {
        $brand = $conn->real_escape_string($filters['brand']);
        $query .= " AND (brand = '$brand')";
        $countQuery .= " AND (brand = '$brand')";
    }
  }
  
  // Add sorting
  if (isset($filters['sort'])) {
      switch($filters['sort']) {
          case 'price_asc':
              $query .= " ORDER BY price ASC";
              break;
          case 'price_desc':
              $query .= " ORDER BY price DESC";
              break;
          case 'newest':
              $query .= " ORDER BY created_at DESC";
              break;
          case 'popular':
              $query .= " ORDER BY sold DESC";
              break;
          default:
              $query .= " ORDER BY product_id DESC";
      }
  } else {
      $query .= " ORDER BY product_id DESC";
  }
  
  // Add pagination
  $query .= " LIMIT $offset, $limit";
  
  // Execute the queries
  $result = $conn->query($query);
  $countResult = $conn->query($countQuery);
  
  $response = [
    'products' => [],
    'total_pages' => 0,
    'current_page' => $page,
    'total_results' => 0,
    'limit' => $limit,
  ];
  
  if ($result && $result->num_rows > 0) {
    $products = array();
    while ($row = $result->fetch_assoc()) {
      $product_id = $row['product_id'];
      $product_name = $row['name'];
      $product_image = $row['image'];
      $product_price = number_format($row['price'], 0, '', '.');
      $product_rating = $row['rating'];
      $product_category = $row['category'];
      $product_brand = $row['brand'];
      $product_stock = $row['stock'];

      if ($product_image != null) {
        $image_src = "../database/api/get-product-image.php?id=" . $product_id;
      } else {
        $image_src = '../assets/product_image/placeholder.png';
      }

      $products[] = [
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
    $response['products'] = $products;
  }
  
  // Get total count for pagination
  $totalCount = $countResult->fetch_assoc()['total'];
  $totalPages = ceil($totalCount / $limit);
  $response['total_pages'] = $totalPages;
  $response['total_results'] = $totalCount;
  
  $conn->close();
  
  return $response;
}

// /**
//  * Get a single product by ID
//  * 
//  * @param int $product_id Product ID
//  * @return array|null Product data or null if not found
//  */
// function getProductById($product_id) {
//   global $db_host, $db_user, $db_pass, $db_name;
  
//   $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
//   if ($conn->connect_error) {
//       die("Connection failed: " . $conn->connect_error);
//   }
  
//   $product_id = (int)$product_id;
//   $stmt = $conn->prepare("SELECT * FROM product WHERE product_id = ?");
//   $stmt->bind_param("i", $product_id);
//   $stmt->execute();
//   $result = $stmt->get_result();
  
//   if ($result->num_rows === 0) {
//       $stmt->close();
//       $conn->close();
//       return null;
//   }
  
//   $product = $result->fetch_assoc();
  
//   // Convert binary image data to base64 if needed
//   if ($product['image']) {
//     $image_src = "../database/api/get-product-image.php?id=" . $product_id;
//   } else {
//     $image_src = '../assets/product_image/placeholder.png';
//   }
  
//   $stmt->close();
//   $conn->close();
  
//   return $product;
// }

function getProductsByIds($product_ids) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Convert array to comma-separated string
  $ids = implode(',', array_map('intval', $product_ids));
  
  $stmt = $conn->prepare("SELECT * FROM product WHERE product_id IN ($ids)");
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
      return null;
  }
  
  $products = array();
  while ($row = $result->fetch_assoc()) {
      // Convert binary image data to base64 if needed
      if ($row['image']) {
        $image_src = "../database/api/get-product-image.php?id=" . $row['product_id'];
      } else {
        $image_src = '../assets/product_image/placeholder.png';
      }
      $products[] = $row;
  }
  
  $stmt->close();
  $conn->close();
  
  return $products;
}

function getProductCategories() {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  $categories = [];
  
  $result = $conn->query("SELECT DISTINCT category FROM product ORDER BY category");
  
  while ($row = $result->fetch_assoc()) {
      $categories[] = $row['category'];
  }
  
  $conn->close();
  
  return $categories;
}

function getProductBrands() {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  $brands = [];
  
  $result = $conn->query("SELECT DISTINCT brand FROM product ORDER BY brand");
  
  while ($row = $result->fetch_assoc()) {
      $brands[] = $row['brand'];
  }
  
  $conn->close();
  
  return $brands;
}

?>
