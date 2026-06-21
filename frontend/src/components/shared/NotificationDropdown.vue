<template>
  <div class="notif-wrap" ref="wrapRef">
    <button class="notif-trigger" @click="toggle">
      <Bell :size="iconSize" />
      <span v-if="store.unreadCount > 0" class="notif-badge">
        {{ store.unreadCount > 9 ? '9+' : store.unreadCount }}
      </span>
    </button>

    <Transition name="fade">
      <div v-if="open" class="notif-panel">
        <div class="notif-header">
          <span>Notifikasi</span>
          <button v-if="store.unreadCount > 0" @click="store.markAllRead()">Baca semua</button>
        </div>

        <p v-if="store.loading" class="notif-state">Memuat...</p>
        <p v-else-if="!store.items.length" class="notif-state">Tidak ada notifikasi</p>

        <div v-else class="notif-list">
          <div
            v-for="n in store.items"
            :key="n.id"
            class="notif-item"
            :class="{ unread: !n.is_read }"
            @click="handleClick(n)"
          >
            <div class="notif-content">
              <p class="notif-title">{{ n.title }}</p>
              <p class="notif-body">{{ n.body }}</p>
            </div>
            <span class="notif-time">{{ timeAgo(n.created_at) }}</span>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Bell } from '@lucide/vue'
import { useNotificationStore } from '@/stores/notifications'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({ iconSize: { type: Number, default: 16 } })

const store = useNotificationStore()
const auth = useAuthStore()
const router = useRouter()
const open = ref(false)
const wrapRef = ref(null)

function toggle() {
  open.value = !open.value
  if (open.value) store.fetch()
}

function handleClick(n) {
  if (!n.is_read) store.markRead(n.id)
  if (n.link) { router.push(n.link); open.value = false }
}

function timeAgo(d) {
  const s = Math.floor((Date.now() - new Date(d)) / 1000)
  if (s < 60) return 'Baru saja'
  if (s < 3600) return `${Math.floor(s / 60)} mnt lalu`
  if (s < 86400) return `${Math.floor(s / 3600)} jam lalu`
  return `${Math.floor(s / 86400)} hari lalu`
}

function onOutside(e) {
  if (wrapRef.value && !wrapRef.value.contains(e.target)) open.value = false
}

let pollTimer = null
watch(() => auth.isLoggedIn, (v) => {
  if (v) { store.fetch(); pollTimer = setInterval(() => store.fetch(), 30000) }
  else clearInterval(pollTimer)
}, { immediate: true })

onMounted(() => document.addEventListener('click', onOutside, true))
onBeforeUnmount(() => { document.removeEventListener('click', onOutside, true); clearInterval(pollTimer) })
</script>

<style scoped>
.notif-wrap { position: relative; }

.notif-trigger {
  position: relative;
  display: flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; border-radius: 8px;
  background: #fdf2f5; border: 1px solid #f3e0e6;
  color: #a06070; cursor: pointer; transition: background .15s, color .15s;
}
.notif-trigger:hover { background: #fce4ec; color: #c41952; }

.notif-badge {
  position: absolute; top: -4px; right: -4px;
  background: #c41952; color: #fff; font-size: 10px; font-weight: 700;
  min-width: 16px; height: 16px; border-radius: 9999px;
  display: flex; align-items: center; justify-content: center;
  padding: 0 3px; border: 2px solid #fff;
}

.notif-panel {
  position: absolute; top: calc(100% + 6px); right: 0;
  width: 300px; max-height: 400px;
  background: #fff; border: 1px solid #f3e0e6;
  border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.08);
  overflow: hidden; display: flex; flex-direction: column;
  z-index: 200;
}

.notif-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 14px; border-bottom: 1px solid #f3e0e6;
  font-size: 13px; font-weight: 600; color: #1a1a1a; flex-shrink: 0;
}
.notif-header button {
  font-size: 11px; color: #c41952; background: none; border: none; cursor: pointer;
}

.notif-state { padding: 28px; text-align: center; font-size: 13px; color: #a06070; }

.notif-list { overflow-y: auto; flex: 1; }

.notif-item {
  display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;
  padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #fdf2f5;
  transition: background .12s;
}
.notif-item:hover { background: #fdf2f5; }
.notif-item.unread { background: #fef6f8; }
.notif-item.unread:hover { background: #fce7ef; }

.notif-content { flex: 1; min-width: 0; }
.notif-title { font-size: 13px; font-weight: 600; color: #1a1a1a; }
.notif-body  { font-size: 12px; color: #5a3a44; margin-top: 2px; line-height: 1.4; }
.notif-time  { font-size: 11px; color: #a06070; flex-shrink: 0; white-space: nowrap; }

.fade-enter-active, .fade-leave-active { transition: opacity .15s, transform .15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
