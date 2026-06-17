<template>
  <div class="admin-shell">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div class="sidebar-logo">
        <div class="logo-mark">
          <ShoppingBag class="w-4 h-4 text-white" />
        </div>
        <span class="logo-text">SEAPEDIA</span>
        <span class="logo-badge">Admin</span>
      </div>

      <nav class="sidebar-body">
        <span class="nav-label">Overview</span>
        <RouterLink to="/admin/dashboard" class="nav-item" :class="isActive('/admin/dashboard') && 'active'">
          <LayoutDashboard class="w-[15px] h-[15px]" /> Dashboard
        </RouterLink>

        <span class="nav-label">Manajemen</span>
        <RouterLink to="/admin/vouchers" class="nav-item" :class="isActive('/admin/vouchers') && 'active'">
          <Tag class="w-[15px] h-[15px]" /> Voucher
        </RouterLink>
        <RouterLink to="/admin/promos" class="nav-item" :class="isActive('/admin/promos') && 'active'">
          <Star class="w-[15px] h-[15px]" /> Promo
        </RouterLink>

        <span class="nav-label">Sistem</span>
        <button class="nav-item" @click="handleLogout">
          <LogOut class="w-[15px] h-[15px]" /> Keluar
        </button>
      </nav>

      <div class="sidebar-footer">
        <div class="user-row" @click="router.push('/select-role')" v-if="(auth.user?.roles?.length ?? 0) > 1">
          <div class="user-avatar">{{ auth.user?.name?.[0]?.toUpperCase() }}</div>
          <div>
            <div class="user-name">{{ auth.user?.name }}</div>
            <div class="user-role">Super Administrator</div>
          </div>
          <RefreshCw class="w-3.5 h-3.5 ml-auto text-[#a06070]" />
        </div>
        <div class="user-row" v-else>
          <div class="user-avatar">{{ auth.user?.name?.[0]?.toUpperCase() }}</div>
          <div>
            <div class="user-name">{{ auth.user?.name }}</div>
            <div class="user-role">Super Administrator</div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="admin-main">
      <!-- Topbar -->
      <header class="admin-topbar">
        <div class="topbar-breadcrumb">
          <LayoutDashboard class="w-3.5 h-3.5 opacity-50" />
          <ChevronRight class="w-3 h-3 opacity-30" />
          <span class="current">{{ currentPageTitle }}</span>
        </div>

        <div class="topbar-right">
          <span class="badge-live">Live</span>
          <span class="topbar-date">{{ todayLabel }}</span>
          <button class="topbar-icon-btn" @click="loadData">
            <RefreshCw class="w-[15px] h-[15px]" />
          </button>
        </div>
      </header>

      <!-- Page content -->
      <main class="admin-content">
        <RouterView />
      </main>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, RouterView, useRouter, useRoute } from 'vue-router'
import { ShoppingBag, LayoutDashboard, Tag, Star, LogOut, RefreshCw, ChevronRight } from '@lucide/vue'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue-sonner'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

function isActive(path) { return route.path.startsWith(path) }

const pageMap = {
  '/admin/dashboard': 'Dashboard',
  '/admin/vouchers':  'Voucher',
  '/admin/promos':    'Promo',
}
const currentPageTitle = computed(() => pageMap[route.path] ?? 'Admin')

const todayLabel = computed(() => new Date().toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }))

async function loadData() {
  // trigger refresh on dashboard by emitting reload — just navigate same route
  router.go(0)
}

async function handleLogout() {
  await auth.logout()
  toast.success('Berhasil keluar.')
  router.push('/login')
}
</script>

<style scoped>
/* ── Shell ── */
.admin-shell {
  display: flex;
  min-height: 100vh;
  background: #fdf2f5;
  font-family: 'Inter', system-ui, sans-serif;
}

/* ── Sidebar ── */
.admin-sidebar {
  width: 240px;
  min-height: 100vh;
  position: fixed;
  left: 0; top: 0; bottom: 0;
  background: #fff;
  border-right: 1px solid #f3e0e6;
  display: flex;
  flex-direction: column;
  z-index: 40;
}

.sidebar-logo {
  height: 57px;
  padding: 0 18px;
  display: flex;
  align-items: center;
  gap: 9px;
  border-bottom: 1px solid #f3e0e6;
  flex-shrink: 0;
}
.logo-mark {
  width: 28px; height: 28px;
  background: #c41952;
  border-radius: 7px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.logo-text { font-weight: 800; font-size: 14px; letter-spacing: -.3px; color: #1a1a1a; }
.logo-badge {
  margin-left: auto;
  font-size: 9px; font-weight: 700;
  background: #fce4ec; color: #c41952;
  padding: 2px 7px; border-radius: 20px;
  border: 1px solid #f3c6d4;
  letter-spacing: .5px; text-transform: uppercase;
}

.sidebar-body { flex: 1; overflow-y: auto; padding: 10px 8px; display: flex; flex-direction: column; gap: 1px; }

.nav-label {
  font-size: 10px; font-weight: 700; letter-spacing: .8px;
  text-transform: uppercase; color: #c4a8b4;
  padding: 12px 12px 5px;
}

.nav-item {
  display: flex; align-items: center; gap: 9px;
  padding: 8px 12px;
  border-radius: 7px;
  color: #a06070;
  font-size: 13.5px; font-weight: 500;
  cursor: pointer;
  transition: background 150ms, color 150ms;
  border: none; background: none;
  width: 100%; text-align: left; text-decoration: none;
}
.nav-item:hover { background: #fce4ec; color: #c41952; }
.nav-item.active { background: #fce4ec; color: #c41952; font-weight: 600; }

.sidebar-footer { padding: 10px; border-top: 1px solid #f3e0e6; }
.user-row {
  display: flex; align-items: center; gap: 9px;
  padding: 8px; border-radius: 7px;
  cursor: pointer; transition: background 150ms;
}
.user-row:hover { background: #fce4ec; }
.user-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  background: #c41952; color: #fff;
  font-size: 12px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.user-name { font-size: 12px; font-weight: 600; line-height: 1.2; color: #1a1a1a; }
.user-role { font-size: 10px; color: #a06070; }

/* ── Main ── */
.admin-main {
  margin-left: 240px;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* ── Topbar ── */
.admin-topbar {
  height: 57px;
  border-bottom: 1px solid #f3e0e6;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 24px;
  position: sticky; top: 0;
  background: rgba(255,255,255,.97);
  backdrop-filter: blur(8px);
  z-index: 30;
}
.topbar-breadcrumb {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; color: #a06070;
}
.topbar-breadcrumb .current { color: #1a1a1a; font-weight: 600; }
.topbar-right { display: flex; align-items: center; gap: 10px; }
.topbar-date { font-size: 12px; color: #a06070; }

.badge-live {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 11px; font-weight: 600;
  color: #15803d; background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 99px; padding: 3px 9px;
}
.badge-live::before {
  content: ''; width: 6px; height: 6px;
  background: #15803d; border-radius: 50%;
  animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

.topbar-icon-btn {
  width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 7px; border: 1px solid #f3e0e6;
  background: #fff; color: #a06070;
  cursor: pointer; transition: all 150ms;
}
.topbar-icon-btn:hover { background: #fce4ec; color: #c41952; border-color: #f3c6d4; }

/* ── Content ── */
.admin-content { flex: 1; padding: 28px 28px 48px; }
</style>
