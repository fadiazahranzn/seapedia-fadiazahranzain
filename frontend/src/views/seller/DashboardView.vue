<template>
  <div class="min-h-full">
    <!-- Header greeting -->
    <div class="mb-8">
      <p class="text-sm font-medium uppercase tracking-widest mb-1" style="color:#c41952">Seller Dashboard</p>
      <h1 class="text-3xl font-bold text-slate-900 leading-tight">
        Selamat datang, <span style="color:#c41952">{{ auth.user?.name }}</span>!
      </h1>
      <p class="text-slate-500 mt-1 text-sm">Pantau performa toko kamu hari ini.</p>
    </div>

    <!-- No store yet -->
    <div
      v-if="!store && !loadingStore"
      class="rounded-2xl border border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-white p-12 text-center shadow-sm"
    >
      <div class="mx-auto mb-4 w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
        <Store class="w-8 h-8 text-slate-400" />
      </div>
      <h2 class="text-lg font-semibold text-slate-800 mb-1">Belum punya toko</h2>
      <p class="text-sm text-slate-500 mb-6 max-w-xs mx-auto">
        Buat toko untuk mulai berjualan di SEAPEDIA dan raih pembeli pertamamu.
      </p>
      <Button
        @click="router.push('/seller/store')"
        class="text-white px-6 py-2 rounded-xl font-medium transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer"
        style="background:#c41952"
      >
        Buat Toko Sekarang
      </Button>
    </div>

    <!-- Loading skeleton -->
    <div v-else-if="loadingStore" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div v-for="i in 3" :key="i" class="h-24 rounded-2xl bg-slate-100 animate-pulse" />
    </div>

    <template v-else-if="store">
      <!-- Period filter row -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-widest">Ringkasan</p>
        <div class="flex flex-wrap items-center gap-2">
          <!-- Preset pills -->
          <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
            <button
              v-for="opt in periodOptions"
              :key="opt.value"
              @click="setPeriod(opt.value)"
              class="text-xs font-medium px-3 py-1.5 rounded-lg transition-all duration-200 cursor-pointer"
              :style="period === opt.value
                ? 'background:#c41952;color:#fff;box-shadow:0 1px 4px rgba(196,25,82,0.3)'
                : 'color:#6b7280'"
            >
              {{ opt.label }}
            </button>
          </div>

          <!-- Custom range toggle -->
          <button
            @click="toggleCustom"
            class="flex items-center gap-1.5 text-xs font-medium px-3 py-2 rounded-xl border transition-all duration-200 cursor-pointer"
            :style="period === 'custom'
              ? 'background:#c41952;color:#fff;border-color:#c41952;box-shadow:0 1px 4px rgba(196,25,82,0.3)'
              : 'background:#fff;color:#6b7280;border-color:#e2e8f0'"
          >
            <CalendarRange class="w-3.5 h-3.5" />
            Custom
          </button>
        </div>
      </div>

      <!-- Custom date range inputs -->
      <div
        v-if="showCustom"
        class="flex flex-wrap items-end gap-3 mb-5 p-4 rounded-2xl border border-slate-200 bg-white shadow-sm"
      >
        <div class="flex flex-col gap-1">
          <label class="text-xs font-medium text-slate-500">Dari tanggal</label>
          <input
            v-model="customFrom"
            type="date"
            class="text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none transition-colors duration-200 cursor-pointer"
            :max="customTo || undefined"
            @focus="$event.target.style.borderColor='#c41952'"
            @blur="$event.target.style.borderColor=''"
          />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-xs font-medium text-slate-500">Sampai tanggal</label>
          <input
            v-model="customTo"
            type="date"
            class="text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none transition-colors duration-200 cursor-pointer"
            :min="customFrom || undefined"
            @focus="$event.target.style.borderColor='#c41952'"
            @blur="$event.target.style.borderColor=''"
          />
        </div>
        <button
          @click="applyCustom"
          :disabled="!customFrom || !customTo || loadingReport"
          class="text-sm font-medium px-4 py-2 rounded-lg text-white transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
          style="background:#c41952"
        >
          Terapkan
        </button>
        <button
          @click="clearCustom"
          class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all duration-200 cursor-pointer"
        >
          Reset
        </button>
      </div>

      <!-- Stat cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <!-- Total Produk -->
        <div
          class="relative overflow-hidden rounded-2xl p-5 cursor-pointer"
          style="background:linear-gradient(135deg,#c41952,#a0133f);box-shadow:0 4px 15px rgba(196,25,82,0.3)"
          @click="router.push('/seller/products')"
          role="button"
          tabindex="0"
          aria-label="Lihat produk"
        >
          <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10" />
          <div class="absolute -right-2 -bottom-6 w-32 h-32 rounded-full bg-white/5" />
          <div class="relative">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mb-3">
              <Package class="w-5 h-5 text-white" />
            </div>
            <p class="text-pink-100 text-xs font-medium uppercase tracking-wider mb-1">Total Produk</p>
            <p class="text-white text-3xl font-bold">{{ store.products?.length ?? 0 }}</p>
            <p class="text-pink-200 text-xs mt-1">Tidak terpengaruh filter</p>
          </div>
        </div>

        <!-- Total Pesanan -->
        <div
          class="relative overflow-hidden rounded-2xl p-5 cursor-pointer"
          style="background:linear-gradient(135deg,#d4205f,#c41952);box-shadow:0 4px 15px rgba(196,25,82,0.3)"
          @click="router.push('/seller/orders')"
          role="button"
          tabindex="0"
          aria-label="Lihat pesanan"
        >
          <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10" />
          <div class="absolute -right-2 -bottom-6 w-32 h-32 rounded-full bg-white/5" />
          <div class="relative">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mb-3">
              <ClipboardList class="w-5 h-5 text-white" />
            </div>
            <p class="text-pink-100 text-xs font-medium uppercase tracking-wider mb-1">Total Pesanan</p>
            <div v-if="loadingReport" class="w-16 h-8 bg-white/20 rounded animate-pulse mt-1" />
            <p v-else class="text-white text-3xl font-bold">{{ report.summary.total_orders ?? '—' }}</p>
            <p class="text-pink-200 text-xs mt-1">{{ activePeriodLabel }}</p>
          </div>
        </div>

        <!-- Pendapatan Bersih -->
        <div
          class="relative overflow-hidden rounded-2xl p-5 cursor-pointer"
          style="background:linear-gradient(135deg,#8c0f2e,#a0133f);box-shadow:0 4px 15px rgba(196,25,82,0.3)"
          @click="router.push('/seller/report')"
          role="button"
          tabindex="0"
          aria-label="Lihat laporan pendapatan"
        >
          <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white/10" />
          <div class="absolute -right-2 -bottom-6 w-32 h-32 rounded-full bg-white/5" />
          <div class="relative">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mb-3">
              <TrendingUp class="w-5 h-5 text-white" />
            </div>
            <p class="text-pink-100 text-xs font-medium uppercase tracking-wider mb-1">Pendapatan Bersih</p>
            <div v-if="loadingReport" class="w-28 h-8 bg-white/20 rounded animate-pulse mt-1" />
            <p v-else class="text-white text-2xl font-bold leading-tight">
              {{ report.summary.net_income != null ? formatPrice(report.summary.net_income) : '—' }}
            </p>
            <p class="text-pink-200 text-xs mt-1">{{ activePeriodLabel }}</p>
          </div>
        </div>
      </div>

      <!-- Store info + Quick actions row -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- Store info card -->
        <div class="md:col-span-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:#fce4ec">
                <Store class="w-6 h-6" style="color:#c41952" />
              </div>
              <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Info Toko</p>
                <h2 class="font-bold text-slate-900 text-lg leading-tight">{{ store.name }}</h2>
              </div>
            </div>
            <button
              @click="router.push('/seller/store')"
              class="text-xs font-medium px-3 py-1.5 rounded-lg transition-colors duration-200 cursor-pointer shrink-0 border"
              style="color:#c41952;border-color:#f5a7bf"
              @mouseenter="$event.target.style.background='#fce4ec'"
              @mouseleave="$event.target.style.background=''"
            >
              Edit Toko
            </button>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            {{ store.description || 'Belum ada deskripsi toko. Tambahkan deskripsi untuk menarik pembeli.' }}
          </p>

          <div class="mt-4 flex items-center gap-2">
            <span
              class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full border"
              style="color:#c41952;background:#fce4ec;border-color:#f5a7bf"
            >
              <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background:#c41952" />
              Toko Aktif
            </span>
          </div>
        </div>

        <!-- Quick actions card -->
        <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col gap-3">
          <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Aksi Cepat</p>

          <button
            @click="router.push('/seller/products')"
            class="w-full flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 transition-all duration-200 cursor-pointer text-left"
            @mouseenter="e => { e.currentTarget.style.background='#fce4ec'; e.currentTarget.style.borderColor='#f5a7bf' }"
            @mouseleave="e => { e.currentTarget.style.background=''; e.currentTarget.style.borderColor='' }"
          >
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:#fce4ec">
              <Package class="w-4 h-4" style="color:#c41952" />
            </div>
            <div>
              <p class="text-sm font-medium text-slate-800">Kelola Produk</p>
              <p class="text-xs text-slate-400">Tambah atau edit produk</p>
            </div>
            <ChevronRight class="w-4 h-4 text-slate-400 ml-auto" />
          </button>

          <button
            @click="router.push('/seller/orders')"
            class="w-full flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 transition-all duration-200 cursor-pointer text-left"
            @mouseenter="e => { e.currentTarget.style.background='#fce4ec'; e.currentTarget.style.borderColor='#f5a7bf' }"
            @mouseleave="e => { e.currentTarget.style.background=''; e.currentTarget.style.borderColor='' }"
          >
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:#fce4ec">
              <ClipboardList class="w-4 h-4" style="color:#c41952" />
            </div>
            <div>
              <p class="text-sm font-medium text-slate-800">Lihat Pesanan</p>
              <p class="text-xs text-slate-400">Proses pesanan masuk</p>
            </div>
            <ChevronRight class="w-4 h-4 text-slate-400 ml-auto" />
          </button>

          <button
            @click="router.push('/seller/report')"
            class="w-full flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 transition-all duration-200 cursor-pointer text-left"
            @mouseenter="e => { e.currentTarget.style.background='#fce4ec'; e.currentTarget.style.borderColor='#f5a7bf' }"
            @mouseleave="e => { e.currentTarget.style.background=''; e.currentTarget.style.borderColor='' }"
          >
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:#fce4ec">
              <TrendingUp class="w-4 h-4" style="color:#c41952" />
            </div>
            <div>
              <p class="text-sm font-medium text-slate-800">Laporan</p>
              <p class="text-xs text-slate-400">Lihat performa penjualan</p>
            </div>
            <ChevronRight class="w-4 h-4 text-slate-400 ml-auto" />
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Store, Package, ClipboardList, TrendingUp, ChevronRight, CalendarRange } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { useAuthStore } from '@/stores/auth'
import { sellerApi } from '@/services/seller'

const auth = useAuthStore()
const router = useRouter()
const store = ref(null)
const loadingStore = ref(true)
const loadingReport = ref(false)
const report = ref({ summary: {} })
const period = ref('all')
const showCustom = ref(false)
const customFrom = ref('')
const customTo = ref('')

const periodOptions = [
  { value: 'all',   label: 'Semua' },
  { value: '7d',    label: '7 Hari' },
  { value: '30d',   label: '30 Hari' },
  { value: 'month', label: 'Bulan Ini' },
  { value: 'year',  label: 'Tahun Ini' },
]

const activePeriodLabel = computed(() => {
  if (period.value === 'custom' && customFrom.value && customTo.value) {
    return `${customFrom.value} – ${customTo.value}`
  }
  return periodOptions.find(o => o.value === period.value)?.label ?? 'Semua'
})

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}

async function fetchReport() {
  loadingReport.value = true
  try {
    const from = period.value === 'custom' ? customFrom.value : null
    const to   = period.value === 'custom' ? customTo.value   : null
    const { data } = await sellerApi.getReport(period.value, from, to)
    report.value = data.data
  } catch {
  } finally {
    loadingReport.value = false
  }
}

async function setPeriod(val) {
  period.value = val
  showCustom.value = false
  await fetchReport()
}

function toggleCustom() {
  showCustom.value = !showCustom.value
  if (showCustom.value) period.value = 'custom'
  else setPeriod('all')
}

async function applyCustom() {
  if (!customFrom.value || !customTo.value) return
  period.value = 'custom'
  await fetchReport()
}

function clearCustom() {
  customFrom.value = ''
  customTo.value = ''
  setPeriod('all')
  showCustom.value = false
}

onMounted(async () => {
  try {
    const { data } = await sellerApi.getStore()
    store.value = data.data
  } catch {
  } finally {
    loadingStore.value = false
  }
  await fetchReport()
})
</script>
