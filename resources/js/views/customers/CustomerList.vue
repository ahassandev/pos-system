<template>
  <div class="customers-view">
    <div class="header-actions">
      <h1>Customers</h1>
      <button class="add-btn" @click="showModal = true">+ Add Customer</button>
    </div>

    <div class="filters-section">
      <h3 class="filters-title">Filters</h3>
      <div class="filters">
        <div class="filter-group">
          <label>Name</label>
          <input v-model="searchName" placeholder="Enter name..." />
        </div>
        <div class="filter-group">
          <label>Phone Number</label>
          <input v-model="searchPhone" placeholder="Enter phone number..." />
        </div>
      </div>
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
        <tr v-for="customer in filteredCustomers" :key="customer.id">
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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const customers = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const form = ref({ name: '', phone: '' });

const searchName = ref('');
const searchPhone = ref('');

const filteredCustomers = computed(() => {
  return customers.value.filter(c => {
    const matchName = !searchName.value || c.name.toLowerCase().includes(searchName.value.toLowerCase());
    const matchPhone = !searchPhone.value || (c.phone && c.phone.toLowerCase().includes(searchPhone.value.toLowerCase()));

    return matchName && matchPhone;
  });
});

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
.header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.filters-section { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.filters-title { margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: #444; }
.filters { display: flex; gap: 1.5rem; }
.filter-group { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; text-align: left; }
.filter-group label { font-size: 0.9rem; font-weight: 500; color: #555; }
.filter-group input { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #ddd); border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; background: #fafafa; transition: border-color 0.2s, background 0.2s; }
.filter-group input:focus { outline: none; border-color: var(--primary-color, #4f46e5); background: white; }
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
