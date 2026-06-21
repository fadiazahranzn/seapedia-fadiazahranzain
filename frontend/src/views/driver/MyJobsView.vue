<template>
  <div class="min-h-screen bg-background">
    <!-- Header -->
    <div class="sticky top-0 z-20 bg-card/90 backdrop-blur border-b border-border">
      <div class="flex items-center justify-between px-4 py-3">
        <h1 class="text-lg font-bold text-foreground tracking-tight">Job Saya</h1>
        <button
          v-if="!loading && completedJobs.length"
          @click="showFilter = !showFilter"
          class="flex items-center gap-1.5 text-xs font-medium px-3 py-2 rounded-full border transition-colors cursor-pointer"
          :class="hasActiveFilters
            ? 'bg-primary text-primary-foreground border-primary'
            : 'bg-secondary text-foreground border-border hover:bg-accent'"
        >
          <SlidersHorizontal class="w-3.5 h-3.5" />
          Filter
          <span v-if="hasActiveFilters" class="w-1.5 h-1.5 rounded-full bg-primary-foreground" />
        </button>
      </div>

      <!-- Filter Panel -->
      <div v-if="showFilter && !loading && completedJobs.length" class="border-t border-border px-4 py-3 flex flex-wrap items-center gap-2">
        <input
          v-model="filters.dateFrom"
          type="date"
          :max="filters.dateTo || undefined"
          class="text-xs border border-slate-200 rounded-lg px-3 py-2 outline-none transition-colors cursor-pointer text-slate-600"
          @focus="$event.target.style.borderColor='#c41952'"
          @blur="$event.target.style.borderColor=''"
        />
        <span class="text-slate-300 text-sm">—</span>
        <input
          v-model="filters.dateTo"
          type="date"
          :min="filters.dateFrom || undefined"
          class="text-xs border border-slate-200 rounded-lg px-3 py-2 outline-none transition-colors cursor-pointer text-slate-600"
          @focus="$event.target.style.borderColor='#c41952'"
          @blur="$event.target.style.borderColor=''"
        />
        <button
          v-if="filters.dateFrom || filters.dateTo"
          @click="filters.dateFrom = ''; filters.dateTo = ''"
          class="text-xs font-medium px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-500 hover:border-red-200 hover:text-red-500 transition-all cursor-pointer"
        >✕</button>

        <div class="w-px h-5 bg-slate-200 mx-1 hidden sm:block" />

        <div class="flex flex-wrap gap-1.5">
          <button
            v-for="opt in sortOptions" :key="opt.value"
            @click="filters.sort = opt.value"
            class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-all cursor-pointer"
            :style="filters.sort === opt.value
              ? 'border-color:#c41952;color:#c41952;background:#fdf2f5'
              : 'border-color:#e2e8f0;color:#6b7280;background:#fff'"
          >
            {{ opt.label }}
          </button>
        </div>
      </div>
    </div>

    <div class="px-4 py-4 space-y-4">
      <!-- Skeleton -->
      <div v-if="loading" class="space-y-3">
        <div class="grid grid-cols-3 gap-3">
          <div v-for="i in 3" :key="i" class="h-20 bg-muted rounded-2xl animate-pulse" />
        </div>
        <div v-for="i in 2" :key="i" class="h-32 bg-muted rounded-2xl animate-pulse" />
      </div>

      <template v-else>
        <!-- Summary Cards — selalu pakai data asli, tidak difilter -->
        <div class="grid grid-cols-3 gap-3">
          <div class="bg-card border border-border rounded-2xl px-3 py-3 text-center">
            <p class="text-[10px] text-muted-foreground mb-1">Total Job</p>
            <p class="text-2xl font-extrabold text-foreground">{{ report.summary.total_jobs }}</p>
          </div>
          <div class="bg-card border border-border rounded-2xl px-3 py-3 text-center">
            <p class="text-[10px] text-muted-foreground mb-1">Selesai</p>
            <p class="text-2xl font-extrabold text-primary">{{ report.summary.completed_jobs }}</p>
          </div>
          <div class="bg-card border border-border rounded-2xl px-3 py-3 text-center">
            <p class="text-[10px] text-muted-foreground mb-1">Earning</p>
            <p class="text-sm font-extrabold text-primary leading-tight">{{ formatPrice(report.summary.total_earning) }}</p>
            <p class="text-[10px] text-muted-foreground mt-0.5">{{ report.summary.earning_rate }}</p>
          </div>
        </div>

        <!-- Earning di range filter -->
        <div
          v-if="hasActiveFilters && filteredCompletedJobs.length"
          class="bg-secondary border border-border rounded-2xl px-4 py-3 flex items-center justify-between"
        >
          <p class="text-xs text-muted-foreground">Earning dalam rentang ini</p>
          <p class="text-sm font-extrabold text-primary">{{ formatPrice(filteredEarning) }}</p>
        </div>

        <!-- Active Job -->
        <div v-if="report.active_job">
          <h2 class="text-sm font-semibold text-foreground mb-2">Job Aktif</h2>
          <div class="bg-primary rounded-2xl overflow-hidden shadow-md">
            <div class="px-4 pt-4 pb-3">
              <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-bold text-primary-foreground">Pesanan #{{ report.active_job.order_id }}</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-primary-foreground/20 text-primary-foreground">
                      <Truck class="w-2.5 h-2.5" />
                      Sedang Dikirim
                    </span>
                  </div>
                  <p class="text-xs text-primary-foreground/70">{{ report.active_job.order?.store?.name }}</p>
                  <p class="text-xs text-primary-foreground/70">Diambil: {{ formatDateTime(report.active_job.picked_up_at) }}</p>
                </div>
                <div class="text-right shrink-0">
                  <p class="text-[10px] text-primary-foreground/70 mb-0.5">Estimasi earning</p>
                  <p class="font-extrabold text-primary-foreground text-base">{{ formatPrice(report.active_job.order?.delivery_fee * 0.8) }}</p>
                </div>
              </div>

              <!-- Route -->
              <div class="flex items-start gap-2 text-xs text-primary-foreground/70 mb-3">
                <div class="flex flex-col items-center mt-0.5 shrink-0">
                  <div class="w-2 h-2 rounded-full bg-primary-foreground/60" />
                  <div class="w-px h-3 bg-primary-foreground/20 my-0.5" />
                  <div class="w-2 h-2 rounded-full border-2 border-primary-foreground/60" />
                </div>
                <div class="flex-1 min-w-0 space-y-1">
                  <p class="truncate font-medium text-primary-foreground/90">{{ report.active_job.order?.store?.name ?? 'Toko' }}</p>
                  <p class="truncate">{{ report.active_job.order?.address_snapshot?.full_address }}, {{ report.active_job.order?.address_snapshot?.city }}</p>
                </div>
              </div>

              <!-- Items -->
              <div class="bg-primary-foreground/10 rounded-xl p-3 mb-3 space-y-1">
                <div v-for="item in report.active_job.order?.items" :key="item.id" class="text-xs flex justify-between">
                  <span class="text-primary-foreground/70">{{ item.product_name }} x{{ item.quantity }}</span>
                  <span class="text-primary-foreground font-medium">{{ formatPrice(item.subtotal) }}</span>
                </div>
              </div>
            </div>

            <div class="px-4 pb-4">
              <button
                :disabled="completing"
                @click="completeJob(report.active_job)"
                class="w-full py-3 rounded-xl text-sm font-bold transition-all duration-150 cursor-pointer bg-primary-foreground text-primary hover:opacity-90 active:scale-[0.98] shadow-sm"
                :class="{ 'opacity-60 cursor-not-allowed': completing }"
              >
                <span v-if="completing" class="flex items-center justify-center gap-2">
                  <Loader2 class="w-4 h-4 animate-spin" />
                  Memproses...
                </span>
                <span v-else class="flex items-center justify-center gap-1.5">
                  Konfirmasi Pesanan Selesai
                  <CheckCircle class="w-4 h-4" />
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Job History -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold text-foreground">Riwayat Job</h2>
            <span v-if="hasActiveFilters" class="text-xs text-muted-foreground">
              {{ filteredCompletedJobs.length }} dari {{ completedJobs.length }}
            </span>
          </div>

          <div v-if="!completedJobs.length" class="flex flex-col items-center py-12 text-center">
            <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-3">
              <PackageSearch class="w-7 h-7 text-muted-foreground/40" />
            </div>
            <p class="text-sm font-medium text-foreground">Belum ada job selesai</p>
            <p class="text-xs text-muted-foreground mt-1">Job yang sudah kamu selesaikan akan muncul di sini.</p>
          </div>

          <div v-else-if="!filteredCompletedJobs.length" class="flex flex-col items-center py-10 text-center">
            <div class="w-14 h-14 rounded-full bg-muted flex items-center justify-center mb-3">
              <FilterX class="w-6 h-6 text-muted-foreground/40" />
            </div>
            <p class="text-sm font-medium text-foreground">Tidak ada job di rentang ini</p>
            <button @click="resetFilters" class="mt-3 text-xs font-semibold text-primary bg-secondary hover:bg-accent px-4 py-2 rounded-full transition-colors cursor-pointer">
              Reset Filter
            </button>
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="job in filteredCompletedJobs"
              :key="job.id"
              class="bg-card border border-border rounded-2xl px-4 py-3 flex items-start justify-between gap-3"
            >
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                  <span class="font-semibold text-sm text-foreground">Pesanan #{{ job.order_id }}</span>
                  <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-secondary text-secondary-foreground border border-border">
                    <CheckCircle class="w-2.5 h-2.5" />
                    Selesai
                  </span>
                </div>
                <p class="text-xs text-muted-foreground truncate">{{ job.order?.store?.name }}</p>
                <p class="text-xs text-muted-foreground">Selesai: {{ formatDateTime(job.delivered_at) }}</p>
              </div>
              <div class="text-right shrink-0">
                <p class="font-bold text-primary">+{{ formatPrice(job.earning) }}</p>
                <p class="text-[10px] text-muted-foreground mt-0.5">{{ job.order?.items?.length }} item</p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { Truck, Loader2, CheckCircle, PackageSearch, CalendarDays, SlidersHorizontal, FilterX } from '@lucide/vue'
import { driverApi } from '@/services/driver'
import { toast } from 'vue-sonner'

const report = ref({ summary: {}, active_job: null, jobs: [] })
const loading = ref(true)
const completing = ref(false)
const showFilter = ref(false)

const filters = reactive({
  dateFrom: '',
  dateTo: '',
  sort: 'newest',
})

const sortOptions = [
  { value: 'newest', label: 'Terbaru' },
  { value: 'oldest', label: 'Terlama' },
  { value: 'earning_high', label: 'Earning ↑' },
  { value: 'earning_low', label: 'Earning ↓' },
]

const completedJobs = computed(() => report.value.jobs?.filter(j => j.status === 'completed') ?? [])

const hasActiveFilters = computed(() =>
  filters.dateFrom !== '' || filters.dateTo !== '' || filters.sort !== 'newest'
)

const filteredCompletedJobs = computed(() => {
  let result = [...completedJobs.value]

  if (filters.dateFrom) {
    const from = new Date(filters.dateFrom)
    result = result.filter(j => j.delivered_at && new Date(j.delivered_at) >= from)
  }

  if (filters.dateTo) {
    const to = new Date(filters.dateTo)
    to.setHours(23, 59, 59, 999)
    result = result.filter(j => j.delivered_at && new Date(j.delivered_at) <= to)
  }

  if (filters.sort === 'newest')
    result.sort((a, b) => new Date(b.delivered_at) - new Date(a.delivered_at))
  else if (filters.sort === 'oldest')
    result.sort((a, b) => new Date(a.delivered_at) - new Date(b.delivered_at))
  else if (filters.sort === 'earning_high')
    result.sort((a, b) => (b.earning ?? 0) - (a.earning ?? 0))
  else if (filters.sort === 'earning_low')
    result.sort((a, b) => (a.earning ?? 0) - (b.earning ?? 0))

  return result
})

const filteredEarning = computed(() =>
  filteredCompletedJobs.value.reduce((sum, j) => sum + (j.earning ?? 0), 0)
)

function resetFilters() {
  filters.dateFrom = ''
  filters.dateTo = ''
  filters.sort = 'newest'
}

function formatDateShort(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}

function formatDateTime(d) {
  if (!d) return ''
  return new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function loadReport() {
  loading.value = true
  try {
    const { data } = await driverApi.getMyJobs()
    report.value = data.data
  } finally { loading.value = false }
}

async function completeJob(job) {
  completing.value = true
  try {
    await driverApi.completeJob(job.id)
    toast.success('Pengiriman selesai!')
    await loadReport()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal konfirmasi.')
  } finally { completing.value = false }
}

onMounted(loadReport)
</script>
