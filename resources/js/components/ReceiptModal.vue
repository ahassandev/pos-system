<template>
  <div class="receipt-modal-overlay" v-if="show" @click.self="$emit('close')">
    <div class="receipt-modal">
      <div class="print-actions">
        <button @click="printReceipt" class="print-btn">Print Receipt Now</button>
        <button @click="$emit('close')" class="close-btn">Close</button>
      </div>

      <div id="printable-receipt" class="receipt-content">
        <div class="receipt-header">
          <h2 class="store-name">BAKERY NAME</h2>
          <p class="store-info">Shop #12, Bakery Street, Karachi</p>
          <div class="divider"></div>
        </div>

        <div v-if="order && order.invoice_number">
          <div class="order-info">
            <div class="info-row"><span>Invoice:</span> <span>{{ order.invoice_number }}</span></div>
            <div class="info-row"><span>Date:</span> <span>{{ formatDate(order.created_at) }}</span></div>
            <div class="info-row" v-if="order.customer"><span>Customer:</span> <span>{{ order.customer.name }}</span></div>
          </div>

          <div class="divider"></div>

          <table class="receipt-table">
            <thead>
              <tr>
                <th style="width: 50%">Item</th>
                <th style="text-align: center">Qty</th>
                <th style="text-align: right">Total</th>
              </tr>
            </thead>
            <tbody>
              <template v-if="order.items && order.items.length > 0">
                <tr v-for="item in order.items" :key="item.id">
                  <td>{{ item.product?.name || 'Product' }}</td>
                  <td style="text-align: center">{{ item.quantity }}</td>
                  <td style="text-align: right">{{ (item.price * item.quantity).toFixed(2) }}</td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="3" style="text-align: center; padding: 2rem;">
                  <div class="spinner" style="width: 20px; height: 20px;"></div>
                  Loading Items...
                </td>
              </tr>
            </tbody>
          </table>

          <div class="divider"></div>

          <div class="receipt-footer">
            <div class="final-total">
              <span>GRAND TOTAL:</span>
              <span>${{ order.total || '0.00' }}</span>
            </div>
            <div class="divider"></div>
            <p class="thank-you">Thank you for your visit!</p>
          </div>
        </div>
        <div v-else class="loading-receipt">
           <div class="spinner"></div>
           <p>Preparing Receipt...</p>
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
    .store-name { text-align: center; font-size: 20px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
    .store-info { text-align: center; font-size: 12px; margin: 2px 0; }
    .divider { border-top: 1px dashed #000; margin: 10px 0; width: 100%; }
    .info-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 3px; }
    .receipt-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 10px 0; }
    .receipt-table th { border-bottom: 1px solid #000; text-align: left; padding-bottom: 5px; }
    .receipt-table td { padding: 5px 0; }
    .summary-line { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 3px; }
    .final-total { display: flex; justify-content: space-between; font-size: 18px; margin-top: 10px; font-weight: bold; border-top: 2px solid #000; padding-top: 5px; }
    .thank-you { margin-top: 20px; font-style: italic; text-align: center; font-size: 12px; }
    .designed-by { text-align: center; font-size: 10px; color: #666; margin-top: 5px; }
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
  border-radius: 20px;
  width: 550px;
  max-width: 95vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.print-actions { display: flex; gap: 1rem; margin-bottom: 2rem; }
.print-btn { flex: 4; background: #6366f1; color: white; border: none; padding: 1rem; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 1.1rem; transition: background 0.2s; }
.print-btn:hover { background: #4f46e5; }
.close-btn { flex: 1; background: #f3f4f6; color: #374151; border: none; padding: 1rem; border-radius: 12px; cursor: pointer; font-weight: 500; transition: background 0.2s; }
.close-btn:hover { background: #e5e7eb; }

.receipt-content { background: #fff; padding: 1.5rem; color: #000; font-family: 'Courier New', Courier, monospace; border: 1px solid #eee; }
.store-name { text-align: center; font-size: 1.5rem; font-weight: bold; margin-bottom: 0.25rem; text-transform: uppercase; }
.store-info { text-align: center; font-size: 0.85rem; margin: 0; color: #444; }
.divider { border-top: 1px dashed #000; margin: 1rem 0; width: 100%; }
.info-row { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.25rem; }
.receipt-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
.receipt-table th { text-align: left; font-size: 0.85rem; border-bottom: 1px solid #000; padding-bottom: 0.5rem; }
.receipt-table td { font-size: 0.85rem; padding: 0.5rem 0; }
.summary-line { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.25rem; }
.final-total { display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: bold; margin-top: 1rem; border-top: 2px solid #000; padding-top: 0.5rem; }
.thank-you { margin-top: 1.5rem; font-size: 0.85rem; font-style: italic; color: #888; text-align: center; }
.designed-by { text-align: center; font-size: 0.7rem; color: #999; margin-top: 0.5rem; }
.loading-receipt, .loading-items { text-align: center; padding: 2rem; }
.loading-receipt p, .loading-items p { font-size: 1.2rem; color: #374151; margin-bottom: 1rem; font-family: 'Inter', sans-serif; }
.spinner { border: 4px solid #f3f3f3; border-top: 4px solid #6366f1; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
