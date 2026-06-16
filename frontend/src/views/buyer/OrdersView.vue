<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Riwayat Pesanan</h1>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="h-24 bg-muted rounded-lg animate-pulse" />
    </div>

    <div v-else-if="orders.length === 0" class="text-center py-16 text-muted-foreground">
      <ClipboardList class="w-12 h-12 mx-auto mb-3 opacity-30" />
      <p>Belum ada pesanan.</p>
      <Button class="mt-4" as-child><RouterLink to="/products">Mulai Belanja</RouterLink></Button>
    </div>

    <div v-else class="space-y-3">
      <Card v-for="order in orders" :key="order.id" class="cursor-pointer hover:shadow-md transition-shadow" @click="router.push(`/buyer/orders/${order.id}`)">
        <CardContent class="py-4">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <p class="font-medium text-sm">{{ order.store?.name }}</p>
                <Badge :class="statusColor(order.status)" class="text-xs">{{ statusLabel(order.status) }}</Badge>
              </div>
              <p class="text-xs text-muted-foreground mb-2">{{ formatDate(order.created_at) }}</p>
              <p class="text-xs text-muted-foreground">{{ order.items?.length }} produk</p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-bold text-primary">{{ formatPrice(order.total) }}</p>
              <p class="text-xs text-muted-foreground capitalize">{{ methodLabel(order.delivery_method) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { ClipboardList } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { buyerApi } from '@/services/buyer'

const router = useRouter()
const orders = ref([])
const loading = ref(true)

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p)
}
function formatDate(d) {
  return new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
}
function methodLabel(m) {
  return { instant: 'Instant', next_day: 'Next Day', regular: 'Regular' }[m] || m
}
function statusLabel(s) {
  return { sedang_dikemas: 'Dikemas', menunggu_pengirim: 'Menunggu Pengirim', sedang_dikirim: 'Dikirim', pesanan_selesai: 'Selesai', dikembalikan: 'Dikembalikan' }[s] || s
}
function statusColor(s) {
  return { sedang_dikemas: 'bg-yellow-100 text-yellow-800', menunggu_pengirim: 'bg-blue-100 text-blue-800', sedang_dikirim: 'bg-orange-100 text-orange-800', pesanan_selesai: 'bg-green-100 text-green-800', dikembalikan: 'bg-red-100 text-red-800' }[s] || ''
}

onMounted(async () => {
  try {
    const { data } = await buyerApi.getOrders()
    orders.value = data.data || data
  } finally { loading.value = false }
})
</script>
