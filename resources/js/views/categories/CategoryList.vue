<template>
  <div class="categories-view">
    <div class="header-actions">
      <h1>Categories</h1>
      <button class="add-btn" @click="showModal = true">+ Add Category</button>
    </div>

    <div class="filters-section">
      <h3 class="filters-title">Filters</h3>
      <div class="filters">
        <div class="filter-group">
          <label>Category Name</label>
          <input v-model="searchCategory" placeholder="Enter category name..." />
        </div>
      </div>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Products Count</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="cat in filteredCategories" :key="cat.id">
          <td>{{ cat.name }}</td>
          <td>{{ cat.products_count }}</td>
          <td>
            <button @click="editCategory(cat)">Edit</button>
            <button @click="deleteCategory(cat.id)" class="delete">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="showModal" class="modal-overlay">
      <div class="modal">
        <h3>{{ editingId ? 'Edit' : 'Add' }} Category</h3>
        <form @submit.prevent="saveCategory">
          <div class="form-group">
            <label>Name</label>
            <input v-model="form.name" required />
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

const categories = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const form = ref({ name: '' });

const searchCategory = ref('');

const filteredCategories = computed(() => {
  return categories.value.filter(cat => 
    !searchCategory.value || cat.name.toLowerCase().includes(searchCategory.value.toLowerCase())
  );
});

const fetchCategories = async () => {
  const res = await axios.get('/api/categories');
  categories.value = res.data;
};

const saveCategory = async () => {
  if (editingId.value) {
    await axios.put(`/api/categories/${editingId.value}`, form.value);
  } else {
    await axios.post('/api/categories', form.value);
  }
  closeModal();
  fetchCategories();
};

const editCategory = (cat) => {
  editingId.value = cat.id;
  form.value = { name: cat.name };
  showModal.value = true;
};

const deleteCategory = async (id) => {
  if (confirm('Are you sure?')) {
    await axios.delete(`/api/categories/${id}`);
    fetchCategories();
  }
};

const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
  form.value = { name: '' };
};

onMounted(fetchCategories);
</script>

<style scoped>
.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.filters-section { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.filters-title { margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: #444; }
.filters { display: flex; gap: 1.5rem; }
.filter-group { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; text-align: left; max-width: 300px; }
.filter-group label { font-size: 0.9rem; font-weight: 500; color: #555; }
.filter-group input { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #ddd); border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; background: #fafafa; transition: border-color 0.2s, background 0.2s; }
.filter-group input:focus { outline: none; border-color: var(--primary-color, #4f46e5); background: white; }

.data-table {
  width: 100%;
  background: white;
  border-collapse: collapse;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

.add-btn, .save-btn {
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
}

.delete {
  color: var(--danger);
  margin-left: 1rem;
}

.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 400px;
}

.modal h3 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  color: #333;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #444;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color, #ddd);
  border-radius: 6px;
  box-sizing: border-box;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary-color, #4f46e5);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.modal-actions button[type="button"] {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #cbd5e1;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.modal-actions button[type="button"]:hover {
  background: #e2e8f0;
}
</style>
