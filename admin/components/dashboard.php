<?php
require_once '../../database/admin/dashboard-data.php';

// Get dashboard statistics
$stats = getDashboardStats();
$recentOrders = getRecentOrders();
$topProducts = getTopSellingProducts();
$lowStockProducts = getLowStockProducts();
?>

<div class="dashboard-container">
  <!-- Stats Overview Cards -->
  <div class="stats-cards">
    <div class="stat-card">
      <div class="stat-icon sales-icon">
        <img src="../assets/icon/wallet-fill-orange.png" alt="Sales">
      </div>
      <div class="stat-info">
        <h3>Total Sales</h3>
        <p class="stat-value">Rp <?php echo number_format($stats['total_sales'], 0, '', '.'); ?></p>
        <p class="stat-change positive">+<?php echo $stats['sales_increase']; ?>%</p>
      </div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon orders-icon">
        <img src="../assets/icon/shopping-cart-2-fill-blue.png" alt="Orders">
      </div>
      <div class="stat-info">
        <h3>Orders</h3>
        <p class="stat-value"><?php echo $stats['total_orders']; ?></p>
        <p class="stat-change <?php echo ($stats['orders_change'] >= 0) ? 'positive' : 'negative'; ?>">
          <?php echo ($stats['orders_change'] >= 0) ? '+' : ''; ?><?php echo $stats['orders_change']; ?>%
        </p>
      </div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon customers-icon">
        <img src="../assets/icon/user-3-fill-green.png" alt="Customers">
      </div>
      <div class="stat-info">
        <h3>Customers</h3>
        <p class="stat-value"><?php echo $stats['total_customers']; ?></p>
        <p class="stat-change positive">+<?php echo $stats['customers_increase']; ?>%</p>
      </div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon products-icon">
        <img src="../assets/icon/box-3-fill-purple.png" alt="Products">
      </div>
      <div class="stat-info">
        <h3>Products</h3>
        <p class="stat-value"><?php echo $stats['total_products']; ?></p>
        <p class="stat-secondary"><?php echo $stats['out_of_stock']; ?> out of stock</p>
      </div>
    </div>
  </div>
  
  <!-- Charts Section -->
  <div class="charts-container">
    <div class="chart-card">
      <div class="chart-header">
        <h3>Sales Overview</h3>
        <div class="chart-filters">
          <button class="chart-filter active" data-period="weekly">Weekly</button>
          <button class="chart-filter" data-period="monthly">Monthly</button>
          <button class="chart-filter" data-period="yearly">Yearly</button>
        </div>
      </div>
      <div class="chart-body">
        <canvas id="salesChart"></canvas>
      </div>
    </div>
    
    <div class="chart-card">
      <div class="chart-header">
        <h3>Category Distribution</h3>
      </div>
      <div class="chart-body">
        <canvas id="categoryChart"></canvas>
      </div>
    </div>
  </div>
  
  <!-- Recent Orders -->
  <div class="table-card">
    <div class="table-header">
      <h3>Recent Orders</h3>
      <a href="?page=orders" class="view-all">View All</a>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $order): ?>
          <tr>
            <td>#<?php echo $order['order_id']; ?></td>
            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
            <td>Rp <?php echo number_format($order['amount'], 0, '', '.'); ?></td>
            <td><span class="status-badge <?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
            <td>
              <div class="action-buttons">
                <button class="action-btn view-btn" data-id="<?php echo $order['order_id']; ?>">
                  <a href="?page=orders">
                    <img src="../assets/icon/eye-fill.png" alt="View">
                  </a>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <!-- Product Insights -->
  <div class="insights-container">
    <div class="insight-card">
      <div class="table-header">
          <h3>Top Selling Products</h3>
          <a href="?page=products" class="view-all">View All Products</a>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Sold</th>
              <th>Revenue</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topProducts as $product): ?>
            <tr>
              <td>
                <div class="product-cell">
                  <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                  <span><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
              </td>
              <td>Rp <?php echo number_format($product['price'], 0, '', '.'); ?></td>
              <td><?php echo $product['sold']; ?> units</td>
              <td>Rp <?php echo number_format($product['revenue'], 0, '', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="insight-card">
      <div class="table-header">
        <h3>Low Stock Alert</h3>
        <a href="?page=products&filter=low-stock" class="view-all">View All</a>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Stock</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lowStockProducts as $product): ?>
            <tr>
              <td>
                <div class="product-cell">
                  <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                  <span><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
              </td>
              <td><?php echo $product['stock']; ?> units</td>
              <td>
                <span class="status-badge <?php echo $product['stock'] <= 0 ? 'out-of-stock' : 'low-stock'; ?>">
                    <?php echo $product['stock'] <= 0 ? 'Out of Stock' : 'Low Stock'; ?>
                </span>
              </td>
              <td>
                <button class="btn btn-small update-stock" data-id="<?php echo $product['product_id']; ?>">
                  <a href="?page=products&filter=low-stock">Update Stock</a>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
