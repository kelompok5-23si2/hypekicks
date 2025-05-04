<?php
require_once '../../database/products.php';

$categories = getProductCategories();
$brands = getProductBrands();

?>

<section class="product-listing">
    
  <div class="product-filters">
    <input type="hidden" name="page" value="1">
    <input type="hidden" name="search" value="">
    <div class="filter-group">
      <h3 class="filter-heading">Categories</h3>
      <ul class="filter-list">
        <?php foreach ($categories as $cat): ?>
        <li class="filter-item">
          <label class="filter-label">
            <input type="radio" class="filter" name="category" value="<?php echo $cat;?>"> <?php echo $cat;?>
          </label>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    
    <div class="filter-group">
      <h3 class="filter-heading">Price Range</h3>
      <ul class="filter-list">
        <li class="filter-item">
          <label class="filter-label">
            <input type="radio" class="filter" value="1" name="price"> < 250.000
          </label>
        </li>
        <li class="filter-item">
          <label class="filter-label">
            <input type="radio" class="filter" value="2" name="price"> 250.000 - 500.000
          </label>
        </li>
        <li class="filter-item">
          <label class="filter-label">
            <input type="radio" class="filter" value="3" name="price"> 500.000 - 1.000.000
          </label>
        </li>
        <li class="filter-item">
          <label class="filter-label">
            <input type="radio" class="filter" value="4" name="price"> 1.000.000 - 2.000.000
          </label>
        </li>
        <li class="filter-item">
          <label class="filter-label">
            <input type="radio" class="filter" value="5" name="price"> > 2.000.000
          </label>
        </li>
      </ul>
    </div>
    
    <div class="filter-group">
      <h3 class="filter-heading">Brands</h3>
      <ul class="filter-list">
        <?php foreach ($brands as $brand): ?>
        <li class="filter-item">
          <label class="filter-label">
            <input type="radio" class="filter" name="brand" value="<?php echo $brand; ?>"> <?php echo $brand; ?>
          </label>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    
    <div>
      <button class="btn btn-primary filter-apply reset-filter-js">Reset Filter</button>
    </div>
  </div>
  
  <div class="product-section">
    <h2>Product List</h2>
    
    <div class="product-grid"></div>

    <div class='pagination'></div>

  </div>
</section>