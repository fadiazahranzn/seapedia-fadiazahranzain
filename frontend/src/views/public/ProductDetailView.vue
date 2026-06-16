<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <Button variant="ghost" size="sm" class="mb-4 -ml-2" @click="router.back()">
      <ArrowLeft class="w-4 h-4 mr-1" /> Kembali
    </Button>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="aspect-square bg-muted rounded-lg animate-pulse" />
      <div class="space-y-3">
        <div class="h-6 bg-muted rounded animate-pulse w-3/4" />
        <div class="h-4 bg-muted rounded animate-pulse w-1/2" />
        <div class="h-8 bg-muted rounded animate-pulse w-1/3" />
      </div>
    </div>

    <div v-else-if="!product" class="text-center py-16 text-muted-foreground">
      Produk tidak ditemukan.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Image -->
      <div class="aspect-square bg-muted rounded-lg flex items-center justify-center overflow-hidden">
        <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
        <Package v-else class="w-20 h-20 text-muted-foreground/30" />
      </div>

      <!-- Info -->
      <div class="space-y-4">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <Badge variant="outline">{{ product.category }}</Badge>
            <span v-if="product.brand" class="text-sm text-muted-foreground">{{ product.brand }}</span>
          </div>
          <h1 class="text-2xl font-bold">{{ product.name }}</h1>
        </div>

        <p class="text-3xl font-bold text-primary">{{ formatPrice(product.price) }}</p>

        <div class="flex items-center gap-2 text-sm">
          <span class="text-muted-foreground">Stok:</span>
          <Badge :variant="product.stock > 0 ? 'default' : 'destructive'">
            {{ product.stock > 0 ? `${product.stock} tersedia` : 'Habis' }}
          </Badge>
        </div>

        <div v-if="product.description" class="text-sm text-muted-foreground leading-relaxed">
          {{ product.description }}
        </div>

        <!-- Store info -->
        <Card>
          <CardContent class="py-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
              <Store class="w-5 h-5 text-primary" />
            </div>
            <div>
              <p class="font-medium text-sm">{{ product.store?.name }}</p>
              <p class="text-xs text-muted-foreground">{{ product.store?.description || 'Toko resmi SEAPEDIA' }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Specs -->
        <div v-if="product.specifications && Object.keys(product.specifications).length">
          <h3 class="font-semibold mb-2 text-sm">Spesifikasi</h3>
          <div class="grid grid-cols-2 gap-2">
            <div v-for="(val, key) in product.specifications" :key="key" class="bg-muted/50 rounded p-2">
              <p class="text-xs text-muted-foreground">{{ key }}</p>
              <p class="text-sm font-medium">{{ val }}</p>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
          <div class="flex items-center border rounded-md">
            <button class="px-3 py-2 hover:bg-muted" @click="qty = Math.max(1, qty - 1)">-</button>
            <span class="px-4 py-2 text-sm font-medium">{{ qty }}</span>
            <button class="px-3 py-2 hover:bg-muted" @click="qty = Math.min(product.stock, qty + 1)">+</button>
          </div>
          <Button v-if="!auth.isLoggedIn" class="flex-1" as-child>
            <RouterLink to="/login">Login untuk Beli</RouterLink>
          </Button>
          <Button v-else-if="auth.activeRole !== 'buyer'" class="flex-1" variant="outline" disabled>
            Aktifkan role Buyer untuk membeli
          </Button>
          <Button v-else class="flex-1" :disabled="product.stock === 0 || adding" @click="addToCart">
            <ShoppingCart class="w-4 h-4 mr-2" />
            {{ adding ? 'Menambahkan...' : product.stock === 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { ArrowLeft, Package, Store, ShoppingCart } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { buyerApi } from '@/services/buyer'
import { toast } from 'vue-sonner'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const product = ref(null)
const loading = ref(true)
const qty = ref(1)
const adding = ref(false)

async function addToCart() {
  adding.value = true
  try {
    await buyerApi.addItem({ product_id: product.value.id, quantity: qty.value })
    toast.success('Produk ditambahkan ke keranjang!')
    router.push('/buyer/cart')
  } catch (e) {
    const msg = e.response?.data?.message || 'Gagal menambahkan.'
    if (e.response?.data?.conflict) {
      if (confirm(`${msg}\n\nKosongkan keranjang dan tambah produk ini?`)) {
        await buyerApi.clearCart()
        await buyerApi.addItem({ product_id: product.value.id, quantity: qty.value })
        toast.success('Produk ditambahkan ke keranjang!')
        router.push('/buyer/cart')
      }
    } else {
      toast.error(msg)
    }
  } finally { adding.value = false }
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/products/${route.params.id}`)
    product.value = data.data || data
  } catch {
    product.value = null
  } finally {
    loading.value = false
  }
})
</script>
