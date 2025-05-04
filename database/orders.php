<?php
require_once 'auth/config.php';

/**
 * Create a new order
 * 
 * @param int $cust_id Customer ID
 * @param float $total_order Total order amount before tax/delivery
 * @param float $delivery Delivery fee
 * @param float $tax Tax amount
 * @param float $final_price Final price after tax/delivery
 * @param array $order_items Items in the order (product_id, price, quantity)
 * @return int|string New order ID or false if failed
 */
function createOrder($cust_id, $total_order, $delivery, $tax, $final_price, $order_items) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Start transaction
  $conn->begin_transaction();
  
  try {
      // Check if stock is sufficient for all items
      $stmt = $conn->prepare("SELECT stock, name FROM product WHERE product_id = ?");
      foreach ($order_items as $item) {
        $stmt->bind_param("i", $item['product_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if ($product['stock'] < $item['quantity']) {
          throw new Exception("Insufficient stock for product: " . $item['product_name'] . ", " . $product['stock'] . " left.");
        }
      }

      // Insert order
      $stmt = $conn->prepare("INSERT INTO orders (cust_id, total_order, delivery, tax, final_price, status, created_at) VALUES (?, ?, ?, ?, ?, 'Waiting payment', NOW())");
      $stmt->bind_param("idddd", $cust_id, $total_order, $delivery, $tax, $final_price);
      
      if (!$stmt->execute()) {
          throw new Exception("Failed to create order");
      }
      
      $order_id = $conn->insert_id;
      
      // Insert order details
      $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
      
      foreach ($order_items as $item) {
        $stmt->bind_param("iidd", $order_id, $item['product_id'], $item['price'], $item['quantity']);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add order item");
        }
        
        // Update product stock and sold count
        $update_stmt = $conn->prepare("UPDATE product SET stock = stock - ?, sold = sold + ? WHERE product_id = ? AND stock >= ?");
        $update_stmt->bind_param("iidd", $item['quantity'], $item['quantity'], $item['product_id'], $item['quantity']);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update product stock");
        }
      }
      
      // Clear the user's cart
      $cart_stmt = $conn->prepare("DELETE FROM cart WHERE cust_id = ?");
      $cart_stmt->bind_param("i", $cust_id);
      $cart_stmt->execute();
      
      // Commit transaction
      $conn->commit();
      $stmt->close();
      $conn->close();
      
      return $order_id;
      
  } catch (Exception $e) {
      // Rollback transaction on error
      $conn->rollback();
      $conn->close();
      return $e->getMessage();
  }
}

/**
 * Update the status of an order
 * 
 * @param int $order_id Order ID
 * @param string $status New status
 * @return bool Success or failure
 */
function updateOrderStatus($order_id, $status) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
  $stmt->bind_param("si", $status, $order_id);
  $success = $stmt->execute();
  
  $stmt->close();
  $conn->close();
  
  return $success;
}

/**
 * Fetch orders by status
 * 
 * @param int $cust_id Customer ID
 * @param string $status Order status
 * @return array Orders matching the status
 */
function getOrdersByStatus($cust_id, $status) {
  global $db_host, $db_user, $db_pass, $db_name;

  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $status = '%' . $status . '%'; // Use LIKE for partial match

  $stmt = $conn->prepare("SELECT * FROM orders WHERE cust_id = ? AND status LIKE ? ORDER BY created_at DESC");
  $stmt->bind_param("is", $cust_id, $status);
  $stmt->execute();
  $result = $stmt->get_result();

  $orders = [];
  while ($row = $result->fetch_assoc()) {
    // Fetch order details join with image from product table
    $order_id = $row['order_id'];
    $stmt_items = $conn->prepare("SELECT od.*, p.name, p.image FROM order_details od JOIN product p ON od.product_id = p.product_id WHERE od.order_id = ?");
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    $items = [];
    while ($item = $result_items->fetch_assoc()) {
      if ($item['image'] != null) {
        $image_src = "../database/api/get-product-image.php?id=" . $item['product_id'];
      } else {
        $image_src = '../assets/product_image/placeholder.png';
      }
      $item['image'] = $image_src;
      $items[] = $item;
    }
    $stmt_items->close();
    $row['items'] = $items;

    // Fetch order status history
    $stmt_history = $conn->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC");
    $stmt_history->bind_param("i", $order_id);
    $stmt_history->execute();
    $result_history = $stmt_history->get_result();
    $history = [];
    while ($history_row = $result_history->fetch_assoc()) {
      $history[] = $history_row;
    }
    $stmt_history->close();
    $row['status_history'] = $history;

    $orders[] = $row;
  }

  $stmt->close();
  $conn->close();

  return $orders;
}
?>
