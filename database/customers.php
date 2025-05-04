<?php
require_once 'auth/config.php';
/**
 * Add product to user's liked items
 * 
 * @param int $cust_id Customer ID
 * @param int $product_id Product ID
 * @return bool Success or failure
 */
function addToLiked($cust_id, $product_id) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // First get the current liked items for the customer
  $stmt = $conn->prepare("SELECT liked_product FROM customer WHERE cust_id = ?");
  $stmt->bind_param("i", $cust_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
      $stmt->close();
      $conn->close();
      return false;
  }
  
  $row = $result->fetch_assoc();
  $likedProducts = json_decode($row['liked_product'] ?? '[]', true);
  
  // Add the new product if not already in the list
  if (!in_array($product_id, $likedProducts)) {
      $likedProducts[] = $product_id;
      
      // Update the customer record
      $likedJson = json_encode($likedProducts);
      $stmt = $conn->prepare("UPDATE customer SET liked_product = ? WHERE cust_id = ?");
      $stmt->bind_param("si", $likedJson, $cust_id);
      $success = $stmt->execute();
      
      // Also increment the liked counter in the product table
      $stmt = $conn->prepare("UPDATE product SET liked = liked + 1 WHERE product_id = ?");
      $stmt->bind_param("i", $product_id);
      $stmt->execute();
      
      $stmt->close();
      $conn->close();
      
      return $success;
  }
  
  $stmt->close();
  $conn->close();
  
  return true; // Already in liked list
}

/**
 * Remove product from user's liked items
 * 
 * @param int $cust_id Customer ID
 * @param int $product_id Product ID
 * @return bool Success or failure
 */
function removeFromLiked($cust_id, $product_id) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // First get the current liked items for the customer
  $stmt = $conn->prepare("SELECT liked_product FROM customer WHERE cust_id = ?");
  $stmt->bind_param("i", $cust_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
      $stmt->close();
      $conn->close();
      return false;
  }
  
  $row = $result->fetch_assoc();
  $likedProducts = json_decode($row['liked_product'] ?? '[]', true);
  
  // Remove the product if it's in the list
  $index = array_search($product_id, $likedProducts);
  if ($index !== false) {
      array_splice($likedProducts, $index, 1);
      
      // Update the customer record
      $likedJson = json_encode($likedProducts);
      $stmt = $conn->prepare("UPDATE customer SET liked_product = ? WHERE cust_id = ?");
      $stmt->bind_param("si", $likedJson, $cust_id);
      $success = $stmt->execute();
      
      // Also decrement the liked counter in the product table
      $stmt = $conn->prepare("UPDATE product SET liked = liked - 1 WHERE product_id = ? AND liked > 0");
      $stmt->bind_param("i", $product_id);
      $stmt->execute();
      
      $stmt->close();
      $conn->close();
      
      return $success;
  }
  
  $stmt->close();
  $conn->close();
  
  return true; // Not in liked list
}

function getCustomerLikedProducts ($cust_id) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Get the liked products for the customer
  $stmt = $conn->prepare("SELECT liked_product FROM customer WHERE cust_id = ?");
  $stmt->bind_param("i", $cust_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
      return null;
  }
  
  $row = $result->fetch_assoc();
  return json_decode($row['liked_product'] ?? '[]', true);
}