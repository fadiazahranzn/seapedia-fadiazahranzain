<template>
  <div class="min-h-screen bg-background">
    <!-- Header -->
    <div class="sticky top-0 z-20 bg-card/90 backdrop-blur border-b border-border px-4 py-3">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-lg font-bold text-foreground tracking-tight">Cari Job</h1>
          <p class="text-xs text-muted-foreground mt-0.5">{{ jobs.length }} job tersedia sekarang</p>
        </div>
        <button
          @click="loadJobs"
          class="flex items-center gap-1.5 text-xs font-medium text-primary bg-secondary hover:bg-accent px-3 py-2 rounded-full transition-colors duration-150 cursor-pointer"
          :class="{ 'opacity-60 pointer-events-none': loading }"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
          Refresh
        </button>
      </div>
    </div>

    <div class="px-4 py-4 space-y-3">
      <!-- Active Job Banner -->
      <div
        v-if="activeJob"
        class="relative overflow-hidden rounded-2xl bg-primary text-primary-foreground p-4 shadow-lg"
      >
        <div class="absolute right-0 top-0 opacity-10">
          <Truck class="w-32 h-32 -mt-4 -mr-4" />
        </div>
        <div class="relative">
          <div class="flex items-center gap-1.5 mb-2">
            <span class="w-2 h-2 rounded-full bg-white/70 animate-pulse" />
            <span class="text-xs font-semibold text-primary-foreground/70 uppercase tracking-wide">Job Aktif</span>
          </div>
          <p class="font-bold text-base">Pesanan #{{ activeJob.order_id }}</p>
          <p class="text-xs text-primary-foreground/70 mt-0.5">
            {{ activeJob.order?.store?.name }} · Diambil {{ formatDateTime(activeJob.picked_up_at) }}
          </p>
          <button
            @click="$router.push('/driver/my-jobs')"
            class="mt-3 inline-flex items-center gap-1.5 bg-primary-foreground text-primary text-xs font-semibold px-3 py-1.5 rounded-full hover:opacity-90 transition-opacity cursor-pointer"
          >
            Lihat Detail
            <ArrowRight class="w-3 h-3" />
          </button>
        </div>
      </div>

      <!-- Skeleton -->
      <template v-if="loading">
        <div v-for="i in 3" :key="i" class="bg-card rounded-2xl p-4 space-y-3 animate-pulse border border-border">
          <div class="flex justify-between">
            <div class="space-y-2">
              <div class="h-3 w-28 bg-muted rounded-full" />
              <div class="h-4 w-20 bg-muted rounded-full" />
            </div>
            <div class="h-10 w-20 bg-muted rounded-xl" />
          </div>
          <div class="h-px bg-muted" />
          <div class="grid grid-cols-3 gap-2">
            <div v-for="j in 3" :key="j" class="h-10 bg-muted rounded-xl" />
          </div>
          <div class="h-8 w-full bg-muted rounded-xl" />
        </div>
      </template>

      <!-- Empty State -->
      <div
        v-else-if="!jobs.length"
        class="flex flex-col items-center justify-center py-20 text-center"
      >
        <div class="w-20 h-20 rounded-full bg-muted flex items-center justify-center mb-4">
          <PackageSearch class="w-9 h-9 text-muted-foreground/40" />
        </div>
        <p class="font-semibold text-foreground">Belum ada job tersedia</p>
        <p class="text-sm text-muted-foreground mt-1 max-w-[220px]">Coba refresh beberapa saat lagi untuk melihat job baru.</p>
        <button
          @click="loadJobs"
          class="mt-5 text-sm font-semibold text-primary bg-secondary hover:bg-accent px-5 py-2.5 rounded-full transition-colors cursor-pointer"
        >
          Refresh Sekarang
        </button>
      </div>

      <!-- Job Cards -->
      <div v-else class="space-y-3">
        <div
          v-for="job in jobs"
          :key="job.id"
          class="bg-card rounded-2xl overflow-hidden shadow-sm border border-border hover:shadow-md transition-shadow duration-200"
        >
          <!-- Card Header -->
          <div class="px-4 pt-4 pb-3 flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-bold text-foreground">
                  #{{ job.order_id }}
                </span>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-secondary text-secondary-foreground border border-border">
                  <Clock class="w-2.5 h-2.5" />
                  Menunggu Driver
                </span>
              </div>
              <p class="text-xs text-muted-foreground mt-1 truncate">
                dari <span class="font-medium text-foreground">{{ job.order?.store?.name }}</span>
              </p>
            </div>

            <!-- Earnings -->
            <div class="text-right shrink-0">
              <p class="text-lg font-extrabold text-primary leading-tight">
                {{ formatPrice(job.order?.delivery_fee * 0.8) }}
              </p>
              <p class="text-[10px] text-muted-foreground mt-0.5">penghasilanmu</p>
            </div>
          </div>

          <!-- Divider -->
          <div class="h-px bg-border mx-4" />

          <!-- Stats Row -->
          <div class="grid grid-cols-3 gap-px bg-border mx-4 rounded-xl overflow-hidden my-3">
            <div class="bg-card px-2.5 py-2 text-center">
              <p class="text-[10px] text-muted-foreground mb-0.5">Ongkir</p>
              <p class="text-xs font-semibold text-foreground">{{ formatPrice(job.order?.delivery_fee) }}</p>
            </div>
            <div class="bg-card px-2.5 py-2 text-center">
              <p class="text-[10px] text-muted-foreground mb-0.5">Item</p>
              <p class="text-xs font-semibold text-foreground">{{ job.order?.items?.length ?? 0 }}</p>
            </div>
            <div class="bg-card px-2.5 py-2 text-center">
              <p class="text-[10px] text-muted-foreground mb-0.5">Metode</p>
              <p class="text-xs font-semibold text-foreground capitalize">{{ (job.order?.delivery_method ?? '').replace('_', ' ') || '—' }}</p>
            </div>
          </div>

          <!-- Delivery Route -->
          <div class="mx-4 mb-3 flex items-start gap-2 text-xs text-muted-foreground">
            <div class="flex flex-col items-center mt-0.5 shrink-0">
              <div class="w-2 h-2 rounded-full bg-primary" />
              <div class="w-px h-3 bg-border my-0.5" />
              <div class="w-2 h-2 rounded-full border-2 border-primary" />
            </div>
            <div class="flex-1 min-w-0 space-y-1">
              <p class="truncate">
                <span class="font-medium text-foreground">{{ job.order?.store?.name ?? 'Toko' }}</span>
              </p>
              <p class="truncate">
                {{ job.order?.address_snapshot?.full_address }}<template v-if="job.order?.address_snapshot?.city">, {{ job.order?.address_snapshot?.city }}</template>
              </p>
            </div>
          </div>

          <!-- CTA -->
          <div class="px-4 pb-4">
            <button
              @click="takeJob(job)"
              :disabled="!!activeJob || taking[job.id]"
              class="w-full py-3 rounded-xl text-sm font-bold transition-all duration-150 cursor-pointer"
              :class="[
                activeJob
                  ? 'bg-muted text-muted-foreground cursor-not-allowed'
                  : taking[job.id]
                    ? 'bg-primary/60 text-primary-foreground cursor-not-allowed'
                    : 'bg-primary hover:opacity-90 active:scale-[0.98] text-primary-foreground shadow-sm'
              ]"
            >
              <span v-if="taking[job.id]" class="flex items-center justify-center gap-2">
                <Loader2 class="w-4 h-4 animate-spin" />
                Mengambil job...
              </span>
              <span v-else-if="activeJob">Selesaikan job aktif dulu</span>
              <span v-else class="flex items-center justify-center gap-1.5">
                Ambil Job Ini
                <ArrowRight class="w-3.5 h-3.5" />
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Truck, RefreshCw, Clock, ArrowRight, Loader2, PackageSearch } from '@lucide/vue'
import { driverApi } from '@/services/driver'
import { toast } from 'vue-sonner'

const router = useRouter()
const jobs = ref([])
const activeJob = ref(null)
const loading = ref(true)
const taking = reactive({})

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}
function formatDateTime(d) {
  if (!d) return ''
  return new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

async function loadJobs() {
  loading.value = true
  try {
    const [jobsRes, activeRes] = await Promise.all([
      driverApi.getAvailableJobs(),
      driverApi.getActiveJob(),
    ])
    jobs.value = jobsRes.data.data
    activeJob.value = activeRes.data.data
  } finally {
    loading.value = false
  }
}

async function takeJob(job) {
  if (activeJob.value) return
  taking[job.id] = true
  try {
    await driverApi.takeJob(job.id)
    toast.success('Job berhasil diambil!')
    router.push('/driver/my-jobs')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal mengambil job.')
  } finally {
    taking[job.id] = false
  }
}

onMounted(loadJobs)
</script>
