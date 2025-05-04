<?php
require_once __DIR__ . '/../auth/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

/**
 * Get customers with filtering and pagination
 * 
 * @param array $filters Filter criteria
 * @param int $page Current page number
 * @param int $limit Number of items per page
 * @return array Customers with pagination data
 */
function getAdminCustomers($filters = [], $page = 1, $limit = 10) {
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
    
    // Filter by active status
    if (!empty($filters['filter'])) {
        switch ($filters['filter']) {
            case 'active':
                $whereClause[] = "c.active = 1";
                break;
            case 'inactive':
                $whereClause[] = "c.active = 0";
                break;
        }
    }
    
    // Filter by search term
    if (!empty($filters['search'])) {
        $searchTerm = "%{$filters['search']}%";
        $whereClause[] = "(c.full_name LIKE ? OR c.email LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    // Combine where clauses
    $whereClauseStr = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';
    
    // Count total records for pagination
    $countQuery = "SELECT COUNT(*) as total FROM customer c $whereClauseStr";
    
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
    
    // Get customers with pagination
    $query = "
        SELECT 
            c.cust_id,
            c.full_name,
            c.email,
            c.active,
            c.created_at,
            COUNT(DISTINCT o.order_id) AS order_count
        FROM 
            customer c
        LEFT JOIN 
            orders o ON c.cust_id = o.cust_id
        $whereClauseStr
        GROUP BY 
            c.cust_id
        ORDER BY 
            c.cust_id ASC
        LIMIT ?, ?
    ";
    
    $stmt = $conn->prepare($query);
    
    // Add pagination params
    $params[] = $offset;
    $params[] = $limit;
    $types .= 'ii';
    
    // Bind all params
    $stmt->bind_param($types, ...$params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    
    $stmt->close();
    $countStmt->close();
    $conn->close();
    
    return [
        'customers' => $customers,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'total_records' => $totalRecords
    ];
}

/**
 * Get customer details by ID
 * 
 * @param int $customerId Customer ID
 * @return array|false Customer details or false if not found
 */
function getCustomerDetails($customerId) {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get customer basic info
    $stmt = $conn->prepare("
        SELECT 
            c.cust_id,
            c.email,
            c.full_name,
            c.birth_date,
            c.active,
            c.created_at,
            COUNT(DISTINCT o.order_id) AS order_count,
            IFNULL(SUM(o.final_price), 0) AS total_spent
        FROM 
            customer c
        LEFT JOIN 
            orders o ON c.cust_id = o.cust_id
        WHERE 
            c.cust_id = ?
        GROUP BY 
            c.cust_id
    ");
    
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return false;
    }
    
    $customer = $result->fetch_assoc();
    
    $stmt = $conn->prepare("
        SELECT 
            o.order_id,
            o.final_price,
            o.status,
            o.created_at
        FROM 
            orders o
        WHERE 
            o.cust_id = ?
        ORDER BY 
            o.created_at DESC
        LIMIT 5
    ");
    
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $ordersResult = $stmt->get_result();
    
    $customer['recent_orders'] = [];
    while ($order = $ordersResult->fetch_assoc()) {
        $customer['recent_orders'][] = $order;
    }
    
    $stmt->close();
    $conn->close();
    
    return $customer;
}

/**
 * Update customer status (activate/deactivate)
 * 
 * @param int $customerId Customer ID
 * @param int $active Active status (1 = active, 0 = inactive)
 * @return bool True if successful, false otherwise
 */
function updateCustomerStatus($customerId, $active) {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $stmt = $conn->prepare("UPDATE customer SET active = ? WHERE cust_id = ?");
    $stmt->bind_param("ii", $active, $customerId);
    $stmt->execute();
    
    $success = $stmt->affected_rows > 0;
    $stmt->close();
    $conn->close();
    
    return $success;
}

/**
 * Send email to customer (functionality would depend on your mail system)
 * 
 * @param int $customerId Customer ID
 * @param string $subject Email subject
 * @param string $message Email message
 * @return bool True if successful, false otherwise
 */
function sendEmailToCustomer($customerId, $subject, $message) {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get customer email
    $stmt = $conn->prepare("SELECT email, full_name FROM customer WHERE cust_id = ?");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return false;
    }
    
    $customer = $result->fetch_assoc();
    $email = $customer['email'];
    $fullName = $customer['full_name'];
    
    $stmt->close();
    $conn->close();

    global $email_host, $email_port, $email_username, $email_password, $email_from, $activation_url;
    $mail = new PHPMailer();
    
    try {
      // Server settings
      $mail->isSMTP();
      $mail->Host = $email_host;
      $mail->SMTPAuth = true;
      $mail->Username = $email_username;
      $mail->Password = $email_password;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = $email_port;
      
      // Recipients
      $mail->setFrom($email_username, 'HypeKicks');
      $mail->addAddress($email, $fullName);
      
      // Content
      $mail->isHTML(true);
      $mail->Subject = $subject;
      
      $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
          <div style="padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
            <h2>Hi ' . $fullName . '</h2>
            <p>'. $message .'</p>
          </div>
        </div>
      ';
      
      $mail->AltBody = $message .
                      "Regards,\nHypeKicks Team";
      
      return $mail->send();
      
    } catch (Exception $e) {
      error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
      return false;
    }
}
