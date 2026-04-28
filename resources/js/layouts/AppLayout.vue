<template>
  <div class="app-container">
    <aside class="sidebar" :class="{ 'collapsed': isCollapsed }">
      <div class="logo">
        <span v-if="!isCollapsed">POS System</span>
        <span v-else>P</span>
      </div>
      <nav class="menu">
        <router-link to="/" class="menu-item">
          <i class="icon">📊</i>
          <span v-if="!isCollapsed">Dashboard</span>
        </router-link>
        <router-link to="/pos" class="menu-item pos-btn">
          <i class="icon">🛒</i>
          <span v-if="!isCollapsed">POS Billing</span>
        </router-link>
        <router-link v-if="auth.user?.role === 'admin'" to="/products" class="menu-item">
          <i class="icon">📦</i>
          <span v-if="!isCollapsed">Products</span>
        </router-link>
        <router-link v-if="auth.user?.role === 'admin'" to="/categories" class="menu-item">
          <i class="icon">📁</i>
          <span v-if="!isCollapsed">Categories</span>
        </router-link>
        <router-link to="/customers" class="menu-item">
          <i class="icon">👥</i>
          <span v-if="!isCollapsed">Customers</span>
        </router-link>
        <router-link to="/orders" class="menu-item">
          <i class="icon">📜</i>
          <span v-if="!isCollapsed">Orders</span>
        </router-link>
        <router-link v-if="auth.user?.role === 'admin'" to="/users" class="menu-item">
          <i class="icon">👤</i>
          <span v-if="!isCollapsed">Users</span>
        </router-link>
      </nav>
      <div class="footer-sidebar">
        <button @click="logout" class="logout-btn">
          <i>🚪</i>
          <span v-if="!isCollapsed">Logout</span>
        </button>
      </div>
    </aside>

    <main class="main-content">
      <header class="top-nav">
        <button @click="isCollapsed = !isCollapsed" class="toggle-btn">☰</button>
        <div class="user-info">
          <span>{{ auth.user?.name }} ({{ auth.user?.role }})</span>
        </div>
      </header>
      <section class="content">
        <router-view></router-view>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();
const isCollapsed = ref(false);

const logout = async () => {
  await auth.logout();
  router.push('/login');
};
</script>

<style scoped>
.app-container {
  display: flex;
  height: 100vh;
}

.sidebar {
  width: 280px;
  background: var(--sidebar-bg);
  color: #fed7aa;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 10px rgba(0,0,0,0.1);
}

.sidebar.collapsed {
  width: 70px;
}

.logo {
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  font-family: 'Outfit', sans-serif;
  color: white;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.menu {
  flex: 1;
  padding: 1rem 0;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 0.85rem 2rem;
  color: #fdba74;
  text-decoration: none;
  gap: 1.25rem;
  font-weight: 500;
  transition: all 0.2s;
}

.menu-item:hover, .router-link-active {
  background: rgba(255,255,255,0.1);
  color: white;
  border-left: 4px solid var(--primary-color);
}

.pos-btn {
  background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
  color: white;
  margin: 1.5rem;
  border-radius: 12px;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
}

.footer-sidebar {
  padding: 1rem;
  border-top: 1px solid #334155;
}

.logout-btn {
  width: 100%;
  background: transparent;
  border: none;
  color: #ef4444;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  cursor: pointer;
}

.main-content {
  flex: 1;
  background: #f1f5f9;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.top-nav {
  height: 64px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.toggle-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

.content {
  padding: 2rem;
  flex: 1;
}
</style>
