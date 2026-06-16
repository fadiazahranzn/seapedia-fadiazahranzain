<template>
  <div>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-primary/10 via-background to-background py-20 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <Badge class="mb-4">Platform Marketplace Multi-Peran</Badge>
        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-4">
          Belanja, Jual, dan Kirim<br />
          <span class="text-primary">di Satu Platform</span>
        </h1>
        <p class="text-muted-foreground text-lg mb-8 max-w-2xl mx-auto">
          SEAPEDIA menghubungkan Pembeli, Penjual, dan Driver dalam satu ekosistem marketplace yang mudah digunakan.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <Button size="lg" as-child>
            <RouterLink to="/products">Jelajahi Produk</RouterLink>
          </Button>
          <Button size="lg" variant="outline" as-child>
            <RouterLink to="/register">Bergabung Sekarang</RouterLink>
          </Button>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="py-16 px-4">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-2xl font-bold text-center mb-10">Satu Platform, Empat Peran</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <Card v-for="role in roles" :key="role.title">
            <CardContent class="pt-6">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-4" :class="role.bg">
                <component :is="role.icon" class="w-5 h-5" :class="role.color" />
              </div>
              <h3 class="font-semibold mb-1">{{ role.title }}</h3>
              <p class="text-sm text-muted-foreground">{{ role.desc }}</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>

    <!-- Products preview -->
    <section class="py-12 px-4 bg-muted/20">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold">Produk Terbaru</h2>
          <Button variant="outline" size="sm" as-child>
            <RouterLink to="/products">Lihat Semua</RouterLink>
          </Button>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <Card v-for="product in products" :key="product.id" class="cursor-pointer hover:shadow-md transition-shadow" @click="router.push(`/products/${product.id}`)">
            <div class="aspect-square bg-muted rounded-t-lg flex items-center justify-center overflow-hidden">
              <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
              <Package v-else class="w-12 h-12 text-muted-foreground/40" />
            </div>
            <CardContent class="p-3">
              <p class="text-xs text-muted-foreground mb-1">{{ product.store?.name }}</p>
              <h3 class="font-medium text-sm line-clamp-2 mb-1">{{ product.name }}</h3>
              <p class="font-bold text-primary text-sm">{{ formatPrice(product.price) }}</p>
            </CardContent>
          </Card>
        </div>
        <div v-if="loading" class="text-center py-8 text-muted-foreground">Memuat produk...</div>
      </div>
    </section>

    <!-- Reviews preview -->
    <section class="py-16 px-4">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold">Apa Kata Mereka?</h2>
          <Button variant="outline" size="sm" as-child>
            <RouterLink to="/reviews">Semua Ulasan</RouterLink>
          </Button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <Card v-for="review in latestReviews" :key="review.id">
            <CardContent class="pt-6">
              <div class="flex items-center gap-1 mb-2">
                <Star v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-muted'" />
              </div>
              <p class="text-sm text-muted-foreground mb-3 line-clamp-3">{{ review.comment }}</p>
              <p class="text-sm font-medium">{{ review.reviewer_name }}</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { ShoppingCart, Store, Truck, ShieldCheck, Star, Package } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import api from '@/services/api'

const router = useRouter()
const products = ref([])
const latestReviews = ref([])
const loading = ref(true)

const roles = [
  { title: 'Pembeli', desc: 'Temukan produk terbaik dari berbagai toko dengan harga terjangkau.', icon: ShoppingCart, bg: 'bg-blue-100', color: 'text-blue-600' },
  { title: 'Penjual', desc: 'Buka toko, kelola produk, dan terima pesanan dengan mudah.', icon: Store, bg: 'bg-green-100', color: 'text-green-600' },
  { title: 'Driver', desc: 'Ambil job pengiriman dan raih penghasilan tambahan.', icon: Truck, bg: 'bg-orange-100', color: 'text-orange-600' },
  { title: 'Admin', desc: 'Pantau seluruh aktivitas marketplace dari satu dashboard.', icon: ShieldCheck, bg: 'bg-purple-100', color: 'text-purple-600' },
]

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

onMounted(async () => {
  try {
    const [prodRes, reviewRes] = await Promise.allSettled([
      api.get('/products', { params: { per_page: 8 } }),
      api.get('/reviews', { params: { per_page: 3 } }),
    ])
    if (prodRes.status === 'fulfilled') products.value = prodRes.value.data.data || prodRes.value.data
    if (reviewRes.status === 'fulfilled') latestReviews.value = reviewRes.value.data.data || reviewRes.value.data
  } catch {}
  finally { loading.value = false }
})
</script>
