import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const activeRole = ref(localStorage.getItem('active_role') || null)

  const isLoggedIn = computed(() => !!token.value)

  async function login(credentials) {
    const { data } = await api.post('/auth/login', credentials)
    token.value = data.token
    activeRole.value = null
    // Normalize roles to string array for UI consumption
    const roles = data.roles || []
    user.value = { ...data.user, roles }
    localStorage.setItem('token', data.token)
    localStorage.removeItem('active_role')

    // Auto-select role if only one; otherwise caller redirects to /select-role
    if (roles.length === 1) {
      await switchRole(roles[0])
    }
  }

  async function register(payload) {
    const { data } = await api.post('/auth/register', payload)
    token.value = data.token
    activeRole.value = data.active_role
    user.value = data.user
    localStorage.setItem('token', data.token)
    localStorage.setItem('active_role', data.active_role)
  }

  async function fetchMe() {
    const { data } = await api.get('/auth/me')
    const roles = (data.user.roles || []).map(r => (typeof r === 'string' ? r : r.role))
    user.value = { ...data.user, roles }
    activeRole.value = data.active_role
  }

  async function switchRole(role) {
    const { data } = await api.post('/auth/switch-role', { role })
    token.value = data.token
    activeRole.value = data.active_role
    localStorage.setItem('token', data.token)
    localStorage.setItem('active_role', data.active_role)
  }

  async function logout() {
    await api.post('/auth/logout')
    token.value = null
    user.value = null
    activeRole.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('active_role')
  }

  return { user, token, activeRole, isLoggedIn, login, register, fetchMe, switchRole, logout }
})
