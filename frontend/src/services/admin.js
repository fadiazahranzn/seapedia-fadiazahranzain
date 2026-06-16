import api from './api'

export const adminApi = {
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
