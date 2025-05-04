<?php
require_once '../../database/admin/order-manager.php';

// Get filter/pagination parameters
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 1000;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '';

// Get orders with pagination
$filters = [
  'status' => $status,
  'search' => $search,
  'date_range' => $dateRange
];
$ordersData = getAdminOrders($filters, $page, $limit);
$orders = $ordersData['orders'];
$totalPages = $ordersData['total_pages'];

?>

<div class="orders-container">
  
  <!-- Orders Table -->
  <div class="table-card">
      <div class="table-responsive">
          <table class="data-table orders-table">
              <thead>
                  <tr>
                      <th>Order ID</th>
                      <th>Customer</th>
                      <th>Date</th>
                      <th>Total</th>
                      <th>Status</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if (empty($orders)): ?>
                  <tr>
                      <td colspan="6" class="no-data">No orders found matching your criteria</td>
                  </tr>
                  <?php else: ?>
                      <?php foreach ($orders as $order): ?>
                      <tr data-id="<?php echo $order['order_id']; ?>">
                          <td>#<?php echo $order['order_id']; ?></td>
                          <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                          <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                          <td>Rp <?php echo number_format($order['final_price'], 0, '', '.'); ?></td>
                          <td>
                              <span class="status-badge">
                                  <?php echo $order['status']; ?>
                              </span>
                          </td>
                          <td>
                              <div class="action-buttons">
                                  <button class="action-btn view-btn" data-id="<?php echo $order['order_id']; ?>">
                                      <img src="../assets/icon/eye-fill.png" alt="View">
                                  </button>
                                  <button class="action-btn edit-btn" data-id="<?php echo $order['order_id']; ?>">
                                      <img src="../assets/icon/edit-2-fill.png" alt="Edit">
                                  </button>
                              </div>
                          </td>
                      </tr>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
      
      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
          <?php if ($page > 1): ?>
          <a href="#" class="pagination-link" data-page="<?php echo $page - 1; ?>">Previous</a>
          <?php endif; ?>
          
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="#" class="pagination-link <?php echo ($i === $page) ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
              <?php echo $i; ?>
          </a>
          <?php endfor; ?>
          
          <?php if ($page < $totalPages): ?>
          <a href="#" class="pagination-link" data-page="<?php echo $page + 1; ?>">Next</a>
          <?php endif; ?>
      </div>
      <?php endif; ?>
  </div>
</div>

<style>
.order-summary {
  margin-bottom: 2rem;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.order-header h3 {
  margin: 0;
  font-size: var(--font-size-large);
}

.order-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.order-info-item {
  margin-bottom: 0.5rem;
}

.info-label {
  display: inline-block;
  min-width: 80px;
  color: var(--color-text-semi-transparent);
  font-weight: 500;
}

.order-sections {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.order-section h4 {
  font-size: var(--font-size-base);
  margin-top: 0;
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--color-border);
}

.order-section p {
  margin: 0.25rem 0;
}

.order-items {
  margin-bottom: 2rem;
}

.order-items h4 {
  font-size: var(--font-size-base);
  margin-top: 0;
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--color-border);
}

.order-totals {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 2rem;
}

.totals-table {
  width: 350px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
}

.grand-total {
  font-weight: 700;
  font-size: var(--font-size-semi-large);
  padding-top: 0.75rem;
  margin-top: 0.5rem;
  border-top: 1px solid var(--color-border);
}

.order-actions-container {
  display: flex;
  justify-content: space-between;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid var(--color-border);
}

.order-action-buttons {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.status-timeline {
  list-style: none;
  padding: 0;
  margin: 0;
}

.status-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.status-marker {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  margin-top: 5px;
}

.status-marker.waiting {
  background-color: #eab308;
}

.status-marker.processing {
  background-color: #3b82f6;
}

.status-marker.shipping {
  background-color:rgb(136, 50, 217);
}

.status-marker.arrived {
  background-color: #22c55e;
}

.status-marker.cancelled {
  background-color: #ef4444;
}

.status-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.25rem;
}

.status-name {
  font-weight: 600;
}

.status-date {
  color: var(--color-text-semi-transparent);
  font-size: var(--font-size-small);
}

.status-notes {
  font-size: var(--font-size-small);
  color: var(--color-text-semi-transparent);
  margin: 0;
}

@media (max-width: 768px) {
  .order-sections,
  .order-info-grid {
      grid-template-columns: 1fr;
  }
  
  .order-actions-container {
      flex-direction: column;
      gap: 1.5rem;
  }
  
  .order-action-buttons {
      justify-content: flex-start;
  }
  
  .order-totals {
      justify-content: flex-start;
  }
}
</style>
