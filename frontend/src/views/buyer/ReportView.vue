<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Laporan Belanja</h1>

    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="i in 4" :key="i" class="h-24 bg-muted rounded-xl animate-pulse" />
    </div>

    <template v-else>
      <!-- Summary cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <Card>
          <CardContent class="pt-5">
            <p class="text-xs text-muted-foreground">Total Pesanan</p>
            <p class="text-2xl font-bold mt-1">{{ report.summary.total_orders }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <p class="text-xs text-muted-foreground">Selesai</p>
            <p class="text-2xl font-bold mt-1 text-green-600">{{ report.summary.completed_orders }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <p class="text-xs text-muted-foreground">Diproses</p>
            <p class="text-2xl font-bold mt-1 text-yellow-600">{{ report.summary.pending_orders }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <p class="text-xs text-muted-foreground">Total Belanja</p>
            <p class="text-lg font-bold mt-1 text-primary">{{ formatPrice(report.summary.total_spent) }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Order list -->
      <Card>
        <CardContent class="pt-6">
          <h2 class="font-semibold mb-4">Riwayat Transaksi</h2>
          <div v-if="!report.orders?.length" class="text-center py-8 text-muted-foreground text-sm">Belum ada transaksi.</div>
          <div v-else class="space-y-3">
            <div v-for="order in report.orders" :key="order.id"
              class="border rounded-lg p-4 hover:bg-muted/30 cursor-pointer transition-colors"
              @click="$router.push(`/buyer/orders/${order.id}`)">
              <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-sm font-medium">#{{ order.id }}</span>
                    <span :class="statusClass(order.status)" class="text-xs px-2 py-0.5 rounded-full font-medium">
                      {{ statusLabel(order.status) }}
                    </span>
                  </div>
                  <p class="text-xs text-muted-foreground">{{ order.store?.name }} · {{ formatDate(order.created_at) }}</p>
                  <p class="text-xs text-muted-foreground mt-1">{{ order.items?.length }} produk</p>
                </div>
                <div class="text-right shrink-0">
                  <p class="font-bold text-sm">{{ formatPrice(order.total) }}</p>
                  <div class="mt-1 space-y-0.5 text-xs text-muted-foreground">
                    <div v-if="order.discount_amount > 0" class="text-green-600">Diskon -{{ formatPrice(order.discount_amount) }}</div>
                    <div>Ongkir {{ formatPrice(order.delivery_fee) }}</div>
                    <div>PPN {{ formatPrice(order.ppn_amount) }}</div>
                  </div>
                </div>
              </div>
              <!-- Voucher / Promo badge -->
              <div v-if="order.voucher || order.promo" class="mt-2 flex gap-2">
                <span v-if="order.voucher" class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded">
                  Voucher: {{ order.voucher.code }}
                </span>
                <span v-if="order.promo" class="text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded">
                  Promo: {{ order.promo.code }}
                </span>
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
import { Card, CardContent } from '@/components/ui/card'
import { buyerApi } from '@/services/buyer'

const report = ref({ summary: {}, orders: [] })
const loading = ref(true)

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const STATUS_MAP = {
  sedang_dikemas: { label: 'Sedang Dikemas', class: 'bg-yellow-100 text-yellow-700' },
  menunggu_pengirim: { label: 'Menunggu Pengirim', class: 'bg-blue-100 text-blue-700' },
  sedang_dikirim: { label: 'Sedang Dikirim', class: 'bg-indigo-100 text-indigo-700' },
  pesanan_selesai: { label: 'Pesanan Selesai', class: 'bg-green-100 text-green-700' },
  dikembalikan: { label: 'Dikembalikan', class: 'bg-red-100 text-red-700' },
}

function statusLabel(s) { return STATUS_MAP[s]?.label ?? s }
function statusClass(s) { return STATUS_MAP[s]?.class ?? '' }

onMounted(async () => {
  try {
    const { data } = await buyerApi.getReport()
    report.value = data.data
  } finally { loading.value = false }
})
</script>
