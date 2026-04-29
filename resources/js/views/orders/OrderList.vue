<template>
  <div class="orders-view">
    <h1>Order History</h1>
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
        <tr v-for="order in orders" :key="order.id">
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
      v-show="showReceipt"
      :show="showReceipt"
      :order="receiptOrder"
      @close="showReceipt = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ReceiptModal from '../../components/ReceiptModal.vue';

const orders = ref([]);
const selectedOrder = ref(null);
const showReceipt = ref(false);
const receiptOrder = ref({});
const printLoading = ref(null);

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
.data-table { width: 100%; background: white; border-collapse: collapse; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
.print-btn-small { margin-left: 0.5rem; background: var(--primary-color); color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; }
.modal { background: white; padding: 2rem; border-radius: 12px; width: 600px; }
.items-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.modal-actions { margin-top: 2rem; display: flex; justify-content: flex-end; }
</style>
