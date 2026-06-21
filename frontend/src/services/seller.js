import api from './api'

export const sellerApi = {
  // Store
  getStore: () => api.get('/seller/store'),
  createStore: (data) => api.post('/seller/store', data),
  updateStore: (data) => api.put('/seller/store', data),

  // Products
  getProducts: (params) => api.get('/seller/products', { params }),
  createProduct: (data) => api.post('/seller/products', data, data instanceof FormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : {}),
  updateProduct: (id, data) => {
    if (data instanceof FormData) {
      return api.post(`/seller/products/${id}`, data, { headers: { 'Content-Type': 'multipart/form-data' } })
    }
    return api.put(`/seller/products/${id}`, data)
  },
  deleteProduct: (id) => api.delete(`/seller/products/${id}`),

  // Orders
  getOrders: () => api.get('/seller/orders'),
  processOrder: (id) => api.patch(`/seller/orders/${id}/process`),
  getTracking: (id) => api.get(`/seller/orders/${id}/tracking`),
  getReport: () => api.get('/seller/report'),
}
