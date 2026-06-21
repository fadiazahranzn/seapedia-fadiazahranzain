import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { notificationApi } from '@/services/api'

export const useNotificationStore = defineStore('notifications', () => {
  const items = ref([])
  const loading = ref(false)

  const unreadCount = computed(() => items.value.filter(n => !n.is_read).length)

  async function fetch() {
    loading.value = true
    try {
      const { data } = await notificationApi.getAll()
      items.value = data.data
    } catch {}
    finally { loading.value = false }
  }

  async function markRead(id) {
    await notificationApi.markRead(id)
    const n = items.value.find(i => i.id === id)
    if (n) n.is_read = true
  }

  async function markAllRead() {
    await notificationApi.markAllRead()
    items.value.forEach(n => { n.is_read = true })
  }

  return { items, loading, unreadCount, fetch, markRead, markAllRead }
})
