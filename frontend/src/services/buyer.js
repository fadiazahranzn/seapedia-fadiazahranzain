import api from './api'

export const buyerApi = {
  // Wallet
  getWallet: () => api.get('/buyer/wallet'),
  topUp: (amount) => api.post('/buyer/wallet/topup', { amount }),
  getTransactions: () => api.get('/buyer/wallet/transactions'),

  // Addresses
  getAddresses: () => api.get('/buyer/addresses'),
  createAddress: (data) => api.post('/buyer/addresses', data),
  updateAddress: (id, data) => api.put(`/buyer/addresses/${id}`, data),
  deleteAddress: (id) => api.delete(`/buyer/addresses/${id}`),
  setDefault: (id) => api.patch(`/buyer/addresses/${id}/default`),

  // Cart
  getCart: () => api.get('/buyer/cart'),
  addItem: (data) => api.post('/buyer/cart/items', data),
  updateItem: (id, quantity) => api.put(`/buyer/cart/items/${id}`, { quantity }),
  removeItem: (id) => api.delete(`/buyer/cart/items/${id}`),
  clearCart: () => api.delete('/buyer/cart'),

  // Orders
  previewCheckout: (delivery_method) => api.post('/buyer/checkout/preview', { delivery_method }),
  checkout: (data) => api.post('/buyer/checkout', data),
  getOrders: () => api.get('/buyer/orders'),
  getOrder: (id) => api.get(`/buyer/orders/${id}`),
}
