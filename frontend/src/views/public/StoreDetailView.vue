<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <Button variant="ghost" size="sm" class="-ml-2 mb-4" @click="router.back()">
      <ArrowLeft class="w-4 h-4 mr-1" /> Kembali
    </Button>

    <div v-if="loading" class="space-y-4">
      <div class="h-24 bg-muted rounded-lg animate-pulse" />
    </div>

    <template v-else-if="store">
      <div class="flex items-center gap-4 mb-8">
        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
          <Store class="w-8 h-8 text-primary" />
        </div>
        <div>
          <h1 class="text-2xl font-bold">{{ store.name }}</h1>
          <p class="text-muted-foreground text-sm">{{ store.description || 'Toko resmi SEAPEDIA' }}</p>
          <p class="text-xs text-muted-foreground mt-1">Bergabung sejak {{ formatDate(store.created_at) }}</p>
        </div>
      </div>

      <h2 class="font-semibold text-lg mb-4">Produk Toko ({{ store.products?.length ?? 0 }})</h2>

      <div v-if="!store.products?.length" class="text-center py-12 text-muted-foreground">
        <Package class="w-12 h-12 mx-auto mb-3 opacity-30" />
        <p>Belum ada produk.</p>
      </div>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <Card
          v-for="product in store.products"
          :key="product.id"
          class="cursor-pointer hover:shadow-md transition-shadow"
          @click="router.push(`/products/${product.id}`)"
        >
          <div class="aspect-square bg-muted rounded-t-lg flex items-center justify-center overflow-hidden">
            <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
            <Package v-else class="w-10 h-10 text-muted-foreground/30" />
          </div>
          <CardContent class="p-3">
            <h3 class="font-medium text-sm line-clamp-2 mb-1">{{ product.name }}</h3>
            <p class="font-bold text-primary text-sm">{{ formatPrice(product.price) }}</p>
          </CardContent>
        </Card>
      </div>
    </template>

    <div v-else class="text-center py-16 text-muted-foreground">Toko tidak ditemukan.</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft, Store, Package } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const store = ref(null)
const loading = ref(true)

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p)
}
function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/stores/${route.params.id}`)
    store.value = data.data
  } catch {}
  finally { loading.value = false }
})
</script>
