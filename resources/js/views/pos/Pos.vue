<template>
  <div class="pos-screen">
    <div class="products-section">
      <div class="search-bar">
        <input v-model="searchQuery" placeholder="Search products by name or scan barcode..." @keyup.enter="handleBarcode" />
      </div>

      <div class="categories-filter">
        <button v-for="cat in categories" :key="cat.id" @click="selectedCategory = cat.id" :class="{ active: selectedCategory === cat.id }">
          {{ cat.name }}
        </button>
        <button @click="selectedCategory = null" :class="{ active: !selectedCategory }">All</button>
      </div>

      <div class="products-grid">
        <div v-for="product in filteredProducts" :key="product.id" class="product-card" @click="addToCart(product)">
          <div class="product-info">
            <h4>{{ product.name }}</h4>
            <p class="price">${{ product.price }}</p>
            <p class="stock" :class="{ 'low': product.stock < 10 }">Stock: {{ product.stock }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="cart-section">
      <div class="cart-header">
        <h3>Current Order</h3>
        <button class="clear-cart" @click="cart = []" v-if="cart.length > 0">Clear All</button>
      </div>
      <div class="cart-items">
        <transition-group name="list">
          <div v-for="item in cart" :key="item.id" class="cart-item">
            <div class="item-details">
              <span class="item-name">{{ item.name }}</span>
              <div class="item-price-info">
                <span class="unit-price">${{ item.price }}</span>
                <span class="item-total-price">Total: ${{(item.price * item.quantity).toFixed(2)}}</span>
              </div>
            </div>
            <div class="item-actions">
              <button @click="updateQty(item, -1)" class="qty-btn">-</button>
              <span class="qty-display">{{ item.quantity }}</span>
              <button @click="updateQty(item, 1)" class="qty-btn">+</button>
              <button @click="removeFromCart(item)" class="remove-btn">×</button>
            </div>
          </div>
        </transition-group>
        <div v-if="cart.length === 0" class="empty-cart">
          <i class="icon">🛒</i>
          <p>Your cart is empty</p>
        </div>
      </div>

      <div class="cart-summary">
        <div class="customer-info-inputs">
          <h4>Customer Details</h4>
          <div class="input-group">
            <input v-model="customerName" placeholder="Customer Name (optional)" class="pos-input" />
          </div>
          <div class="input-group">
            <input v-model="customerPhone" placeholder="Phone Number (optional)" class="pos-input" />
          </div>
        </div>
        <div class="total-row discount-row">
          <span>Discount (%)</span>
          <div class="discount-input-wrapper">
             <input v-model.number="discountPercentage" type="number" min="0" max="100" step="0.5" class="discount-input" placeholder="0" />
             <span class="currency-sign">%</span>
          </div>
        </div>
        <div class="total-row">
          <span>Total Payable</span>
          <span class="total-value">${{ (total - (total * (discountPercentage || 0) / 100)).toFixed(2) }}</span>
        </div>
        <button class="checkout-btn" @click="handleCheckout" :disabled="cart.length === 0 || processing">
          <span v-if="!processing">Place Order</span>
          <span v-else class="loader"></span>
        </button>
      </div>
    </div>

    <ReceiptModal
      :show="showReceipt"
      :order="lastOrder"
      @close="showReceipt = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ReceiptModal from '../../components/ReceiptModal.vue';

const products = ref([]);
const categories = ref([]);
const customers = ref([]);
const searchQuery = ref('');
const selectedCategory = ref(null);
const cart = ref([]);
const customerName = ref('');
const customerPhone = ref('');
const discountPercentage = ref(0);
const processing = ref(false);

const showReceipt = ref(false);
const lastOrder = ref({});

const filteredProducts = computed(() => {
// ...
// (filteredProducts logic remains same)
// ...
  return products.value.filter(p => {
    const matchesSearch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || p.barcode === searchQuery.value;
    const matchesCategory = !selectedCategory.value || p.category_id === selectedCategory.value;
    return matchesSearch && matchesCategory;
  });
});

const total = computed(() => {
  return cart.value.reduce((acc, item) => acc + (item.price * item.quantity), 0);
});

const addToCart = (product) => {
  const existing = cart.value.find(i => i.id === product.id);
  if (existing) {
    existing.quantity++;
  } else {
    cart.value.push({ ...product, quantity: 1 });
  }
};

const updateQty = (item, delta) => {
  item.quantity += delta;
  if (item.quantity <= 0) removeFromCart(item);
};

const removeFromCart = (item) => {
  cart.value = cart.value.filter(i => i.id !== item.id);
};

const handleBarcode = () => {
  const product = products.value.find(p => p.barcode === searchQuery.value);
  if (product) {
    addToCart(product);
    searchQuery.value = '';
  }
};

const handleCheckout = async () => {
  processing.value = true;
  try {
    const response = await axios.post('/api/orders', {
      customer_name: customerName.value,
      customer_phone: customerPhone.value,
      discount_percentage: discountPercentage.value,
      items: cart.value.map(i => ({ product_id: i.id, quantity: i.quantity }))
    });

    lastOrder.value = response.data;
    showReceipt.value = true;

    cart.value = [];
    customerName.value = '';
    customerPhone.value = '';
    discountPercentage.value = 0;
    fetchData(); // Refresh stock
  } catch (err) {
    alert(err.response?.data?.message || 'Checkout failed');
  } finally {
    processing.value = false;
  }
};

const fetchData = async () => {
  const [pRes, cRes, cuRes] = await Promise.all([
    axios.get('/api/products'),
    axios.get('/api/categories'),
    axios.get('/api/customers')
  ]);
  products.value = pRes.data;
  categories.value = cRes.data;
  customers.value = cuRes.data;
};

onMounted(fetchData);
</script>

<style scoped>
.pos-screen {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
  align-items: start;
}

.products-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding-right: 1rem;
}

.search-bar input {
  width: 100%;
  padding: 1.25rem;
  border-radius: 12px;
  border: 2px solid var(--border-color);
  font-size: 1.1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
  transition: all 0.3s;
}

.search-bar input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.1);
}

.categories-filter {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.categories-filter button {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  border: 1px solid var(--border-color);
  background: white;
  cursor: pointer;
}

.categories-filter button.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
  overflow-y: auto;
  padding: 0.5rem;
}

.product-card {
  background: white;
  padding: 1.25rem;
  border-radius: 16px;
  cursor: pointer;
  border: 1px solid var(--border-color);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
  border-color: var(--primary-color);
}

.product-info h4 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
  color: var(--text-main);
}

.price {
  font-weight: 700;
  color: var(--primary-color);
  font-size: 1.25rem;
  margin: 0;
}

.cart-section {
  background: white;
  border-radius: 20px;
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
  box-shadow: var(--shadow);
  border: 1px solid var(--border-color);
  overflow: hidden;
  position: sticky;
  top: 10px;
  max-height: calc(100vh - 100px);
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.cart-header h3 {
  margin: 0;
  font-family: 'Outfit', sans-serif;
  color: var(--text-main);
}

.clear-cart {
  background: none;
  border: none;
  color: var(--danger);
  font-size: 0.85rem;
  cursor: pointer;
  text-decoration: underline;
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 1rem;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #fffbf0;
  border-radius: 12px;
  margin-bottom: 0.75rem;
  border: 1px solid #fef3c7;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.item-name {
  font-weight: 600;
  color: var(--text-main);
}

.item-price-info {
  display: flex;
  flex-direction: column;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.item-total-price {
  font-weight: 700;
  color: var(--primary-color);
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.qty-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 1px solid var(--primary-color);
  background: white;
  color: var(--primary-color);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.qty-btn:hover {
  background: var(--primary-color);
  color: white;
}

.qty-display {
  font-weight: 700;
  min-width: 20px;
  text-align: center;
}

.remove-btn {
  background: #fee2e2;
  color: #dc2626;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.empty-cart {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: var(--text-muted);
}

.empty-cart .icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.3;
}

.cart-summary {
  padding-top: 1.5rem;
  border-top: 2px dashed var(--border-color);
}

.customer-info-inputs {
  margin-bottom: 1.5rem;
}

.customer-info-inputs h4 {
  margin: 0 0 1rem;
  font-size: 0.9rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.input-group {
  margin-bottom: 0.75rem;
}

.pos-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  background: #fdfdfd;
  transition: all 0.2s;
}

.pos-input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.1);
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.total-row span:first-child {
  color: var(--text-muted);
  font-weight: 600;
}

.total-value {
  font-size: 2rem;
  font-weight: 800;
  color: var(--text-main);
}

.discount-row {
  margin-bottom: 0.75rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f1f5f9;
}

.discount-input-wrapper {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 0 0.5rem;
}

.currency-sign {
  color: #64748b;
  font-weight: 600;
}

.discount-input {
  border: none;
  background: transparent;
  padding: 0.5rem;
  width: 80px;
  text-align: right;
  font-weight: 600;
  color: var(--danger);
  outline: none;
}

.checkout-btn {
  width: 100%;
  padding: 1.25rem;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
}

.checkout-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(217, 119, 6, 0.4);
}

.checkout-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  box-shadow: none;
}

/* Animations */
.list-enter-active, .list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from, .list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.loader {
  width: 24px;
  height: 24px;
  border: 3px solid #FFF;
  border-bottom-color: transparent;
  border-radius: 50%;
  display: inline-block;
  box-sizing: border-box;
  animation: rotation 1s linear infinite;
}

@keyframes rotation {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
