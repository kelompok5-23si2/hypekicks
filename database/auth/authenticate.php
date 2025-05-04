<?php
require_once 'config.php';

/**
 * Authenticate user with email and password
 * 
 * @param string $email User's email
 * @param string $password User's password
 * @return array|false User data array on success, false on failure
 */
function authenticateUser($email, $password) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  // Prepare SQL statement
  $stmt = $conn->prepare("SELECT cust_id, full_name, pass, active FROM customer WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    return false; // No user found with this email
  }
  
  $user = $result->fetch_assoc();
  
  // Verify password
  if (!password_verify($password, $user['pass'])) {
    $stmt->close();
    $conn->close();
    return false; // Password doesn't match
  }
  
  // Check if account is activated
  if ($user['active'] != 1) {
    $stmt->close();
    $conn->close();
    return ['activated' => false, 'cust_id' => $user['cust_id']]; // Account not activated
  }
  
  // Authentication successful
  $stmt->close();
  $conn->close();
  
  return [
    'authenticated' => true,
    'cust_id' => $user['cust_id'],
    'full_name' => $user['full_name'],
  ];
}

/**
 * Create a new user account
 * 
 * @param string $fullname User's full name
 * @param string $email User's email
 * @param string $password User's password
 * @param string $birthdate User's birth date
 * @return array|false User ID on success or status message, false on failure
 */
function createUser($fullname, $email, $password, $birthdate) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  // Check if email already exists
  $stmt = $conn->prepare("SELECT cust_id, active FROM customer WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if ($user['active'] == 1) {
      // Already registered and active
      $stmt->close();
      $conn->close();
      return ['exists' => true, 'active' => true];
    } else {
      // Already registered but not activated
      $stmt->close();
      $conn->close();
      return ['exists' => true, 'active' => false, 'cust_id' => $user['cust_id']];
    }
  }
  
  // Hash the password
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
  
  // Insert new user
  $stmt = $conn->prepare("INSERT INTO customer (full_name, email, pass, birth_date, active) VALUES (?, ?, ?, ?, 0)");
  $stmt->bind_param("ssss", $fullname, $email, $hashedPassword, $birthdate);
  
  if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    return false; // Failed to create user
  }
  
  $userId = $conn->insert_id;
  $stmt->close();
  $conn->close();
  
  return ['created' => true, 'cust_id' => $userId];
}

/**
 * Create an activation token for a user
 * 
 * @param int $custId Customer ID
 * @return string|false Token on success, false on failure
 */
function createActivationToken($custId) {
  global $db_host, $db_user, $db_pass, $db_name;
  global $token_expiry;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  // Generate random token
  $token = bin2hex(random_bytes(32));
  
  // Calculate expiration time
  $expiresAt = date('Y-m-d H:i:s', time() + $token_expiry);
  
  // Invalidate any existing tokens for this user
  $stmt = $conn->prepare("UPDATE activation_tokens SET status = 'unavailable' WHERE cust_id = ? AND status = 'available'");
  $stmt->bind_param("i", $custId);
  $stmt->execute();
  
  // Insert new token
  $stmt = $conn->prepare("INSERT INTO activation_tokens (cust_id, token, expires_at, status) VALUES (?, ?, ?, 'available')");
  $stmt->bind_param("iss", $custId, $token, $expiresAt);
  
  if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    return false;
  }
  
  $stmt->close();
  $conn->close();
  
  return $token;
}

/**
 * Verify an activation token
 * 
 * @param string $token Activation token
 * @return int|false User ID if token valid, false otherwise
 */
function verifyActivationToken($token) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $now = date('Y-m-d H:i:s');
  
  // Check for valid token
  $stmt = $conn->prepare("SELECT t.cust_id FROM activation_tokens t WHERE t.token = ? AND t.expires_at > ? AND t.status = 'available'");
  $stmt->bind_param("ss", $token, $now);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    return false;
  }
  
  $tokenData = $result->fetch_assoc();
  $custId = $tokenData['cust_id'];
  
  // Mark token as used
  $stmt = $conn->prepare("UPDATE activation_tokens SET status = 'activated' WHERE token = ?");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  
  // Activate the user's account
  $stmt = $conn->prepare("UPDATE customer SET active = 1 WHERE cust_id = ?");
  $stmt->bind_param("i", $custId);
  $stmt->execute();
  
  $stmt->close();
  $conn->close();
  
  return $custId;
}
?>
