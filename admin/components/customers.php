<?php
require_once '../../database/admin/customer-manager.php';

// Get filter/pagination parameters
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Get customers with pagination
$filters = [
  'search' => $search,
  'filter' => $filter
];
$customersData = getAdminCustomers($filters, $page, $limit);
$customers = $customersData['customers'];
$totalPages = $customersData['total_pages'];
?>

<div class="customers-container">  
  <!-- Customers Table -->
  <div class="table-card">
    <div class="table-responsive">
      <table class="data-table customers-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Joined</th>
            <th>Orders</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($customers)): ?>
          <tr>
              <td colspan="7" class="no-data">No customers found matching your criteria</td>
          </tr>
          <?php else: ?>
              <?php foreach ($customers as $customer): ?>
              <tr>
                <td>#<?php echo $customer['cust_id']; ?></td>
                <td>
                  <div class="product-cell">
                    <img src="../assets/icon/user-3-line.png" alt="<?php echo htmlspecialchars($customer['full_name']); ?>" class="product-thumbnail">
                    <span><?php echo htmlspecialchars($customer['full_name']); ?></span>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                <td><?php echo $customer['order_count']; ?></td>
                <td>
                  <span class="status-badge <?php echo $customer['active'] ? 'completed' : 'cancelled'; ?>">
                      <?php echo $customer['active'] ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td>
                  <div class="action-buttons">
                    <button class="action-btn view-btn" data-id="<?php echo $customer['cust_id']; ?>">
                        <img src="../assets/icon/eye-fill.png" alt="View">
                    </button>
                    <button class="action-btn email-btn" data-id="<?php echo $customer['cust_id']; ?>">
                        <img src="../assets/icon/mail-line.png" alt="Email">
                    </button>
                    <?php if ($customer['active']): ?>
                    <button class="action-btn block-btn" data-id="<?php echo $customer['cust_id']; ?>" title="Deactivate Account">
                        <img src="../assets/icon/user-unfollow-line.png" alt="Block">
                    </button>
                    <?php else: ?>
                    <button class="action-btn activate-btn" data-id="<?php echo $customer['cust_id']; ?>" title="Activate Account">
                        <img src="../assets/icon/user-follow-line.png" alt="Activate">
                    </button>
                    <?php endif; ?>
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

<!-- Customer Details Modal -->
<div class="modal" id="customer-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Customer Details</h2>
      <button class="modal-close">&times;</button>
    </div>
    <div class="modal-body" id="customer-details">
      <div class="loading-indicator">
        <div class="loading-dots-container">
          <div class="loading-dot"></div>
          <div class="loading-dot"></div>
          <div class="loading-dot"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Email Modal -->
<div class="modal" id="email-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Send Email</h2>
      <button class="modal-close">&times;</button>
    </div>
    <div class="modal-body">
      <form id="email-form">
        <input type="hidden" id="customer-id" name="customer_id" value="">
        
        <div class="form-group">
            <label for="recipient">To:</label>
            <input type="text" id="recipient" class="form-input" readonly>
        </div>
        
        <div class="form-group">
            <label for="email-subject">Subject:</label>
            <input type="text" id="email-subject" name="subject" class="form-input" required>
        </div>
        
        <div class="form-group">
            <label for="email-content">Message:</label>
            <textarea id="email-content" name="message" class="form-input" rows="10" required></textarea>
        </div>
        
        <div class="form-actions">
            <button type="button" class="btn btn-outline" id="cancel-email-btn">Cancel</button>
            <button type="submit" class="btn btn-primary">Send Email</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Account Status Confirmation Modal -->
<div class="modal" id="status-modal">
  <div class="modal-content modal-sm">
    <div class="modal-header">
        <h2 id="status-modal-title">Change Account Status</h2>
        <button class="modal-close">&times;</button>
    </div>
    <div class="modal-body">
      <p id="status-modal-message">Are you sure you want to change this customer's account status?</p>
      <div class="form-actions">
          <button class="btn btn-outline" id="cancel-status-btn">Cancel</button>
          <button class="btn btn-primary" id="confirm-status-btn" data-id="" data-action="">Confirm</button>
      </div>
    </div>
  </div>
</div>
