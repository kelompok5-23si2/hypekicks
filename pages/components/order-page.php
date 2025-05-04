<link rel="stylesheet" href="../styles/pages/order-page.css">

<div class="order-page">
    <div class="tabs">
      <button class="tab-button" data-status="waiting">Waiting Payment</button>
      <button class="tab-button" data-status="processing">Processing</button>
      <button class="tab-button" data-status="shipping">On Shipping</button>
      <button class="tab-button" data-status="arrived">Arrived</button>
      <button class="tab-button" data-status="cancelled">Cancelled</button>
    </div>
  <div class="tab-content">
    <div id="waiting" class="tab-panel"></div>
    <div id="processing" class="tab-panel hidden"></div>
    <div id="shipping" class="tab-panel hidden"></div>
    <div id="arrived" class="tab-panel hidden"></div>
    <div id="cancelled" class="tab-panel hidden"></div>
  </div>
</div>