import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // Public (with PublicLayout)
  {
    path: '/',
    component: () => import('@/layouts/PublicLayout.vue'),
    children: [
      { path: '', component: () => import('@/views/public/HomeView.vue') },
      { path: 'products', component: () => import('@/views/public/ProductsView.vue') },
      { path: 'products/:id', component: () => import('@/views/public/ProductDetailView.vue') },
      { path: 'stores/:id', component: () => import('@/views/public/StoreDetailView.vue') },
      { path: 'reviews', component: () => import('@/views/public/ReviewsView.vue') },
    ],
  },

  // Auth
  { path: '/login', component: () => import('@/views/auth/LoginView.vue'), meta: { guest: true } },
  { path: '/register', component: () => import('@/views/auth/RegisterView.vue'), meta: { guest: true } },
  { path: '/select-role', component: () => import('@/views/auth/SelectRoleView.vue'), meta: { auth: true } },

  // Buyer
  {
    path: '/buyer',
    component: () => import('@/layouts/BuyerLayout.vue'),
    meta: { auth: true, role: 'buyer' },
    children: [
      { path: '', redirect: '/buyer/home' },
      { path: 'home', component: () => import('@/views/buyer/HomeView.vue') },
      { path: 'wallet', component: () => import('@/views/buyer/WalletView.vue') },
      { path: 'addresses', component: () => import('@/views/buyer/AddressesView.vue') },
      { path: 'cart', component: () => import('@/views/buyer/CartView.vue') },
      { path: 'orders', component: () => import('@/views/buyer/OrdersView.vue') },
      { path: 'orders/:id', component: () => import('@/views/buyer/OrderDetailView.vue') },
      { path: 'products', component: () => import('@/views/public/ProductsView.vue') },
    ],
  },

  // Seller
  {
    path: '/seller',
    component: () => import('@/layouts/SellerLayout.vue'),
    meta: { auth: true, role: 'seller' },
    children: [
      { path: '', redirect: '/seller/dashboard' },
      { path: 'dashboard', component: () => import('@/views/seller/DashboardView.vue') },
      { path: 'store', component: () => import('@/views/seller/StoreView.vue') },
      { path: 'products', component: () => import('@/views/seller/ProductsView.vue') },
    ],
  },

  // Driver
  {
    path: '/driver',
    component: () => import('@/layouts/DriverLayout.vue'),
    meta: { auth: true, role: 'driver' },
    children: [
      { path: '', redirect: '/driver/jobs' },
      { path: 'jobs', component: () => import('@/views/driver/JobsView.vue') },
    ],
  },

  // Admin
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { auth: true, role: 'admin' },
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', component: () => import('@/views/admin/DashboardView.vue') },
    ],
  },

  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (to.meta.auth && !auth.isLoggedIn) return '/login'
  if (to.meta.guest && auth.isLoggedIn) {
    return auth.activeRole ? `/${auth.activeRole}` : '/select-role'
  }
  if (to.meta.role && auth.activeRole !== to.meta.role) {
    return auth.activeRole ? `/${auth.activeRole}` : '/select-role'
  }
})

export default router
