<?php
require_once __DIR__ . '/../auth/config.php';

/**
 * Get orders with filtering and pagination
 * 
 * @param array $filters Filter criteria
 * @param int $page Current page number
 * @param int $limit Number of items per page
 * @return array Orders with pagination data
 */
function getAdminOrders($filters = [], $page = 1, $limit = 10) {
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
    
    // Filter by status
    if (!empty($filters['status'])) {
        $whereClause[] = "o.status = ?";
        $params[] = $filters['status'];
        $types .= 's';
    }
    
    // Filter by search term (order ID or customer name)
    if (!empty($filters['search'])) {
        $searchTerm = "%{$filters['search']}%";
        $whereClause[] = "(o.order_id LIKE ? OR c.full_name LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    // Filter by date range
    if (!empty($filters['date_range'])) {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $lastWeek = date('Y-m-d', strtotime('-7 days'));
        $lastMonth = date('Y-m-d', strtotime('-30 days'));
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');
        $firstDayOfLastMonth = date('Y-m-01', strtotime('-1 month'));
        $lastDayOfLastMonth = date('Y-m-t', strtotime('-1 month'));
        
        switch ($filters['date_range']) {
            case 'today':
                $whereClause[] = "DATE(o.created_at) = ?";
                $params[] = $today;
                $types .= 's';
                break;
            case 'yesterday':
                $whereClause[] = "DATE(o.created_at) = ?";
                $params[] = $yesterday;
                $types .= 's';
                break;
            case 'last7days':
                $whereClause[] = "o.created_at >= ?";
                $params[] = $lastWeek;
                $types .= 's';
                break;
            case 'last30days':
                $whereClause[] = "o.created_at >= ?";
                $params[] = $lastMonth;
                $types .= 's';
                break;
            case 'thisMonth':
                $whereClause[] = "o.created_at BETWEEN ? AND ?";
                $params[] = $firstDayOfMonth;
                $params[] = $lastDayOfMonth;
                $types .= 'ss';
                break;
            case 'lastMonth':
                $whereClause[] = "o.created_at BETWEEN ? AND ?";
                $params[] = $firstDayOfLastMonth;
                $params[] = $lastDayOfLastMonth;
                $types .= 'ss';
                break;
        }
    }
    
    // Combine where clauses
    $whereClauseStr = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';
    
    // Count total records for pagination
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM orders o
        JOIN customer c ON o.cust_id = c.cust_id
        $whereClauseStr
    ";
    
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
    
    // Get orders with pagination
    $query = "
        SELECT 
            o.order_id,
            o.final_price,
            o.status,
            o.created_at,
            c.full_name AS customer_name
        FROM 
            orders o
        JOIN 
            customer c ON o.cust_id = c.cust_id
        $whereClauseStr
        ORDER BY 
            o.order_id ASC
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
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
    $countStmt->close();
    $conn->close();
    
    return [
        'orders' => $orders,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'total_records' => $totalRecords
    ];
}

/**
 * Get order details by ID, including customer info and products
 * 
 * @param int $orderId Order ID
 * @return array|false Order details or false if not found
 */
function getOrderDetails($orderId) {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get order and customer details
    $stmt = $conn->prepare("
        SELECT 
            o.order_id,
            o.cust_id,
            o.total_order,
            o.delivery,
            o.tax,
            o.final_price,
            o.status,
            o.created_at,
            c.full_name AS customer_name,
            c.email
        FROM 
            orders o
        JOIN 
            customer c ON o.cust_id = c.cust_id
        WHERE 
            o.order_id = ?
    ");
    
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return false;
    }
    
    $orderDetails = $result->fetch_assoc();
    
    // Get order items
    $stmt = $conn->prepare("
        SELECT 
            od.detail_id,
            od.product_id,
            od.price,
            od.quantity,
            p.name,
            CASE
                WHEN p.image IS NOT NULL THEN CONCAT('../database/api/get-product-image.php?id=', p.product_id)
                ELSE '../assets/placeholder/product-placeholder.png'
            END AS image_url
        FROM 
            order_details od
        JOIN 
            product p ON od.product_id = p.product_id
        WHERE 
            od.order_id = ?
    ");
    
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $itemsResult = $stmt->get_result();
    
    $orderDetails['items'] = [];
    while ($item = $itemsResult->fetch_assoc()) {
        $orderDetails['items'][] = $item;
    }
    
    // Get status history (if available)
    $stmt = $conn->prepare("
        SELECT 
            status,
            notes,
            created_at AS date
        FROM 
            order_status_history
        WHERE 
            order_id = ?
        ORDER BY 
            created_at DESC
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $historyResult = $stmt->get_result();
        
        $orderDetails['status_history'] = [];
        while ($history = $historyResult->fetch_assoc()) {
            $orderDetails['status_history'][] = $history;
        }
        $stmt->close();
    } else {
        // If the table doesn't exist or query fails, use a dummy history entry with the current status
        $orderDetails['status_history'] = [
            [
                'status' => $orderDetails['status'],
                'notes' => 'Order created',
                'date' => $orderDetails['created_at']
            ]
        ];
    }
    
    $conn->close();
    
    return $orderDetails;
}

/**
 * Update order status
 * 
 * @param int $orderId Order ID
 * @param string $status New status
 * @param string $notes Optional notes about the status update
 * @return bool True on success, false on failure
 */
function updateOrderStatus($orderId, $status, $notes = '') {
    global $db_host, $db_user, $db_pass, $db_name;
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $throttleKey = "order_update_{$orderId}";
    $currentTime = time();
    
    if (isset($_SESSION[$throttleKey]) && ($currentTime - $_SESSION[$throttleKey]) < 2) {
      throw new Exception('Please wait before updating the order status again.');
    }
    
    $_SESSION[$throttleKey] = $currentTime;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $orderId);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $orderId, $status, $notes);
        $stmt->execute();
        
        $conn->commit();
        $stmt->close();
        $conn->close();
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $conn->close();
        return false;
    }
}