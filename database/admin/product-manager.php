<?php
require_once __DIR__ . '/../auth/config.php';

/**
 * Get products with filtering and pagination for admin
 * 
 * @param array $filters Filter criteria
 * @param int $page Current page number
 * @param int $limit Number of items per page
 * @return array Products with pagination data
 */
function getAdminProducts($filters = [], $page = 1, $limit = 10) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Calculate offset
  $offset = ($page - 1) * $limit;
  
  // Build the WHERE clause based on filters
  $whereClause = [];
  $params = [];
  $types = '';
  
  // Filter by category
  if (!empty($filters['category'])) {
      $whereClause[] = "category = ?";
      $params[] = $filters['category'];
      $types .= 's';
  }
  
  // Filter by stock status
  if (!empty($filters['filter'])) {
      switch ($filters['filter']) {
          case 'in-stock':
              $whereClause[] = "stock > 10";
              break;
          case 'low-stock':
              $whereClause[] = "stock > 0 AND stock <= 10";
              break;
          case 'out-of-stock':
              $whereClause[] = "stock = 0";
              break;
      }
  }
  
  // Filter by search term
  if (!empty($filters['search'])) {
      $searchTerm = "%{$filters['search']}%";
      $whereClause[] = "(name LIKE ? OR description LIKE ?)";
      $params[] = $searchTerm;
      $params[] = $searchTerm;
      $types .= 'ss';
  }
  
  // Combine where clauses
  $whereClauseStr = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';
  
  // Count total records for pagination
  $countQuery = "SELECT COUNT(*) as total FROM product $whereClauseStr";
  
  $countStmt = $conn->prepare($countQuery);
  
  // Bind params for count query if any
  if (!empty($params)) {
      $countStmt->bind_param($types, ...$params);
  }
  
  $countStmt->execute();
  $countResult = $countStmt->get_result();
  $row = $countResult->fetch_assoc();
  $totalRecords = $row['total'];
  $totalPages = ceil($totalRecords / $limit);
  
  // Get products with pagination
  $query = "
      SELECT 
          product_id,
          name,
          category,
          price,
          stock,
          sold,
          brand,
          CASE
              WHEN image IS NOT NULL THEN CONCAT('../database/api/get-product-image.php?id=', product_id)
              ELSE '../assets/product_image/placeholder.png'
          END AS image_url
      FROM 
          product
      $whereClauseStr
      ORDER BY 
          product_id ASC
  ";
  
  $stmt = $conn->prepare($query);
  
  // Add pagination params
  // $params[] = $offset;
  // $params[] = $limit;
  // $types .= 'ii';
  
  // // Bind all params
  // $stmt->bind_param($types, ...$params);
  
  $stmt->execute();
  $result = $stmt->get_result();
  
  $products = [];
  while ($row = $result->fetch_assoc()) {
      $products[] = $row;
  }
  
  $stmt->close();
  $countStmt->close();
  $conn->close();
  
  return [
    'products' => $products,
    'total_pages' => $totalPages,
    'current_page' => $page,
    'total_records' => $totalRecords
  ];
}

/**
 * Get product details by ID
 * 
 * @param int $productId Product ID
 * @return array|false Product details or false if not found
 */
function getProductDetails($productId) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  $stmt = $conn->prepare("
      SELECT 
          product_id,
          name,
          description,
          category,
          price,
          stock,
          sold,
          brand,
          CASE
              WHEN image IS NOT NULL THEN CONCAT('../database/api/get-product-image.php?id=', product_id)
              ELSE '../assets/product_image/placeholder.png'
          END AS image_url
      FROM 
          product
      WHERE 
          product_id = ?
  ");
  
  $stmt->bind_param("i", $productId);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
      $stmt->close();
      $conn->close();
      return false;
  }
  
  $product = $result->fetch_assoc();
  $stmt->close();
  $conn->close();
  
  return $product;
}

/**
 * Add a new product
 * 
 * @param array $productData Product data
 * @param array $imageData Optional image data
 * @return int|false New product ID or false if failed
 */
function addProduct($productData, $imageData = null) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Prepare product data
  $name = $productData['name'];
  $description = $productData['description'];
  $category = $productData['category'];
  $price = $productData['price'];
  $stock = $productData['stock'];
  $brand = $productData['brand'];
  $image = $imageData;
  
  // Start transaction
  $conn->begin_transaction();
  
  try {
      // Insert product without image first
      if ($image === null) {
          $stmt = $conn->prepare("
              INSERT INTO product 
                  (name, description, category, brand, price, stock, created_at) 
              VALUES 
                  (?, ?, ?, ?, ?, ?, NOW())
          ");
          $stmt->bind_param("ssssdi", $name, $description, $category, $brand, $price, $stock);
      } else {
          // Insert product with image
          $stmt = $conn->prepare("
              INSERT INTO product 
                  (name, description, category, brand, price, stock, image, created_at) 
              VALUES 
                  (?, ?, ?, ?, ?, ?, ?, NOW())
          ");
          $stmt->bind_param("ssssdis", $name, $description, $category, $brand, $price, $stock, $image);
      }
      
      $stmt->execute();
      
      if ($stmt->affected_rows === 0) {
          throw new Exception("Failed to add product");
      }
      
      $productId = $conn->insert_id;
      
      // Commit transaction
      $conn->commit();
      $stmt->close();
      $conn->close();
      
      return $productId;
  } catch (Exception $e) {
      $conn->rollback();
      $conn->close();
      return false;
  }
}

/**
 * Update an existing product
 * 
 * @param int $productId Product ID
 * @param array $productData Product data
 * @param array $imageData Optional image data
 */
function updateProduct($productId, $productData, $imageData = null, $imageChanged) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Prepare product data
  $name = $productData['name'];
  $description = $productData['description'];
  $category = $productData['category'];
  $price = $productData['price'];
  $stock = $productData['stock'];
  $brand = $productData['brand'];
  
  // Start transaction
  $conn->begin_transaction();
  
  try {
      // Update product without image
      if ($imageData === null && $imageChanged == 0) {
          $stmt = $conn->prepare("
              UPDATE product 
              SET 
                  name = ?,
                  description = ?,
                  category = ?,
                  brand = ?,
                  price = ?,
                  stock = ?,
                  updated_at = NOW()
              WHERE 
                  product_id = ?
          ");
          $stmt->bind_param("ssssdii", $name, $description, $category, $brand, $price, $stock, $productId);
      } else {
          // Update product with image
          $stmt = $conn->prepare("
              UPDATE product 
              SET 
                  name = ?,
                  description = ?,
                  category = ?,
                  brand = ?,
                  price = ?,
                  stock = ?,
                  image = ?,
                  updated_at = NOW()
              WHERE 
                  product_id = ?
          ");
          $stmt->bind_param("ssssdisi", $name, $description, $category, $brand, $price, $stock, $imageData, $productId);
      }
      
      $stmt->execute();
      
      // Commit transaction
      $conn->commit();
      $stmt->close();
      $conn->close();
      
      return true;
  } catch (Exception $e) {
      $conn->rollback();
      $conn->close();
      return $e->getMessage();
  }
}

/**
 * Delete a product
 * 
 * @param int $productId Product ID
 * @return bool True if successful, false otherwise
 */
function deleteProduct($productId) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Check if product is used in any orders
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_details WHERE product_id = ?");
  $stmt->bind_param("i", $productId);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  
  // If product is in orders, don't allow deletion
  if ($row['count'] > 0) {
      $stmt->close();
      $conn->close();
      return false;
  }
  
  // Delete product
  $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
  $stmt->bind_param("i", $productId);
  $stmt->execute();
  
  $success = $stmt->affected_rows > 0;
  $stmt->close();
  $conn->close();
  
  return $success;
}

/**
 * Get all product categories
 * 
 * @return array List of categories
 */
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

function getStockClass($stock) {
  if (!empty($filters['filter'])) {
    if ($stock > 10) {
      return 'in-stock';
    } elseif ($stock > 0 && $stock <= 10) {
      return 'low-stock';
    } else {
      return 'out-of-stock';
    }
  }
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