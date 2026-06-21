<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Laporan Pendapatan</h1>
      <p class="text-muted-foreground text-sm mt-1">Ringkasan keuangan dan riwayat transaksi toko kamu.</p>
    </div>

    <!-- Skeleton loading -->
    <div v-if="loading">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div v-for="i in 3" :key="i" class="h-28 bg-muted rounded-xl animate-pulse" />
      </div>
      <div class="h-48 bg-muted rounded-xl animate-pulse mb-6" />
      <div class="h-64 bg-muted rounded-xl animate-pulse" />
    </div>

    <template v-else>
      <!-- Hero net income -->
      <div class="rounded-2xl bg-gradient-to-br from-primary/90 to-primary p-6 mb-6 text-primary-foreground shadow-lg">
        <div class="flex items-center gap-2 mb-1 opacity-80">
          <TrendingUp class="w-4 h-4" />
          <p class="text-sm font-medium">Net Pendapatan</p>
        </div>
        <p class="text-4xl font-bold tracking-tight">{{ formatPrice(report.summary.net_income) }}</p>
        <div class="flex gap-4 mt-4 text-sm opacity-80">
          <span class="flex items-center gap-1.5">
            <ArrowUpRight class="w-3.5 h-3.5" />
            Pemasukan {{ formatPrice(report.summary.total_income) }}
          </span>
          <span class="flex items-center gap-1.5">
            <ArrowDownLeft class="w-3.5 h-3.5" />
            Pengembalian {{ formatPrice(report.summary.total_reversal) }}
          </span>
        </div>

        <!-- Income vs reversal bar -->
        <div v-if="report.summary.total_income > 0" class="mt-4">
          <div class="h-1.5 rounded-full bg-white/20 overflow-hidden">
            <div
              class="h-full rounded-full bg-white transition-all duration-700"
              :style="{ width: incomeRatio + '%' }"
            />
          </div>
          <p class="text-xs opacity-60 mt-1">{{ incomeRatio }}% pendapatan bersih dari total pemasukan</p>
        </div>

        <!-- Stats row -->
        <div class="mt-5 flex items-center divide-x divide-white/20 bg-white/10 rounded-xl px-1">
          <div class="flex items-center gap-2.5 px-4 py-3 flex-1">
            <div class="w-7 h-7 rounded-lg bg-white/15 flex items-center justify-center shrink-0">
              <ArrowUpRight class="w-3.5 h-3.5" />
            </div>
            <div>
              <p class="text-[10px] opacity-60 leading-none mb-1">Total Pemasukan</p>
              <p class="text-sm font-bold leading-none">{{ formatPrice(report.summary.total_income) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2.5 px-4 py-3 flex-1">
            <div class="w-7 h-7 rounded-lg bg-white/15 flex items-center justify-center shrink-0">
              <ArrowDownLeft class="w-3.5 h-3.5" />
            </div>
            <div>
              <p class="text-[10px] opacity-60 leading-none mb-1">Total Pengembalian</p>
              <p class="text-sm font-bold leading-none">{{ formatPrice(report.summary.total_reversal) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2.5 px-4 py-3 flex-1">
            <div class="w-7 h-7 rounded-lg bg-white/15 flex items-center justify-center shrink-0">
              <Clock class="w-3.5 h-3.5" />
            </div>
            <div>
              <p class="text-[10px] opacity-60 leading-none mb-1">Update Terakhir</p>
              <p class="text-sm font-bold leading-none">{{ report.summary.last_updated ?? '—' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary stat cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <Card>
          <CardContent class="pt-5">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                <ShoppingBag class="w-5 h-5 text-slate-600" />
              </div>
              <div>
                <p class="text-xs text-muted-foreground">Total Pesanan</p>
                <p class="text-2xl font-bold">{{ report.summary.total_orders }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                <Package class="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <p class="text-xs text-muted-foreground">Sudah Diproses</p>
                <p class="text-2xl font-bold text-blue-600">{{ report.summary.processed_orders }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-5">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                <CheckCircle class="w-5 h-5 text-green-600" />
              </div>
              <div>
                <p class="text-xs text-muted-foreground">Selesai</p>
                <p class="text-2xl font-bold text-green-600">{{ report.summary.completed_orders }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Tabbed section: Transaksi & Pesanan -->
      <Card>
        <CardContent class="pt-6">
          <!-- Tab toggle -->
          <div class="flex gap-1 p-1 bg-muted rounded-lg w-fit mb-5">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="activeTab = tab.key"
              :class="activeTab === tab.key
                ? 'bg-background shadow-sm text-foreground'
                : 'text-muted-foreground hover:text-foreground'"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-all"
            >
              <component :is="tab.icon" class="w-3.5 h-3.5" />
              {{ tab.label }}
              <span class="text-xs opacity-60">({{ tab.count }})</span>
            </button>
          </div>

          <!-- Filter bar (shared date range + context-specific chips) -->
          <div class="flex flex-wrap items-center gap-2 mb-5 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50">
            <input
              v-model="dateFrom"
              type="date"
              :max="dateTo || undefined"
              class="text-xs border border-slate-200 rounded-lg px-3 py-2 outline-none transition-colors cursor-pointer text-slate-600 bg-white"
              @focus="$event.target.style.borderColor='#c41952'"
              @blur="$event.target.style.borderColor=''"
            />
            <span class="text-slate-300 text-sm">—</span>
            <input
              v-model="dateTo"
              type="date"
              :min="dateFrom || undefined"
              class="text-xs border border-slate-200 rounded-lg px-3 py-2 outline-none transition-colors cursor-pointer text-slate-600 bg-white"
              @focus="$event.target.style.borderColor='#c41952'"
              @blur="$event.target.style.borderColor=''"
            />
            <button
              v-if="dateFrom || dateTo"
              @click="dateFrom = ''; dateTo = ''"
              class="text-xs font-medium px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-500 hover:border-red-200 hover:text-red-500 transition-all cursor-pointer bg-white"
            >✕</button>

            <template v-if="activeTab === 'orders'">
              <div class="w-px h-5 bg-slate-300 mx-1 hidden sm:block" />
              <div class="flex flex-wrap gap-1.5">
                <button
                  v-for="s in statusFilters"
                  :key="s.key"
                  @click="activeStatus = s.key"
                  class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-all cursor-pointer"
                  :style="activeStatus === s.key
                    ? 'border-color:#c41952;color:#c41952;background:#fdf2f5'
                    : 'border-color:#e2e8f0;color:#6b7280;background:#fff'"
                >
                  {{ s.label }}
                  <span class="ml-1 opacity-70">{{ s.count }}</span>
                </button>
              </div>
            </template>

            <template v-if="activeTab === 'transactions'">
              <div class="w-px h-5 bg-slate-300 mx-1 hidden sm:block" />
              <div class="flex flex-wrap gap-1.5">
                <button
                  v-for="f in txTypeFilters"
                  :key="f.value"
                  @click="activeTxType = f.value"
                  class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-all cursor-pointer"
                  :style="activeTxType === f.value
                    ? 'border-color:#c41952;color:#c41952;background:#fdf2f5'
                    : 'border-color:#e2e8f0;color:#6b7280;background:#fff'"
                >
                  {{ f.label }}
                </button>
              </div>
            </template>
          </div>

          <!-- Riwayat Transaksi -->
          <div v-if="activeTab === 'transactions'">
            <div v-if="!filteredTransactions.length" class="flex flex-col items-center py-10 text-muted-foreground">
              <ReceiptText class="w-10 h-10 mb-2 opacity-20" />
              <p class="text-sm">Belum ada transaksi.</p>
            </div>
            <div v-else class="divide-y">
              <div
                v-for="tx in filteredTransactions"
                :key="tx.id"
                class="py-3 flex items-center justify-between gap-3"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div
                    :class="tx.type === 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500'"
                    class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                  >
                    <ArrowUpRight v-if="tx.type === 'income'" class="w-4 h-4" />
                    <ArrowDownLeft v-else class="w-4 h-4" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-medium truncate">{{ tx.description }}</p>
                    <p class="text-xs text-muted-foreground">
                      Pesanan #{{ tx.order_id }} · {{ formatDate(tx.created_at) }}
                    </p>
                  </div>
                </div>
                <span
                  :class="tx.type === 'income' ? 'text-green-600' : 'text-red-500'"
                  class="font-semibold text-sm shrink-0"
                >
                  {{ tx.type === 'income' ? '+' : '-' }}{{ formatPrice(tx.amount) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Semua Pesanan -->
          <div v-else-if="activeTab === 'orders'">
            <div v-if="!filteredOrders.length" class="flex flex-col items-center py-10 text-muted-foreground">
              <ClipboardList class="w-10 h-10 mb-2 opacity-20" />
              <p class="text-sm">Tidak ada pesanan dengan status ini.</p>
            </div>
            <div v-else class="divide-y">
              <div v-for="order in filteredOrders" :key="order.id" class="py-3">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                      <span class="text-sm font-semibold">#{{ order.id }}</span>
                      <span :class="statusClass(order.status)" class="text-xs px-2 py-0.5 rounded-full font-medium">
                        {{ statusLabel(order.status) }}
                      </span>
                    </div>
                    <p class="text-xs text-muted-foreground">
                      {{ order.user?.name }} · {{ formatDate(order.created_at) }}
                    </p>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="font-semibold text-sm">{{ formatPrice(order.total) }}</p>
                    <p v-if="order.discount_amount > 0" class="text-xs text-green-600">
                      Diskon -{{ formatPrice(order.discount_amount) }}
                    </p>
                  </div>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import {
  TrendingUp, ArrowUpRight, ArrowDownLeft,
  ShoppingBag, Package, CheckCircle,
  ReceiptText, ClipboardList, Clock
} from '@lucide/vue'
import { Card, CardContent } from '@/components/ui/card'
import { sellerApi } from '@/services/seller'

const report = ref({ summary: {}, orders: [], transactions: [] })
const loading = ref(true)
const activeTab = ref('transactions')
const activeStatus = ref('all')
const activeTxType = ref('all')
const dateFrom = ref('')
const dateTo = ref('')

const txTypeFilters = [
  { value: 'all',      label: 'Semua' },
  { value: 'income',   label: 'Pemasukan' },
  { value: 'reversal', label: 'Pengembalian' },
]

function inDateRange(dateStr) {
  if (!dateFrom.value && !dateTo.value) return true
  const d = new Date(dateStr)
  if (dateFrom.value && d < new Date(dateFrom.value)) return false
  if (dateTo.value && d > new Date(dateTo.value + 'T23:59:59')) return false
  return true
}

const dateFilteredOrders = computed(() =>
  (report.value.orders ?? []).filter(o => inDateRange(o.created_at))
)

const statusFilters = computed(() => {
  const orders = dateFilteredOrders.value
  const counts = {}
  for (const o of orders) counts[o.status] = (counts[o.status] ?? 0) + 1
  return [
    { key: 'all', label: 'Semua', count: orders.length },
    ...Object.keys(STATUS_MAP)
      .filter(k => counts[k])
      .map(k => ({ key: k, label: STATUS_MAP[k].label, count: counts[k] })),
  ]
})

const filteredOrders = computed(() => {
  const orders = dateFilteredOrders.value
  return activeStatus.value === 'all' ? orders : orders.filter(o => o.status === activeStatus.value)
})

const filteredTransactions = computed(() => {
  let txs = (report.value.transactions ?? []).filter(t => inDateRange(t.created_at))
  if (activeTxType.value !== 'all') txs = txs.filter(t => t.type === activeTxType.value)
  return txs
})

const tabs = computed(() => [
  { key: 'transactions', label: 'Riwayat Transaksi', icon: ReceiptText, count: report.value.transactions?.length ?? 0 },
  { key: 'orders', label: 'Semua Pesanan', icon: ClipboardList, count: report.value.orders?.length ?? 0 },
])

const incomeRatio = computed(() => {
  const income = report.value.summary.total_income ?? 0
  const net = report.value.summary.net_income ?? 0
  if (!income) return 0
  return Math.round((net / income) * 100)
})

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

async function fetchReport() {
  try {
    const { data } = await sellerApi.getReport()
    report.value = data.data
  } finally {
    loading.value = false
  }
}

let pollInterval = null

onMounted(() => {
  fetchReport()
  pollInterval = setInterval(fetchReport, 30_000)
})

onUnmounted(() => {
  clearInterval(pollInterval)
})
</script>
