<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold">Dashboard Admin</h1>
        <p class="text-muted-foreground text-sm mt-1">Monitor seluruh aktivitas SEAPEDIA</p>
      </div>
      <Button variant="outline" size="sm" @click="loadDashboard">
        <RefreshCw class="w-4 h-4 mr-1" /> Refresh
      </Button>
    </div>

    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div v-for="i in 8" :key="i" class="h-24 bg-muted rounded-xl animate-pulse" />
    </div>

    <template v-else>
      <!-- Users -->
      <h2 class="font-semibold text-sm text-muted-foreground uppercase tracking-wide mb-3">Pengguna</h2>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="pt-5">
            <Users class="w-5 h-5 text-blue-600 mb-2" />
            <p class="text-xs text-muted-foreground">Total User</p>
            <p class="text-2xl font-bold">{{ data.users?.total }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <ShoppingCart class="w-5 h-5 text-green-600 mb-2" />
            <p class="text-xs text-muted-foreground">Buyer</p>
            <p class="text-2xl font-bold">{{ data.users?.buyers }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <Store class="w-5 h-5 text-orange-600 mb-2" />
            <p class="text-xs text-muted-foreground">Seller</p>
            <p class="text-2xl font-bold">{{ data.users?.sellers }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <Truck class="w-5 h-5 text-purple-600 mb-2" />
            <p class="text-xs text-muted-foreground">Driver</p>
            <p class="text-2xl font-bold">{{ data.users?.drivers }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Orders -->
      <h2 class="font-semibold text-sm text-muted-foreground uppercase tracking-wide mb-3">Pesanan</h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <Card v-for="s in orderStats" :key="s.key">
          <CardContent class="pt-5">
            <p class="text-xs text-muted-foreground">{{ s.label }}</p>
            <p class="text-2xl font-bold mt-1" :class="s.color">{{ data.orders?.[s.key] }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Stores, Products, Deliveries -->
      <h2 class="font-semibold text-sm text-muted-foreground uppercase tracking-wide mb-3">Marketplace</h2>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="pt-5">
            <Store class="w-5 h-5 text-orange-600 mb-2" />
            <p class="text-xs text-muted-foreground">Total Toko</p>
            <p class="text-2xl font-bold">{{ data.stores?.total }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <Package class="w-5 h-5 text-green-600 mb-2" />
            <p class="text-xs text-muted-foreground">Total Produk</p>
            <p class="text-2xl font-bold">{{ data.products?.total }}</p>
            <p class="text-xs text-red-500 mt-1" v-if="data.products?.out_of_stock">{{ data.products.out_of_stock }} habis stok</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <Truck class="w-5 h-5 text-blue-600 mb-2" />
            <p class="text-xs text-muted-foreground">Total Pengiriman</p>
            <p class="text-2xl font-bold">{{ data.deliveries?.total }}</p>
          </CardContent>
        </Card>
        <Card :class="data.overdue?.total > 0 ? 'border-red-300 bg-red-50/50' : ''">
          <CardContent class="pt-5">
            <AlertTriangle class="w-5 h-5 mb-2" :class="data.overdue?.total > 0 ? 'text-red-600' : 'text-muted-foreground'" />
            <p class="text-xs text-muted-foreground">Overdue</p>
            <p class="text-2xl font-bold" :class="data.overdue?.total > 0 ? 'text-red-600' : ''">{{ data.overdue?.total }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Vouchers & Promos -->
      <h2 class="font-semibold text-sm text-muted-foreground uppercase tracking-wide mb-3">Diskon</h2>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="pt-5">
            <Tag class="w-5 h-5 text-blue-600 mb-2" />
            <p class="text-xs text-muted-foreground">Total Voucher</p>
            <p class="text-2xl font-bold">{{ data.vouchers?.total }}</p>
            <p class="text-xs text-green-600">{{ data.vouchers?.active }} aktif</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <Tag class="w-5 h-5 text-purple-600 mb-2" />
            <p class="text-xs text-muted-foreground">Total Promo</p>
            <p class="text-2xl font-bold">{{ data.promos?.total }}</p>
            <p class="text-xs text-green-600">{{ data.promos?.active }} aktif</p>
          </CardContent>
        </Card>
      </div>

      <!-- Overdue actions -->
      <h2 class="font-semibold text-sm text-muted-foreground uppercase tracking-wide mb-3">Overdue Handling</h2>
      <Card>
        <CardContent class="pt-6">
          <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
              <p class="font-semibold">Simulasi & Proses Overdue</p>
              <p class="text-sm text-muted-foreground mt-1">
                SLA: Instant = 1 hari · Next Day = 2 hari · Regular = 5 hari
              </p>
            </div>
            <div class="flex gap-2 shrink-0">
              <Button variant="outline" :disabled="simulating" @click="simulateNextDay">
                {{ simulating ? 'Memproses...' : '⏭ Simulasi Hari Berikutnya' }}
              </Button>
              <Button variant="destructive" :disabled="processing" @click="processOverdue">
                {{ processing ? 'Memproses...' : 'Proses Overdue' }}
              </Button>
            </div>
          </div>
          <div v-if="overdueResult" class="mt-4 p-3 rounded-lg bg-muted text-sm">
            <p class="font-medium">{{ overdueResult.message }}</p>
            <div v-if="overdueResult.processed?.length" class="mt-2 space-y-1">
              <div v-for="o in overdueResult.processed" :key="o.order_id" class="text-muted-foreground text-xs">
                Pesanan #{{ o.order_id }} ({{ o.delivery_method }}) — refund {{ formatPrice(o.refunded) }}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RefreshCw, Users, Package, Store, Truck, Tag, ShoppingCart, AlertTriangle } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { adminApi } from '@/services/admin'
import { toast } from 'vue-sonner'

const data = ref({})
const loading = ref(true)
const simulating = ref(false)
const processing = ref(false)
const overdueResult = ref(null)

const orderStats = [
  { key: 'total', label: 'Total', color: '' },
  { key: 'sedang_dikemas', label: 'Sedang Dikemas', color: 'text-yellow-600' },
  { key: 'menunggu_pengirim', label: 'Menunggu Pengirim', color: 'text-blue-600' },
  { key: 'sedang_dikirim', label: 'Sedang Dikirim', color: 'text-indigo-600' },
  { key: 'pesanan_selesai', label: 'Selesai', color: 'text-green-600' },
  { key: 'dikembalikan', label: 'Dikembalikan', color: 'text-red-600' },
]

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}

async function loadDashboard() {
  loading.value = true
  try {
    const { data: res } = await adminApi.getDashboard()
    data.value = res.data
  } finally { loading.value = false }
}

async function simulateNextDay() {
  simulating.value = true
  try {
    const { data: res } = await adminApi.simulateNextDay()
    toast.success(res.message)
    await loadDashboard()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal simulasi.')
  } finally { simulating.value = false }
}

async function processOverdue() {
  processing.value = true
  overdueResult.value = null
  try {
    const { data: res } = await adminApi.processOverdue()
    overdueResult.value = res
    toast.success(res.message)
    await loadDashboard()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal proses overdue.')
  } finally { processing.value = false }
}

onMounted(loadDashboard)
</script>
