<template>
  <div class="products-view">
    <div class="header-actions">
      <h1>Products</h1>
      <button class="add-btn" @click="showModal = true">+ Add Product</button>
    </div>

    <div class="filters-section">
      <h3 class="filters-title">Filters</h3>
      <div class="filters">
        <div class="filter-group">
          <label>Product Name</label>
          <input v-model="searchName" placeholder="Enter product name..." />
        </div>
        <div class="filter-group">
          <label>Category</label>
          <select v-model="searchCategory">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Barcode</label>
          <input v-model="searchBarcode" placeholder="Enter barcode..." />
        </div>
      </div>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Barcode</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in filteredProducts" :key="product.id">
          <td>{{ product.name }}</td>
          <td>{{ product.category?.name }}</td>
          <td>${{ product.price }}</td>
          <td>{{ product.stock }}</td>
          <td>{{ product.barcode }}</td>
          <td>
            <button @click="editProduct(product)">Edit</button>
            <button @click="deleteProduct(product.id)" class="delete">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="showModal" class="modal-overlay">
      <div class="modal">
        <h3>{{ editingId ? 'Edit' : 'Add' }} Product</h3>
        <form @submit.prevent="saveProduct">
          <div class="form-group">
            <label>Name</label>
            <input v-model="form.name" required />
          </div>
          <div class="form-group">
            <label>Category</label>
            <select v-model="form.category_id" required>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Price</label>
              <input type="number" step="0.01" v-model="form.price" required />
            </div>
            <div class="form-group">
              <label>Stock</label>
              <input type="number" v-model="form.stock" required />
            </div>
          </div>
          <div class="form-group">
            <label>Barcode</label>
            <input v-model="form.barcode" />
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

const products = ref([]);
const categories = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const form = ref({ name: '', category_id: null, price: 0, stock: 0, barcode: '' });

const searchName = ref('');
const searchCategory = ref('');
const searchBarcode = ref('');

const filteredProducts = computed(() => {
  return products.value.filter(p => {
    const matchName = !searchName.value || p.name.toLowerCase().includes(searchName.value.toLowerCase());
    const matchCategory = searchCategory.value === '' || p.category_id == searchCategory.value;
    const matchBarcode = !searchBarcode.value || (p.barcode && p.barcode.toLowerCase().includes(searchBarcode.value.toLowerCase()));
    
    return matchName && matchCategory && matchBarcode;
  });
});

const fetchData = async () => {
  const [pRes, cRes] = await Promise.all([
    axios.get('/api/products'),
    axios.get('/api/categories')
  ]);
  products.value = pRes.data;
  categories.value = cRes.data;
};

const saveProduct = async () => {
  if (editingId.value) {
    await axios.put(`/api/products/${editingId.value}`, form.value);
  } else {
    await axios.post('/api/products', form.value);
  }
  closeModal();
  fetchData();
};

const editProduct = (p) => {
  editingId.value = p.id;
  form.value = { ...p };
  showModal.value = true;
};

const deleteProduct = async (id) => {
  if (confirm('Are you sure?')) {
    await axios.delete(`/api/products/${id}`);
    fetchData();
  }
};

const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
  form.value = { name: '', category_id: null, price: 0, stock: 0, barcode: '' };
};

onMounted(fetchData);
</script>

<style scoped>
.header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.filters-section { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.filters-title { margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: #444; }
.filters { display: flex; gap: 1.5rem; }
.filter-group { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; }
.filter-group label { font-size: 0.9rem; font-weight: 500; color: #555; }
.filter-group input, .filter-group select { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #ddd); border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; background: #fafafa; transition: border-color 0.2s, background 0.2s; }
.filter-group input:focus, .filter-group select:focus { outline: none; border-color: var(--primary-color, #4f46e5); background: white; }
.data-table { width: 100%; background: white; border-collapse: collapse; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; }
.form-group input, .form-group select { width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; box-sizing: border-box; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; padding: 2rem; border-radius: 12px; width: 500px; }
.modal-actions { margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem; }
.add-btn, .save-btn { background: var(--primary-color); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; }
.delete { color: var(--danger); margin-left: 1rem; background: none; border: none; cursor: pointer; }
</style>
