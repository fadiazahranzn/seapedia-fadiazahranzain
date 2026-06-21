<template>
  <div class="min-h-screen bg-background">
    <!-- Header -->
    <div class="sticky top-0 z-20 bg-card/90 backdrop-blur border-b border-border px-4 py-3">
      <h1 class="text-lg font-bold text-foreground tracking-tight">Job Saya</h1>
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
        <!-- Summary Cards -->
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

            <!-- CTA -->
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
          <h2 class="text-sm font-semibold text-foreground mb-2">Riwayat Job</h2>

          <div v-if="!completedJobs.length" class="flex flex-col items-center py-12 text-center">
            <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-3">
              <PackageSearch class="w-7 h-7 text-muted-foreground/40" />
            </div>
            <p class="text-sm font-medium text-foreground">Belum ada job selesai</p>
            <p class="text-xs text-muted-foreground mt-1">Job yang sudah kamu selesaikan akan muncul di sini.</p>
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="job in completedJobs"
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
import { ref, computed, onMounted } from 'vue'
import { Truck, Loader2, CheckCircle, PackageSearch } from '@lucide/vue'
import { driverApi } from '@/services/driver'
import { toast } from 'vue-sonner'

const report = ref({ summary: {}, active_job: null, jobs: [] })
const loading = ref(true)
const completing = ref(false)

const completedJobs = computed(() => report.value.jobs?.filter(j => j.status === 'completed') ?? [])

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
