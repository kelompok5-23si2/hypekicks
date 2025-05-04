<h1 class="cart-header">My Cart</h1>
<div class="cart_main-container">
  <div class="cart-container">
  </div>
  <div class="order-summary">
    <h3>Order Summary</h3>
    <div class="price-details">
      <span>Subtotal</span>
      <span class="price-subtotal-js" id="final-subtotal">-</span>
    </div>
    <div class="price-details">
      <span>Tax</span>
      <span class="price-tax-js" id="final-tax">-</span>
    </div>
    <div class="price-details">
      <span>Delivery Fee</span>
      <span class="price-delivery-js" id="final-delivery">-</span>
    </div>
    <div class="price-details total-order">
      <span>Total</span>
      <span class="price-total-js" itemref="final-price-total">-</span>
    </div>
    <button id="checkout-button" class="order-checkout">
      Go to Checkout
      <img src="../assets/icon/arrow-right-line-white.png">
    </button>
  </div>
</div>

<div class="checkout-modal" id="checkout-modal">
  <div class="checkout-modal__content" id="order-summary-modal" style="display: none;">
    <span class="checkout-modal__close" id="close-modal">&times;</span>
    <h2 class="checkout-modal__title">Order Summary</h2>
    <div class="checkout-modal__summary">
      <div class="checkout-modal__price-row">
        <span>Subtotal</span>
        <span class="price-subtotal-js">-</span>
      </div>
      <div class="checkout-modal__price-row">
        <span>Tax</span>
        <span class="price-tax-js">-</span>
      </div>
      <div class="checkout-modal__price-row">
        <span>Delivery Fee</span>
        <span class="price-delivery-js">-</span>
      </div>
      <div class="checkout-modal__price-row checkout-modal__price-row--total">
        <span>Total</span>
        <span class="price-total-js">-</span>
      </div>
    </div>
    <button id="confirm-order-button" class="checkout-modal__button">Confirm Order</button>
  </div>
  <div class="checkout-modal__content checkout-modal__content--payment" id="payment-modal" style="display: none;">
    <img src="../assets/others/qr-code.png" alt="QR Code" class="checkout-modal__qr-code">
    <h3 class="checkout-modal__subtitle">Bank Details</h3>
    <p class="checkout-modal__detail">Account Name: &ensp;&ensp;&nbsp;HypeKicks Indonesia</p>
    <p class="checkout-modal__detail">Account Number: &nbsp;&nbsp;195 012 3456</p>
    <p class="checkout-modal__detail">Bank Name: &ensp;&ensp;&ensp;&ensp;&ensp;&nbsp;PT. Bank Central Asia</p>
    <p class="checkout-modal__instruction">Scan the QR code to pay</p>
    <p class="checkout-modal__instruction">Or transfer to the account details above.</p>
    <button style="background-color: rgb(239, 68, 68);" id="close-payment-modal" class="checkout-modal__button checkout-modal__button--cancel">Cancel</button>
    <button id="confirm-payment-button" class="checkout-modal__button">Confirm Payment</button>
  </div>
</div>

<style>
  .checkout-modal {
    display: none;
    justify-content: center;
    align-items: center;
    position: fixed;
    z-index: 100;
    left: 0;
    top: 0;
    width: 100%; 
    min-height: 100vh;
    padding: 30px;
    overflow: hidden; 
    background-color: rgba(0,0,0,0.4);
  }
  
  .checkout-modal__content {
    background-color: #fefefe;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #888;
    width: min(500px, 80%);
    max-height: 80vh;
    overflow-y: auto; /* Enable scrolling if content is too long */
    border-radius: 10px;
  }
  
  .checkout-modal__close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
  }
  
  .checkout-modal__close:hover,
  .checkout-modal__close:focus {
    color: black;
    text-decoration: none;
  }
  
  .checkout-modal__title {
    margin-bottom: 20px;
  }
  
  .checkout-modal__subtitle {
    margin: 10px 0;
  }
  
  .checkout-modal__summary {
    margin: 20px 0;
  }
  
  .checkout-modal__price-row {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
  }
  
  .checkout-modal__price-row--total {
    font-weight: bold;
  }
  
  .checkout-modal__button {
    background-color: #4CAF50;
    border: none;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
    border-radius: 5px;
    width: 100%;
  }
  
  .checkout-modal__button:hover {
    background-color: #45a049;
  }
  
  .checkout-modal__button--cancel:hover {
    background-color: rgb(220, 38, 38);
  }
  
  .checkout-modal__qr-code {
    width: clamp(150px, 20vw, 320px);
    height: auto;
    margin: 20px auto;
    display: block;
  }
  
  .checkout-modal__detail,
  .checkout-modal__instruction {
    margin: 5px 0;
  }
</style>