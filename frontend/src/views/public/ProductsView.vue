<template>
  <div class="page-products">
    <div class="page-head">
      <h1>Katalog Produk</h1>
      <p>Temukan produk dari berbagai toko di SEAPEDIA</p>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar">
      <div class="search-wrap">
        <Search :size="16" class="search-icon" />
        <input
          v-model="search"
          class="search-input"
          type="text"
          placeholder="Cari produk..."
          @input="debouncedFetch"
        />
      </div>
      <select v-model="category" class="filter-select" @change="onFilterChange">
        <option value="">Semua Kategori</option>
        <option value="Electronics">Electronics</option>
        <option value="Fashion">Fashion</option>
        <option value="Food">Food</option>
        <option value="Kosmetik">Kosmetik</option>
        <option value="Other">Other</option>
      </select>
      <select v-model="sort" class="filter-select" @change="onFilterChange">
        <option value="terbaru">Terbaru</option>
        <option value="harga_asc">Harga: Rendah ke Tinggi</option>
        <option value="harga_desc">Harga: Tinggi ke Rendah</option>
      </select>
    </div>

    <!-- Active filters -->
    <div v-if="search || category" class="active-filters">
      <span class="filter-label">Filter aktif:</span>
      <span v-if="search" class="filter-chip">
        "{{ search }}"
        <button @click="search = ''; onFilterChange()">✕</button>
      </span>
      <span v-if="category" class="filter-chip">
        {{ category }}
        <button @click="category = ''; onFilterChange()">✕</button>
      </span>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="products-grid">
      <div v-for="i in 12" :key="i" class="product-card" style="pointer-events:none;">
        <div class="skeleton img-skeleton" />
        <div class="product-body">
          <div class="skeleton" style="height:10px;width:55%;margin-bottom:6px;" />
          <div class="skeleton" style="height:13px;width:90%;margin-bottom:4px;" />
          <div class="skeleton" style="height:13px;width:70%;margin-bottom:10px;" />
          <div class="skeleton" style="height:16px;width:45%;" />
        </div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="products.length === 0" class="empty-state">
      <Package :size="44" color="#cbd5e1" />
      <p>Tidak ada produk ditemukan.</p>
      <button v-if="search || category" class="reset-btn" @click="resetFilters">Reset filter</button>
    </div>

    <!-- Products grid -->
    <div v-else class="products-grid">
      <div
        v-for="product in products"
        :key="product.id"
        class="product-card"
        @click="router.push(`/products/${product.id}`)"
      >
        <div class="product-img">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name" />
          <Package v-else :size="40" color="#cbd5e1" />
        </div>
        <div class="product-body">
          <div class="product-store">{{ product.store?.name }}</div>
          <div class="product-name">{{ product.name }}</div>
          <div class="product-footer">
            <span class="product-price">{{ formatPrice(product.price) }}</span>
            <span v-if="product.stock === 0" class="badge-out">Habis</span>
            <span v-else class="product-stock">Stok {{ product.stock }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="pagination">
      <button class="page-btn" :disabled="page === 1" @click="changePage(page - 1)">← Sebelumnya</button>
      <span class="page-info">{{ page }} / {{ lastPage }}</span>
      <button class="page-btn" :disabled="page === lastPage" @click="changePage(page + 1)">Selanjutnya →</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Search, Package } from '@lucide/vue'
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
function onFilterChange() { page.value = 1; fetchProducts() }
function resetFilters() { search.value = ''; category.value = ''; sort.value = 'terbaru'; page.value = 1; fetchProducts() }
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
      params: { search: search.value || undefined, category: category.value || undefined, sort: sort.value !== 'terbaru' ? sort.value : undefined, page: page.value, per_page: 12 },
    })
    products.value = data.data || data
    lastPage.value = data.last_page || 1
  } catch { products.value = [] }
  finally { loading.value = false }
}
function changePage(p) { page.value = p; fetchProducts(); window.scrollTo({ top: 0, behavior: 'smooth' }) }

onMounted(fetchProducts)
</script>

<style scoped>
.page-products { max-width: 1200px; margin: 0 auto; padding: 32px 24px; }

.page-head { margin-bottom: 24px; }
.page-head h1 { font-size: 24px; font-weight: 700; letter-spacing: -0.03em; color: #0f172a; margin-bottom: 4px; }
.page-head p { font-size: 14px; color: #64748b; }

/* Filter bar */
.filter-bar { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; display: flex; pointer-events: none; }
.search-input {
  width: 100%; height: 40px; padding: 0 14px 0 38px;
  font-family: inherit; font-size: 14px; color: #0f172a;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 0.5rem;
  outline: none; transition: border-color .15s, box-shadow .15s;
}
.search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgb(99 102 241/.12); }
.filter-select {
  height: 40px; padding: 0 32px 0 12px;
  font-family: inherit; font-size: 14px; color: #0f172a;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 0.5rem;
  outline: none; appearance: none; cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 10px center;
  transition: border-color .15s;
}
.filter-select:focus { border-color: #6366f1; }

/* Active filters */
.active-filters { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.filter-label { font-size: 13px; color: #64748b; }
.filter-chip {
  display: inline-flex; align-items: center; gap: 6px;
  background: #eef2ff; color: #6366f1; border: 1px solid #c7d2fe;
  border-radius: 9999px; padding: 3px 10px; font-size: 12px; font-weight: 500;
}
.filter-chip button { background: none; border: none; cursor: pointer; color: #6366f1; opacity: .7; font-size: 13px; padding: 0; line-height: 1; }
.filter-chip button:hover { opacity: 1; }

/* Products grid */
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
.product-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem;
  overflow: hidden; cursor: pointer; transition: box-shadow .2s, transform .2s;
}
.product-card:hover { box-shadow: 0 10px 15px -3px rgb(0 0 0/.08),0 4px 6px -4px rgb(0 0 0/.05); transform: translateY(-2px); }
.product-img { aspect-ratio: 1; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.product-img img { width: 100%; height: 100%; object-fit: cover; }
.product-body { padding: 12px; }
.product-store { font-size: 11px; color: #64748b; margin-bottom: 3px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.product-name { font-size: 13px; font-weight: 600; color: #0f172a; line-height: 1.4; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.product-footer { display: flex; align-items: center; justify-content: space-between; }
.product-price { font-size: 14px; font-weight: 700; color: #6366f1; }
.product-stock { font-size: 11px; color: #64748b; }
.badge-out { font-size: 11px; color: #ef4444; font-weight: 600; background: #fef2f2; border: 1px solid #fecaca; padding: 1px 6px; border-radius: 9999px; }

/* Skeleton */
.skeleton { background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 0.375rem; }
.img-skeleton { aspect-ratio: 1; border-radius: 0; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Empty */
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 64px 24px; color: #94a3b8; text-align: center; }
.empty-state p { font-size: 15px; }
.reset-btn { font-size: 13px; color: #6366f1; background: none; border: none; cursor: pointer; font-family: inherit; text-decoration: underline; }

/* Pagination */
.pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 40px; }
.page-btn { height: 36px; padding: 0 16px; border-radius: 0.5rem; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer; border: 1px solid #e2e8f0; background: #fff; color: #334155; transition: all .15s; }
.page-btn:hover:not(:disabled) { border-color: #6366f1; color: #6366f1; background: #eef2ff; }
.page-btn:disabled { opacity: .4; cursor: not-allowed; }
.page-info { font-size: 13px; color: #64748b; padding: 0 8px; }
</style>
