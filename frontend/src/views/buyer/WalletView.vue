<template>
  <div class="max-w-[680px] mx-auto">

    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-[22px] font-bold tracking-[-0.03em]">Dompet Saya</h1>
      <p class="text-[13px] text-muted-foreground mt-0.5">Kelola saldo dan riwayat transaksi</p>
    </div>

    <!-- Balance card -->
    <div class="relative rounded-2xl p-7 mb-5 overflow-hidden" style="background:linear-gradient(135deg,#6366f1 0%,#818cf8 40%,#06b6d4 100%);box-shadow:0 8px 32px rgba(99,102,241,.35)">
      <!-- decorative blobs -->
      <div class="pointer-events-none absolute -top-12 -right-12 w-48 h-48 rounded-full" style="background:rgba(255,255,255,.10)"/>
      <div class="pointer-events-none absolute -bottom-14 -left-8 w-44 h-44 rounded-full" style="background:rgba(255,255,255,.07)"/>

      <div class="relative flex items-start justify-between gap-4">
        <div>
          <p class="text-[11px] font-semibold tracking-widest uppercase mb-2" style="color:rgba(255,255,255,.65)">Saldo Kamu</p>
          <p class="text-[2.25rem] font-extrabold tracking-[-0.03em] text-white leading-none">
            {{ walletLoading ? '...' : formatPrice(wallet?.balance ?? 0) }}
          </p>
          <p class="text-[11px] mt-3" style="color:rgba(255,255,255,.45)">SEAPEDIA · Buyer Wallet</p>
        </div>
        <div class="w-[52px] h-[52px] rounded-[14px] flex items-center justify-center shrink-0" style="background:rgba(255,255,255,.18);backdrop-filter:blur(8px)">
          <WalletCards class="w-5 h-5 text-white" />
        </div>
      </div>
    </div>

    <!-- Mini stats -->
    <div class="grid grid-cols-2 gap-3 mb-5">
      <div class="bg-card border rounded-xl px-4 py-3.5 flex items-center gap-2.5">
        <div class="w-[34px] h-[34px] rounded-lg bg-green-50 flex items-center justify-center shrink-0">
          <TrendingUp class="w-4 h-4 text-green-600" />
        </div>
        <div>
          <p class="text-[11px] text-muted-foreground font-medium">Total Masuk</p>
          <p class="text-[14px] font-bold tracking-[-0.01em]">{{ formatPrice(totalIn) }}</p>
        </div>
      </div>
      <div class="bg-card border rounded-xl px-4 py-3.5 flex items-center gap-2.5">
        <div class="w-[34px] h-[34px] rounded-lg bg-red-50 flex items-center justify-center shrink-0">
          <TrendingDown class="w-4 h-4 text-red-500" />
        </div>
        <div>
          <p class="text-[11px] text-muted-foreground font-medium">Total Keluar</p>
          <p class="text-[14px] font-bold tracking-[-0.01em]">{{ formatPrice(totalOut) }}</p>
        </div>
      </div>
    </div>

    <!-- Top Up -->
    <div class="bg-card border rounded-xl p-6 mb-4">
      <h2 class="text-[15px] font-bold mb-4">Top Up Saldo</h2>

      <div class="flex flex-wrap gap-2 mb-4">
        <button
          v-for="amount in quickAmounts"
          :key="amount"
          type="button"
          class="px-3.5 py-[6px] rounded-full border-[1.5px] text-[13px] font-medium transition-all cursor-pointer"
          :class="topUpAmount == amount
            ? 'border-primary bg-primary/10 text-primary font-semibold'
            : 'border-border text-slate-600 bg-background hover:border-primary/50 hover:text-primary'"
          @click="topUpAmount = amount"
        >
          {{ formatPrice(amount) }}
        </button>
      </div>

      <div class="flex gap-2.5">
        <input
          v-model="topUpAmount"
          type="number"
          placeholder="Nominal (min Rp 10.000)"
          class="flex-1 h-[42px] px-3.5 rounded-xl border-[1.5px] text-[13px] outline-none font-sans bg-background transition-all placeholder:text-muted-foreground/60"
          style="border-color:var(--border)"
          @focus="e => e.target.style.borderColor='#6366f1'"
          @blur="e => e.target.style.borderColor='var(--border)'"
          @input="topUpAmount = $event.target.value"
          @keyup.enter="doTopUp"
        />
        <button
          class="h-[42px] px-[22px] rounded-xl border-0 text-[13px] font-bold text-white cursor-pointer transition-all whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed"
          style="background:linear-gradient(135deg,#6366f1,#06b6d4);box-shadow:0 2px 10px rgba(99,102,241,.3)"
          :disabled="topping || !topUpAmount"
          @click="doTopUp"
        >
          {{ topping ? 'Memproses…' : 'Top Up' }}
        </button>
      </div>

      <p v-if="topUpError" class="flex items-center gap-1.5 text-[12px] text-red-500 mt-2">
        <AlertCircle class="w-3.5 h-3.5 shrink-0" /> {{ topUpError }}
      </p>
    </div>

    <!-- Transactions -->
    <div class="bg-card border rounded-xl overflow-hidden">

      <!-- header -->
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h2 class="text-[15px] font-bold">Riwayat Transaksi</h2>
        <span class="text-[11px] font-semibold text-muted-foreground bg-muted px-2.5 py-1 rounded-full">
          {{ filteredTxs.length }} transaksi
        </span>
      </div>

      <!-- filter tabs -->
      <div class="flex gap-1 px-6 py-3 border-b bg-muted/30">
        <button
          v-for="f in filters"
          :key="f.value"
          class="px-3 py-1 rounded-full text-[12px] font-medium border cursor-pointer transition-all"
          :class="activeFilter === f.value
            ? 'bg-primary text-white border-primary'
            : 'border-transparent text-muted-foreground hover:bg-muted hover:text-foreground'"
          @click="activeFilter = f.value"
        >
          {{ f.label }}
        </button>
      </div>

      <!-- skeleton -->
      <div v-if="loadingTx" class="divide-y">
        <div v-for="i in 3" :key="i" class="flex items-center gap-3.5 px-6 py-4">
          <div class="w-10 h-10 rounded-full bg-muted animate-pulse shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-3 w-24 bg-muted animate-pulse rounded" />
            <div class="h-2.5 w-36 bg-muted animate-pulse rounded" />
          </div>
          <div class="space-y-1.5 text-right">
            <div class="h-3 w-20 bg-muted animate-pulse rounded" />
            <div class="h-2.5 w-14 bg-muted animate-pulse rounded ml-auto" />
          </div>
        </div>
      </div>

      <!-- empty -->
      <div v-else-if="filteredTxs.length === 0" class="flex flex-col items-center gap-2 py-14 text-center">
        <ReceiptText class="w-10 h-10 text-muted-foreground/25" />
        <p class="text-[13px] text-muted-foreground">Tidak ada transaksi.</p>
      </div>

      <!-- list -->
      <div v-else class="divide-y">
        <div
          v-for="tx in filteredTxs"
          :key="tx.id"
          class="flex items-center gap-3.5 px-6 py-3.5 hover:bg-muted/30 transition-colors"
        >
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
            :class="{
              'bg-green-50': tx.type === 'topup',
              'bg-red-50':   tx.type === 'payment',
              'bg-blue-50':  tx.type === 'refund',
            }"
          >
            <ArrowUp   v-if="tx.type === 'topup'"   class="w-[18px] h-[18px] text-green-600" />
            <ArrowDown v-else-if="tx.type === 'payment'" class="w-[18px] h-[18px] text-red-500" />
            <RotateCcw v-else                         class="w-[18px] h-[18px] text-blue-500" />
          </div>

          <div class="flex-1 min-w-0">
            <p class="text-[13px] font-semibold">{{ txLabel(tx.type) }}</p>
            <p class="text-[11px] text-muted-foreground mt-0.5">{{ formatDate(tx.created_at) }}</p>
          </div>

          <div class="text-right shrink-0">
            <p class="text-[13px] font-bold" :class="tx.type === 'payment' ? 'text-red-500' : 'text-green-600'">
              {{ tx.type === 'payment' ? '−' : '+' }}{{ formatPrice(tx.amount) }}
            </p>
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border mt-1 inline-block bg-green-50 text-green-700 border-green-200">
              Berhasil
            </span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { WalletCards, TrendingUp, TrendingDown, ArrowUp, ArrowDown, RotateCcw, AlertCircle, ReceiptText } from '@lucide/vue'
import { buyerApi } from '@/services/buyer'
import { toast } from 'vue-sonner'

const wallet       = ref(null)
const transactions = ref([])
const topUpAmount  = ref('')
const topping      = ref(false)
const topUpError   = ref('')
const loadingTx    = ref(true)
const walletLoading = ref(true)
const activeFilter = ref('all')

const quickAmounts = [50000, 100000, 200000, 500000, 1000000]

const filters = [
  { value: 'all',     label: 'Semua' },
  { value: 'topup',   label: 'Top Up' },
  { value: 'payment', label: 'Pembayaran' },
  { value: 'refund',  label: 'Refund' },
]

const filteredTxs = computed(() =>
  activeFilter.value === 'all'
    ? transactions.value
    : transactions.value.filter(t => t.type === activeFilter.value)
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
  return { topup: 'Top Up', payment: 'Pembayaran', refund: 'Refund' }[type] || type
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
