<template>
  <div class="user-management">
    <div class="header-row">
      <h1>User Management</h1>
      <button @click="showModal = true" class="add-btn">Add New User</button>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>
            <span :class="['role-badge', user.role]">{{ user.role }}</span>
          </td>
          <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
          <td>
            <button @click="deleteUser(user)" class="delete-btn" v-if="user.id !== auth.user?.id">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Add User Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal">
        <h3>Add New User</h3>
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label>Full Name</label>
            <input v-model="form.name" required placeholder="Enter full name" />
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input v-model="form.email" type="email" required placeholder="Enter email" />
          </div>
          <div class="form-group">
            <label>Password</label>
            <input v-model="form.password" type="password" required placeholder="Min 8 characters" />
          </div>
          <div class="form-group">
            <label>Role</label>
            <select v-model="form.role" required>
              <option value="cashier">Cashier</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" @click="showModal = false" class="cancel-btn">Cancel</button>
            <button type="submit" class="save-btn" :disabled="loading">
              {{ loading ? 'Saving...' : 'Create User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const users = ref([]);
const showModal = ref(false);
const loading = ref(false);

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'cashier'
});

const fetchUsers = async () => {
  const res = await axios.get('/api/users');
  users.value = res.data;
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    await axios.post('/api/users', form.value);
    await fetchUsers();
    showModal.value = false;
    form.value = { name: '', email: '', password: '', role: 'cashier' };
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to create user');
  } finally {
    loading.value = false;
  }
};

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}?`)) return;
  try {
    await axios.delete(`/api/users/${user.id}`);
    await fetchUsers();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete user');
  }
};

onMounted(fetchUsers);
</script>

<style scoped>
.header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.add-btn {
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
}

.data-table {
  width: 100%;
  background: white;
  border-collapse: collapse;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow);
}

th, td {
  padding: 1.25rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

th {
  background: #f8fafc;
  font-weight: 600;
  color: #64748b;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.role-badge.admin { background: #dcfce7; color: #166534; }
.role-badge.cashier { background: #dbeafe; color: #1e40af; }

.delete-btn {
  color: #ef4444;
  background: none;
  border: none;
  cursor: pointer;
  font-weight: 500;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 2.5rem;
  border-radius: 20px;
  width: 450px;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #1e293b;
}

.form-group input, .form-group select {
  width: 100%;
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid var(--border-color);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.cancel-btn {
  background: #f1f5f9;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
}

.save-btn {
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}
</style>
