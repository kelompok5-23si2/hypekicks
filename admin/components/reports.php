<?php
require_once '../../database/admin/dashboard-data.php';

// Get dashboard statistics for reports
$stats = getDashboardStats();

// Get date ranges for filter
$currentYear = date('Y');
$currentMonth = date('m');
$lastMonth = $currentMonth > 1 ? $currentMonth - 1 : 12;
$lastMonthYear = $currentMonth > 1 ? $currentYear : $currentYear - 1;
?>

<div class="reports-container">    
    <!-- Report Content -->
    <div id="report-content">
        <!-- Sales Report (Default) -->
        <div class="report-section sales-report">
            <h2>Sales Report - This Month</h2>
            
            <!-- Summary Cards -->
            <div class="report-summary">
                <div class="summary-card">
                    <div class="summary-title">Total Sales</div>
                    <div class="summary-value">Rp <?php echo number_format($stats['total_sales'], 0, '', '.'); ?></div>
                    <div class="summary-change positive">+<?php echo $stats['sales_increase']; ?>% from last month</div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-title">Orders</div>
                    <div class="summary-value"><?php echo $stats['total_orders']; ?></div>
                    <div class="summary-change <?php echo $stats['orders_change'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $stats['orders_change'] >= 0 ? '+' : ''; ?><?php echo $stats['orders_change']; ?>% from last month
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-title">Average Order Value</div>
                    <div class="summary-value">
                        Rp <?php echo $stats['total_orders'] > 0 ? number_format($stats['total_sales'] / $stats['total_orders'], 0, '', '.') : '0'; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sales Chart -->
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
            
            <!-- Sales by Category -->
            <div class="report-grid">
              <div class="chart-card">
                <div class="chart-header">
                  <h3>Category Distribution</h3>
                </div>
                <div class="chart-body">
                  <canvas id="categoryChart"></canvas>
                </div>
              </div>
                
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Best Selling Items</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Units Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['top_products'] ?? [] as $index => $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name'] ?? "Product " . ($index + 1)); ?></td>
                                    <td><?php echo $product['sold'] ?? rand(10, 100); ?></td>
                                    <td>Rp <?php echo number_format($product['revenue'] ?? rand(1000, 10000), 0, '', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($stats['top_products'])): ?>
                                    <tr>
                                        <td>Sample Product 1</td>
                                        <td>56</td>
                                        <td>Rp 78.400.000</td>
                                    </tr>
                                    <tr>
                                        <td>Sample Product 2</td>
                                        <td>42</td>
                                        <td>Rp 86.180.000</td>
                                    </tr>
                                    <tr>
                                        <td>Sample Product 3</td>
                                        <td>35</td>
                                        <td>Rp 25.380.000</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>