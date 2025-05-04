<?php
require_once '../auth/config.php';
session_start();
header('Content-Type: application/json');

// Ensure this script can only be accessed by admins if admin check is available
if (file_exists('../auth/admin-check.php')) {
  require_once '../auth/admin-check.php';
  if (function_exists('isAdminLoggedIn') && !isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
  }
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['error' => 'Database connection failed']);
  exit();
}

// Initialize with default values to ensure we always return something
$chartData = [
  'weekly_sales' => array_fill(0, 7, 0),
  'monthly_sales' => array_fill(0, 12, 0),
  'yearly_sales' => array_fill(0, 5, 0),
  'category_distribution' => [],
];

try {
  // Get weekly sales (current week)
  $result = $conn->query("
    SELECT 
      DAYOFWEEK(created_at) AS day,
      SUM(final_price) AS total
    FROM 
      orders
    WHERE 
      YEARWEEK(created_at, 1) = YEARWEEK(CURRENT_DATE, 1)
    GROUP BY 
      DAYOFWEEK(created_at)
    ORDER BY 
      day
  ");
  if ($result) {
    $weeklySales = array_fill(0, 12, 0); // Initialize with zeros for all months
    while ($row = $result->fetch_assoc()) {
        $day = (int)$row['day'] - 1; // Convert to 0-based index for array
        $weeklySales[$day] = (float)$row['total'];
    }
    $chartData['weekly_sales'] = $weeklySales;
  }
  
  // Get monthly sales (current year)
  $result = $conn->query("
    SELECT 
      MONTH(created_at) AS month,
      SUM(final_price) AS total
    FROM 
      orders
    WHERE 
      YEAR(created_at) = YEAR(CURRENT_DATE)
    GROUP BY 
      MONTH(created_at)
    ORDER BY 
      month
  ");
  
  if ($result) {
    $monthlySales = array_fill(0, 12, 0); // Initialize with zeros for all months
    while ($row = $result->fetch_assoc()) {
        $month = (int)$row['month'] - 1; // Convert to 0-based index for array
        $monthlySales[$month] = (float)$row['total'];
    }
    $chartData['monthly_sales'] = $monthlySales;
  }

  // Get yearly sales (last 5 years)
  $result = $conn->query("
    SELECT 
      YEAR(created_at) AS year,
      SUM(final_price) AS total
    FROM 
      orders
    WHERE 
      YEAR(created_at) >= YEAR(CURRENT_DATE) - 4
    GROUP BY 
      YEAR(created_at)
    ORDER BY 
      year DESC
  ");
  if ($result) {
    $yearlySales = array_fill(0, 5, 0); // Initialize with zeros for last 5 years
    while ($row = $result->fetch_assoc()) {
        $year = (int)$row['year'];
        $index = 4 - ($year - (int)date('Y')); // Calculate index for last 5 years
        if ($index >= 0 && $index < 5) {
            $yearlySales[$index] = (float)$row['total'];
        }
    }
    $chartData['yearly_sales'] = $yearlySales;
  }

  // Get product category distribution
  $result = $conn->query("
  SELECT 
      category,
      COUNT(*) AS count
  FROM 
      product
  GROUP BY 
      category
  ORDER BY 
      count DESC
  ");

  if ($result && $result->num_rows > 0) {
    $chartData['category_distribution'] = [];
    while ($row = $result->fetch_assoc()) {
      $chartData['category_distribution'][] = [
          'category' => ucfirst($row['category']),
          'count' => (int)$row['count']
      ];
    }
  }
} catch (Exception $e) {
  // Silent fail and use default data
}

$conn->close();

// Remove debug info in production
echo json_encode($chartData);
?>