<template>
  <div v-if="show" class="receipt-modal-overlay">
    <div class="receipt-modal">
      <div class="print-actions">
        <button @click="printReceipt" class="print-btn">Print Receipt</button>
        <button @click="$emit('close')" class="close-btn">Close</button>
      </div>

      <div id="printable-receipt" class="receipt-content">
        <div class="receipt-header">
          <h2>THE POS BAKERY</h2>
          <p>Address: Shop #12, Bakery Street, Food City</p>
          <p>Contact: +92 300 1234567</p>
          <hr />
        </div>

        <div class="order-info">
          <p><strong>Invoice:</strong> {{ order.invoice_number }}</p>
          <p><strong>Date:</strong> {{ formatDate(order.created_at) }}</p>
          <p v-if="order.customer">
            <strong>Customer:</strong> {{ order.customer.name }} <br />
            <strong>Phone:</strong> {{ order.customer.phone || 'N/A' }}
          </p>
          <p v-else-if="order.customer_name">
            <strong>Customer:</strong> {{ order.customer_name }} <br />
            <strong>Phone:</strong> {{ order.customer_phone || 'N/A' }}
          </p>
        </div>

        <table class="receipt-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Sub</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in order.items" :key="item.id">
              <td>{{ item.product?.name }}</td>
              <td>{{ item.quantity }}</td>
              <td>{{ item.price }}</td>
              <td>{{ (item.price * item.quantity).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="receipt-footer">
          <hr />
          <div class="summary-line">
            <span>Subtotal:</span>
            <span>${{ order.subtotal?.toFixed(2) }}</span>
          </div>
          <div class="summary-line discount" v-if="order.discount > 0">
            <span>Discount ({{ order.discount_percentage }}%):</span>
            <span>-${{ order.discount?.toFixed(2) }}</span>
          </div>
          <hr />
          <div class="final-total">
            <span>TOTAL:</span>
            <strong>${{ order.total?.toFixed(2) }}</strong>
          </div>
          <p class="thank-you">Thank you for your purchase!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
  order: Object
});

const emit = defineEmits(['close']);

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleString();
};

const printReceipt = () => {
  const printContents = document.getElementById('printable-receipt').innerHTML;
  const originalContents = document.body.innerHTML;

  // Create a temporary style for the print window to make it look like a receipt
  const printWindow = window.open('', '', 'height=600,width=400');
  printWindow.document.write('<html><head><title>Print Receipt</title>');
  printWindow.document.write('<style>');
  printWindow.document.write(`
    body { font-family: 'Courier New', Courier, monospace; padding: 20px; color: #000; }
    .receipt-content { width: 300px; margin: 0 auto; }
    .receipt-header { text-align: center; margin-bottom: 20px; }
    .receipt-header h2 { margin: 0; font-size: 20px; }
    .receipt-header p { margin: 2px 0; font-size: 12px; }
    .order-info { margin-bottom: 15px; font-size: 14px; }
    .receipt-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .receipt-table th { border-bottom: 1px dashed #000; text-align: left; }
    .receipt-table td { padding: 5px 0; }
    .receipt-footer { margin-top: 20px; }
    .summary-line { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px; }
    .final-total { display: flex; justify-content: space-between; font-size: 18px; margin-top: 10px; font-weight: bold; }
    .thank-you { margin-top: 20px; font-style: italic; text-align: center; }
    hr { border: none; border-top: 1px dashed #000; margin: 10px 0; }
  `);
  printWindow.document.write('</style></head><body>');
  printWindow.document.write(printContents);
  printWindow.document.write('</body></html>');
  printWindow.document.close();
  printWindow.print();
};
</script>

<style scoped>
.receipt-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.receipt-modal {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  width: 400px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.print-actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.print-btn {
  flex: 1;
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.close-btn {
  background: #f3f4f6;
  color: #374151;
  border: none;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  cursor: pointer;
}

.receipt-content {
  background: #fdfdfd;
  padding: 1rem;
  border: 1px solid var(--border-color);
  color: #1a1a1a;
  font-family: 'Courier New', Courier, monospace;
}

.receipt-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.receipt-header h2 {
  margin: 0;
  font-size: 1.5rem;
}

.receipt-header p {
  font-size: 0.85rem;
  color: #666;
}

.order-info {
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.order-info p {
  margin: 0.25rem 0;
}

.receipt-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.receipt-table th {
  text-align: left;
  border-bottom: 1px dashed #ccc;
  padding-bottom: 0.5rem;
}

.receipt-table td {
  padding: 0.5rem 0;
}

.receipt-footer {
  margin-top: 1.5rem;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.25rem;
  font-size: 0.9rem;
}

.final-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.5rem;
  margin-top: 0.5rem;
  font-family: inherit;
}

.thank-you {
  margin-top: 1.5rem;
  font-size: 0.85rem;
  font-style: italic;
  color: #888;
  text-align: center;
}
</style>
