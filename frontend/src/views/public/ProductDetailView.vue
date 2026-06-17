<template>
  <div class="page-detail">
    <button class="back-btn" @click="router.back()">
      <ArrowLeft :size="16" />
      Kembali
    </button>

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
      <!-- Left: Image -->
      <div>
        <div class="detail-img-wrap">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name" />
          <Package v-else :size="96" color="#cbd5e1" />
        </div>
      </div>

      <!-- Right: Info -->
      <div class="detail-info">
        <div class="detail-badges">
          <span v-if="product.category" class="badge badge-outline">{{ product.category }}</span>
          <span v-if="product.brand" class="badge badge-muted">{{ product.brand }}</span>
        </div>

        <h1 class="detail-name">{{ product.name }}</h1>
        <div class="detail-price">{{ formatPrice(product.price) }}</div>

        <div class="stock-row">
          <span class="stock-label">Stok:</span>
          <span v-if="product.stock > 0" class="badge badge-success">{{ product.stock }} tersedia</span>
          <span v-else class="badge badge-destructive">Habis</span>
        </div>

        <div v-if="product.description" class="desc-text">{{ product.description }}</div>

        <!-- Store -->
        <div class="store-card">
          <div class="store-avatar">
            <Store :size="18" color="#6366f1" />
          </div>
          <div>
            <div class="store-name">{{ product.store?.name }}</div>
            <div class="store-desc">{{ product.store?.description || 'Toko resmi SEAPEDIA' }}</div>
          </div>
        </div>

        <!-- Specs -->
        <div v-if="product.specifications && Object.keys(product.specifications).length">
          <div class="detail-section-title">Spesifikasi</div>
          <div class="specs-grid">
            <div v-for="(val, key) in product.specifications" :key="key" class="spec-item">
              <div class="spec-key">{{ key }}</div>
              <div class="spec-val">{{ val }}</div>
            </div>
          </div>
        </div>

        <!-- Qty + CTA -->
        <div class="buy-row">
          <div class="qty-control">
            <button class="qty-btn" @click="qty = Math.max(1, qty - 1)">−</button>
            <div class="qty-val">{{ qty }}</div>
            <button class="qty-btn" @click="qty = Math.min(product.stock, qty + 1)">+</button>
          </div>

          <RouterLink v-if="!auth.isLoggedIn" to="/login" class="btn-add-cart">
            Login untuk Beli
          </RouterLink>
          <button
            v-else-if="auth.activeRole !== 'buyer'"
            class="btn-add-cart"
            disabled
            style="background:linear-gradient(135deg,#94a3b8,#64748b);box-shadow:none;"
          >
            Aktifkan role Buyer
          </button>
          <button
            v-else
            class="btn-add-cart"
            :disabled="product.stock === 0 || adding"
            @click="addToCart"
          >
            <span v-if="adding" class="spinner" />
            <ShoppingCart v-else :size="16" />
            {{ adding ? 'Menambahkan...' : product.stock === 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { ArrowLeft, Package, Store, ShoppingCart } from '@lucide/vue'
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

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

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

onMounted(async () => {
  try {
    const { data } = await api.get(`/products/${route.params.id}`)
    product.value = data.data || data
  } catch { product.value = null }
  finally { loading.value = false }
})
</script>

<style scoped>
.page-detail { max-width: 1200px; margin: 0 auto; padding: 32px 24px; }

.back-btn {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 13px; font-weight: 500; color: #64748b;
  background: none; border: none; cursor: pointer; font-family: inherit;
  padding: 6px 10px; border-radius: 0.5rem; margin-bottom: 28px;
  transition: background .15s, color .15s;
}
.back-btn:hover { background: #f1f5f9; color: #0f172a; }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: start; }
@media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; gap: 24px; } }

.detail-img-wrap {
  aspect-ratio: 1; background: #f1f5f9; border-radius: 1rem;
  border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.detail-img-wrap img { width: 100%; height: 100%; object-fit: cover; }

.detail-info { display: flex; flex-direction: column; gap: 20px; }
.detail-badges { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.badge { display: inline-flex; align-items: center; font-size: 12px; font-weight: 500; padding: 3px 10px; border-radius: 9999px; }
.badge-outline { background: none; border: 1px solid #e2e8f0; color: #334155; }
.badge-muted { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
.badge-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.badge-destructive { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

.detail-name { font-size: 26px; font-weight: 700; letter-spacing: -0.03em; color: #0f172a; line-height: 1.2; }
.detail-price { font-size: 32px; font-weight: 800; letter-spacing: -0.04em; color: #6366f1; }

.stock-row { display: flex; align-items: center; gap: 8px; }
.stock-label { font-size: 14px; color: #64748b; }
.desc-text { font-size: 14px; color: #334155; line-height: 1.7; }

.store-card {
  background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem;
  padding: 16px; display: flex; align-items: center; gap: 12px;
}
.store-avatar {
  width: 42px; height: 42px; border-radius: 10px;
  background: linear-gradient(135deg, #eef2ff, #e0f2fe);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.store-name { font-size: 14px; font-weight: 600; color: #0f172a; }
.store-desc { font-size: 12px; color: #64748b; margin-top: 2px; }

.detail-section-title { font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 8px; }
.specs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.spec-item { background: #f1f5f9; border-radius: 0.5rem; padding: 10px 12px; }
.spec-key { font-size: 11px; color: #64748b; margin-bottom: 2px; font-weight: 500; }
.spec-val { font-size: 13px; font-weight: 600; color: #0f172a; }

.buy-row { display: flex; align-items: center; gap: 12px; }
.qty-control { display: flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 0.5rem; overflow: hidden; flex-shrink: 0; }
.qty-btn { width: 36px; height: 44px; display: flex; align-items: center; justify-content: center; background: none; border: none; cursor: pointer; font-size: 20px; color: #334155; font-family: inherit; transition: background .15s; }
.qty-btn:hover { background: #f1f5f9; }
.qty-val { width: 44px; text-align: center; font-size: 15px; font-weight: 600; color: #0f172a; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; height: 44px; display: flex; align-items: center; justify-content: center; }

.btn-add-cart {
  flex: 1; height: 44px; border-radius: 0.5rem; border: none; cursor: pointer; font-family: inherit;
  font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;
  background: linear-gradient(135deg, #6366f1, #06b6d4); color: #fff; text-decoration: none;
  box-shadow: 0 4px 14px rgb(99 102 241/.3); transition: opacity .15s, transform .1s;
}
.btn-add-cart:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); }
.btn-add-cart:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

.spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

.skeleton { background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 0.5rem; }
.img-skeleton { aspect-ratio: 1; border-radius: 1rem; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.empty-state { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 64px 24px; color: #94a3b8; text-align: center; }
.empty-state p { font-size: 15px; }
.reset-btn { font-size: 13px; color: #6366f1; background: none; border: none; cursor: pointer; font-family: inherit; text-decoration: underline; }
</style>
