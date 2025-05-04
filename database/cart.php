<?php
require_once 'auth/config.php';

function addToCart($cust_id, $product_id) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $increment = 1;
  
  // Check if the product is already in the cart
  $stmt = $conn->prepare("SELECT quantity FROM cart WHERE cust_id = ? AND product_id = ?");
  $stmt->bind_param("ii", $cust_id, $product_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
      // Product is already in the cart, update the quantity
      $row = $result->fetch_assoc();
      $new_quantity = $row['quantity'] + $increment;
      
      // Update the cart with the new quantity
      $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cust_id = ? AND product_id = ?");
      $stmt->bind_param("iii", $new_quantity, $cust_id, $product_id);
      if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return true; // Successfully updated quantity
      } else {
        $stmt->close();
        $conn->close();
        return false; // Failed to update quantity
      }
  } else {
      // Product is not in the cart, insert a new record
      $stmt = $conn->prepare("INSERT INTO cart (cust_id, product_id, quantity) VALUES (?, ?, ?)");
      $stmt->bind_param("iii", $cust_id, $product_id, $increment);
      if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return true; // Successfully added to cart
      } else {
        $stmt->close();
        $conn->close();
        return false; // Failed to add to cart
      }
  }
}

function removeFromCart($cust_id, $product_id) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Delete the product from the cart
  $stmt = $conn->prepare("DELETE FROM cart WHERE cust_id = ? AND product_id = ?");
  $stmt->bind_param("ii", $cust_id, $product_id);
  if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    return true; // Successfully removed from cart
  } else {
    $stmt->close();
    $conn->close();
    return false; // Failed to remove from cart
  }
  
}

function substractFromCart($cust_id, $product_id) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Check if the product is already in the cart
  $stmt = $conn->prepare("SELECT quantity FROM cart WHERE cust_id = ? AND product_id = ?");
  $stmt->bind_param("ii", $cust_id, $product_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
      // Product is already in the cart, update the quantity
      $row = $result->fetch_assoc();
      if ($row['quantity'] > 1) {
          // Decrease the quantity by 1
          $new_quantity = $row['quantity'] - 1;
          
          // Update the cart with the new quantity
          $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cust_id = ? AND product_id = ?");
          $stmt->bind_param("iii", $new_quantity, $cust_id, $product_id);
          if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return true; // Successfully updated quantity
          } else {
            $stmt->close();
            $conn->close();
            return false; // Failed to update quantity
          }
      } else {
          // If quantity is 1 or less, remove the item from the cart
          $stmt->close();
          $conn->close();
          return removeFromCart($cust_id, $product_id);
      }
  } else {
    $stmt->close();
    $conn->close();
    return false; // Product not found in cart
  }
}

function getCartItems($cust_id) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Get all items in the cart for the customer
  $stmt = $conn->prepare("SELECT cart.product_id, cart.quantity, product.name, product.price, product.category, product.brand, product.image, product.stock FROM cart JOIN product ON cart.product_id = product.product_id WHERE cart.cust_id = ?");
  $stmt->bind_param("i", $cust_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  $cart_items = array();
  
  while ($row = $result->fetch_assoc()) {
    $cart_items[] = array(
      'product_id' => $row['product_id'],
      'quantity' => $row['quantity'],
      'product_name' => $row['name'],
      'price' => $row['price'],
      'category' => $row['category'],
      'brand' => $row['brand'],
      'stock' => $row['stock'],
      'image_src' => $row['image'] != null ? "../database/api/get-product-image.php?id=" . $row['product_id'] : '../assets/product_image/placeholder.png'
    );
  }
  
  return $cart_items;
}

function getCartCount($cust_id) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  
  // Get the count of items in the cart for the customer
  $stmt = $conn->prepare("SELECT COUNT(product_id) AS total_item FROM cart WHERE cust_id = ?");
  $stmt->bind_param("i", $cust_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($row = $result->fetch_assoc()) {
    return (int)$row['total_item'];
  } else {
    return 0;
  }
}

?>