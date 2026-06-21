<template>
  <div class="max-w-[800px] mx-auto">

    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-[22px] font-bold tracking-[-0.03em]">Riwayat Pesanan</h1>
      <p class="text-[13px] text-muted-foreground mt-0.5">Semua pesanan yang pernah kamu buat</p>
    </div>

    <!-- Loading -->
    <div v-if="loading">
      <div class="grid grid-cols-3 gap-3 mb-5">
        <div v-for="i in 3" :key="i" class="h-20 bg-muted rounded-2xl animate-pulse" />
      </div>
      <div class="space-y-3">
        <div v-for="i in 3" :key="i" class="h-28 bg-muted rounded-2xl animate-pulse" />
      </div>
    </div>

    <template v-else>
      <!-- Summary stats -->
      <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-card border rounded-2xl px-4 py-3.5 flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
            <FileText class="w-4 h-4 text-primary" />
          </div>
          <div>
            <p class="text-[11px] text-muted-foreground font-medium">Total</p>
            <p class="text-[20px] font-bold tracking-[-0.03em] leading-tight">{{ orders.length }}</p>
          </div>
        </div>
        <div class="bg-card border rounded-2xl px-4 py-3.5 flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
            <CheckCircle class="w-4 h-4 text-green-600" />
          </div>
          <div>
            <p class="text-[11px] text-muted-foreground font-medium">Selesai</p>
            <p class="text-[20px] font-bold tracking-[-0.03em] leading-tight text-green-600">{{ doneCount }}</p>
          </div>
        </div>
        <div class="bg-card border rounded-2xl px-4 py-3.5 flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <Clock class="w-4 h-4 text-amber-500" />
          </div>
          <div>
            <p class="text-[11px] text-muted-foreground font-medium">Diproses</p>
            <p class="text-[20px] font-bold tracking-[-0.03em] leading-tight text-amber-600">{{ activeCount }}</p>
          </div>
        </div>
      </div>

      <!-- Filter bar: date range + status chips -->
      <div class="flex flex-wrap items-center gap-2 mb-5 px-4 py-3 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <input
          v-model="dateFrom"
          type="date"
          :max="dateTo || undefined"
          placeholder="Dari"
          class="text-xs border border-slate-200 rounded-lg px-3 py-2 outline-none transition-colors cursor-pointer text-slate-600"
          @focus="$event.target.style.borderColor='#c41952'"
          @blur="$event.target.style.borderColor=''"
        />
        <span class="text-slate-300 text-sm">—</span>
        <input
          v-model="dateTo"
          type="date"
          :min="dateFrom || undefined"
          placeholder="Sampai"
          class="text-xs border border-slate-200 rounded-lg px-3 py-2 outline-none transition-colors cursor-pointer text-slate-600"
          @focus="$event.target.style.borderColor='#c41952'"
          @blur="$event.target.style.borderColor=''"
        />
        <button
          v-if="dateFrom || dateTo"
          @click="dateFrom = ''; dateTo = ''"
          class="text-xs font-medium px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-500 hover:border-red-200 hover:text-red-500 transition-all cursor-pointer"
        >✕</button>

        <div class="w-px h-5 bg-slate-200 mx-1 hidden sm:block" />

        <div class="flex flex-wrap gap-1.5">
          <button
            v-for="f in filters"
            :key="f.value"
            class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-all cursor-pointer"
            :style="activeFilter === f.value
              ? 'border-color:#c41952;color:#c41952;background:#fdf2f5'
              : 'border-color:#e2e8f0;color:#6b7280;background:#fff'"
            @click="activeFilter = f.value"
          >
            {{ f.label }}
            <span v-if="f.value !== 'all'" class="ml-1 opacity-70">{{ filterCount(f.value) }}</span>
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div
        v-if="!filteredOrders.length"
        class="flex flex-col items-center gap-3 py-20 text-center bg-card border rounded-2xl"
      >
        <div class="w-14 h-14 rounded-2xl bg-muted flex items-center justify-center">
          <ClipboardList class="w-6 h-6 text-muted-foreground/30" />
        </div>
        <div>
          <p class="text-[15px] font-semibold">{{ activeFilter === 'all' ? 'Belum ada pesanan' : 'Tidak ada pesanan' }}</p>
          <p class="text-[13px] text-muted-foreground mt-0.5">
            {{ activeFilter === 'all' ? 'Mulai belanja dan pesananmu akan muncul di sini' : 'Coba pilih filter lain' }}
          </p>
        </div>
        <button
          v-if="activeFilter === 'all'"
          class="mt-1 px-5 py-2 rounded-xl border-0 text-[13px] font-semibold text-white bg-primary hover:opacity-90 cursor-pointer"
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
          class="bg-card border rounded-2xl overflow-hidden cursor-pointer hover:shadow-md transition-all hover:-translate-y-px group"
          @click="router.push(`/buyer/orders/${order.id}`)"
        >
          <!-- Card body -->
          <div class="px-5 pt-4 pb-4">
            <div class="flex items-start justify-between gap-4">
              <!-- Left info -->
              <div class="flex-1 min-w-0">
                <!-- Order id + status -->
                <div class="flex items-center gap-2 flex-wrap mb-2">
                  <span class="text-[13px] font-bold">Pesanan #{{ order.id }}</span>
                  <span :class="statusColor(order.status)" class="text-[11px] font-semibold rounded-full px-2.5 py-0.5 border">
                    {{ statusLabel(order.status) }}
                  </span>
                </div>
                <!-- Store name -->
                <p class="text-[13px] font-medium text-foreground/80 truncate">{{ order.store?.name }}</p>
                <!-- Meta row -->
                <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                  <span class="text-[11px] text-muted-foreground">{{ formatDate(order.created_at) }}</span>
                  <span class="w-[3px] h-[3px] rounded-full bg-muted-foreground/30 inline-block" />
                  <span class="text-[11px] text-muted-foreground">{{ order.items?.length }} produk</span>
                  <span class="w-[3px] h-[3px] rounded-full bg-muted-foreground/30 inline-block" />
                  <span class="text-[11px] text-muted-foreground">{{ methodLabel(order.delivery_method) }}</span>
                </div>
              </div>

              <!-- Right: price + link -->
              <div class="text-right shrink-0">
                <p class="text-[15px] font-bold text-primary">{{ formatPrice(order.total) }}</p>
                <p class="text-[11px] text-muted-foreground mt-1.5 flex items-center justify-end gap-0.5 group-hover:text-primary transition-colors">
                  Lihat Detail <ChevronRight class="w-3 h-3" />
                </p>
              </div>
            </div>
          </div>

          <!-- Progress steps (active orders) -->
          <template v-if="order.status !== 'pesanan_selesai' && order.status !== 'dikembalikan'">
            <div class="px-5 pb-4 pt-0">
              <div class="flex items-center gap-1">
                <div
                  v-for="(step, i) in progressSteps"
                  :key="step.key"
                  class="flex items-center flex-1 last:flex-none"
                >
                  <!-- dot -->
                  <div
                    class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 text-[9px] font-bold transition-colors"
                    :class="stepDone(order.status, step.key)
                      ? 'bg-primary text-white'
                      : 'bg-muted text-muted-foreground/50'"
                  >
                    <component :is="step.icon" class="w-2.5 h-2.5" />
                  </div>
                  <!-- connector line (not after last) -->
                  <div
                    v-if="i < progressSteps.length - 1"
                    class="flex-1 h-[2px] mx-1 rounded-full transition-colors"
                    :class="stepDone(order.status, progressSteps[i + 1].key) ? 'bg-primary' : 'bg-muted'"
                  />
                </div>
              </div>
              <div class="flex justify-between mt-1.5">
                <span
                  v-for="step in progressSteps"
                  :key="step.key"
                  class="text-[9px] font-medium flex-1 text-center first:text-left last:text-right"
                  :class="stepDone(order.status, step.key) ? 'text-primary' : 'text-muted-foreground/50'"
                >
                  {{ step.label }}
                </span>
              </div>
            </div>
          </template>

          <!-- Completed / returned footer strip -->
          <div
            v-else
            class="px-5 py-2.5 border-t text-[11px] font-medium flex items-center gap-1.5"
            :class="order.status === 'pesanan_selesai'
              ? 'bg-green-50/60 text-green-700'
              : 'bg-red-50/60 text-red-600'"
          >
            <component :is="order.status === 'pesanan_selesai' ? CheckCircle : XCircle" class="w-3.5 h-3.5 shrink-0" />
            {{ order.status === 'pesanan_selesai' ? 'Pesanan telah selesai' : 'Pesanan dikembalikan' }}
          </div>
        </div>
      </div>
    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { FileText, CheckCircle, Clock, ClipboardList, ChevronRight, Package, Truck, MapPin, XCircle } from '@lucide/vue'
import { buyerApi } from '@/services/buyer'

const router = useRouter()
const orders = ref([])
const loading = ref(true)
const activeFilter = ref('all')
const dateFrom = ref('')
const dateTo = ref('')

const filters = [
  { value: 'all',               label: 'Semua' },
  { value: 'sedang_dikemas',    label: 'Dikemas' },
  { value: 'sedang_dikirim',    label: 'Dikirim' },
  { value: 'pesanan_selesai',   label: 'Selesai' },
  { value: 'dikembalikan',      label: 'Dikembalikan' },
]

const progressSteps = [
  { key: 'sedang_dikemas',    label: 'Dikemas',  icon: Package },
  { key: 'menunggu_pengirim', label: 'Pengirim', icon: Clock },
  { key: 'sedang_dikirim',    label: 'Dikirim',  icon: Truck },
]

const STATUS_ORDER = ['sedang_dikemas', 'menunggu_pengirim', 'sedang_dikirim']

function stepDone(currentStatus, stepKey) {
  const ci = STATUS_ORDER.indexOf(currentStatus)
  const si = STATUS_ORDER.indexOf(stepKey)
  return si <= ci
}

const dateFilteredOrders = computed(() => {
  if (!dateFrom.value && !dateTo.value) return orders.value
  return orders.value.filter(o => {
    const d = new Date(o.created_at)
    if (dateFrom.value && d < new Date(dateFrom.value)) return false
    if (dateTo.value && d > new Date(dateTo.value + 'T23:59:59')) return false
    return true
  })
})

const filteredOrders = computed(() =>
  activeFilter.value === 'all'
    ? dateFilteredOrders.value
    : dateFilteredOrders.value.filter(o => o.status === activeFilter.value)
)
const doneCount   = computed(() => dateFilteredOrders.value.filter(o => o.status === 'pesanan_selesai').length)
const activeCount = computed(() => dateFilteredOrders.value.filter(o => !['pesanan_selesai', 'dikembalikan'].includes(o.status)).length)

function filterCount(status) {
  return dateFilteredOrders.value.filter(o => o.status === status).length
}

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
    sedang_dikemas:    'bg-amber-50 text-amber-700 border-amber-200',
    menunggu_pengirim: 'bg-blue-50 text-blue-700 border-blue-200',
    sedang_dikirim:    'bg-indigo-50 text-indigo-700 border-indigo-200',
    pesanan_selesai:   'bg-green-50 text-green-700 border-green-200',
    dikembalikan:      'bg-red-50 text-red-700 border-red-200',
  }[s] || 'bg-muted text-muted-foreground border-border'
}

onMounted(async () => {
  try {
    const { data } = await buyerApi.getOrders()
    orders.value = data.data || data
  } finally { loading.value = false }
})
</script>
