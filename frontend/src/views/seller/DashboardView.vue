<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Dashboard Seller</h1>
      <p class="text-muted-foreground text-sm mt-1">Selamat datang, {{ auth.user?.name }}!</p>
    </div>

    <!-- No store yet -->
    <Card v-if="!store && !loadingStore" class="mb-6">
      <CardContent class="pt-6 text-center py-12">
        <Store class="w-12 h-12 mx-auto mb-3 text-muted-foreground/30" />
        <p class="font-medium mb-1">Kamu belum punya toko</p>
        <p class="text-sm text-muted-foreground mb-4">Buat toko untuk mulai berjualan di SEAPEDIA</p>
        <Button @click="router.push('/seller/store')">Buat Toko</Button>
      </CardContent>
    </Card>

    <template v-else-if="store">
      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <Card>
          <CardContent class="pt-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <Package class="w-5 h-5 text-green-600" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Total Produk</p>
                <p class="font-bold text-xl">{{ store.products?.length ?? 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <ClipboardList class="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Pesanan Masuk</p>
                <p class="font-bold text-xl">{{ report.summary.total_orders ?? '—' }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="pt-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                <TrendingUp class="w-5 h-5 text-orange-600" />
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Pendapatan</p>
                <p class="font-bold text-xl">{{ report.summary.net_income != null ? formatPrice(report.summary.net_income) : '—' }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Store info -->
      <Card class="mb-6">
        <CardContent class="pt-6">
          <div class="flex items-center justify-between mb-2">
            <h2 class="font-semibold">Info Toko</h2>
            <Button variant="outline" size="sm" @click="router.push('/seller/store')">Edit Toko</Button>
          </div>
          <p class="font-bold text-lg">{{ store.name }}</p>
          <p class="text-sm text-muted-foreground">{{ store.description || 'Belum ada deskripsi.' }}</p>
        </CardContent>
      </Card>

      <!-- Quick actions -->
      <div class="flex gap-3">
        <Button @click="router.push('/seller/products')">Kelola Produk</Button>
        <Button variant="outline" @click="router.push('/seller/orders')">Lihat Pesanan</Button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Store, Package, ClipboardList, TrendingUp } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { useAuthStore } from '@/stores/auth'
import { sellerApi } from '@/services/seller'

const auth = useAuthStore()
const router = useRouter()
const store = ref(null)
const loadingStore = ref(true)
const report = ref({ summary: {} })

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}

onMounted(async () => {
  try {
    const { data } = await sellerApi.getStore()
    store.value = data.data
  } catch {}
  finally { loadingStore.value = false }

  try {
    const { data } = await sellerApi.getReport()
    report.value = data.data
  } catch {}
})
</script>
