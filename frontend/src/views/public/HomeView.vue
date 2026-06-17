<template>
  <div>
    <!-- HERO -->
    <section class="hero">
      <div class="hero-bg" />
      <div class="container">
        <div class="hero-badge">
          <span class="hero-badge-dot" />
          Platform Marketplace Multi-Peran
        </div>
        <h1 class="hero-title">
          Belanja, Jual, dan Kirim<br />
          <span class="gradient-text">di Satu Platform</span>
        </h1>
        <p class="hero-desc">
          SEAPEDIA menghubungkan Pembeli, Penjual, dan Driver dalam satu ekosistem marketplace yang mudah digunakan.
        </p>
        <div class="hero-actions">
          <RouterLink to="/products" class="btn-hero-primary">
            <Search :size="16" />
            Jelajahi Produk
          </RouterLink>
          <RouterLink to="/register" class="btn-hero-outline">
            Bergabung Sekarang
            <ArrowRight :size="16" />
          </RouterLink>
        </div>

        <div class="hero-stats">
          <div class="stat-item">
            <div class="stat-num">2.4K+</div>
            <div class="stat-label">Produk Tersedia</div>
          </div>
          <div class="stat-item">
            <div class="stat-num">480+</div>
            <div class="stat-label">Penjual Aktif</div>
          </div>
          <div class="stat-item">
            <div class="stat-num">1.2K+</div>
            <div class="stat-label">Pesanan Selesai</div>
          </div>
          <div class="stat-item">
            <div class="stat-num">4.8★</div>
            <div class="stat-label">Rating Rata-rata</div>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES -->
    <section class="section section-muted">
      <div class="container">
        <div class="section-header">
          <span class="section-label">Ekosistem Lengkap</span>
          <h2 class="section-title">Satu Platform, Empat Peran</h2>
          <p class="section-sub">Semua yang kamu butuhkan ada di sini</p>
        </div>
        <div class="features-grid">
          <div v-for="role in roles" :key="role.title" class="feature-card">
            <div class="feature-icon" :style="{ background: role.iconBg }">
              <component :is="role.icon" :size="22" :color="role.iconColor" />
            </div>
            <div class="feature-title">{{ role.title }}</div>
            <div class="feature-desc">{{ role.desc }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- PRODUCTS -->
    <section class="section">
      <div class="container">
        <div class="section-row">
          <h2 class="section-row-title">Produk Terbaru</h2>
          <RouterLink to="/products" class="btn-link">Lihat Semua →</RouterLink>
        </div>

        <div class="products-grid">
          <template v-if="loading">
            <div v-for="i in 8" :key="i" class="product-card skeleton-card">
              <div class="skeleton img-skeleton" />
              <div class="product-body">
                <div class="skeleton" style="height:10px;width:55%;margin-bottom:6px;" />
                <div class="skeleton" style="height:13px;width:90%;margin-bottom:4px;" />
                <div class="skeleton" style="height:13px;width:70%;margin-bottom:10px;" />
                <div class="skeleton" style="height:16px;width:45%;" />
              </div>
            </div>
          </template>

          <template v-else-if="products.length">
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
                  <span v-if="product.stock === 0" class="out-of-stock">Habis</span>
                  <span v-else class="product-stock">Stok {{ product.stock }}</span>
                </div>
              </div>
            </div>
          </template>

          <div v-else class="empty-state">
            <Package :size="40" color="#cbd5e1" />
            <p>Belum ada produk tersedia.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- REVIEWS -->
    <section class="section section-muted">
      <div class="container">
        <div class="section-row">
          <div>
            <span class="section-label" style="display:block;text-align:left;margin-bottom:4px;">Testimoni</span>
            <h2 class="section-row-title">Apa Kata Mereka?</h2>
          </div>
          <RouterLink to="/reviews" class="btn-link">Semua Ulasan →</RouterLink>
        </div>

        <div class="reviews-grid">
          <div v-for="review in latestReviews" :key="review.id" class="review-card">
            <div class="review-stars">
              <Star
                v-for="i in 5"
                :key="i"
                :size="14"
                :fill="i <= review.rating ? '#f59e0b' : 'none'"
                :color="i <= review.rating ? '#f59e0b' : '#e2e8f0'"
              />
            </div>
            <p class="review-text">{{ review.comment }}</p>
            <div class="review-author">
              <div class="review-avatar">{{ review.reviewer_name?.charAt(0)?.toUpperCase() }}</div>
              <div>
                <div class="review-name">{{ review.reviewer_name }}</div>
              </div>
            </div>
          </div>

          <div v-if="!latestReviews.length && !loading" class="empty-state">
            <p>Belum ada ulasan.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
      <div class="container" style="text-align:center;">
        <h2 class="cta-title">Siap Bergabung dengan SEAPEDIA?</h2>
        <p class="cta-sub">Daftar gratis sekarang dan mulai perjalananmu sebagai buyer, seller, atau driver.</p>
        <div class="cta-actions">
          <RouterLink to="/register" class="btn-cta-white">Daftar Sekarang</RouterLink>
          <RouterLink to="/products" class="btn-cta-outline">Jelajahi Produk</RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { Search, ArrowRight, ShoppingCart, Store, Truck, ShieldCheck, Star, Package } from '@lucide/vue'
import api from '@/services/api'

const router = useRouter()
const products = ref([])
const latestReviews = ref([])
const loading = ref(true)

const roles = [
  { title: 'Pembeli', desc: 'Temukan produk terbaik dari berbagai toko dengan harga terjangkau dan pengiriman cepat.', icon: ShoppingCart, iconBg: '#eff6ff', iconColor: '#3b82f6' },
  { title: 'Penjual', desc: 'Buka toko, kelola produk, dan terima pesanan dengan mudah dari satu dashboard.', icon: Store, iconBg: '#f0fdf4', iconColor: '#22c55e' },
  { title: 'Driver', desc: 'Ambil job pengiriman kapan saja dan raih penghasilan tambahan yang fleksibel.', icon: Truck, iconBg: '#fff7ed', iconColor: '#f97316' },
  { title: 'Admin', desc: 'Pantau seluruh aktivitas marketplace dan kelola pengguna dari satu dashboard.', icon: ShieldCheck, iconBg: '#faf5ff', iconColor: '#a855f7' },
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

<style scoped>
.container { max-width: 1200px; margin: 0 auto; }

/* ── HERO ── */
.hero {
  padding: 80px 24px 72px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.hero-bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 70% 60% at 50% -10%, #eef2ff 0%, transparent 70%),
    radial-gradient(ellipse 40% 40% at 80% 50%, #e0f2fe 0%, transparent 60%);
  pointer-events: none;
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #eef2ff;
  color: #6366f1;
  border: 1px solid #c7d2fe;
  border-radius: 9999px;
  padding: 4px 14px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.02em;
  margin-bottom: 24px;
  position: relative;
}
.hero-badge-dot {
  width: 6px; height: 6px;
  background: #6366f1;
  border-radius: 50%;
  display: inline-block;
}
.hero-title {
  font-size: clamp(32px, 5vw, 52px);
  font-weight: 800;
  letter-spacing: -0.04em;
  line-height: 1.1;
  color: #0f172a;
  margin-bottom: 20px;
  position: relative;
}
.gradient-text {
  background: linear-gradient(135deg, #6366f1, #06b6d4);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-desc {
  font-size: 16px;
  color: #64748b;
  max-width: 560px;
  margin: 0 auto 36px;
  line-height: 1.7;
  position: relative;
}
.hero-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
  position: relative;
}
.btn-hero-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border-radius: 0.625rem;
  font-size: 15px;
  font-weight: 600;
  color: #fff;
  background: linear-gradient(135deg, #6366f1, #06b6d4);
  text-decoration: none;
  box-shadow: 0 4px 20px rgb(99 102 241 / .35);
  transition: opacity .15s, transform .1s;
}
.btn-hero-primary:hover { opacity: .92; transform: translateY(-1px); }
.btn-hero-outline {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border-radius: 0.625rem;
  font-size: 15px;
  font-weight: 600;
  color: #334155;
  background: #fff;
  border: 1.5px solid #e2e8f0;
  text-decoration: none;
  transition: border-color .15s, color .15s, background .15s;
}
.btn-hero-outline:hover { border-color: #6366f1; color: #6366f1; background: #eef2ff; }

.hero-stats {
  display: flex;
  justify-content: center;
  gap: 40px;
  margin-top: 56px;
  padding-top: 40px;
  border-top: 1px solid #e2e8f0;
  position: relative;
  flex-wrap: wrap;
}
.stat-item { text-align: center; }
.stat-num { font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: -0.04em; line-height: 1; }
.stat-label { font-size: 12px; color: #64748b; margin-top: 4px; font-weight: 500; }

/* ── SECTION ── */
.section { padding: 72px 24px; }
.section-muted { background: #f8fafc; }
.section-header { text-align: center; margin-bottom: 48px; }
.section-label {
  display: inline-block;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #6366f1;
  margin-bottom: 10px;
}
.section-title { font-size: clamp(22px, 3vw, 32px); font-weight: 700; letter-spacing: -0.03em; color: #0f172a; line-height: 1.2; }
.section-sub { font-size: 15px; color: #64748b; margin-top: 10px; }
.section-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
.section-row-title { font-size: 22px; font-weight: 700; letter-spacing: -0.03em; color: #0f172a; }
.btn-link {
  font-size: 13px;
  font-weight: 600;
  color: #6366f1;
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 6px 14px;
  text-decoration: none;
  transition: border-color .15s, background .15s;
  white-space: nowrap;
}
.btn-link:hover { border-color: #6366f1; background: #eef2ff; }

/* ── FEATURES ── */
.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}
.feature-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  padding: 28px 24px;
  transition: box-shadow .2s, border-color .2s, transform .2s;
}
.feature-card:hover { box-shadow: 0 10px 15px -3px rgb(0 0 0/.08),0 4px 6px -4px rgb(0 0 0/.05); border-color: #c7d2fe; transform: translateY(-2px); }
.feature-icon {
  width: 44px; height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}
.feature-title { font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: 6px; }
.feature-desc { font-size: 13px; color: #64748b; line-height: 1.6; }

/* ── PRODUCTS ── */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}
.product-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  overflow: hidden;
  cursor: pointer;
  transition: box-shadow .2s, transform .2s;
}
.product-card:hover { box-shadow: 0 10px 15px -3px rgb(0 0 0/.08),0 4px 6px -4px rgb(0 0 0/.05); transform: translateY(-2px); }
.product-img {
  aspect-ratio: 1;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.product-img img { width: 100%; height: 100%; object-fit: cover; }
.product-body { padding: 12px; }
.product-store { font-size: 11px; color: #64748b; margin-bottom: 3px; font-weight: 500; }
.product-name { font-size: 13px; font-weight: 600; color: #0f172a; line-height: 1.4; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.product-footer { display: flex; align-items: center; justify-content: space-between; }
.product-price { font-size: 14px; font-weight: 700; color: #6366f1; }
.product-stock { font-size: 11px; color: #64748b; }
.out-of-stock { font-size: 11px; color: #ef4444; font-weight: 600; }

/* Skeleton */
.skeleton {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 0.375rem;
}
.img-skeleton { aspect-ratio: 1; border-radius: 0; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Empty */
.empty-state {
  grid-column: 1/-1;
  text-align: center;
  padding: 48px 24px;
  color: #94a3b8;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

/* ── REVIEWS ── */
.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}
.review-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  padding: 24px;
}
.review-stars { display: flex; gap: 2px; margin-bottom: 12px; }
.review-text { font-size: 14px; color: #334155; line-height: 1.6; margin-bottom: 16px; }
.review-author { display: flex; align-items: center; gap: 10px; }
.review-avatar {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #06b6d4);
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700; color: #fff;
  flex-shrink: 0;
}
.review-name { font-size: 13px; font-weight: 600; color: #0f172a; }

/* ── CTA ── */
.cta-section {
  background: linear-gradient(135deg, #1e1b4b, #312e81);
  padding: 72px 24px;
}
.cta-title { font-size: clamp(24px, 4vw, 40px); font-weight: 800; color: #fff; letter-spacing: -0.04em; margin-bottom: 14px; }
.cta-sub { font-size: 15px; color: rgba(255,255,255,0.65); margin-bottom: 32px; }
.cta-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.btn-cta-white {
  padding: 12px 28px;
  border-radius: 0.625rem;
  font-size: 15px;
  font-weight: 600;
  color: #1e1b4b;
  background: #fff;
  text-decoration: none;
  transition: opacity .15s;
}
.btn-cta-white:hover { opacity: .9; }
.btn-cta-outline {
  padding: 12px 28px;
  border-radius: 0.625rem;
  font-size: 15px;
  font-weight: 600;
  color: rgba(255,255,255,0.85);
  background: rgba(255,255,255,0.1);
  border: 1.5px solid rgba(255,255,255,0.25);
  text-decoration: none;
  transition: background .15s;
}
.btn-cta-outline:hover { background: rgba(255,255,255,0.18); }
</style>
