<?php
session_start();
require_once '../database/auth/admin-check.php';
require_once '../database/products.php';

// If not logged in as admin, redirect to login page
if (!isAdminLoggedIn()) {
  header("Location: login.php");
  exit();
}

// Get available status values for filter dropdown
$orderStatuses = ['Waiting Payment', 'Processing', 'On Shipping', 'Arrived', 'Cancelled'];

// Get product categories for filter dropdown
$categories = getProductCategories();
$brands = getProductBrands();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="../assets/logo/logo.png" type="image/x-icon">
  <title>HypeKicks Admin Dashboard</title>
  
  <link rel="stylesheet" href="../styles/theme.css">
  <link rel="stylesheet" href="../styles/base/reset.css">
  <link rel="stylesheet" href="../styles/base/typography.css">
  <link rel="stylesheet" href="../styles/base/utilities.css">
  <link rel="stylesheet" href="../styles/components/buttons.css">
  <link rel="stylesheet" href="../styles/components/forms.css">
  <link rel="stylesheet" href="../styles/components/loading.css">
  <link rel="stylesheet" href="../styles/components/notification.css">
  
  <link rel="stylesheet" href="styles/admin.css">
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="admin-container">
    <aside class="admin-sidebar">
      <div class="admin-logo">
        <img src="../assets/logo/logo.png" alt="HypeKicks">
        <span>HypeKicks</span>
      </div>
      
      <nav class="admin-nav">
        <ul>
          <li class="nav-item active" data-page="dashboard">
            <img src="../assets/icon/dashboard-horizontal-fill.png" alt="Dashboard">
            <span>Dashboard</span>
          </li>
          <li class="nav-item" data-page="orders">
            <img src="../assets/icon/shopping-cart-2-fill.png" alt="Orders">
            <span>Orders</span>
          </li>
          <li class="nav-item" data-page="products">
            <img src="../assets/icon/box-3-fill.png" alt="Products">
            <span>Products</span>
          </li>
          <li class="nav-item" data-page="customers">
            <img src="../assets/icon/team-fill.png" alt="Customers">
            <span>Customers</span>
          </li>
          <li class="nav-item" data-page="reports">
            <img src="../assets/icon/bar-chart-box-ai-fill.png" alt="Reports">
            <span>Reports</span>
          </li>
        </ul>
      </nav>
      
      <div class="admin-sidebar-footer">
        <a href="logout.php" class="logout-btn">
          <img src="../assets/icon/logout-box-r-fill.png" alt="Logout">
          <span>Logout</span>
        </a>
      </div>
    </aside>
    
    <!-- Main Content Area -->
    <main class="admin-content">
      <!-- Header -->
      <header class="admin-header">
        <div class="header-left">
          <button class="menu-toggle">
            <img src="../assets/icon/menu-line.png" alt="Menu">
          </button>
          <h1 id="page-title">Dashboard</h1>
        </div>
        
        <div class="header-right">
          
          <div class="admin-profile">
            <img src="../assets/icon/admin-line.png" alt="Admin" class="profile-image">
            <span class="admin-name"><?php echo getAdminName(); ?></span>
          </div>
        </div>
      </header>
      
      <div class="admin-dynamic-content" id="dynamic-content">
        <div class="loading-indicator visible">
          <div class="loading-dots-container">
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
          </div>
        </div>
      </div>
    </main>
  </div>
  
  <!-- Notification container -->
<div class="notification-container"></div>
<div class="notification-templates">
  <template id="notif-success-template">
    <div class="notification success">
      <div class="notification-icon">
        <img src="../assets/icon/checkbox-circle-line.png" alt="Success">
      </div>
      <div class="notification-content">
        <span class="notification-text"></span>
      </div>
      <div class="notification-close">
        <img src="../assets/icon/close-line.png" alt="Close">
      </div>
      <div class="notification-progress-bar"></div>
    </div>
  </template>
  <template id="notif-error-template">
    <div class="notification error">
        <div class="notification-icon">
            <img src="../assets/icon/close-circle-line.png" alt="Error">
        </div>
        <div class="notification-content">
            <span class="notification-text"></span>
        </div>
        <div class="notification-close">
            <img src="../assets/icon/close-line.png" alt="Close">
        </div>
        <div class="notification-progress-bar"></div>
    </div>
</template>
<template id="notif-warning-template">
    <div class="notification warning">
        <div class="notification-icon">
            <img src="../assets/icon/error-warning-line.png" alt="Warning">
        </div>
        <div class="notification-content">
            <span class="notification-text"></span>
        </div>
        <div class="notification-close">
            <img src="../assets/icon/close-line.png" alt="Close">
        </div>
        <div class="notification-progress-bar"></div>
    </div>
</template>
<template id="notif-info-template">
    <div class="notification info">
        <div class="notification-icon">
            <img src="../assets/icon/information-line.png" alt="Info">
        </div>
        <div class="notification-content">
            <span class="notification-text"></span>
        </div>
        <div class="notification-close">
            <img src="../assets/icon/close-line.png" alt="Close">
        </div>
        <div class="notification-progress-bar"></div>
    </div>
</template>
</div>



<!-- Order Details Modal -->
<div class="modal" id="order-modal">
  <div class="modal-content">
      <div class="modal-header">
          <h2>Order Details</h2>
          <button class="modal-close">&times;</button>
      </div>
      <div class="modal-body" id="order-details">
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

<!-- Update Order Status Modal -->
<div class="modal" id="status-modal">
  <div class="modal-content modal-sm">
      <div class="modal-header">
          <h2>Update Order Status</h2>
          <button class="modal-close">&times;</button>
      </div>
      <div class="modal-body">
          <form id="update-status-form">
              <input type="hidden" id="edit-order-id" name="order_id" value="">
              
              <div class="form-group">
                  <label for="order-status">Status:</label>
                  <select id="order-status" name="status" class="form-input">
                      <?php foreach ($orderStatuses as $statusOption): ?>
                      <option value="<?php echo $statusOption; ?>"><?php echo $statusOption; ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              
              <div class="form-group">
                  <label for="status-notes">Notes:</label>
                  <textarea id="status-notes" name="notes" class="form-input" rows="3" required></textarea>
              </div>
              
              <div class="form-actions">
                  <button type="button" class="btn btn-outline" id="cancel-status-btn">Cancel</button>
                  <button type="submit" class="btn btn-primary">Update Status</button>
              </div>
          </form>
      </div>
  </div>
</div>

<!-- Product Form Modal -->
<div class="modal" id="product-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modal-title">Add New Product</h2>
      <button class="modal-close">&times;</button>
    </div>
    <div class="modal-body">
      <form id="product-form">
        <input type="hidden" id="product-id" name="product_id" value="">
        <input type="hidden" id="image-changed" name="image-changed" value="0">
        
        <div class="form-row">
          <div class="form-group">
            <label for="product-name">Product Name *</label>
            <input type="text" id="product-name" name="name" class="form-input" required>
          </div>
          
          <div class="form-group">
            <label for="product-category">Category *</label>
            <select id="product-category" name="category" class="form-input" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="product-price">Price *</label>
            <div class="input-prefix">
              <span class="prefix">Rp</span>
              <input type="number" id="product-price" name="price" class="form-input" min="0" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="product-stock">Stock *</label>
            <input type="number" id="product-stock" name="stock" class="form-input" min="0" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="product-brand">Brand *</label>
            <select id="product-brand" name="brand" class="form-input" required>
              <option value="">Select Brand</option>
              <?php foreach ($brands as $brand): ?>
              <option value="<?php echo $brand; ?>"><?php echo ucfirst($brand); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
        <div class="form-group">
            <label for="product-description">Description *</label>
            <textarea id="product-description" name="description" class="form-input" rows="5" required></textarea>
        </div>
        
        <div class="form-group">
          <label for="product-image">Product Image</label>
          <div class="image-upload-container">
            <div class="image-preview-wrapper">
                <img id="image-preview" src="../assets/icon/image-fill.png" alt="Preview">
            </div>
            <input type="file" id="product-image" name="image" accept="image/*" class="form-input">
            <div class="image-upload-actions">
                <label for="product-image" class="upload-btn">Choose Image</label>
                <button type="button" class="remove-image-btn" id="remove-image-btn">Remove</button>
            </div>
          </div>
        </div>
        
        <div class="form-actions">
          <button type="button" class="btn btn-outline" id="cancel-form-btn">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submit-form-btn">Save Product</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="delete-modal">
  <div class="modal-content modal-sm">
      <div class="modal-header">
          <h2>Delete Product</h2>
          <button class="modal-close">&times;</button>
      </div>
      <div class="modal-body">
          <p>Are you sure you want to delete this product? This action cannot be undone.</p>
          <div class="form-actions">
              <button class="btn btn-outline" id="cancel-delete-btn">Cancel</button>
              <button class="btn btn-danger" id="confirm-delete-btn" data-id="">Delete</button>
          </div>
      </div>
  </div>
</div>
  
  <!-- Scripts -->
  <script src="../scripts/core/notification.js"></script>
  <script src="scripts/admin.js"></script>
</body>
</html>
