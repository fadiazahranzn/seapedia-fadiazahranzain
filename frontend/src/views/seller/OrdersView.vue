<template>
  <div class="min-h-full">
    <!-- Header -->
    <div class="mb-6">
      <p class="text-sm font-medium uppercase tracking-widest mb-1" style="color:#c41952">Seller</p>
      <h1 class="text-3xl font-bold text-slate-900 leading-tight">Pesanan Masuk</h1>
      <p class="text-slate-500 mt-1 text-sm">Kelola dan proses pesanan dari pembeli.</p>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div v-for="i in 4" :key="i" class="h-24 rounded-2xl bg-slate-100 animate-pulse" />
      </div>
      <div v-for="i in 3" :key="i" class="h-36 rounded-2xl bg-slate-100 animate-pulse" />
    </div>

    <template v-else>
      <!-- Summary stats -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div
          class="relative overflow-hidden rounded-2xl p-5"
          style="background:linear-gradient(135deg,#c41952,#a0133f);box-shadow:0 4px 15px rgba(196,25,82,0.3)"
        >
          <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10" />
          <div class="relative">
            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center mb-3">
              <ClipboardList class="w-4 h-4 text-white" />
            </div>
            <p class="text-pink-100 text-xs font-medium uppercase tracking-wider mb-1">Total</p>
            <p class="text-white text-2xl font-bold">{{ dateFilteredOrders.length }}</p>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3" style="background:#fefce8">
            <Package class="w-4 h-4" style="color:#d97706" />
          </div>
          <p class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-1">Dikemas</p>
          <p class="text-slate-900 text-2xl font-bold">{{ countByStatus('sedang_dikemas') }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3" style="background:#eff6ff">
            <Truck class="w-4 h-4" style="color:#3b82f6" />
          </div>
          <p class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-1">Dikirim</p>
          <p class="text-slate-900 text-2xl font-bold">{{ countByStatus('sedang_dikirim') + countByStatus('menunggu_pengirim') }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3" style="background:#f0fdf4">
            <CheckCircle class="w-4 h-4" style="color:#16a34a" />
          </div>
          <p class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-1">Selesai</p>
          <p class="text-slate-900 text-2xl font-bold">{{ countByStatus('pesanan_selesai') }}</p>
        </div>
      </div>

      <!-- Filter bar: date range + status chips -->
      <div class="flex flex-wrap items-center gap-2 mb-6 px-4 py-3 rounded-2xl border border-slate-200 bg-white shadow-sm">
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
          @click="clearDateFilter"
          class="text-xs font-medium px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-500 hover:border-red-200 hover:text-red-500 transition-all cursor-pointer"
        >✕</button>

        <div class="w-px h-5 bg-slate-200 mx-1 hidden sm:block" />

        <div class="flex flex-wrap gap-1.5">
          <button
            v-for="tab in filterTabs"
            :key="tab.value"
            @click="activeFilter = tab.value"
            class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-all cursor-pointer"
            :style="activeFilter === tab.value
              ? 'border-color:#c41952;color:#c41952;background:#fdf2f5'
              : 'border-color:#e2e8f0;color:#6b7280;background:#fff'"
          >
            {{ tab.label }}
            <span v-if="tab.value !== 'all'" class="ml-1 opacity-70">
              {{ tab.value === 'sedang_dikirim' ? countByStatus('sedang_dikirim') + countByStatus('menunggu_pengirim') : countByStatus(tab.value) }}
            </span>
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!filteredOrders.length" class="rounded-2xl border border-dashed border-slate-300 bg-white p-16 text-center shadow-sm">
        <div class="mx-auto mb-4 w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
          <Package class="w-7 h-7 text-slate-400" />
        </div>
        <p class="font-semibold text-slate-700 mb-1">Belum ada pesanan</p>
        <p class="text-sm text-slate-400">Pesanan dari pembeli akan muncul di sini.</p>
      </div>

      <!-- Order list -->
      <div v-else class="space-y-3">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
        >
          <!-- Card header -->
          <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#fce4ec">
                <ClipboardList class="w-4 h-4" style="color:#c41952" />
              </div>
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-bold text-slate-800 text-sm">#{{ order.id }}</span>
                  <span
                    class="text-xs font-semibold px-2.5 py-0.5 rounded-full border"
                    :style="statusStyle(order.status)"
                  >{{ statusLabel(order.status) }}</span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                  Pembeli: <span class="text-slate-600 font-medium">{{ order.user?.name }}</span>
                  · {{ formatDate(order.created_at) }}
                </p>
              </div>
            </div>
            <div class="text-right shrink-0">
              <p class="font-bold text-slate-900 text-sm">{{ formatPrice(order.total) }}</p>
              <p class="text-xs text-slate-400">{{ order.items?.length }} produk</p>
            </div>
          </div>

          <!-- Items -->
          <div class="px-5 py-3 space-y-1.5">
            <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between text-sm">
              <span class="text-slate-500">{{ item.product_name }}
                <span class="font-medium text-slate-700">x{{ item.quantity }}</span>
              </span>
              <span class="text-slate-700 font-medium">{{ formatPrice(item.subtotal) }}</span>
            </div>
          </div>

          <!-- Price detail -->
          <div class="mx-5 mb-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs space-y-1.5">
            <div class="flex justify-between text-slate-500">
              <span>Subtotal</span><span class="text-slate-700">{{ formatPrice(order.subtotal) }}</span>
            </div>
            <div v-if="order.discount_amount > 0" class="flex justify-between" style="color:#16a34a">
              <span>Diskon ({{ [order.voucher?.code, order.promo?.code].filter(Boolean).join(' + ') || 'diskon' }})</span>
              <span>-{{ formatPrice(order.discount_amount) }}</span>
            </div>
            <div class="flex justify-between text-slate-500">
              <span>Ongkir ({{ order.delivery_method }})</span>
              <span class="text-slate-700">{{ formatPrice(order.delivery_fee) }}</span>
            </div>
            <div class="flex justify-between text-slate-500">
              <span>PPN 12%</span>
              <span class="text-slate-700">{{ formatPrice(order.ppn_amount) }}</span>
            </div>
            <div class="flex justify-between font-semibold text-slate-800 border-t border-slate-200 pt-1.5 mt-1">
              <span>Total</span><span>{{ formatPrice(order.total) }}</span>
            </div>
          </div>

          <!-- Status timeline toggle -->
          <div class="px-5 mb-3">
            <button
              class="text-xs font-medium flex items-center gap-1 transition-colors duration-150 cursor-pointer"
              style="color:#c41952"
              @click="toggleTimeline(order.id)"
            >
              <ChevronDown
                class="w-3.5 h-3.5 transition-transform duration-200"
                :style="showTimeline[order.id] ? 'transform:rotate(180deg)' : ''"
              />
              {{ showTimeline[order.id] ? 'Sembunyikan' : 'Lihat' }} riwayat status
            </button>
            <div v-if="showTimeline[order.id]" class="mt-2 space-y-2 pl-3 border-l-2 border-slate-200">
              <div v-for="h in order.status_histories" :key="h.id" class="relative pl-4">
                <span class="absolute -left-[9px] top-1.5 w-3 h-3 rounded-full border-2 border-white shadow-sm" style="background:#c41952" />
                <p class="text-xs font-semibold text-slate-700">{{ statusLabel(h.status) }}</p>
                <p class="text-xs text-slate-400">{{ h.note }}</p>
                <p class="text-xs text-slate-400">{{ formatDateTime(h.created_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Delivery tracking -->
          <div
            v-if="['sedang_dikirim', 'pesanan_selesai', 'dikembalikan'].includes(order.status)"
            class="mx-5 mb-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs"
          >
            <div class="flex items-center gap-1.5 mb-2 font-semibold text-blue-700">
              <Truck class="w-3.5 h-3.5" /> Info Pengiriman
            </div>
            <template v-if="trackingData[order.id]">
              <div class="space-y-1">
                <div class="flex justify-between">
                  <span class="text-slate-500">Driver</span>
                  <span class="font-medium text-slate-700">{{ trackingData[order.id].delivery?.driver?.name ?? 'Belum ada' }}</span>
                </div>
                <div v-if="trackingData[order.id].delivery?.picked_up_at" class="flex justify-between">
                  <span class="text-slate-500">Diambil</span>
                  <span class="text-slate-700">{{ formatDateTime(trackingData[order.id].delivery.picked_up_at) }}</span>
                </div>
                <div v-if="trackingData[order.id].delivery?.delivered_at" class="flex justify-between">
                  <span class="text-slate-500">Diterima</span>
                  <span class="text-slate-700">{{ formatDateTime(trackingData[order.id].delivery.delivered_at) }}</span>
                </div>
              </div>
            </template>
            <button v-else class="font-medium hover:underline cursor-pointer" style="color:#3b82f6" @click="loadTracking(order)">
              Muat info pengiriman
            </button>
          </div>

          <!-- Footer action -->
          <div class="px-5 pb-4 flex justify-end">
            <button
              v-if="order.status === 'sedang_dikemas'"
              :disabled="processing[order.id]"
              @click="processOrder(order)"
              class="flex items-center gap-2 text-sm font-semibold px-5 py-2 rounded-xl text-white transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
              style="background:#c41952;box-shadow:0 2px 8px rgba(196,25,82,0.3)"
            >
              <CheckCircle class="w-4 h-4" />
              {{ processing[order.id] ? 'Memproses...' : 'Proses Pesanan' }}
            </button>
            <span v-else class="text-xs text-slate-400 font-medium py-2">{{ statusLabel(order.status) }}</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Package, Truck, ClipboardList, CheckCircle, ChevronDown } from '@lucide/vue'
import { sellerApi } from '@/services/seller'
import { toast } from 'vue-sonner'

const orders = ref([])
const loading = ref(true)
const processing = reactive({})
const showTimeline = reactive({})
const trackingData = reactive({})
const activeFilter = ref('all')
const dateFrom = ref('')
const dateTo = ref('')

const filterTabs = [
  { value: 'all', label: 'Semua' },
  { value: 'sedang_dikemas', label: 'Dikemas' },
  { value: 'sedang_dikirim', label: 'Dikirim' },
  { value: 'pesanan_selesai', label: 'Selesai' },
  { value: 'dikembalikan', label: 'Dikembalikan' },
]

const dateFilteredOrders = computed(() => {
  if (!dateFrom.value && !dateTo.value) return orders.value
  return orders.value.filter(o => {
    const d = new Date(o.created_at)
    if (dateFrom.value && d < new Date(dateFrom.value)) return false
    if (dateTo.value && d > new Date(dateTo.value + 'T23:59:59')) return false
    return true
  })
})

const filteredOrders = computed(() => {
  const base = dateFilteredOrders.value
  if (activeFilter.value === 'all') return base
  if (activeFilter.value === 'sedang_dikirim') {
    return base.filter(o => ['sedang_dikirim', 'menunggu_pengirim'].includes(o.status))
  }
  return base.filter(o => o.status === activeFilter.value)
})

function countByStatus(status) {
  return dateFilteredOrders.value.filter(o => o.status === status).length
}

function clearDateFilter() {
  dateFrom.value = ''
  dateTo.value = ''
}

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}
function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
function formatDateTime(d) {
  return new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const STATUS_MAP = {
  sedang_dikemas:    { label: 'Sedang Dikemas',    style: 'background:#fefce8;color:#d97706;border-color:#fde68a' },
  menunggu_pengirim: { label: 'Menunggu Pengirim', style: 'background:#eff6ff;color:#3b82f6;border-color:#bfdbfe' },
  sedang_dikirim:    { label: 'Sedang Dikirim',    style: 'background:#eff6ff;color:#3b82f6;border-color:#bfdbfe' },
  pesanan_selesai:   { label: 'Pesanan Selesai',   style: 'background:#f0fdf4;color:#16a34a;border-color:#bbf7d0' },
  dikembalikan:      { label: 'Dikembalikan',      style: 'background:#fef2f2;color:#dc2626;border-color:#fecaca' },
}

function statusLabel(s) { return STATUS_MAP[s]?.label ?? s }
function statusStyle(s) { return STATUS_MAP[s]?.style ?? '' }

function toggleTimeline(id) {
  showTimeline[id] = !showTimeline[id]
}

async function loadTracking(order) {
  try {
    const { data } = await sellerApi.getTracking(order.id)
    trackingData[order.id] = data.data
  } catch {
    toast.error('Gagal memuat info pengiriman.')
  }
}

async function processOrder(order) {
  processing[order.id] = true
  try {
    await sellerApi.processOrder(order.id)
    order.status = 'menunggu_pengirim'
    if (!order.status_histories) order.status_histories = []
    order.status_histories.push({
      status: 'menunggu_pengirim',
      note: 'Pesanan diproses oleh seller.',
      created_at: new Date().toISOString(),
    })
    toast.success('Pesanan berhasil diproses!')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal memproses pesanan.')
  } finally { processing[order.id] = false }
}

onMounted(async () => {
  try {
    const { data } = await sellerApi.getOrders()
    orders.value = data.data ?? data.data?.data ?? []
  } finally { loading.value = false }
})
</script>
