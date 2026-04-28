<template>
  <div class="customers-view">
    <div class="header-actions">
      <h1>Customers</h1>
      <button class="add-btn" @click="showModal = true">+ Add Customer</button>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Phone</th>
          <th>Orders Count</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="customer in customers" :key="customer.id">
          <td>{{ customer.name }}</td>
          <td>{{ customer.phone || 'N/A' }}</td>
          <td>{{ customer.orders_count }}</td>
          <td>
            <button @click="editCustomer(customer)">Edit</button>
            <button @click="deleteCustomer(customer.id)" class="delete">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="showModal" class="modal-overlay">
      <div class="modal">
        <h3>{{ editingId ? 'Edit' : 'Add' }} Customer</h3>
        <form @submit.prevent="saveCustomer">
          <div class="form-group">
            <label>Name</label>
            <input v-model="form.name" required />
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input v-model="form.phone" />
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModal">Cancel</button>
            <button type="submit" class="save-btn">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const customers = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const form = ref({ name: '', phone: '' });

const fetchCustomers = async () => {
  const res = await axios.get('/api/customers');
  customers.value = res.data;
};

const saveCustomer = async () => {
  if (editingId.value) {
    await axios.put(`/api/customers/${editingId.value}`, form.value);
  } else {
    await axios.post('/api/customers', form.value);
  }
  closeModal();
  fetchCustomers();
};

const editCustomer = (c) => {
  editingId.value = c.id;
  form.value = { name: c.name, phone: c.phone };
  showModal.value = true;
};

const deleteCustomer = async (id) => {
  if (confirm('Are you sure?')) {
    await axios.delete(`/api/customers/${id}`);
    fetchCustomers();
  }
};

const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
  form.value = { name: '', phone: '' };
};

onMounted(fetchCustomers);
</script>

<style scoped>
.header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.data-table { width: 100%; background: white; border-collapse: collapse; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; padding: 2rem; border-radius: 12px; width: 400px; }
.modal-actions { margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem; }
.add-btn, .save-btn { background: var(--primary-color); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; }
.delete { color: var(--danger); margin-left: 1rem; background: none; border: none; cursor: pointer; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; }
.form-group input { width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; box-sizing: border-box; }
</style>
