<template>
  <div class="max-w-2xl">
    <button
      class="inline-flex items-center gap-1 text-[13px] font-medium text-muted-foreground hover:text-foreground -ml-1 mb-5 border-0 bg-transparent cursor-pointer transition-colors px-2 py-1.5 rounded-[8px] hover:bg-muted"
      @click="router.back()"
    >
      <ArrowLeft class="w-4 h-4" /> Kembali
    </button>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-24 bg-muted rounded-xl animate-pulse" />
    </div>

    <template v-else-if="order">
      <div class="flex items-center justify-between mb-5">
        <h1 class="text-[20px] font-bold tracking-[-0.03em]">Pesanan #{{ order.id }}</h1>
        <span :class="statusColor(order.status)" class="text-[12px] font-semibold rounded-full px-3 py-1 border">
          {{ statusLabel(order.status) }}
        </span>
      </div>

      <!-- Status timeline -->
      <div class="bg-card border rounded-xl p-5 mb-4">
        <h2 class="font-bold text-[14px] mb-4">Riwayat Status</h2>
        <div class="pl-2 border-l-2 border-muted">
          <div v-for="(hist, i) in order.status_histories" :key="i" class="relative pl-4 pb-4 last:pb-0">
            <span class="absolute -left-[9px] top-1 w-3 h-3 rounded-full border-2 border-primary bg-background" />
            <p class="text-[13px] font-semibold">{{ statusLabel(hist.status) }}</p>
            <p class="text-[11px] text-muted-foreground">{{ formatDate(hist.created_at) }}</p>
            <p v-if="hist.note" class="text-[11px] text-muted-foreground">{{ hist.note }}</p>
          </div>
        </div>
      </div>

      <!-- Delivery tracking -->
      <div v-if="tracking?.delivery" class="bg-card border rounded-xl p-5 mb-4">
        <h2 class="font-bold text-[14px] mb-3 flex items-center gap-2">
          <Truck class="w-4 h-4 text-primary" /> Info Pengiriman
        </h2>
        <div class="space-y-1.5">
          <div class="flex justify-between text-[13px]">
            <span class="text-muted-foreground">Driver</span>
            <span class="font-medium">{{ tracking.delivery.driver?.name ?? 'Belum ada driver' }}</span>
          </div>
          <div v-if="tracking.delivery.picked_up_at" class="flex justify-between text-[13px]">
            <span class="text-muted-foreground">Diambil</span>
            <span>{{ formatDate(tracking.delivery.picked_up_at) }}</span>
          </div>
          <div v-if="tracking.delivery.delivered_at" class="flex justify-between text-[13px]">
            <span class="text-muted-foreground">Diterima</span>
            <span>{{ formatDate(tracking.delivery.delivered_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="bg-card border rounded-xl p-5 mb-4">
        <h2 class="font-bold text-[14px] mb-3">Produk <span class="font-normal text-muted-foreground">({{ order.store?.name }})</span></h2>
        <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center py-2.5 border-b last:border-0">
          <div>
            <p class="text-[13px] font-medium">{{ item.product_name }}</p>
            <p class="text-[11px] text-muted-foreground">{{ formatPrice(item.product_price) }} × {{ item.quantity }}</p>
          </div>
          <p class="font-semibold text-[13px]">{{ formatPrice(item.subtotal) }}</p>
        </div>
      </div>

      <!-- Address -->
      <div class="bg-card border rounded-xl p-5 mb-4">
        <h2 class="font-bold text-[14px] mb-2">Alamat Pengiriman</h2>
        <p class="text-[13px] font-medium">{{ order.address_snapshot?.recipient_name }}</p>
        <p class="text-[13px] text-muted-foreground">{{ order.address_snapshot?.phone }}</p>
        <p class="text-[13px] text-muted-foreground">
          {{ order.address_snapshot?.full_address }}, {{ order.address_snapshot?.district }},
          {{ order.address_snapshot?.city }}, {{ order.address_snapshot?.province }}
        </p>
      </div>

      <!-- Price breakdown -->
      <div class="bg-card border rounded-xl p-5">
        <h2 class="font-bold text-[14px] mb-3">Rincian Pembayaran</h2>
        <div class="space-y-2 text-[13px]">
          <div class="flex justify-between"><span class="text-muted-foreground">Subtotal</span><span>{{ formatPrice(order.subtotal) }}</span></div>
          <div class="flex justify-between">
            <span class="text-muted-foreground">Ongkir ({{ methodLabel(order.delivery_method) }})</span>
            <span>{{ formatPrice(order.delivery_fee) }}</span>
          </div>
          <div v-if="order.discount_amount > 0" class="flex justify-between text-green-600">
            <span>Diskon ({{ [order.voucher?.code, order.promo?.code].filter(Boolean).join(' + ') || 'diskon' }})</span>
            <span>−{{ formatPrice(order.discount_amount) }}</span>
          </div>
          <div class="flex justify-between"><span class="text-muted-foreground">PPN 12%</span><span>{{ formatPrice(order.ppn_amount) }}</span></div>
          <div class="flex justify-between font-bold text-[15px] pt-2 border-t mt-1">
            <span>Total</span><span class="text-primary">{{ formatPrice(order.total) }}</span>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="text-center py-16 text-muted-foreground text-[13px]">Pesanan tidak ditemukan.</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft, Truck } from '@lucide/vue'
import { buyerApi } from '@/services/buyer'

const route = useRoute()
const router = useRouter()
const order = ref(null)
const tracking = ref(null)
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
  return {
    sedang_dikemas: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    menunggu_pengirim: 'bg-blue-50 text-blue-700 border-blue-200',
    sedang_dikirim: 'bg-blue-50 text-blue-700 border-blue-200',
    pesanan_selesai: 'bg-green-50 text-green-700 border-green-200',
    dikembalikan: 'bg-red-50 text-red-700 border-red-200',
  }[s] || 'bg-muted text-muted-foreground border-border'
}

onMounted(async () => {
  try {
    const [orderRes, trackingRes] = await Promise.all([
      buyerApi.getOrder(route.params.id),
      buyerApi.getTracking(route.params.id).catch(() => null),
    ])
    order.value = orderRes.data.data
    tracking.value = trackingRes?.data?.data ?? null
  } finally { loading.value = false }
})
</script>
