<template>
  <div class="max-w-[760px] mx-auto">

    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-[22px] font-bold tracking-[-0.03em]">Riwayat Pesanan</h1>
      <p class="text-[13px] text-muted-foreground mt-0.5">Semua pesanan yang pernah kamu buat</p>
    </div>

    <!-- Summary stats -->
    <div class="grid grid-cols-3 gap-3 mb-5">
      <div class="bg-card border rounded-xl px-4 py-3.5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-[9px] bg-primary/10 flex items-center justify-center shrink-0">
          <FileText class="w-4 h-4 text-primary" />
        </div>
        <div>
          <p class="text-[11px] text-muted-foreground font-medium">Total Pesanan</p>
          <p class="text-[16px] font-bold tracking-[-0.02em] leading-tight">{{ orders.length }}</p>
        </div>
      </div>
      <div class="bg-card border rounded-xl px-4 py-3.5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-[9px] bg-green-50 flex items-center justify-center shrink-0">
          <CheckCircle class="w-4 h-4 text-green-600" />
        </div>
        <div>
          <p class="text-[11px] text-muted-foreground font-medium">Selesai</p>
          <p class="text-[16px] font-bold tracking-[-0.02em] leading-tight">{{ doneCount }}</p>
        </div>
      </div>
      <div class="bg-card border rounded-xl px-4 py-3.5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-[9px] bg-blue-50 flex items-center justify-center shrink-0">
          <Clock class="w-4 h-4 text-blue-500" />
        </div>
        <div>
          <p class="text-[11px] text-muted-foreground font-medium">Dalam Proses</p>
          <p class="text-[16px] font-bold tracking-[-0.02em] leading-tight">{{ activeCount }}</p>
        </div>
      </div>
    </div>

    <!-- Filter tabs -->
    <div class="flex gap-1.5 flex-wrap mb-4">
      <button
        v-for="f in filters"
        :key="f.value"
        class="px-3.5 py-1.5 rounded-full text-[12px] font-medium border-[1.5px] cursor-pointer transition-all"
        :class="activeFilter === f.value
          ? 'bg-primary text-white border-primary'
          : 'border-border text-muted-foreground bg-card hover:border-primary hover:text-primary'"
        @click="activeFilter = f.value"
      >
        {{ f.label }}
      </button>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="h-[88px] bg-muted rounded-xl animate-pulse" />
    </div>

    <!-- Empty -->
    <div v-else-if="!filteredOrders.length" class="flex flex-col items-center gap-3 py-20 text-center bg-card border rounded-xl">
      <ClipboardList class="w-12 h-12 text-muted-foreground/25" />
      <p class="text-[15px] font-medium">{{ activeFilter === 'all' ? 'Belum ada pesanan' : 'Tidak ada pesanan' }}</p>
      <p class="text-[13px] text-muted-foreground">
        {{ activeFilter === 'all' ? 'Mulai belanja dan pesananmu akan muncul di sini' : 'Coba pilih filter lain' }}
      </p>
      <button
        v-if="activeFilter === 'all'"
        class="mt-1 px-4 py-2 rounded-[10px] border-0 text-[13px] font-semibold text-white bg-primary hover:opacity-90 cursor-pointer"
        @click="router.push('/buyer/products')"
      >
        Mulai Belanja
      </button>
    </div>

    <!-- Order cards -->
    <div v-else class="space-y-3">
      <div
        v-for="order in filteredOrders"
        :key="order.id"
        class="bg-card border rounded-xl px-5 py-4 cursor-pointer hover:shadow-md transition-all hover:-translate-y-px"
        @click="router.push(`/buyer/orders/${order.id}`)"
      >
        <!-- top row -->
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-1">
              <span class="text-[13px] font-bold">#{{ order.id }}</span>
              <span :class="statusColor(order.status)" class="text-[11px] font-semibold rounded-full px-2.5 py-0.5 border">
                {{ statusLabel(order.status) }}
              </span>
            </div>
            <p class="text-[13px] text-muted-foreground truncate">{{ order.store?.name }}</p>
            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
              <span class="text-[11px] text-muted-foreground">{{ formatDate(order.created_at) }}</span>
              <span class="w-[3px] h-[3px] rounded-full bg-muted-foreground/40 inline-block" />
              <span class="text-[11px] text-muted-foreground">{{ order.items?.length }} produk</span>
              <span class="w-[3px] h-[3px] rounded-full bg-muted-foreground/40 inline-block" />
              <span class="text-[11px] text-muted-foreground">{{ methodLabel(order.delivery_method) }}</span>
            </div>
          </div>
          <div class="text-right shrink-0">
            <p class="text-[14px] font-bold text-primary">{{ formatPrice(order.total) }}</p>
            <p class="text-[11px] text-muted-foreground mt-1 flex items-center justify-end gap-0.5">
              Lihat Detail <ChevronRight class="w-3 h-3" />
            </p>
          </div>
        </div>

        <!-- progress strip (only for in-progress orders) -->
        <template v-if="order.status !== 'pesanan_selesai' && order.status !== 'dikembalikan'">
          <div class="mt-3 h-[3px] bg-muted rounded-full overflow-hidden">
            <div
              class="h-full rounded-full"
              style="background:linear-gradient(90deg,#6366f1,#06b6d4)"
              :style="{ width: progressPct(order.status) + '%' }"
            />
          </div>
          <div class="flex justify-between mt-1">
            <span class="text-[10px] text-muted-foreground">Progres pengiriman</span>
            <span class="text-[10px] font-semibold text-primary">{{ progressPct(order.status) }}%</span>
          </div>
        </template>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { FileText, CheckCircle, Clock, ClipboardList, ChevronRight } from '@lucide/vue'
import { buyerApi } from '@/services/buyer'

const router = useRouter()
const orders = ref([])
const loading = ref(true)
const activeFilter = ref('all')

const filters = [
  { value: 'all',              label: 'Semua' },
  { value: 'sedang_dikemas',   label: 'Dikemas' },
  { value: 'sedang_dikirim',   label: 'Dikirim' },
  { value: 'pesanan_selesai',  label: 'Selesai' },
  { value: 'dikembalikan',     label: 'Dikembalikan' },
]

const filteredOrders = computed(() =>
  activeFilter.value === 'all'
    ? orders.value
    : orders.value.filter(o => o.status === activeFilter.value)
)
const doneCount   = computed(() => orders.value.filter(o => o.status === 'pesanan_selesai').length)
const activeCount = computed(() => orders.value.filter(o => o.status !== 'pesanan_selesai' && o.status !== 'dikembalikan').length)

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
  return {
    sedang_dikemas:    'Dikemas',
    menunggu_pengirim: 'Menunggu Pengirim',
    sedang_dikirim:    'Dikirim',
    pesanan_selesai:   'Selesai',
    dikembalikan:      'Dikembalikan',
  }[s] || s
}
function statusColor(s) {
  return {
    sedang_dikemas:    'bg-yellow-50 text-yellow-700 border-yellow-200',
    menunggu_pengirim: 'bg-blue-50 text-blue-700 border-blue-200',
    sedang_dikirim:    'bg-blue-50 text-blue-700 border-blue-200',
    pesanan_selesai:   'bg-green-50 text-green-700 border-green-200',
    dikembalikan:      'bg-red-50 text-red-700 border-red-200',
  }[s] || 'bg-muted text-muted-foreground border-border'
}
function progressPct(s) {
  return { sedang_dikemas: 25, menunggu_pengirim: 50, sedang_dikirim: 75 }[s] ?? 0
}

onMounted(async () => {
  try {
    const { data } = await buyerApi.getOrders()
    orders.value = data.data || data
  } finally { loading.value = false }
})
</script>
