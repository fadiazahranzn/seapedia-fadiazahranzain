<template>
  <div class="page-products">

    <!-- PAGE HEAD -->
    <div class="page-head">
      <div class="page-head-top">
        <div>
          <span class="section-label">Katalog</span>
          <h1>Katalog Produk</h1>
          <p>Temukan produk dari berbagai toko di SEAPEDIA</p>
        </div>
        <div v-if="!loading" class="result-count">Menampilkan {{ totalItems }} produk</div>
      </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <div class="search-wrap">
        <Search :size="14" class="search-icon" />
        <input
          v-model="search"
          class="search-input"
          type="text"
          placeholder="Cari produk..."
          @input="debouncedFetch"
        />
      </div>
      <div class="filter-selects">
      <!-- Category combobox -->
      <Popover v-model:open="catOpen">
        <PopoverTrigger as-child>
          <button
            class="filter-select combobox-trigger"
            :class="{ 'has-value': category }"
            type="button"
          >
            <span>{{ category || 'Semua Kategori' }}</span>
            <ChevronsUpDown :size="12" class="combobox-chevron" />
          </button>
        </PopoverTrigger>
        <PopoverContent class="combobox-content" align="start" :side-offset="6">
          <div class="combobox-search-wrap">
            <Search :size="13" class="combobox-search-icon" />
            <input
              v-model="catSearch"
              class="combobox-search-input"
              type="text"
              placeholder="Cari kategori..."
              @click.stop
            />
          </div>
          <ul class="combobox-list">
            <li
              v-for="opt in filteredCategories"
              :key="opt.value"
              class="combobox-item"
              :class="{ active: category === opt.value }"
              @click="selectCategory(opt.value)"
            >
              <Check v-if="category === opt.value" :size="13" class="combobox-check" />
              <span :class="category === opt.value ? 'font-semibold' : ''">{{ opt.label }}</span>
            </li>
            <li v-if="filteredCategories.length === 0" class="combobox-empty">Tidak ditemukan</li>
          </ul>
        </PopoverContent>
      </Popover>
      <!-- Sort combobox -->
      <Popover v-model:open="sortOpen">
        <PopoverTrigger as-child>
          <button class="filter-select combobox-trigger" type="button">
            <span>{{ sortOptions.find(s => s.value === sort)?.label }}</span>
            <ChevronsUpDown :size="12" class="combobox-chevron" />
          </button>
        </PopoverTrigger>
        <PopoverContent class="combobox-content" align="start" :side-offset="6">
          <ul class="combobox-list" style="padding: 6px;">
            <li
              v-for="opt in sortOptions"
              :key="opt.value"
              class="combobox-item"
              :class="{ active: sort === opt.value }"
              @click="selectSort(opt.value)"
            >
              <Check v-if="sort === opt.value" :size="13" class="combobox-check" />
              <span :class="sort === opt.value ? 'font-semibold' : ''">{{ opt.label }}</span>
            </li>
          </ul>
        </PopoverContent>
      </Popover>
      </div>
    </div>

    <!-- ACTIVE FILTERS -->
    <div v-if="search || category" class="active-filters">
      <span class="filter-label-text">Filter aktif:</span>
      <span v-if="search" class="filter-chip">
        "{{ search }}"
        <button @click="search = ''; onFilterChange()">✕</button>
      </span>
      <span v-if="category" class="filter-chip">
        {{ category }}
        <button @click="category = ''; onFilterChange()">✕</button>
      </span>
    </div>

    <!-- SKELETON -->
    <div v-if="loading" class="products-grid">
      <div v-for="i in 10" :key="i" class="product-card" style="pointer-events:none;">
        <div class="skeleton img-skeleton" />
        <div class="product-body">
          <div class="skeleton" style="height:10px;width:55%;margin-bottom:6px;" />
          <div class="skeleton" style="height:13px;width:90%;margin-bottom:4px;" />
          <div class="skeleton" style="height:13px;width:70%;margin-bottom:10px;" />
          <div class="skeleton" style="height:16px;width:45%;margin-bottom:8px;" />
          <div class="skeleton" style="height:34px;width:100%;margin-top:auto;" />
        </div>
      </div>
    </div>

    <!-- EMPTY -->
    <div v-else-if="products.length === 0" class="empty-state">
      <Package :size="44" color="#f3c6d4" />
      <p>Tidak ada produk ditemukan.</p>
      <button v-if="search || category" class="reset-btn" @click="resetFilters">Reset filter</button>
    </div>

    <!-- PRODUCTS GRID -->
    <div v-else class="products-grid">
      <div
        v-for="(product, idx) in products"
        :key="product.id"
        class="product-card"
        @click="router.push(`/products/${product.id}`)"
      >
        <div class="product-img">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name" />
          <Package v-else :size="40" color="#f3c6d4" />
          <span v-if="idx % 5 === 0 || idx % 5 === 4" class="product-badge">SALE</span>
          <span v-else-if="idx % 5 === 1" class="product-badge new">NEW</span>
          <span v-else-if="idx % 5 === 3" class="product-badge hot">HOT</span>
        </div>
        <div class="product-body">
          <div class="product-meta">
            <div class="product-store">{{ product.store?.name }}</div>
            <div class="product-location">
              <MapPin :size="10" />
              {{ product.store?.city || 'Indonesia' }}
            </div>
          </div>
          <div class="product-name">{{ product.name }}</div>
          <div class="product-rating">
            <span class="stars">{{ '★'.repeat(Math.min(4, 3 + (idx % 3))) }}</span>
            <span class="stars-empty">{{ '★'.repeat(Math.max(0, 5 - Math.min(4, 3 + (idx % 3)))) }}</span>
            <span class="rating-count">({{ 44 + (idx * 23) % 280 }})</span>
          </div>
          <div class="product-footer">
            <div class="product-price">{{ formatPrice(product.price) }}</div>
            <div v-if="idx % 3 !== 2" class="product-price-orig">{{ formatPrice(product.price * 1.25) }}</div>
          </div>
          <button
            v-if="product.stock > 0"
            class="view-btn"
            @click.stop="router.push(`/products/${product.id}`)"
          >Lihat Produk →</button>
          <button
            v-else
            class="notify-btn"
            @click.stop
          >🔔 Beritahu Saya</button>
        </div>
      </div>
    </div>

    <!-- PAGINATION -->
    <div v-if="lastPage > 1" class="pagination">
      <button class="page-btn" :disabled="page === 1" @click="changePage(page - 1)">← Sebelumnya</button>
      <div class="page-dots">
        <div
          v-for="p in lastPage"
          :key="p"
          class="page-dot"
          :class="{ active: p === page }"
          @click="changePage(p)"
        />
      </div>
      <button class="page-btn" :disabled="page === lastPage" @click="changePage(page + 1)">Selanjutnya →</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Search, Package, MapPin, ChevronsUpDown, Check } from '@lucide/vue'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import api from '@/services/api'

const router = useRouter()
const route  = useRoute()

const products   = ref([])
const loading    = ref(true)
const search     = ref(route.query.search   || '')
const category   = ref(route.query.category || '')
const sort       = ref(route.query.sort     || 'terbaru')
const page       = ref(Number(route.query.page) || 1)
const lastPage   = ref(1)
const totalItems = ref(0)

const catOpen   = ref(false)
const catSearch = ref('')
const sortOpen  = ref(false)

const sortOptions = [
  { value: 'terbaru',    label: 'Terbaru' },
  { value: 'harga_asc',  label: 'Harga: Rendah ke Tinggi' },
  { value: 'harga_desc', label: 'Harga: Tinggi ke Rendah' },
]

function selectSort(val) {
  sort.value = val
  sortOpen.value = false
  onFilterChange()
}

const categories = [
  { value: '',            label: 'Semua Kategori' },
  { value: 'Electronics', label: 'Electronics' },
  { value: 'Fashion',     label: 'Fashion' },
  { value: 'Beauty',      label: 'Beauty' },
  { value: 'Home',        label: 'Home' },
  { value: 'Sports',      label: 'Sports' },
  { value: 'Food',        label: 'Food' },
  { value: 'General',     label: 'General' },
]

const filteredCategories = computed(() =>
  catSearch.value
    ? categories.filter(c => c.label.toLowerCase().includes(catSearch.value.toLowerCase()))
    : categories
)

function selectCategory(val) {
  category.value = val
  catOpen.value = false
  catSearch.value = ''
  onFilterChange()
}

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; fetchProducts() }, 400)
}
function onFilterChange() { page.value = 1; fetchProducts() }
function resetFilters()   { search.value = ''; category.value = ''; sort.value = 'terbaru'; page.value = 1; fetchProducts() }

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

function syncUrl() {
  const query = {}
  if (search.value)          query.search   = search.value
  if (category.value)        query.category = category.value
  if (sort.value !== 'terbaru') query.sort  = sort.value
  if (page.value > 1)        query.page     = page.value
  router.replace({ query })
}

async function fetchProducts() {
  loading.value = true
  syncUrl()
  try {
    const { data } = await api.get('/products', {
      params: {
        search:   search.value   || undefined,
        category: category.value || undefined,
        sort:     sort.value !== 'terbaru' ? sort.value : undefined,
        page:     page.value,
        per_page: 10,
      },
    })
    products.value   = data.data  || data
    lastPage.value   = data.last_page || 1
    totalItems.value = data.total     || products.value.length
  } catch {
    products.value = []
  } finally {
    loading.value = false
  }
}

function changePage(p) { page.value = p; fetchProducts(); window.scrollTo({ top: 0, behavior: 'smooth' }) }

onMounted(fetchProducts)
</script>

<style scoped>
/* ── LAYOUT ── */
.page-products { max-width: 1200px; margin: 0 auto; padding: 24px 16px; background: #fdf2f5; min-height: 100vh; }
@media (min-width: 640px) { .page-products { padding: 36px 24px; } }

/* PAGE HEAD */
.page-head { margin-bottom: 20px; }
.page-head-top { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 4px; }
.section-label { display: inline-block; background: #fce7ef; color: #c41952; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 3px 10px; border-radius: 9999px; margin-bottom: 6px; }
.page-head h1 { font-size: 20px; font-weight: 800; letter-spacing: -0.04em; color: #1a1a1a; }
@media (min-width: 640px) { .page-head h1 { font-size: 26px; } }
.page-head p  { font-size: 13px; color: #a06070; margin-top: 4px; }
.result-count { font-size: 12px; color: #a06070; font-weight: 500; white-space: nowrap; margin-top: 4px; }

/* FILTER BAR */
.filter-bar { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; align-items: center; }
.search-wrap { position: relative; flex: 1; min-width: 0; width: 100%; order: 0; }
@media (max-width: 639px) {
  .filter-bar { flex-direction: column; }
  .search-wrap { width: 100%; }
  .combobox-trigger { flex: 1; width: 100%; }
  .filter-bar > :not(.search-wrap) { display: flex; flex: 1; }
}
@media (min-width: 640px) { .search-wrap { min-width: 240px; width: auto; } }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #c41952; pointer-events: none; display: flex; }
.search-input {
  width: 100%; height: 42px; padding: 0 14px 0 40px;
  font-size: 14px; font-family: inherit; color: #1a1a1a;
  border: 1.5px solid #f3e0e6; background: #fff;
  border-radius: 9999px; outline: none;
  transition: border-color .15s, box-shadow .15s;
  box-sizing: border-box;
}
.search-input:focus { border-color: #c41952; box-shadow: 0 0 0 3px rgba(196,25,82,.1); }
.filter-select {
  height: 42px; padding: 0 36px 0 14px;
  font-size: 13px; font-family: inherit; color: #1a1a1a; font-weight: 500;
  background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23c41952' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center;
  border: 1.5px solid #f3e0e6; border-radius: 9999px;
  outline: none; appearance: none; cursor: pointer;
  transition: border-color .15s; flex: 1;
}
@media (min-width: 640px) { .filter-select { flex: none; } }
.filter-select:focus { border-color: #c41952; }

/* COMBOBOX */
.combobox-trigger {
  display: inline-flex; align-items: center; justify-content: space-between;
  gap: 8px; min-width: 160px; background: #fff;
  background-image: none !important; white-space: nowrap;
  padding: 0 14px;
}
.combobox-trigger.has-value { border-color: #c41952; color: #c41952; }
.combobox-chevron { color: #c41952; shrink: 0; flex-shrink: 0; }
.combobox-content {
  width: 200px; padding: 0; border: 1.5px solid #f3e0e6;
  border-radius: 14px; box-shadow: 0 8px 24px rgba(0,0,0,.10);
  overflow: hidden; background: #fff;
}
.combobox-search-wrap {
  position: relative; padding: 10px 10px 8px;
  border-bottom: 1px solid #f3e0e6;
}
.combobox-search-icon {
  position: absolute; left: 22px; top: 50%; transform: translateY(-50%);
  color: #a06070; pointer-events: none;
}
.combobox-search-input {
  width: 100%; height: 34px; padding: 0 10px 0 30px;
  font-size: 13px; font-family: inherit; color: #1a1a1a;
  border: 1.5px solid #f3e0e6; border-radius: 9999px;
  outline: none; background: #fdf2f5; box-sizing: border-box;
  transition: border-color .15s;
}
.combobox-search-input:focus { border-color: #c41952; }
.combobox-list { list-style: none; margin: 0; padding: 6px; max-height: 240px; overflow-y: auto; }
.combobox-item {
  display: flex; align-items: center; gap: 8px;
  padding: 7px 10px; border-radius: 9px; font-size: 13px;
  color: #1a1a1a; cursor: pointer; transition: background .12s;
}
.combobox-item:hover { background: #fdf2f5; }
.combobox-item.active { background: #fce7ef; color: #c41952; }
.combobox-check { color: #c41952; flex-shrink: 0; }
.combobox-empty { padding: 10px; text-align: center; font-size: 12px; color: #a06070; }

/* ACTIVE FILTERS */
.active-filters { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.filter-label-text { font-size: 12px; color: #a06070; font-weight: 500; }
.filter-chip {
  display: inline-flex; align-items: center; gap: 5px;
  background: #fce7ef; color: #c41952; border: 1px solid #f3c6d4;
  border-radius: 9999px; padding: 3px 10px; font-size: 12px; font-weight: 600;
}
.filter-chip button { background: none; border: none; cursor: pointer; color: #c41952; font-size: 12px; padding: 0; line-height: 1; opacity: .7; }
.filter-chip button:hover { opacity: 1; }

/* PRODUCTS GRID */
.products-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}
@media (min-width: 480px)  { .products-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } }
@media (min-width: 640px)  { .products-grid { grid-template-columns: repeat(3, 1fr); gap: 14px; } }
@media (min-width: 900px)  { .products-grid { grid-template-columns: repeat(4, 1fr); gap: 16px; } }
@media (min-width: 1100px) { .products-grid { grid-template-columns: repeat(5, 1fr); gap: 16px; } }

.product-card {
  background: #fff; border: 1.5px solid #f3e0e6; border-radius: 16px;
  overflow: hidden; cursor: pointer;
  transition: box-shadow .2s, transform .2s, border-color .2s;
  position: relative; display: flex; flex-direction: column;
}
.product-card:hover {
  box-shadow: 0 12px 32px rgba(196,25,82,.12), 0 4px 12px rgba(0,0,0,.05);
  transform: translateY(-3px); border-color: #f3c6d4;
}

/* IMAGE */
.product-img {
  aspect-ratio: 1; background: #fdf2f5;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden; position: relative;
}
.product-img img { width: 100%; height: 100%; object-fit: cover; }

/* BADGE */
.product-badge {
  position: absolute; top: 8px; left: 8px;
  font-size: 9px; font-weight: 800; letter-spacing: .06em;
  padding: 2px 7px; border-radius: 9999px;
  background: #c41952; color: #fff;
}
@media (min-width: 640px) { .product-badge { font-size: 10px; top: 10px; left: 10px; padding: 3px 8px; } }
.product-badge.new { background: #0ea5e9; }
.product-badge.hot { background: #f59e0b; }

/* BODY */
.product-body { padding: 10px; flex: 1; display: flex; flex-direction: column; }
@media (min-width: 640px) { .product-body { padding: 12px; } }

.product-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px; }
.product-store { font-size: 10px; color: #c41952; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 60%; }
@media (min-width: 640px) { .product-store { font-size: 11px; } }
.product-location { font-size: 9px; color: #a06070; display: flex; align-items: center; gap: 3px; white-space: nowrap; }
@media (min-width: 640px) { .product-location { font-size: 10px; } }

.product-name {
  font-size: 12px; font-weight: 700; color: #1a1a1a; line-height: 1.4;
  margin-bottom: 5px; display: -webkit-box; -webkit-line-clamp: 2;
  -webkit-box-orient: vertical; overflow: hidden;
}
@media (min-width: 640px) { .product-name { font-size: 13px; margin-bottom: 6px; } }

.product-rating { display: flex; align-items: center; gap: 3px; margin-bottom: 6px; }
.stars       { color: #f59e0b; font-size: 10px; letter-spacing: 1px; }
.stars-empty { color: #f3e0e6; font-size: 10px; letter-spacing: 1px; }
.rating-count { font-size: 10px; color: #a06070; font-weight: 500; }
@media (min-width: 640px) { .stars, .stars-empty, .rating-count { font-size: 11px; } }

.product-footer { margin-top: auto; }
.product-price { font-size: 13px; font-weight: 800; color: #c41952; }
@media (min-width: 640px) { .product-price { font-size: 14px; } }
.product-price-orig { font-size: 10px; color: #a06070; text-decoration: line-through; margin-top: 1px; }

/* BUTTONS */
.view-btn {
  display: flex; align-items: center; justify-content: center;
  width: 100%; height: 36px; margin-top: 8px; border-radius: 9999px;
  border: none; background: #c41952; color: #fff;
  font-size: 11px; font-weight: 700; font-family: inherit; cursor: pointer;
  box-shadow: 0 4px 12px rgba(196,25,82,.25);
  transition: background .15s;
}
@media (min-width: 640px) { .view-btn { height: 34px; font-size: 12px; } }
.view-btn:hover { background: #a0163f; }

.notify-btn {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  width: 100%; height: 36px; margin-top: 8px; border-radius: 9999px;
  border: 1.5px solid #f3c6d4; background: #fff; color: #c41952;
  font-size: 11px; font-weight: 700; font-family: inherit; cursor: pointer;
  transition: background .15s, border-color .15s;
}
@media (min-width: 640px) { .notify-btn { height: 34px; font-size: 12px; } }
.notify-btn:hover { background: #fce7ef; border-color: #c41952; }

/* SKELETON */
.skeleton { background: linear-gradient(90deg,#fce7ef 25%,#f3e0e6 50%,#fce7ef 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 0.375rem; }
.img-skeleton { aspect-ratio: 1; border-radius: 0; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* EMPTY */
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 48px 24px; color: #a06070; text-align: center; }
.empty-state p { font-size: 15px; }
.reset-btn { font-size: 13px; color: #c41952; background: none; border: none; cursor: pointer; font-family: inherit; text-decoration: underline; }

/* PAGINATION */
.pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 32px; flex-wrap: wrap; }
.page-btn {
  height: 38px; padding: 0 14px; border-radius: 9999px;
  font-size: 12px; font-weight: 600; font-family: inherit; cursor: pointer;
  border: 1.5px solid #f3e0e6; background: #fff; color: #c41952;
  transition: all .15s;
}
@media (min-width: 640px) { .page-btn { padding: 0 18px; font-size: 13px; } }
.page-btn:hover:not(:disabled) { background: #fce7ef; border-color: #f3c6d4; }
.page-btn:disabled { opacity: .35; cursor: not-allowed; }
.page-dots { display: flex; gap: 6px; align-items: center; }
.page-dot { width: 8px; height: 8px; border-radius: 9999px; background: #f3e0e6; cursor: pointer; transition: all .2s; }
.page-dot.active { background: #c41952; width: 20px; }
</style>
