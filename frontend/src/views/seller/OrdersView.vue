<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Pesanan Masuk</h1>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="h-32 bg-muted rounded-xl animate-pulse" />
    </div>

    <div v-else-if="!orders.length" class="text-center py-16 text-muted-foreground">
      <Package class="w-14 h-14 mx-auto mb-3 opacity-30" />
      <p class="font-medium">Belum ada pesanan masuk.</p>
    </div>

    <div v-else class="space-y-4">
      <Card v-for="order in orders" :key="order.id">
        <CardContent class="pt-5">
          <div class="flex items-start justify-between gap-3 mb-3">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="font-semibold text-sm">#{{ order.id }}</span>
                <span :class="statusClass(order.status)" class="text-xs px-2 py-0.5 rounded-full font-medium">
                  {{ statusLabel(order.status) }}
                </span>
              </div>
              <p class="text-xs text-muted-foreground">
                Pembeli: <strong class="text-foreground">{{ order.user?.name }}</strong> ·
                {{ formatDate(order.created_at) }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-bold">{{ formatPrice(order.total) }}</p>
              <p class="text-xs text-muted-foreground">{{ order.items?.length }} produk</p>
            </div>
          </div>

          <!-- Items preview -->
          <div class="mb-3 space-y-1">
            <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
              <span class="text-muted-foreground">{{ item.product_name }} x{{ item.quantity }}</span>
              <span>{{ formatPrice(item.subtotal) }}</span>
            </div>
          </div>

          <!-- Price detail -->
          <div class="bg-muted/40 rounded-lg p-3 text-xs space-y-1 mb-3">
            <div class="flex justify-between"><span class="text-muted-foreground">Subtotal</span><span>{{ formatPrice(order.subtotal) }}</span></div>
            <div v-if="order.discount_amount > 0" class="flex justify-between text-green-600">
              <span>Diskon ({{ [order.voucher?.code, order.promo?.code].filter(Boolean).join(' + ') || 'diskon' }})</span>
              <span>-{{ formatPrice(order.discount_amount) }}</span>
            </div>
            <div class="flex justify-between"><span class="text-muted-foreground">Ongkir ({{ order.delivery_method }})</span><span>{{ formatPrice(order.delivery_fee) }}</span></div>
            <div class="flex justify-between"><span class="text-muted-foreground">PPN 12%</span><span>{{ formatPrice(order.ppn_amount) }}</span></div>
            <div class="flex justify-between font-semibold border-t pt-1 mt-1"><span>Total</span><span>{{ formatPrice(order.total) }}</span></div>
          </div>

          <!-- Status timeline -->
          <div class="mb-3">
            <button class="text-xs text-primary hover:underline mb-2" @click="toggleTimeline(order.id)">
              {{ showTimeline[order.id] ? 'Sembunyikan' : 'Lihat' }} riwayat status
            </button>
            <div v-if="showTimeline[order.id]" class="space-y-2 pl-2 border-l-2 border-muted">
              <div v-for="h in order.status_histories" :key="h.id" class="relative pl-4">
                <span class="absolute -left-[9px] top-1.5 w-3 h-3 rounded-full border-2 border-primary bg-background" />
                <p class="text-xs font-medium">{{ statusLabel(h.status) }}</p>
                <p class="text-xs text-muted-foreground">{{ h.note }}</p>
                <p class="text-xs text-muted-foreground">{{ formatDateTime(h.created_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Process button -->
          <div v-if="order.status === 'sedang_dikemas'" class="flex justify-end">
            <Button size="sm" :disabled="processing[order.id]" @click="processOrder(order)">
              {{ processing[order.id] ? 'Memproses...' : 'Proses Pesanan' }}
            </Button>
          </div>
          <div v-else class="text-xs text-muted-foreground text-right">
            Status: {{ statusLabel(order.status) }}
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Package } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { sellerApi } from '@/services/seller'
import { toast } from 'vue-sonner'

const orders = ref([])
const loading = ref(true)
const processing = reactive({})
const showTimeline = reactive({})

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
  sedang_dikemas: { label: 'Sedang Dikemas', class: 'bg-yellow-100 text-yellow-700' },
  menunggu_pengirim: { label: 'Menunggu Pengirim', class: 'bg-blue-100 text-blue-700' },
  sedang_dikirim: { label: 'Sedang Dikirim', class: 'bg-indigo-100 text-indigo-700' },
  pesanan_selesai: { label: 'Pesanan Selesai', class: 'bg-green-100 text-green-700' },
  dikembalikan: { label: 'Dikembalikan', class: 'bg-red-100 text-red-700' },
}

function statusLabel(s) { return STATUS_MAP[s]?.label ?? s }
function statusClass(s) { return STATUS_MAP[s]?.class ?? '' }

function toggleTimeline(id) {
  showTimeline[id] = !showTimeline[id]
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
