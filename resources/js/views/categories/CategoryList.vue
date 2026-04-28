<template>
  <div class="categories-view">
    <div class="header-actions">
      <h1>Categories</h1>
      <button class="add-btn" @click="showModal = true">+ Add Category</button>
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
        <tr v-for="cat in categories" :key="cat.id">
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
import { ref, onMounted } from 'vue';
import axios from 'axios';

const categories = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const form = ref({ name: '' });

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
  showModal.ref = false;
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
  margin-bottom: 2rem;
}

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
}

.modal {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 400px;
}
</style>
