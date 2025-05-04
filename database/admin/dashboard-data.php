<?php
require_once __DIR__ . '/../auth/config.php';

/**
 * Get dashboard statistics
 * 
 * @return array Dashboard statistics
 */
function getDashboardStats() {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Initialize stats array
    $stats = [
        'total_sales' => 0,
        'sales_increase' => 0,
        'total_orders' => 0,
        'orders_change' => 0,
        'total_customers' => 0,
        'customers_increase' => 0,
        'total_products' => 0,
        'out_of_stock' => 0,
    ];
    
    // Get total sales
    $result = $conn->query("SELECT SUM(final_price) AS total FROM orders");
    if ($row = $result->fetch_assoc()) {
        $stats['total_sales'] = $row['total'] ?: 0;
    }
    
    // Get sales increase (compare current month to previous month)
    $result = $conn->query("
        SELECT 
            (SELECT SUM(final_price) FROM orders 
             WHERE YEAR(created_at) = YEAR(CURRENT_DATE) 
             AND MONTH(created_at) = MONTH(CURRENT_DATE)) AS current_month,
            (SELECT SUM(final_price) FROM orders 
             WHERE YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) 
             AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) AS previous_month
    ");
    
    if ($row = $result->fetch_assoc()) {
        $currentMonth = $row['current_month'] ?: 0;
        $previousMonth = $row['previous_month'] ?: 1; // Avoid division by zero
        
        $stats['sales_increase'] = round(($currentMonth - $previousMonth) / $previousMonth * 100, 1);
    }
    
    // Get total orders
    $result = $conn->query("SELECT COUNT(*) AS total FROM orders");
    if ($row = $result->fetch_assoc()) {
        $stats['total_orders'] = $row['total'];
    }
    
    // Get orders change (compare current month to previous month)
    $result = $conn->query("
        SELECT 
            (SELECT COUNT(*) FROM orders 
             WHERE YEAR(created_at) = YEAR(CURRENT_DATE) 
             AND MONTH(created_at) = MONTH(CURRENT_DATE)) AS current_month,
            (SELECT COUNT(*) FROM orders 
             WHERE YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) 
             AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) AS previous_month
    ");
    
    if ($row = $result->fetch_assoc()) {
        $currentMonth = $row['current_month'] ?: 0;
        $previousMonth = $row['previous_month'] ?: 1; // Avoid division by zero
        
        $stats['orders_change'] = round(($currentMonth - $previousMonth) / $previousMonth * 100, 1);
    }
    
    // Get total customers
    $result = $conn->query("SELECT COUNT(*) AS total FROM customer WHERE active = 1");
    if ($row = $result->fetch_assoc()) {
        $stats['total_customers'] = $row['total'];
    }
    
    // Get customers increase (compare current month to previous month)
    $result = $conn->query("
      SELECT 
        (SELECT COUNT(*) FROM customer 
          WHERE YEAR(created_at) = YEAR(CURRENT_DATE) 
          AND MONTH(created_at) = MONTH(CURRENT_DATE) AND (active = 1)) AS current_month,
        (SELECT COUNT(*) FROM customer 
          WHERE YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) 
          AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) AND (active = 1)) AS previous_month
    ");
    
    if ($row = $result->fetch_assoc()) {
      $currentMonth = $row['current_month'] ?: 0;
      $previousMonth = $row['previous_month'] ?: 1; // Avoid division by zero
      
      $stats['customers_increase'] = round(($currentMonth - $previousMonth) / $previousMonth * 100, 1);
    }
    
    // Get total products and out of stock count
    $result = $conn->query("
      SELECT COUNT(*) AS total, 
        (SELECT COUNT(*) FROM product WHERE stock = 0) AS out_of_stock 
      FROM product
    ");
    
    if ($row = $result->fetch_assoc()) {
      $stats['total_products'] = $row['total'];
      $stats['out_of_stock'] = $row['out_of_stock'];
    }
    
    $conn->close();
    return $stats;
}

/**
 * Get recent orders for dashboard
 * 
 * @param int $limit Number of orders to get
 * @return array Recent orders
 */
function getRecentOrders($limit = 5) {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $orders = [];
    
    $stmt = $conn->prepare("
        SELECT 
            o.order_id,
            o.final_price AS amount,
            o.status,
            o.created_at AS order_date,
            c.full_name AS customer_name
        FROM 
            orders o
        JOIN 
            customer c ON o.cust_id = c.cust_id
        ORDER BY 
            o.created_at DESC
        LIMIT ?
    ");
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $orders;
}

/**
 * Get top selling products for dashboard
 * 
 * @param int $limit Number of products to get
 * @return array Top selling products
 */
function getTopSellingProducts($limit = 5) {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $products = [];
    
    $stmt = $conn->prepare("
        SELECT 
            p.product_id,
            p.name,
            p.price,
            p.sold,
            p.price * p.sold AS revenue,
            CASE
                WHEN p.image IS NOT NULL THEN CONCAT('../database/api/get-product-image.php?id=', p.product_id)
                ELSE '../assets/product_image/placeholder.png'
            END AS image_url
        FROM 
            product p
        ORDER BY 
            p.sold DESC, revenue DESC
        LIMIT ?
    ");
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $products;
}

/**
 * Get low stock products for dashboard
 * 
 * @param int $limit Number of products to get
 * @param int $threshold Low stock threshold
 * @return array Low stock products
 */
function getLowStockProducts($limit = 5, $threshold = 10) {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $products = [];
    
    $stmt = $conn->prepare("
        SELECT 
            p.product_id,
            p.name,
            p.stock,
            CASE
                WHEN p.image IS NOT NULL THEN CONCAT('../database/api/get-product-image.php?id=', p.product_id)
                ELSE '../assets/product_image/placeholder.png'
            END AS image_url
        FROM 
            product p
        WHERE 
            p.stock <= ?
        ORDER BY 
            p.stock ASC
        LIMIT ?
    ");
    
    $stmt->bind_param("ii", $threshold, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $products;
}