<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Laporan Pendapatan</h1>

    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="i in 4" :key="i" class="h-24 bg-muted rounded-xl animate-pulse" />
    </div>

    <template v-else>
      <!-- Summary cards -->
      <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <Card>
          <CardContent class="pt-5">
            <p class="text-xs text-muted-foreground">Total Pesanan</p>
            <p class="text-2xl font-bold mt-1">{{ report.summary.total_orders }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <p class="text-xs text-muted-foreground">Sudah Diproses</p>
            <p class="text-2xl font-bold mt-1 text-blue-600">{{ report.summary.processed_orders }}</p>
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
            <p class="text-xs text-muted-foreground">Total Pendapatan</p>
            <p class="text-lg font-bold mt-1 text-green-600">{{ formatPrice(report.summary.total_income) }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <p class="text-xs text-muted-foreground">Total Pengembalian</p>
            <p class="text-lg font-bold mt-1 text-red-500">-{{ formatPrice(report.summary.total_reversal) }}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <p class="text-xs text-muted-foreground">Net Pendapatan</p>
            <p class="text-lg font-bold mt-1 text-primary">{{ formatPrice(report.summary.net_income) }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Transaction history -->
      <Card class="mb-6">
        <CardContent class="pt-6">
          <h2 class="font-semibold mb-4">Riwayat Transaksi Toko</h2>
          <div v-if="!report.transactions?.length" class="text-center py-6 text-muted-foreground text-sm">Belum ada transaksi.</div>
          <div v-else class="divide-y">
            <div v-for="tx in report.transactions" :key="tx.id" class="py-3 flex items-center justify-between">
              <div>
                <p class="text-sm font-medium">{{ tx.description }}</p>
                <p class="text-xs text-muted-foreground">Pesanan #{{ tx.order_id }} · {{ formatDate(tx.created_at) }}</p>
              </div>
              <span :class="tx.type === 'income' ? 'text-green-600' : 'text-red-500'" class="font-semibold text-sm">
                {{ tx.type === 'income' ? '+' : '-' }}{{ formatPrice(tx.amount) }}
              </span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Order list -->
      <Card>
        <CardContent class="pt-6">
          <h2 class="font-semibold mb-4">Semua Pesanan</h2>
          <div v-if="!report.orders?.length" class="text-center py-6 text-muted-foreground text-sm">Belum ada pesanan.</div>
          <div v-else class="divide-y">
            <div v-for="order in report.orders" :key="order.id" class="py-3">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-sm font-medium">#{{ order.id }}</span>
                    <span :class="statusClass(order.status)" class="text-xs px-2 py-0.5 rounded-full font-medium">
                      {{ statusLabel(order.status) }}
                    </span>
                  </div>
                  <p class="text-xs text-muted-foreground">{{ order.user?.name }} · {{ formatDate(order.created_at) }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-sm">{{ formatPrice(order.total) }}</p>
                  <p v-if="order.discount_amount > 0" class="text-xs text-green-600">Diskon -{{ formatPrice(order.discount_amount) }}</p>
                </div>
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
import { sellerApi } from '@/services/seller'

const report = ref({ summary: {}, orders: [], transactions: [] })
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
    const { data } = await sellerApi.getReport()
    report.value = data.data
  } finally { loading.value = false }
})
</script>
