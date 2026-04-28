import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    component: () => import('../layouts/AppLayout.vue'),
    meta: { auth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue')
      },
      {
        path: 'pos',
        name: 'POS',
        component: () => import('../views/pos/Pos.vue')
      },
      {
        path: 'products',
        name: 'Products',
        component: () => import('../views/products/ProductList.vue'),
        meta: { roles: ['admin'] }
      },
      {
        path: 'categories',
        name: 'Categories',
        component: () => import('../views/categories/CategoryList.vue'),
        meta: { roles: ['admin'] }
      },
      {
        path: 'customers',
        name: 'Customers',
        component: () => import('../views/customers/CustomerList.vue')
      },
      {
        path: 'orders',
        name: 'Orders',
        component: () => import('../views/orders/OrderList.vue')
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('../views/users/UserList.vue'),
        meta: { roles: ['admin'] }
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore();
  
  if (auth.token && !auth.user) {
    await auth.fetchUser();
  }

  if (to.meta.auth && !auth.isAuthenticated) {
    next({ name: 'Login' });
  } else if (to.meta.guest && auth.isAuthenticated) {
    next({ name: 'Dashboard' });
  } else if (to.meta.roles && !to.meta.roles.includes(auth.user?.role)) {
    next({ name: 'Dashboard' });
  } else {
    next();
  }
});

export default router;
