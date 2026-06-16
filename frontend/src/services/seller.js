import api from './api'

export const sellerApi = {
  // Store
  getStore: () => api.get('/seller/store'),
  createStore: (data) => api.post('/seller/store', data),
  updateStore: (data) => api.put('/seller/store', data),

  // Products
  getProducts: (params) => api.get('/seller/products', { params }),
  createProduct: (data) => api.post('/seller/products', data),
  updateProduct: (id, data) => api.put(`/seller/products/${id}`, data),
  deleteProduct: (id) => api.delete(`/seller/products/${id}`),

  // Orders
  getOrders: () => api.get('/seller/orders'),
  processOrder: (id) => api.patch(`/seller/orders/${id}/process`),
  getReport: () => api.get('/seller/report'),
}
