<template>
  <div class="page-detail">
    <button class="back-btn" @click="router.back()">← Kembali ke Katalog</button>

    <!-- Skeleton -->
    <div v-if="loading" class="detail-grid">
      <div class="skeleton img-skeleton" />
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="skeleton" style="height:14px;width:30%;" />
        <div class="skeleton" style="height:28px;width:80%;" />
        <div class="skeleton" style="height:36px;width:45%;" />
        <div class="skeleton" style="height:20px;width:30%;" />
        <div class="skeleton" style="height:80px;" />
        <div class="skeleton" style="height:60px;" />
      </div>
    </div>

    <!-- Not found -->
    <div v-else-if="!product" class="empty-state">
      <Package :size="44" color="#cbd5e1" />
      <p>Produk tidak ditemukan.</p>
      <button class="reset-btn" @click="router.push('/products')">Kembali ke katalog</button>
    </div>

    <!-- Detail -->
    <div v-else class="detail-grid">
      <!-- Left: Image / Video -->
      <div>
        <!-- Main display -->
        <div class="detail-img-wrap">
          <video
            v-if="activeMedia === 'video' && product.video_url"
            :src="product.video_url"
            controls
            class="detail-video"
          />
          <template v-else>
            <img v-if="activeImg" :src="activeImg" :alt="product.name" />
            <Package v-else :size="96" color="#f3c6d4" />
          </template>
          <span v-if="product.original_price" class="img-badge">SALE</span>
        </div>

        <!-- Thumbnail strip (foto + video) -->
        <div class="thumb-strip">
          <div
            v-for="(img, i) in realImages"
            :key="'img'+i"
            class="thumb-item"
            :class="{ active: activeMedia === 'photo' && activeImg === img }"
            @click="selectPhoto(img)"
          >
            <img :src="img" :alt="product.name" />
          </div>
          <!-- video thumb -->
          <div
            v-if="product.video_url"
            class="thumb-item thumb-video"
            :class="{ active: activeMedia === 'video' }"
            @click="activeMedia = 'video'"
          >
            <video :src="product.video_url" class="thumb-video-preview" />
            <div class="thumb-play"><PlayCircle :size="22" color="#fff" /></div>
          </div>
        </div>
      </div>

      <!-- Right: Info -->
      <div class="detail-info">
        <!-- Tags -->
        <div class="detail-tags">
          <span v-if="product.category" class="tag tag-cat">{{ product.category }}</span>
          <span v-if="product.brand" class="tag tag-brand">{{ product.brand }}</span>
        </div>

        <!-- Name -->
        <h1 class="detail-name">{{ product.name }}</h1>

        <!-- Rating -->
        <div v-if="product.rating" class="detail-rating">
          <span class="stars">{{ '★'.repeat(Math.floor(product.rating)) }}</span>
          <span class="stars-empty">{{ '★'.repeat(5 - Math.floor(product.rating)) }}</span>
          <span class="rating-score">{{ product.rating }}</span>
          <span class="rating-divider">·</span>
          <span class="rating-count">{{ product.review_count ?? 0 }} ulasan</span>
          <span class="rating-divider">·</span>
          <span class="rating-count">{{ product.sold_count ?? 0 }} terjual</span>
        </div>

        <!-- Price -->
        <div class="price-row">
          <div class="detail-price">{{ formatPrice(product.price) }}</div>
          <div v-if="product.original_price" class="detail-price-orig">{{ formatPrice(product.original_price) }}</div>
          <div v-if="product.original_price" class="discount-badge">-{{ discountPercent }}%</div>
        </div>

        <!-- Stock -->
        <div class="stock-row">
          <span class="stock-label">Stok</span>
          <span v-if="product.stock > 0" class="stock-badge-ok">{{ product.stock }} tersedia</span>
          <span v-else class="stock-badge-out">Habis</span>
        </div>

        <hr class="divider" />

        <!-- Store -->
        <div class="store-card">
          <div class="store-avatar">
            <Store :size="20" color="#c41952" />
          </div>
          <div>
            <div class="store-name">{{ product.store?.name }}</div>
            <div class="store-desc">{{ product.store?.description || 'Toko resmi SEAPEDIA' }}</div>
            <div v-if="product.store?.location" class="store-location">
              <MapPin :size="10" />
              {{ product.store.location }}
            </div>
          </div>
        </div>

        <!-- Description -->
        <div v-if="product.description">
          <div class="section-title">Deskripsi Produk</div>
          <div class="desc-box">
            <p class="desc-text">{{ product.description }}</p>
          </div>
        </div>

        <!-- Specs -->
        <div v-if="product.specifications && Object.keys(product.specifications).length">
          <div class="section-title">Spesifikasi</div>
          <div class="specs-grid">
            <div v-for="(val, key) in product.specifications" :key="key" class="spec-item">
              <div class="spec-key">{{ key }}</div>
              <div class="spec-val">{{ val }}</div>
            </div>
          </div>
        </div>

        <hr class="divider" />

        <!-- Qty + CTA -->
        <div class="buy-section">
          <div class="qty-row">
            <span class="qty-label">Jumlah</span>
            <div class="qty-control">
              <button class="qty-btn" @click="qty = Math.max(1, qty - 1)">−</button>
              <div class="qty-val">{{ qty }}</div>
              <button class="qty-btn" @click="qty = Math.min(product.stock, qty + 1)">+</button>
            </div>
          </div>

          <div class="btn-row">
            <RouterLink v-if="!auth.isLoggedIn" to="/login" class="btn-cart">
              🛒 Keranjang
            </RouterLink>
            <template v-else>
              <button
                class="btn-cart"
                :disabled="product.stock === 0 || adding || auth.activeRole !== 'buyer'"
                @click="addToCart(false)"
              >
                <span v-if="adding" class="spinner" />
                <template v-else>🛒 Keranjang</template>
              </button>
              <button
                class="btn-buy"
                :disabled="product.stock === 0 || adding || auth.activeRole !== 'buyer'"
                @click="addToCart(true)"
              >
                ⚡ Beli Sekarang
              </button>
            </template>
          </div>

          <p v-if="auth.isLoggedIn && auth.activeRole !== 'buyer'" class="role-hint">
            Aktifkan role Buyer untuk membeli produk.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { Package, Store, MapPin, PlayCircle } from '@lucide/vue'
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
const activeImg = ref(null)
const activeMedia = ref('photo') // 'photo' | 'video'

const discountPercent = computed(() => {
  if (!product.value?.original_price) return 0
  return Math.round((1 - product.value.price / product.value.original_price) * 100)
})

const realImages = computed(() => {
  if (!product.value) return []
  const imgs = product.value.images?.length
    ? product.value.images
    : (product.value.image_url ? [product.value.image_url] : [])
  return imgs
})

function selectPhoto(img) {
  activeImg.value = img
  activeMedia.value = 'photo'
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

async function addToCart(buyNow = false) {
  adding.value = true
  try {
    await buyerApi.addItem({ product_id: product.value.id, quantity: qty.value })
    toast.success('Produk ditambahkan ke keranjang!')
    router.push(buyNow ? '/buyer/checkout' : '/buyer/cart')
  } catch (e) {
    const msg = e.response?.data?.message || 'Gagal menambahkan.'
    if (e.response?.data?.conflict) {
      if (confirm(`${msg}\n\nKosongkan keranjang dan tambah produk ini?`)) {
        await buyerApi.clearCart()
        await buyerApi.addItem({ product_id: product.value.id, quantity: qty.value })
        toast.success('Produk ditambahkan ke keranjang!')
        router.push(buyNow ? '/buyer/checkout' : '/buyer/cart')
      }
    } else {
      toast.error(msg)
    }
  } finally { adding.value = false }
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/products/${route.params.id}`)
    product.value = data.data || data
    activeImg.value = product.value?.images?.[0] || product.value?.image_url || null
    activeMedia.value = 'photo'
  } catch { product.value = null }
  finally { loading.value = false }
})
</script>

<style scoped>
.page-detail { max-width: 1200px; margin: 0 auto; padding: 32px 24px; }

.back-btn {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 13px; font-weight: 600; color: #a06070;
  background: none; border: none; cursor: pointer; font-family: inherit;
  padding: 6px 10px; border-radius: 9999px; margin-bottom: 28px;
  transition: background .15s, color .15s;
}
.back-btn:hover { background: #fce7ef; color: #c41952; }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: start; }
@media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; gap: 24px; } }

/* IMAGE */
.detail-img-wrap {
  aspect-ratio: 1; background: #fff; border-radius: 20px;
  border: 1.5px solid #f3e0e6;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden; position: relative;
}
.detail-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
.img-badge {
  position: absolute; top: 16px; left: 16px;
  background: #c41952; color: #fff; font-size: 11px; font-weight: 800;
  letter-spacing: .06em; padding: 4px 12px; border-radius: 9999px;
}

/* VIDEO in main wrap */
.detail-video { width: 100%; height: 100%; object-fit: contain; background: #0a0a0a; border-radius: 18px; }

/* THUMBNAILS */
.thumb-strip { display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
.thumb-item {
  width: 72px; height: 72px; background: #fff;
  border: 2px solid #f3e0e6; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden; cursor: pointer; flex-shrink: 0;
  transition: border-color .15s, transform .1s;
}
.thumb-item img { width: 100%; height: 100%; object-fit: cover; }
.thumb-item:hover { border-color: #c41952; transform: translateY(-2px); }
.thumb-item.active { border-color: #c41952; box-shadow: 0 0 0 2px rgba(196,25,82,.2); }

/* Video thumbnail */
.thumb-video { position: relative; }
.thumb-video-preview { width: 100%; height: 100%; object-fit: cover; }
.thumb-play {
  position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
  background: rgba(0,0,0,.35);
}
.thumb-video:hover .thumb-play { background: rgba(196,25,82,.5); }

/* INFO */
.detail-info { display: flex; flex-direction: column; gap: 18px; }

.detail-tags { display: flex; gap: 8px; flex-wrap: wrap; }
.tag { display: inline-flex; align-items: center; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 9999px; }
.tag-cat  { background: #fce7ef; color: #c41952; border: 1px solid #f3c6d4; }
.tag-brand{ background: #fff; color: #a06070; border: 1px solid #f3e0e6; }

.detail-name { font-size: 26px; font-weight: 800; letter-spacing: -0.04em; color: #1a1a1a; line-height: 1.2; }

.detail-rating { display: flex; align-items: center; gap: 8px; }
.stars       { color: #f59e0b; font-size: 15px; letter-spacing: 2px; }
.stars-empty { color: #f3e0e6; font-size: 15px; letter-spacing: 2px; }
.rating-score { font-size: 14px; font-weight: 700; color: #1a1a1a; }
.rating-count { font-size: 13px; color: #a06070; }
.rating-divider { color: #f3e0e6; }

.price-row { display: flex; align-items: flex-end; gap: 12px; }
.detail-price { font-size: 32px; font-weight: 800; letter-spacing: -0.04em; color: #c41952; }
.detail-price-orig { font-size: 16px; color: #a06070; text-decoration: line-through; margin-bottom: 4px; }
.discount-badge { background: #fce7ef; color: #c41952; border: 1px solid #f3c6d4; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; margin-bottom: 4px; }

.stock-row { display: flex; align-items: center; gap: 10px; }
.stock-label { font-size: 14px; color: #a06070; font-weight: 500; }
.stock-badge-ok  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-size: 12px; font-weight: 700; padding: 3px 12px; border-radius: 9999px; }
.stock-badge-out { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; font-size: 12px; font-weight: 700; padding: 3px 12px; border-radius: 9999px; }

.divider { border: none; border-top: 1.5px solid #f3e0e6; }

/* STORE */
.store-card {
  background: #fff; border: 1.5px solid #f3e0e6; border-radius: 14px;
  padding: 14px 16px; display: flex; align-items: center; gap: 12px;
  cursor: pointer; transition: border-color .15s;
}
.store-card:hover { border-color: #f3c6d4; }
.store-avatar {
  width: 42px; height: 42px; border-radius: 10px;
  background: #fce7ef; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.store-name { font-size: 14px; font-weight: 700; color: #1a1a1a; }
.store-desc { font-size: 12px; color: #a06070; margin-top: 2px; }
.store-location { font-size: 11px; color: #c41952; font-weight: 600; margin-top: 3px; display: flex; align-items: center; gap: 4px; }

/* DESC */
.section-title { font-size: 13px; font-weight: 700; color: #1a1a1a; margin-bottom: 10px; text-transform: uppercase; letter-spacing: .06em; }
.desc-box { background: #fff; border: 1.5px solid #f3e0e6; border-radius: 14px; padding: 16px; }
.desc-text { font-size: 14px; color: #5a3a44; line-height: 1.75; }

/* SPECS */
.specs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.spec-item { background: #fff; border: 1.5px solid #f3e0e6; border-radius: 10px; padding: 10px 14px; }
.spec-key { font-size: 11px; color: #a06070; margin-bottom: 3px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.spec-val { font-size: 13px; font-weight: 700; color: #1a1a1a; }

/* BUY */
.buy-section { display: flex; flex-direction: column; gap: 12px; }
.qty-row { display: flex; align-items: center; gap: 8px; }
.qty-label { font-size: 13px; color: #a06070; font-weight: 500; }
.qty-control {
  display: flex; align-items: center; border: 1.5px solid #f3e0e6;
  border-radius: 9999px; overflow: hidden; background: #fff;
}
.qty-btn {
  width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;
  background: none; border: none; cursor: pointer; font-size: 18px; color: #c41952; font-family: inherit;
  transition: background .15s;
}
.qty-btn:hover { background: #fce7ef; }
.qty-val {
  width: 44px; text-align: center; font-size: 15px; font-weight: 700; color: #1a1a1a;
  border-left: 1.5px solid #f3e0e6; border-right: 1.5px solid #f3e0e6;
  height: 38px; display: flex; align-items: center; justify-content: center;
}

.btn-row { display: flex; gap: 10px; }
.btn-cart {
  flex: 1; height: 46px; border-radius: 9999px; cursor: pointer; font-family: inherit;
  font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;
  background: #fff; color: #c41952; border: 2px solid #c41952; text-decoration: none;
  transition: background .15s;
}
.btn-cart:hover:not(:disabled) { background: #fce7ef; }
.btn-cart:disabled { opacity: .45; cursor: not-allowed; }
.btn-buy {
  flex: 1; height: 46px; border-radius: 9999px; border: none; cursor: pointer; font-family: inherit;
  font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;
  background: #c41952; color: #fff;
  box-shadow: 0 4px 16px rgba(196,25,82,.3);
  transition: background .15s, transform .1s;
}
.btn-buy:hover:not(:disabled) { background: #a0163f; transform: translateY(-1px); }
.btn-buy:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; transform: none; }

.role-hint { font-size: 12px; color: #a06070; text-align: center; }

.spinner { width: 16px; height: 16px; border: 2px solid rgba(196,25,82,.3); border-top-color: #c41952; border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

.skeleton { background: linear-gradient(90deg,#fce7ef 25%,#f3c6d4 50%,#fce7ef 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 0.5rem; }
.img-skeleton { aspect-ratio: 1; border-radius: 20px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.empty-state { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 64px 24px; color: #a06070; text-align: center; }
.empty-state p { font-size: 15px; }
.reset-btn { font-size: 13px; color: #c41952; background: none; border: none; cursor: pointer; font-family: inherit; text-decoration: underline; }
</style>
