<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold mb-1">Katalog Produk</h1>
      <p class="text-muted-foreground text-sm">Temukan produk dari berbagai toko di SEAPEDIA</p>
    </div>

    <!-- Search & Filter -->
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
      <div class="relative flex-1">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
        <Input v-model="search" placeholder="Cari produk..." class="pl-9" @input="debouncedFetch" />
      </div>
      <select v-model="category" class="border border-input rounded-md px-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring" @change="onFilterChange">
        <option value="">Semua Kategori</option>
        <option value="Electronics">Electronics</option>
        <option value="Fashion">Fashion</option>
        <option value="Food">Food</option>
        <option value="Kosmetik">Kosmetik</option>
        <option value="Other">Other</option>
      </select>
      <select v-model="sort" class="border border-input rounded-md px-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring" @change="onFilterChange">
        <option value="terbaru">Terbaru</option>
        <option value="harga_asc">Harga: Rendah ke Tinggi</option>
        <option value="harga_desc">Harga: Tinggi ke Rendah</option>
      </select>
    </div>

    <!-- Active filters indicator -->
    <div v-if="category || search" class="flex items-center gap-2 mb-4 text-sm text-muted-foreground">
      <span>Filter aktif:</span>
      <span v-if="search" class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-0.5 rounded-full">
        "{{ search }}"
        <button @click="search = ''; onFilterChange()" class="hover:text-primary/70">✕</button>
      </span>
      <span v-if="category" class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-0.5 rounded-full">
        {{ category }}
        <button @click="category = ''; onFilterChange()" class="hover:text-primary/70">✕</button>
      </span>
    </div>

    <!-- Products -->
    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="i in 8" :key="i" class="aspect-square bg-muted rounded-lg animate-pulse" />
    </div>

    <div v-else-if="products.length === 0" class="text-center py-16 text-muted-foreground">
      <Package class="w-12 h-12 mx-auto mb-3 opacity-30" />
      <p>Tidak ada produk ditemukan.</p>
      <button v-if="category || search" @click="resetFilters" class="mt-3 text-sm text-primary hover:underline">Reset filter</button>
    </div>

    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <Card
        v-for="product in products"
        :key="product.id"
        class="cursor-pointer hover:shadow-md transition-shadow"
        @click="router.push(`/products/${product.id}`)"
      >
        <div class="aspect-square bg-muted rounded-t-lg flex items-center justify-center overflow-hidden">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
          <Package v-else class="w-12 h-12 text-muted-foreground/30" />
        </div>
        <CardContent class="p-3">
          <p class="text-xs text-muted-foreground mb-1 truncate">{{ product.store?.name }}</p>
          <h3 class="font-medium text-sm line-clamp-2 mb-2">{{ product.name }}</h3>
          <div class="flex items-center justify-between">
            <p class="font-bold text-primary text-sm">{{ formatPrice(product.price) }}</p>
            <Badge v-if="product.stock === 0" variant="destructive" class="text-xs">Habis</Badge>
            <span v-else class="text-xs text-muted-foreground">Stok {{ product.stock }}</span>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="flex justify-center gap-2 mt-8">
      <Button variant="outline" size="sm" :disabled="page === 1" @click="changePage(page - 1)">Sebelumnya</Button>
      <span class="flex items-center text-sm text-muted-foreground px-2">{{ page }} / {{ lastPage }}</span>
      <Button variant="outline" size="sm" :disabled="page === lastPage" @click="changePage(page + 1)">Selanjutnya</Button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Search, Package } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()

const products = ref([])
const loading = ref(true)
const search = ref(route.query.search || '')
const category = ref(route.query.category || '')
const sort = ref(route.query.sort || 'terbaru')
const page = ref(Number(route.query.page) || 1)
const lastPage = ref(1)

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; fetchProducts() }, 400)
}

function onFilterChange() {
  page.value = 1
  fetchProducts()
}

function resetFilters() {
  search.value = ''
  category.value = ''
  sort.value = 'terbaru'
  page.value = 1
  fetchProducts()
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

function syncUrl() {
  const query = {}
  if (search.value) query.search = search.value
  if (category.value) query.category = category.value
  if (sort.value !== 'terbaru') query.sort = sort.value
  if (page.value > 1) query.page = page.value
  router.replace({ query })
}

async function fetchProducts() {
  loading.value = true
  syncUrl()
  try {
    const { data } = await api.get('/products', {
      params: {
        search: search.value || undefined,
        category: category.value || undefined,
        sort: sort.value !== 'terbaru' ? sort.value : undefined,
        page: page.value,
        per_page: 12,
      },
    })
    products.value = data.data || data
    lastPage.value = data.last_page || 1
  } catch {
    products.value = []
  } finally {
    loading.value = false
  }
}

function changePage(p) {
  page.value = p
  fetchProducts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(fetchProducts)
</script>
