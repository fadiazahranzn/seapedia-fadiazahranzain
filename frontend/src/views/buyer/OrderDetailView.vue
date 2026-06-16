<template>
  <div class="max-w-2xl">
    <Button variant="ghost" size="sm" class="-ml-2 mb-4" @click="router.back()">
      <ArrowLeft class="w-4 h-4 mr-1" /> Kembali
    </Button>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="h-20 bg-muted rounded-lg animate-pulse" />
    </div>

    <template v-else-if="order">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">Pesanan #{{ order.id }}</h1>
        <Badge :class="statusColor(order.status)">{{ statusLabel(order.status) }}</Badge>
      </div>

      <!-- Status timeline -->
      <Card class="mb-4">
        <CardContent class="pt-6">
          <h2 class="font-semibold mb-4">Status Pesanan</h2>
          <div class="space-y-3">
            <div v-for="(hist, i) in order.status_histories" :key="i" class="flex gap-3 items-start">
              <div class="w-2 h-2 rounded-full bg-primary mt-1.5 shrink-0"></div>
              <div>
                <p class="text-sm font-medium">{{ statusLabel(hist.status) }}</p>
                <p class="text-xs text-muted-foreground">{{ formatDate(hist.created_at) }}</p>
                <p v-if="hist.note" class="text-xs text-muted-foreground">{{ hist.note }}</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Items -->
      <Card class="mb-4">
        <CardContent class="pt-6">
          <h2 class="font-semibold mb-3">Produk ({{ order.store?.name }})</h2>
          <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center py-2 border-b last:border-0">
            <div>
              <p class="text-sm font-medium">{{ item.product_name }}</p>
              <p class="text-xs text-muted-foreground">{{ formatPrice(item.product_price) }} × {{ item.quantity }}</p>
            </div>
            <p class="font-medium text-sm">{{ formatPrice(item.subtotal) }}</p>
          </div>
        </CardContent>
      </Card>

      <!-- Address -->
      <Card class="mb-4">
        <CardContent class="pt-6">
          <h2 class="font-semibold mb-2">Alamat Pengiriman</h2>
          <p class="text-sm font-medium">{{ order.address_snapshot?.recipient_name }}</p>
          <p class="text-sm text-muted-foreground">{{ order.address_snapshot?.phone }}</p>
          <p class="text-sm text-muted-foreground">{{ order.address_snapshot?.full_address }}, {{ order.address_snapshot?.district }}, {{ order.address_snapshot?.city }}, {{ order.address_snapshot?.province }}</p>
        </CardContent>
      </Card>

      <!-- Price breakdown -->
      <Card>
        <CardContent class="pt-6">
          <h2 class="font-semibold mb-3">Rincian Pembayaran</h2>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-muted-foreground">Subtotal</span><span>{{ formatPrice(order.subtotal) }}</span></div>
            <div class="flex justify-between"><span class="text-muted-foreground">Ongkir ({{ methodLabel(order.delivery_method) }})</span><span>{{ formatPrice(order.delivery_fee) }}</span></div>
            <div v-if="order.discount_amount > 0" class="flex justify-between text-green-600"><span>Diskon</span><span>-{{ formatPrice(order.discount_amount) }}</span></div>
            <div class="flex justify-between"><span class="text-muted-foreground">PPN 12%</span><span>{{ formatPrice(order.ppn_amount) }}</span></div>
            <div class="flex justify-between font-bold pt-2 border-t text-base">
              <span>Total</span><span class="text-primary">{{ formatPrice(order.total) }}</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </template>

    <div v-else class="text-center py-16 text-muted-foreground">Pesanan tidak ditemukan.</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { buyerApi } from '@/services/buyer'

const route = useRoute()
const router = useRouter()
const order = ref(null)
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
  return { sedang_dikemas: 'Sedang Dikemas', menunggu_pengirim: 'Menunggu Pengirim', sedang_dikirim: 'Sedang Dikirim', pesanan_selesai: 'Pesanan Selesai', dikembalikan: 'Dikembalikan' }[s] || s
}
function statusColor(s) {
  return { sedang_dikemas: 'bg-yellow-100 text-yellow-800', menunggu_pengirim: 'bg-blue-100 text-blue-800', sedang_dikirim: 'bg-orange-100 text-orange-800', pesanan_selesai: 'bg-green-100 text-green-800', dikembalikan: 'bg-red-100 text-red-800' }[s] || ''
}

onMounted(async () => {
  try {
    const { data } = await buyerApi.getOrder(route.params.id)
    order.value = data.data
  } finally { loading.value = false }
})
</script>
