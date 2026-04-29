<template>
  <div class="orders-view">
    <div class="header-actions">
      <h1>Order History</h1>
    </div>

    <div class="filters-section">
      <h3 class="filters-title">Filters</h3>
      <div class="filters">
        <div class="filter-group">
          <label>Invoice Number</label>
          <input v-model="searchInvoice" placeholder="Enter invoice number..." />
        </div>
        <div class="filter-group">
          <label>From Date</label>
          <input type="date" v-model="startDate" />
        </div>
        <div class="filter-group">
          <label>To Date</label>
          <input type="date" v-model="endDate" />
        </div>
        <div class="filter-actions">
          <button class="apply-btn" @click="applyDateFilter">Apply Date Filter</button>
        </div>
      </div>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Invoice #</th>
          <th>Customer</th>
          <th>Total</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in filteredOrders" :key="order.id">
          <td>{{ order.invoice_number }}</td>
          <td>{{ order.customer?.name || 'Walk-in' }}</td>
          <td>${{ order.total }}</td>
          <td>{{ new Date(order.created_at).toLocaleString() }}</td>
          <td>
            <button @click="viewOrder(order)">View Items</button>
            <button @click="printOrder(order)" class="print-btn-small">Print</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="selectedOrder" class="modal-overlay">
      <div class="modal">
        <h3>Order #{{ selectedOrder.invoice_number }} Details</h3>
        <table class="items-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in selectedOrder.items" :key="item.id">
              <td>{{ item.product?.name }}</td>
              <td>{{ item.quantity }}</td>
              <td>${{ item.price }}</td>
              <td>${{ (item.price * item.quantity).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
        <div class="modal-actions">
          <button @click="selectedOrder = null">Close</button>
        </div>
      </div>
    </div>

    <ReceiptModal
      v-if="showReceipt"
      :show="showReceipt"
      :order="receiptOrder"
      @close="showReceipt = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ReceiptModal from '../../components/ReceiptModal.vue';

const orders = ref([]);
const selectedOrder = ref(null);
const showReceipt = ref(false);
const receiptOrder = ref({});
const printLoading = ref(null);

const searchInvoice = ref('');
const startDate = ref('');
const endDate = ref('');

const appliedStartDate = ref('');
const appliedEndDate = ref('');

const applyDateFilter = () => {
  appliedStartDate.value = startDate.value;
  appliedEndDate.value = endDate.value;
};

const filteredOrders = computed(() => {
  return orders.value.filter(o => {
    const matchInv = !searchInvoice.value || o.invoice_number.toLowerCase().includes(searchInvoice.value.toLowerCase());
    
    let matchDate = true;
    if (appliedStartDate.value || appliedEndDate.value) {
      const orderDate = new Date(o.created_at);
      const offset = orderDate.getTimezoneOffset();
      const localDate = new Date(orderDate.getTime() - (offset*60*1000));
      const orderDateString = localDate.toISOString().split('T')[0];
      
      if (appliedStartDate.value && orderDateString < appliedStartDate.value) {
        matchDate = false;
      }
      if (appliedEndDate.value && orderDateString > appliedEndDate.value) {
        matchDate = false;
      }
    }

    return matchInv && matchDate;
  });
});

const fetchData = async () => {
  const response = await axios.get('/api/orders');
  orders.value = response.data;
};

const viewOrder = (order) => {
  selectedOrder.value = { ...order };
};

const printOrder = (order) => {
  console.log('Print clicked for order:', order.id);
  receiptOrder.value = order;
  showReceipt.value = true;
  
  // Background fetch for items if they are missing
  if (!order.items || order.items.length === 0) {
    axios.get(`/api/orders/${order.id}`).then(res => {
      receiptOrder.value = res.data;
    }).catch(err => {
      console.error('Fetch error:', err);
    });
  }
};

onMounted(() => {
  console.log('OrderList Mounted (v2.0)');
  fetchData();
});
</script>

<style scoped>
.header-actions { margin-bottom: 1rem; }
.filters-section { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.filters-title { margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: #444; }
.filters { display: flex; gap: 1.5rem; align-items: flex-end; }
.filter-group { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; text-align: left; }
.filter-group label { font-size: 0.9rem; font-weight: 500; color: #555; }
.filter-group input { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #ddd); border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; background: #fafafa; transition: border-color 0.2s, background 0.2s; }
.filter-group input:focus { outline: none; border-color: var(--primary-color, #4f46e5); background: white; }
.filter-actions { margin-bottom: 2px; }
.apply-btn { background: var(--primary-color, #4f46e5); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer; font-size: 0.95rem; font-weight: 500; transition: background 0.2s; white-space: nowrap; }
.apply-btn:hover { background: #4338ca; }
.data-table { width: 100%; background: white; border-collapse: collapse; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
.print-btn-small { margin-left: 0.5rem; background: var(--primary-color); color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; }
.modal { background: white; padding: 2rem; border-radius: 12px; width: 600px; }
.items-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.modal-actions { margin-top: 2rem; display: flex; justify-content: flex-end; }
</style>
