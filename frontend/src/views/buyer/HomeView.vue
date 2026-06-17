<template>
  <div>
    <div class="mb-1">
      <h1 class="text-[22px] font-bold tracking-[-0.03em]">Selamat datang, {{ firstName }}! 👋</h1>
    </div>
    <p class="text-sm text-muted-foreground mb-6">
      Anda masuk sebagai <strong class="text-primary">Buyer</strong>
    </p>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-7">
      <div
        class="bg-card border rounded-xl p-5 cursor-pointer transition-all hover:shadow-lg hover:-translate-y-px"
        @click="router.push('/buyer/wallet')"
      >
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-[10px] bg-blue-50 flex items-center justify-center shrink-0">
            <CreditCard class="w-5 h-5 text-blue-500" />
          </div>
          <div>
            <p class="text-xs text-muted-foreground font-medium mb-0.5">Saldo</p>
            <p class="text-[18px] font-bold tracking-[-0.02em] leading-none">
              {{ walletLoading ? '...' : formatPrice(wallet?.balance ?? 0) }}
            </p>
          </div>
        </div>
      </div>

      <div
        class="bg-card border rounded-xl p-5 cursor-pointer transition-all hover:shadow-lg hover:-translate-y-px"
        @click="router.push('/buyer/cart')"
      >
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-[10px] bg-green-50 flex items-center justify-center shrink-0">
            <ShoppingCart class="w-5 h-5 text-green-600" />
          </div>
          <div>
            <p class="text-xs text-muted-foreground font-medium mb-0.5">Keranjang</p>
            <p class="text-[18px] font-bold tracking-[-0.02em] leading-none">{{ cartCount }} item</p>
          </div>
        </div>
      </div>

      <div
        class="bg-card border rounded-xl p-5 cursor-pointer transition-all hover:shadow-lg hover:-translate-y-px"
        @click="router.push('/buyer/orders')"
      >
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-[10px] bg-orange-50 flex items-center justify-center shrink-0">
            <FileText class="w-5 h-5 text-orange-500" />
          </div>
          <div>
            <p class="text-xs text-muted-foreground font-medium mb-0.5">Pesanan</p>
            <p class="text-[18px] font-bold tracking-[-0.02em] leading-none">Lihat Semua</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="flex flex-wrap gap-2.5 mb-8">
      <button
        class="inline-flex items-center gap-1.5 px-[18px] py-[9px] rounded-[10px] text-[13px] font-semibold text-white bg-gradient-to-br from-primary to-cyan-500 shadow-[0_4px_12px_rgba(99,102,241,0.25)] hover:opacity-90 hover:-translate-y-px transition-all border-0 cursor-pointer"
        @click="router.push('/buyer/products')"
      >
        <Search class="w-3.5 h-3.5" />
        Jelajahi Produk
      </button>
      <button
        class="inline-flex items-center gap-1.5 px-[18px] py-[9px] rounded-[10px] text-[13px] font-semibold text-slate-600 bg-white border border-border hover:border-primary hover:text-primary transition-all cursor-pointer"
        @click="router.push('/buyer/wallet')"
      >
        <CreditCard class="w-3.5 h-3.5" />
        Top Up Saldo
      </button>
    </div>

    <!-- Recent orders -->
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-base font-bold">Pesanan Terakhir</h2>
      <RouterLink to="/buyer/orders" class="text-xs font-semibold text-primary hover:underline">Lihat Semua →</RouterLink>
    </div>

    <div v-if="ordersLoading" class="bg-card border rounded-xl overflow-hidden">
      <div v-for="i in 3" :key="i" class="h-[72px] bg-muted/60 animate-pulse" :class="i < 3 ? 'border-b' : ''" />
    </div>

    <div v-else-if="recentOrders.length === 0" class="bg-card border rounded-xl py-12 text-center text-sm text-muted-foreground">
      Belum ada pesanan.
    </div>

    <div v-else class="bg-card border rounded-xl overflow-hidden">
      <div
        v-for="(order, idx) in recentOrders"
        :key="order.id"
        class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-muted/40 transition-colors"
        :class="idx < recentOrders.length - 1 ? 'border-b' : ''"
        @click="router.push(`/buyer/orders/${order.id}`)"
      >
        <div>
          <p class="text-[13px] font-semibold">#{{ order.id }}</p>
          <p class="text-[11px] text-muted-foreground mt-0.5">
            {{ order.store?.name }}
            <template v-if="order.items?.length > 1"> + {{ order.items.length - 1 }} item lainnya</template>
          </p>
        </div>
        <div class="text-right">
          <span :class="statusColor(order.status)" class="text-[12px] font-semibold rounded-full px-2.5 py-0.5 border">
            {{ statusLabel(order.status) }}
          </span>
          <p class="text-[13px] font-bold text-primary mt-1">{{ formatPrice(order.total) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { CreditCard, ShoppingCart, FileText, Search } from '@lucide/vue'
import { useAuthStore } from '@/stores/auth'
import { buyerApi } from '@/services/buyer'

const auth = useAuthStore()
const router = useRouter()
const wallet = ref(null)
const cart = ref(null)
const recentOrders = ref([])
const walletLoading = ref(true)
const ordersLoading = ref(true)

const firstName = computed(() => auth.user?.name?.split(' ')[0] ?? 'Kamu')
const cartCount = computed(() => cart.value?.items?.reduce((s, i) => s + i.quantity, 0) ?? 0)

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p)
}
function statusLabel(s) {
  return { sedang_dikemas: 'Diproses', menunggu_pengirim: 'Menunggu Pengirim', sedang_dikirim: 'Dikirim', pesanan_selesai: 'Selesai', dikembalikan: 'Dikembalikan' }[s] || s
}
function statusColor(s) {
  return {
    sedang_dikemas: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    menunggu_pengirim: 'bg-blue-50 text-blue-700 border-blue-200',
    sedang_dikirim: 'bg-blue-50 text-blue-700 border-blue-200',
    pesanan_selesai: 'bg-green-50 text-green-700 border-green-200',
    dikembalikan: 'bg-red-50 text-red-700 border-red-200',
  }[s] || 'bg-muted text-muted-foreground border-border'
}

onMounted(async () => {
  const [w, c, o] = await Promise.allSettled([
    buyerApi.getWallet(),
    buyerApi.getCart(),
    buyerApi.getOrders(),
  ])
  if (w.status === 'fulfilled') wallet.value = w.value.data.data
  walletLoading.value = false
  if (c.status === 'fulfilled') cart.value = c.value.data.data
  if (o.status === 'fulfilled') recentOrders.value = (o.value.data.data || o.value.data).slice(0, 5)
  ordersLoading.value = false
})
</script>
