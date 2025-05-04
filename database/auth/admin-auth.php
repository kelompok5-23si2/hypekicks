<?php
require_once 'config.php';

function authenticateAdmin($username, $password) {
  global $db_host, $db_user, $db_pass, $db_name;
  
  $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  // Prepare SQL statement
  $stmt = $conn->prepare("SELECT admin_id, admin_name, pass, role FROM admin WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    return ['authenticated' => false, 'message' => 'Invalid username'];
  }
  
  $admin = $result->fetch_assoc();
  
  // Verify password
  if (!password_verify($password, $admin['pass'])) {
    $stmt->close();
    $conn->close();
    return ['authenticated' => false, 'message' => 'Invalid password'];
  }
  
  // Authentication successful
  $stmt->close();
  $conn->close();
  
  return [
    'authenticated' => true,
    'admin_id' => $admin['admin_id'],
    'admin_name' => $admin['admin_name'],
    'admin_role' => $admin['role']
  ];
}