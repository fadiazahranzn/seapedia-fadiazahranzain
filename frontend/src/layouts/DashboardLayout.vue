<template>
  <div class="min-h-screen bg-background flex flex-col">
    <!-- Top bar -->
    <header class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur">
      <div class="h-14 px-4 sm:px-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <RouterLink to="/" class="font-bold text-primary flex items-center gap-1.5">
            <ShoppingBag class="w-5 h-5" />
            <span class="hidden sm:inline">SEAPEDIA</span>
          </RouterLink>
          <span class="text-muted-foreground">/</span>
          <Badge class="capitalize">{{ auth.activeRole }}</Badge>
        </div>

        <nav class="hidden md:flex items-center gap-1">
          <RouterLink
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm transition-colors"
            :class="isActive(item.to) ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
          >
            <component :is="item.icon" class="w-4 h-4" />
            {{ item.label }}
          </RouterLink>
        </nav>

        <div class="flex items-center gap-2">
          <span class="text-sm text-muted-foreground hidden sm:inline">{{ auth.user?.name }}</span>
          <NotificationDropdown :iconSize="16" />
          <Button variant="ghost" size="sm" @click="router.push('/select-role')" v-if="(auth.user?.roles?.length ?? 0) > 1">
            <RefreshCw class="w-4 h-4" />
          </Button>
          <Button variant="ghost" size="sm" @click="handleLogout">
            <LogOut class="w-4 h-4" />
          </Button>
          <!-- Mobile menu -->
          <button class="md:hidden p-1.5 rounded-md hover:bg-muted" @click="mobileOpen = !mobileOpen">
            <Menu class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Mobile nav -->
      <div v-if="mobileOpen" class="md:hidden border-t bg-background px-4 py-2 space-y-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-2 px-3 py-2 rounded-md text-sm"
          :class="isActive(item.to) ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
          @click="mobileOpen = false"
        >
          <component :is="item.icon" class="w-4 h-4" />
          {{ item.label }}
        </RouterLink>
      </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 py-6">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink, RouterView, useRouter, useRoute } from 'vue-router'
import { ShoppingBag, LogOut, RefreshCw, Menu, ShoppingCart, Package, MapPin, Wallet, LayoutDashboard, ClipboardList, Truck, History, Users, Tag, Settings } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue-sonner'
import NotificationDropdown from '@/components/shared/NotificationDropdown.vue'

const props = defineProps({ role: String })
const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const mobileOpen = ref(false)

const navMap = {
  buyer: [
    { to: '/buyer/home', label: 'Beranda', icon: LayoutDashboard },
    { to: '/buyer/products', label: 'Produk', icon: Package },
    { to: '/buyer/cart', label: 'Keranjang', icon: ShoppingCart },
    { to: '/buyer/orders', label: 'Pesanan', icon: ClipboardList },
    { to: '/buyer/report', label: 'Laporan', icon: History },
    { to: '/buyer/wallet', label: 'Dompet', icon: Wallet },
    { to: '/buyer/addresses', label: 'Alamat', icon: MapPin },
  ],
  seller: [
    { to: '/seller/dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { to: '/seller/products', label: 'Produk', icon: Package },
    { to: '/seller/orders', label: 'Pesanan', icon: ClipboardList },
    { to: '/seller/report', label: 'Laporan', icon: History },
    { to: '/seller/store', label: 'Toko', icon: Settings },
  ],
  driver: [
    { to: '/driver/jobs', label: 'Cari Job', icon: Truck },
    { to: '/driver/my-jobs', label: 'Job Saya', icon: History },
  ],
  admin: [
    { to: '/admin/dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { to: '/admin/vouchers', label: 'Voucher', icon: Tag },
    { to: '/admin/promos', label: 'Promo', icon: Tag },
  ],
}

const navItems = computed(() => navMap[props.role || auth.activeRole] || [])

function isActive(path) {
  return route.path.startsWith(path)
}

async function handleLogout() {
  await auth.logout()
  toast.success('Berhasil keluar.')
  router.push('/login')
}
</script>
