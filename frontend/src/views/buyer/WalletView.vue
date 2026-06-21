<template>
  <div class="max-w-[680px] mx-auto">

    <!-- Balance card -->
    <div class="relative rounded-2xl p-6 mb-5 overflow-hidden" style="background: linear-gradient(135deg, #c41952 0%, #9b1240 50%, #6b0e30 100%); box-shadow: 0 8px 32px rgba(196,25,82,.30)">
      <!-- decorative circles -->
      <div class="pointer-events-none absolute -top-10 -right-10 w-44 h-44 rounded-full" style="background:rgba(255,255,255,.07)"/>
      <div class="pointer-events-none absolute -bottom-12 -left-6 w-40 h-40 rounded-full" style="background:rgba(255,255,255,.05)"/>
      <div class="pointer-events-none absolute top-4 right-24 w-20 h-20 rounded-full" style="background:rgba(255,255,255,.04)"/>

      <div class="relative">
        <!-- top row -->
        <div class="flex items-start justify-between gap-4 mb-6">
          <div>
            <p class="text-[11px] font-semibold tracking-widest uppercase mb-1" style="color:rgba(255,255,255,.55)">Saldo Kamu</p>
            <p class="text-[2rem] font-extrabold tracking-[-0.03em] text-white leading-none">
              {{ walletLoading ? '—' : formatPrice(wallet?.balance ?? 0) }}
            </p>
          </div>
          <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:rgba(255,255,255,.15)">
            <WalletCards class="w-5 h-5 text-white" />
          </div>
        </div>

        <!-- bottom row: in/out stats -->
        <div class="flex items-center gap-6 pt-4" style="border-top:1px solid rgba(255,255,255,.12)">
          <div>
            <p class="text-[10px] font-semibold tracking-wider uppercase" style="color:rgba(255,255,255,.45)">Total Masuk</p>
            <p class="text-[13px] font-bold text-white mt-0.5">{{ loadingTx ? '—' : formatPrice(totalIn) }}</p>
          </div>
          <div class="w-px h-8" style="background:rgba(255,255,255,.15)"/>
          <div>
            <p class="text-[10px] font-semibold tracking-wider uppercase" style="color:rgba(255,255,255,.45)">Total Keluar</p>
            <p class="text-[13px] font-bold text-white mt-0.5">{{ loadingTx ? '—' : formatPrice(totalOut) }}</p>
          </div>
          <div class="ml-auto">
            <p class="text-[10px]" style="color:rgba(255,255,255,.35)">SEAPEDIA Wallet</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Up -->
    <div class="bg-card border rounded-2xl p-5 mb-4">
      <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
          <Plus class="w-3.5 h-3.5 text-primary" />
        </div>
        <h2 class="text-[15px] font-bold">Top Up Saldo</h2>
      </div>

      <!-- Quick amounts -->
      <div class="flex flex-wrap gap-2 mb-4">
        <button
          v-for="amount in quickAmounts"
          :key="amount"
          type="button"
          class="px-3.5 py-1.5 rounded-full border-[1.5px] text-[12px] font-semibold transition-all cursor-pointer"
          :class="topUpAmount == amount
            ? 'border-primary bg-primary/8 text-primary'
            : 'border-border text-muted-foreground bg-background hover:border-primary/40 hover:text-foreground'"
          @click="topUpAmount = amount"
        >
          {{ formatPrice(amount) }}
        </button>
      </div>

      <!-- Input + button -->
      <div class="flex gap-2.5">
        <div class="relative flex-1">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-muted-foreground">Rp</span>
          <input
            v-model="topUpAmount"
            type="number"
            placeholder="0"
            class="w-full h-11 pl-9 pr-3.5 rounded-xl border-[1.5px] text-[14px] font-semibold outline-none font-sans bg-background transition-all placeholder:text-muted-foreground/40 focus:border-primary focus:ring-2 focus:ring-primary/10"
            style="border-color:var(--border)"
            @input="topUpAmount = $event.target.value"
            @keyup.enter="doTopUp"
          />
        </div>
        <button
          class="h-11 px-5 rounded-xl border-0 text-[13px] font-bold text-white cursor-pointer transition-all whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed bg-primary hover:opacity-90"
          :disabled="topping || !topUpAmount"
          @click="doTopUp"
        >
          <span v-if="topping" class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Memproses…
          </span>
          <span v-else>Top Up</span>
        </button>
      </div>

      <p v-if="topUpError" class="flex items-center gap-1.5 text-[12px] text-red-500 mt-2.5">
        <AlertCircle class="w-3.5 h-3.5 shrink-0" /> {{ topUpError }}
      </p>
    </div>

    <!-- Transactions -->
    <div class="bg-card border rounded-2xl overflow-hidden">

      <!-- header -->
      <div class="px-5 py-4 border-b bg-muted/30 flex items-center justify-between">
        <h2 class="text-[15px] font-bold">Riwayat Transaksi</h2>
        <span class="text-[11px] font-semibold text-muted-foreground">
          {{ filteredTxs.length }} transaksi
        </span>
      </div>

      <!-- Filter bar: date range + type chips -->
      <div class="px-5 py-3 border-b flex flex-wrap items-center gap-2">
        <input
          v-model="dateFrom"
          type="date"
          :max="dateTo || undefined"
          class="text-xs border border-slate-200 rounded-lg px-3 py-2 outline-none transition-colors cursor-pointer text-slate-600"
          @focus="$event.target.style.borderColor='#c41952'"
          @blur="$event.target.style.borderColor=''"
        />
        <span class="text-slate-300 text-sm">—</span>
        <input
          v-model="dateTo"
          type="date"
          :min="dateFrom || undefined"
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
            class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full border transition-all cursor-pointer"
            :style="activeFilter === f.value
              ? 'border-color:#c41952;color:#c41952;background:#fdf2f5'
              : 'border-color:#e2e8f0;color:#6b7280;background:#fff'"
            @click="activeFilter = f.value"
          >
            {{ f.label }}
          </button>
        </div>
      </div>

      <!-- skeleton -->
      <div v-if="loadingTx" class="divide-y">
        <div v-for="i in 4" :key="i" class="flex items-center gap-3.5 px-5 py-4">
          <div class="w-10 h-10 rounded-2xl bg-muted animate-pulse shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-3 w-24 bg-muted animate-pulse rounded-full" />
            <div class="h-2.5 w-36 bg-muted animate-pulse rounded-full" />
          </div>
          <div class="space-y-1.5 text-right">
            <div class="h-3 w-20 bg-muted animate-pulse rounded-full" />
            <div class="h-2.5 w-14 bg-muted animate-pulse rounded-full ml-auto" />
          </div>
        </div>
      </div>

      <!-- empty -->
      <div v-else-if="filteredTxs.length === 0" class="flex flex-col items-center gap-3 py-16 text-center">
        <div class="w-12 h-12 rounded-2xl bg-muted flex items-center justify-center">
          <ReceiptText class="w-5 h-5 text-muted-foreground/30" />
        </div>
        <p class="text-[13px] text-muted-foreground font-medium">Tidak ada transaksi</p>
      </div>

      <!-- list -->
      <div v-else class="divide-y">
        <div
          v-for="tx in filteredTxs"
          :key="tx.id"
          class="flex items-center gap-3.5 px-5 py-3.5 hover:bg-muted/30 transition-colors"
        >
          <!-- icon -->
          <div
            class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0"
            :class="{
              'bg-green-50': tx.type === 'topup',
              'bg-red-50':   tx.type === 'payment',
              'bg-blue-50':  tx.type === 'refund',
            }"
          >
            <ArrowDownToLine v-if="tx.type === 'topup'"   class="w-4 h-4 text-green-600" />
            <ShoppingBag     v-else-if="tx.type === 'payment'" class="w-4 h-4 text-red-500" />
            <RotateCcw       v-else                        class="w-4 h-4 text-blue-500" />
          </div>

          <!-- label + date -->
          <div class="flex-1 min-w-0">
            <p class="text-[13px] font-semibold">{{ txLabel(tx.type) }}</p>
            <p class="text-[11px] text-muted-foreground mt-0.5 truncate">{{ formatDate(tx.created_at) }}</p>
          </div>

          <!-- amount -->
          <div class="text-right shrink-0">
            <p
              class="text-[14px] font-bold tabular-nums"
              :class="tx.type === 'payment' ? 'text-red-500' : 'text-green-600'"
            >
              {{ tx.type === 'payment' ? '−' : '+' }}{{ formatPrice(tx.amount) }}
            </p>
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full mt-1 inline-block"
              :class="{
                'bg-green-50 text-green-700': tx.type !== 'payment',
                'bg-red-50 text-red-600': tx.type === 'payment',
              }"
            >
              {{ tx.type === 'payment' ? 'Keluar' : 'Berhasil' }}
            </span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { WalletCards, Plus, ArrowDownToLine, ShoppingBag, RotateCcw, AlertCircle, ReceiptText } from '@lucide/vue'
import { buyerApi } from '@/services/buyer'
import { toast } from 'vue-sonner'

const wallet        = ref(null)
const transactions  = ref([])
const topUpAmount   = ref('')
const topping       = ref(false)
const topUpError    = ref('')
const loadingTx     = ref(true)
const walletLoading = ref(true)
const activeFilter  = ref('all')
const dateFrom      = ref('')
const dateTo        = ref('')

const quickAmounts = [50000, 100000, 200000, 500000, 1000000]

const filters = [
  { value: 'all',     label: 'Semua' },
  { value: 'topup',   label: 'Top Up' },
  { value: 'payment', label: 'Pembayaran' },
  { value: 'refund',  label: 'Refund' },
]

const dateFilteredTxs = computed(() => {
  if (!dateFrom.value && !dateTo.value) return transactions.value
  return transactions.value.filter(t => {
    const d = new Date(t.created_at)
    if (dateFrom.value && d < new Date(dateFrom.value)) return false
    if (dateTo.value && d > new Date(dateTo.value + 'T23:59:59')) return false
    return true
  })
})

const filteredTxs = computed(() =>
  activeFilter.value === 'all'
    ? dateFilteredTxs.value
    : dateFilteredTxs.value.filter(t => t.type === activeFilter.value)
)

const totalIn  = computed(() => transactions.value.filter(t => t.type !== 'payment').reduce((s, t) => s + t.amount, 0))
const totalOut = computed(() => transactions.value.filter(t => t.type === 'payment').reduce((s, t) => s + t.amount, 0))

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}
function formatDate(d) {
  return new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
}
function txLabel(type) {
  return { topup: 'Top Up Saldo', payment: 'Pembayaran', refund: 'Refund' }[type] || type
}

async function loadWallet() {
  walletLoading.value = true
  try {
    const { data } = await buyerApi.getWallet()
    wallet.value = data.data
  } catch {} finally { walletLoading.value = false }
}

async function loadTransactions() {
  loadingTx.value = true
  try {
    const { data } = await buyerApi.getTransactions()
    transactions.value = data.data || data
  } catch {} finally { loadingTx.value = false }
}

async function doTopUp() {
  const amount = Number(topUpAmount.value)
  topUpError.value = ''
  if (!amount || amount < 10000)  { topUpError.value = 'Minimal top up adalah Rp 10.000.'; return }
  if (amount > 10000000)          { topUpError.value = 'Maksimal top up adalah Rp 10.000.000.'; return }
  topping.value = true
  try {
    const { data } = await buyerApi.topUp(amount)
    wallet.value.balance = data.data.balance
    topUpAmount.value = ''
    toast.success('Top up berhasil!')
    loadTransactions()
  } catch (e) {
    topUpError.value = e.response?.data?.message || e.response?.data?.errors?.amount?.[0] || 'Gagal top up.'
  } finally { topping.value = false }
}

onMounted(() => { loadWallet(); loadTransactions() })
</script>
