import api from './api'

export const adminApi = {
  // Dashboard & monitoring
  getDashboard: () => api.get('/admin/dashboard'),
  getUsers: () => api.get('/admin/users'),
  getStores: () => api.get('/admin/stores'),
  getProducts: () => api.get('/admin/products'),
  getOrders: () => api.get('/admin/orders'),
  getDeliveries: () => api.get('/admin/deliveries'),
  getOverdueOrders: () => api.get('/admin/overdue'),

  // Overdue handling
  processOverdue: () => api.post('/admin/overdue/process'),
  simulateNextDay: () => api.post('/admin/simulate-next-day'),

  // Vouchers
  getVouchers: () => api.get('/admin/vouchers'),
  getVoucher: (id) => api.get(`/admin/vouchers/${id}`),
  createVoucher: (data) => api.post('/admin/vouchers', data),
  deleteVoucher: (id) => api.delete(`/admin/vouchers/${id}`),

  // Promos
  getPromos: () => api.get('/admin/promos'),
  getPromo: (id) => api.get(`/admin/promos/${id}`),
  createPromo: (data) => api.post('/admin/promos', data),
  deletePromo: (id) => api.delete(`/admin/promos/${id}`),
}
