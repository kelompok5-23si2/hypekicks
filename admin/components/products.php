<?php
require_once '../../database/admin/product-manager.php';

$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 1000;
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get products with pagination
$filters = [
  'filter' => $filter,
  'category' => $category,
  'search' => $search
];
$productsData = getAdminProducts($filters, $page, $limit);
$products = $productsData['products'];
$totalPages = $productsData['total_pages'];


?>

<div class="products-container">
  <div class="page-actions">
    <button style="padding: 1em 2em; margin: 0 auto;" id="add-product-btn" class="btn btn-primary">
      <img src="../assets/icon/add-line.png" alt="Add" class="btn-icon">
      Add New Product
    </button>
  </div>
  
  <!-- Products Table -->
  <div class="table-card">
    <div class="table-responsive">
      <table class="data-table products-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Sales</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="product-list-container">
          <?php if (empty($products)): ?>
          <tr>
              <td colspan="7" class="no-data">No products found matching your criteria</td>
          </tr>
          <?php else: ?>
            <?php foreach ($products as $product): ?>
            <tr>
              <td><?php echo $product['product_id']; ?></td>
              <td>
                <div class="product-cell">
                  <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                  <span><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
              </td>
              <td><?php echo ucfirst($product['category']); ?></td>
              <td>
                <span class="stock-indicator <?php echo getStockClass($product['stock']); ?>">
                    <?php echo $product['stock']; ?> units
                </span>
              </td>
              <td>Rp <?php echo number_format($product['price'], 0, '', '.'); ?></td>
              <td><?php echo $product['sold']; ?> sold</td>
              <td>
                <div class="action-buttons">
                  <button class="action-btn view-btn" data-id="<?php echo $product['product_id']; ?>">
                    <img src="../assets/icon/eye-fill.png" alt="View">
                  </button>
                  <button class="action-btn edit-btn" data-id="<?php echo $product['product_id']; ?>">
                    <img src="../assets/icon/edit-2-fill.png" alt="Edit">
                  </button>
                  <button class="action-btn delete-btn" data-id="<?php echo $product['product_id']; ?>">
                    <img src="../assets/icon/delete-bin-6-fill-black.png" alt="Delete">
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

