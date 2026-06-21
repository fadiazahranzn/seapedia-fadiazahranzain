<template>
  <div>
    <!-- HERO -->
    <section class="hero">
      <div class="hero-glow-1" />
      <div class="hero-glow-2" />
      <div class="hero-inner">
        <div class="hero-content">
          <div class="hero-badge">
            <span class="hero-badge-dot" />
            Platform Marketplace Multi-Peran
          </div>
          <h1 class="hero-title">
            Belanja & Jual<br />
            <span class="accent">Lebih Mudah</span><br />
            di <span class="brand-font">SEAPEDIA</span>
          </h1>
          <p class="hero-desc">
            Temukan ribuan produk dari penjual terpercaya. Buyer, Seller, Driver — semua terhubung dalam satu ekosistem.
          </p>
          <div class="hero-actions">
            <RouterLink to="/products" class="btn-hero-primary">
              <Search :size="15" />
              Jelajahi Produk
            </RouterLink>
            <RouterLink to="/register" class="btn-hero-outline">
              Daftar Gratis
              <ArrowRight :size="14" />
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
              <div class="stat-num">4.8★</div>
              <div class="stat-label">Rating Rata-rata</div>
            </div>
          </div>
        </div>

        <div class="hero-visual">
          <div class="hero-device">
            <svg class="device-svg" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="20" y="30" width="120" height="80" rx="8" fill="rgba(196,25,82,0.08)" stroke="rgba(196,25,82,0.25)" stroke-width="1.5"/>
              <rect x="30" y="40" width="100" height="60" rx="4" fill="rgba(196,25,82,0.04)"/>
              <rect x="60" y="115" width="40" height="6" rx="3" fill="rgba(196,25,82,0.15)"/>
              <rect x="40" y="55" width="30" height="3" rx="1.5" fill="rgba(196,25,82,0.5)"/>
              <rect x="40" y="62" width="50" height="2" rx="1" fill="rgba(196,25,82,0.15)"/>
              <rect x="40" y="68" width="44" height="2" rx="1" fill="rgba(196,25,82,0.1)"/>
              <rect x="40" y="78" width="20" height="8" rx="4" fill="rgba(196,25,82,0.4)"/>
              <circle cx="115" cy="65" r="18" fill="rgba(196,25,82,0.08)" stroke="rgba(196,25,82,0.25)" stroke-width="1"/>
              <path d="M109 65 L113 69 L121 61" stroke="#c41952" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="device-label">marketplace · multi-peran</div>
          </div>
          <div class="hero-float float-1">
            <div class="float-val">+1.2K</div>
            <div class="float-label">Pesanan Bulan Ini</div>
          </div>
          <div class="hero-float float-2">
            <div class="float-val">⚡ Fast</div>
            <div class="float-label">Pengiriman Express</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CATEGORIES -->
    <section class="categories">
      <div class="container">
        <span class="section-label">Kategori</span>
        <h2 class="section-title">Temukan Produk Favoritmu</h2>
        <div class="categories-grid">
          <RouterLink v-for="cat in categories" :key="cat.name" to="/products" class="cat-card">
            <div class="cat-icon" :style="{ background: cat.bg }">{{ cat.emoji }}</div>
            <div class="cat-name">{{ cat.name }}</div>
            <div class="cat-count">{{ cat.count }}</div>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- PROMO BANNER -->
    <section class="promo-banner">
      <div class="container">
        <div class="promo-grid">
          <div class="promo-card promo-card-main">
            <div class="promo-discount">50%</div>
            <div class="promo-tag">Flash Sale</div>
            <div class="promo-title">Diskon Besar<br />Akhir Bulan</div>
            <div class="promo-sub">Penawaran terbatas untuk produk pilihan</div>
            <RouterLink to="/products" class="btn-promo">Belanja Sekarang →</RouterLink>
          </div>
          <div class="promo-card promo-card-side">
            <div class="promo-tag-side">New Seller</div>
            <div class="promo-title-side">Buka Toko<br />Gratis!</div>
            <div class="promo-sub-side">Mulai jual produkmu sekarang tanpa biaya apapun</div>
            <RouterLink to="/register" class="btn-promo-side">Daftar Penjual →</RouterLink>
          </div>
        </div>
      </div>
    </section>

    <!-- PRODUCTS -->
    <section class="products-section">
      <div class="container">
        <div class="products-header">
          <div>
            <span class="section-label">Pilihan</span>
            <h2 class="section-title" style="margin-bottom:0;">Produk Terbaru</h2>
          </div>
          <RouterLink to="/products" class="btn-see-all">Lihat Semua →</RouterLink>
        </div>

        <div class="product-tabs">
          <button
            v-for="tab in tabs"
            :key="tab"
            class="tab"
            :class="{ active: activeTab === tab }"
            @click="activeTab = tab"
          >{{ tab }}</button>
        </div>

        <div class="products-grid">
          <template v-if="loading">
            <div v-for="i in 5" :key="i" class="product-card skeleton-card">
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
              v-for="(product, idx) in products"
              :key="product.id"
              class="product-card"
              @click="router.push(`/products/${product.id}`)"
            >
              <div class="product-img">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name" />
                <Package v-else :size="48" color="#f3c6d4" />
                <span v-if="idx % 5 === 0 || idx % 5 === 4" class="product-badge">SALE</span>
                <span v-else-if="idx % 5 === 1" class="product-badge new">NEW</span>
                <span v-else-if="idx % 5 === 3" class="product-badge hot">HOT</span>
              </div>
              <div class="product-body">
                <div class="product-store">{{ product.store?.name }}</div>
                <div class="product-name">{{ product.name }}</div>
                <div class="product-rating">
                  <span class="stars">★★★★</span><span class="stars-empty">★</span>
                  <span class="rating-count">({{ 80 + (idx * 17) % 200 }})</span>
                </div>
                <div class="product-footer">
                  <div>
                    <div class="product-price">{{ formatPrice(product.price) }}</div>
                    <div v-if="idx % 3 !== 2" class="product-price-orig">{{ formatPrice(product.price * 1.25) }}</div>
                  </div>
                  <button class="add-to-cart" @click.stop="router.push(`/products/${product.id}`)">+</button>
                </div>
              </div>
            </div>
          </template>

          <div v-else class="empty-state">
            <Package :size="40" color="#f3c6d4" />
            <p>Belum ada produk tersedia.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES STRIP -->
    <section class="features-strip">
      <div class="container">
        <div class="features-strip-grid">
          <div v-for="f in features" :key="f.title" class="feature-strip-item">
            <div class="feature-strip-icon">{{ f.icon }}</div>
            <div>
              <div class="feature-strip-title">{{ f.title }}</div>
              <div class="feature-strip-sub">{{ f.sub }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ROLES -->
    <section class="roles-section">
      <div class="container">
        <div class="section-center">
          <span class="section-label">Ekosistem</span>
          <h2 class="section-title" style="margin-bottom:12px;">Satu Platform, Empat Peran</h2>
          <p class="section-sub">Bergabung sebagai buyer, seller, driver, atau admin dan nikmati kemudahan di setiap peran.</p>
        </div>
        <div class="roles-grid">
          <div v-for="role in roles" :key="role.title" class="role-card">
            <div class="role-icon" :style="{ background: role.iconBg }">
              <component :is="role.icon" :size="22" :color="role.iconColor" />
            </div>
            <div class="role-title">{{ role.title }}</div>
            <div class="role-desc">{{ role.desc }}</div>
            <ul class="role-perks">
              <li v-for="perk in role.perks" :key="perk" class="role-perk-item">
                <span class="perk-dot" :style="{ background: role.iconColor }"></span>
                {{ perk }}
              </li>
            </ul>
            <div class="role-tag" :style="{ background: role.iconBg, color: role.iconColor }">{{ role.tag }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- REVIEWS -->
    <section class="reviews-section">
      <div class="container">
        <div class="reviews-header">
          <div>
            <span class="section-label">Testimoni</span>
            <h2 class="section-title" style="margin-bottom:0;">Apa Kata Mereka?</h2>
          </div>
        </div>

        <div class="reviews-grid">
          <div v-for="review in staticReviews" :key="review.id" class="review-card">
            <div class="review-stars">
              <Star
                v-for="i in 5"
                :key="i"
                :size="14"
                :fill="i <= review.rating ? '#f59e0b' : 'none'"
                :color="i <= review.rating ? '#f59e0b' : '#f3e0e6'"
              />
            </div>
            <p class="review-text">{{ review.comment }}</p>
            <div class="review-author">
              <div class="review-avatar">{{ review.name.charAt(0) }}</div>
              <div>
                <div class="review-name">{{ review.name }}</div>
                <div class="review-role">{{ review.role }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
      <div class="cta-glow" />
      <div class="container" style="text-align:center;position:relative;">
        <h2 class="cta-title">Siap Bergabung dengan <span class="brand-font">SEAPEDIA</span>?</h2>
        <p class="cta-sub">Daftar gratis sekarang dan mulai perjalananmu sebagai buyer, seller, atau driver.</p>
        <div class="cta-actions">
          <RouterLink to="/register" class="btn-cta-white">Daftar Sekarang — Gratis</RouterLink>
          <RouterLink to="/products" class="btn-cta-outline">Lihat Produk</RouterLink>
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

const staticReviews = [
  { id: 1, rating: 5, comment: 'Pengiriman super cepat, barang sampai dalam kondisi sempurna. Penjualnya juga ramah dan responsif!', name: 'Andi Pratama', role: 'Verified Buyer' },
  { id: 2, rating: 5, comment: 'Produk original 100%, harga lebih murah dari toko lain. Sudah beli 3 kali di sini dan selalu puas!', name: 'Siti Rahayu', role: 'Verified Buyer' },
  { id: 3, rating: 4, comment: 'Fitur aplikasinya mudah dipakai, buka toko juga gampang banget. Omzet sudah naik 2x sejak pakai SEAPEDIA.', name: 'Budi Santoso', role: 'Verified Seller' },
  { id: 4, rating: 5, comment: 'Sebagai driver, orderan selalu ada dan bayarannya langsung masuk ke dompet. Sangat membantu!', name: 'Reza Firmansyah', role: 'Verified Driver' },
  { id: 5, rating: 5, comment: 'Customer service cepat tanggap. Ada masalah langsung diselesaikan, tidak perlu nunggu lama.', name: 'Dewi Kusuma', role: 'Verified Buyer' },
  { id: 6, rating: 4, comment: 'Pilihan produk elektroniknya lengkap banget. Bisa bandingkan harga dari banyak toko sekaligus, mantap!', name: 'Fajar Nugroho', role: 'Verified Buyer' },
]
const loading = ref(true)

const tabs = ['Semua', 'Smartphone', 'Laptop', 'Audio', 'Gaming']
const activeTab = ref('Semua')

const categories = [
  { name: 'Smartphone', count: '240+ produk', emoji: '📱', bg: '#fff0f4' },
  { name: 'Laptop',     count: '185+ produk', emoji: '💻', bg: '#f0f9ff' },
  { name: 'Audio',      count: '120+ produk', emoji: '🎧', bg: '#fffbeb' },
  { name: 'Wearable',   count: '96+ produk',  emoji: '⌚', bg: '#f0fdf4' },
  { name: 'Gaming',     count: '310+ produk', emoji: '🎮', bg: '#fff1f2' },
  { name: 'Kamera',     count: '78+ produk',  emoji: '📷', bg: '#f5f3ff' },
]

const features = [
  { icon: '🚚', title: 'Pengiriman Cepat',  sub: 'Driver siap antar ke lokasimu' },
  { icon: '🛡️', title: 'Transaksi Aman',    sub: 'Pembayaran terlindungi' },
  { icon: '↩️', title: 'Garansi Retur',     sub: '7 hari pengembalian mudah' },
  { icon: '💬', title: 'Support 24/7',      sub: 'Tim kami siap membantu' },
]

const roles = [
  {
    title: 'Pembeli', tag: 'Belanja Sekarang',
    desc: 'Temukan produk terbaik dari berbagai toko dengan harga terjangkau dan pengiriman cepat.',
    perks: ['Ribuan produk pilihan', 'Pengiriman express', 'Lacak pesanan real-time', 'Garansi retur 7 hari'],
    icon: ShoppingCart, iconBg: '#eff6ff', iconColor: '#1d4ed8',
  },
  {
    title: 'Penjual', tag: 'Buka Toko',
    desc: 'Buka toko, kelola produk, dan terima pesanan dengan mudah dari satu dashboard terintegrasi.',
    perks: ['Toko gratis tanpa biaya', 'Dashboard penjualan lengkap', 'Kelola stok & pesanan', 'Laporan pendapatan otomatis'],
    icon: Store, iconBg: '#f0fdf4', iconColor: '#15803d',
  },
  {
    title: 'Driver', tag: 'Jadi Driver',
    desc: 'Ambil job pengiriman kapan saja dan raih penghasilan tambahan yang fleksibel sesuai jadwalmu.',
    perks: ['Jam kerja fleksibel', 'Order masuk otomatis', 'Pencairan harian', 'Rute navigasi terintegrasi'],
    icon: Truck, iconBg: '#fff7ed', iconColor: '#c2410c',
  },
  {
    title: 'Admin', tag: 'Dashboard',
    desc: 'Pantau seluruh aktivitas marketplace, kelola pengguna, dan kontrol platform dari satu tempat.',
    perks: ['Pantau semua transaksi', 'Kelola voucher & promo', 'Moderasi pengguna', 'Laporan & analitik'],
    icon: ShieldCheck, iconBg: '#f5f3ff', iconColor: '#7c3aed',
  },
]

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

onMounted(async () => {
  try {
    const [prodRes, reviewRes] = await Promise.allSettled([
      api.get('/products', { params: { per_page: 5 } }),
      api.get('/reviews',  { params: { per_page: 3 } }),
    ])
    if (prodRes.status === 'fulfilled')   products.value     = prodRes.value.data.data   || prodRes.value.data
    if (reviewRes.status === 'fulfilled') latestReviews.value = reviewRes.value.data.data || reviewRes.value.data
  } catch {}
  finally { loading.value = false }
})
</script>

<style scoped>
:root {
  --bg:         #fdf2f5;
  --bg-white:   #ffffff;
  --fg:         #1a1a1a;
  --fg-2:       #4b1f30;
  --muted:      #a06070;
  --border:     #f3e0e6;
  --primary:    #c41952;
  --primary-dk: #a0133f;
  --primary-bg: #fce4ec;
  --primary-lt: #fff0f4;
}

.container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
.brand-font { font-family: 'Brawler', serif; }

/* ── HERO ── */
.hero {
  background: #fff;
  padding: 0; overflow: hidden; position: relative;
  min-height: 560px; display: flex; align-items: center;
  border-bottom: 1px solid #f3e0e6;
}
.hero-glow-1 {
  position: absolute; width: 600px; height: 600px;
  background: radial-gradient(circle, rgba(196,25,82,0.07) 0%, transparent 70%);
  top: -200px; left: -100px; pointer-events: none;
}
.hero-glow-2 {
  position: absolute; width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(196,25,82,0.05) 0%, transparent 70%);
  bottom: -100px; right: 100px; pointer-events: none;
}
.hero-inner {
  max-width: 1200px; margin: 0 auto; padding: 80px 24px;
  display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center;
  position: relative; z-index: 1; width: 100%;
}
.hero-badge {
  display: inline-flex; align-items: center; gap: 6px;
  background: #fce4ec; border: 1px solid #f3c6d4;
  border-radius: 9999px; padding: 5px 14px;
  font-size: 11px; font-weight: 600; letter-spacing: 0.06em;
  color: #c41952; text-transform: uppercase; margin-bottom: 24px;
}
.hero-badge-dot {
  width: 6px; height: 6px; background: #c41952; border-radius: 50%;
  box-shadow: 0 0 6px rgba(196,25,82,0.5); animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.hero-title {
  font-size: clamp(32px, 4.5vw, 56px); font-weight: 900;
  letter-spacing: -0.04em; line-height: 1.05; color: #1a1a1a; margin-bottom: 20px;
}
.accent {
  background: linear-gradient(135deg, #c41952, #e8527a);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.hero-desc { font-size: 15px; color: #a06070; max-width: 440px; line-height: 1.75; margin-bottom: 36px; }
.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-hero-primary {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 13px 28px; border-radius: 10px;
  font-size: 14px; font-weight: 700; color: #fff;
  background: #c41952;
  box-shadow: 0 4px 20px rgba(196,25,82,0.3);
  transition: background .15s, transform .1s;
}
.btn-hero-primary:hover { background: #a0133f; transform: translateY(-1px); }
.btn-hero-outline {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 13px 28px; border-radius: 10px;
  font-size: 14px; font-weight: 600; color: #4b1f30;
  background: #fdf2f5; border: 1.5px solid #f3e0e6;
  transition: border-color .15s, color .15s, background .15s;
}
.btn-hero-outline:hover { border-color: #c41952; color: #c41952; background: #fff0f4; }
.hero-stats {
  display: flex; gap: 36px; margin-top: 48px;
  padding-top: 36px; border-top: 1px solid #f3e0e6;
}
.stat-num { font-size: 26px; font-weight: 800; color: #1a1a1a; letter-spacing: -0.04em; line-height: 1; }
.stat-label { font-size: 11px; color: #a06070; margin-top: 4px; font-weight: 500; }

.hero-visual { position: relative; display: flex; justify-content: center; align-items: center; }
.hero-device {
  width: 320px; height: 320px;
  background: linear-gradient(135deg, #fff0f4 0%, #fdf2f5 100%);
  border-radius: 24px; border: 1px solid #f3c6d4;
  box-shadow: 0 8px 40px rgba(196,25,82,0.08), 0 20px 60px rgba(0,0,0,0.04);
  display: flex; align-items: center; justify-content: center;
  position: relative; overflow: hidden;
}
.device-svg { width: 160px; height: 160px; }
.device-label {
  position: absolute; bottom: 20px; left: 0; right: 0; text-align: center;
  font-size: 12px; font-weight: 600; color: #a06070; letter-spacing: 0.05em;
}
.hero-float {
  position: absolute; background: #fff; border: 1px solid #f3e0e6;
  border-radius: 12px; padding: 10px 14px;
  box-shadow: 0 4px 16px rgba(196,25,82,0.08);
}
.float-1 { top: 20px; left: -20px; }
.float-2 { bottom: 40px; right: -20px; }
.float-val { font-size: 16px; font-weight: 800; color: #c41952; }
.float-label { color: #a06070; font-size: 11px; margin-top: 2px; font-weight: 500; }

/* ── SECTION COMMONS ── */
.section-label {
  font-size: 11px; font-weight: 600; text-transform: uppercase;
  letter-spacing: 0.08em; color: #c41952; margin-bottom: 10px; display: block;
}
.section-title {
  font-size: clamp(20px, 3vw, 30px); font-weight: 800;
  letter-spacing: -0.03em; color: #1a1a1a; margin-bottom: 32px;
}
.section-center { text-align: center; margin-bottom: 48px; }
.section-sub { font-size: 15px; color: #a06070; max-width: 480px; margin: 10px auto 0; }

/* ── CATEGORIES ── */
.categories { padding: 64px 0; background: #fdf2f5; border-bottom: 1px solid #f3e0e6; }
.categories-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; }
.cat-card {
  background: #fff; border: 1px solid #f3e0e6;
  border-radius: 16px; padding: 24px 16px; text-align: center;
  cursor: pointer; transition: background .2s, border-color .2s, transform .2s, box-shadow .2s;
  text-decoration: none;
}
.cat-card:hover {
  background: #fff0f4; border-color: #f3c6d4;
  transform: translateY(-3px); box-shadow: 0 8px 20px rgba(196,25,82,0.08);
}
.cat-icon {
  width: 52px; height: 52px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 14px; font-size: 24px;
}
.cat-name { font-size: 13px; font-weight: 600; color: #1a1a1a; margin-bottom: 4px; }
.cat-count { font-size: 11px; color: #a06070; }

/* ── PROMO BANNER ── */
.promo-banner { padding: 48px 0; background: #fff; border-bottom: 1px solid #f3e0e6; }
.promo-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
.promo-card {
  border-radius: 20px; padding: 40px; position: relative; overflow: hidden;
  min-height: 200px; display: flex; flex-direction: column; justify-content: flex-end;
}
.promo-card-main { background: linear-gradient(135deg, #c41952 0%, #8b0f36 100%); }
.promo-card-side { background: #fdf2f5; border: 1px solid #f3e0e6; }
.promo-discount {
  position: absolute; top: 50%; right: 28px; transform: translateY(-50%);
  font-size: 96px; font-weight: 900; color: rgba(255,255,255,0.25);
  letter-spacing: -0.06em; line-height: 1; pointer-events: none; user-select: none;
}
.promo-tag {
  display: inline-block; background: rgba(255,255,255,0.2);
  border-radius: 6px; padding: 3px 10px;
  font-size: 11px; font-weight: 600; color: #fff; letter-spacing: 0.05em;
  text-transform: uppercase; margin-bottom: 12px;
}
.promo-title { font-size: 24px; font-weight: 800; color: #fff; letter-spacing: -0.03em; line-height: 1.2; margin-bottom: 8px; }
.promo-sub { font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 20px; }
.promo-tag-side {
  display: inline-block; background: #fce4ec; border-radius: 6px; padding: 3px 10px;
  font-size: 11px; font-weight: 600; color: #c41952; letter-spacing: 0.05em;
  text-transform: uppercase; margin-bottom: 12px;
}
.promo-title-side { font-size: 22px; font-weight: 800; color: #1a1a1a; letter-spacing: -0.03em; line-height: 1.2; margin-bottom: 8px; }
.promo-sub-side { font-size: 13px; color: #a06070; margin-bottom: 20px; }
.btn-promo {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 20px; border-radius: 8px;
  font-size: 13px; font-weight: 600; color: #c41952;
  background: #fff; width: fit-content; transition: opacity .15s; text-decoration: none;
}
.btn-promo:hover { opacity: .88; }
.btn-promo-side {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 20px; border-radius: 8px;
  font-size: 13px; font-weight: 600; color: #fff;
  background: #c41952; width: fit-content; transition: background .15s; text-decoration: none;
}
.btn-promo-side:hover { background: #a0133f; }

/* ── PRODUCTS ── */
.products-section { padding: 64px 0; background: #fdf2f5; }
.products-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 28px; }
.btn-see-all {
  font-size: 13px; font-weight: 600; color: #c41952;
  background: #fce4ec; border: 1px solid #f3c6d4;
  border-radius: 8px; padding: 7px 16px; transition: background .15s; text-decoration: none;
}
.btn-see-all:hover { background: #f9d0dc; }

.product-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
.tab {
  padding: 7px 18px; border-radius: 9999px;
  font-size: 13px; font-weight: 600; cursor: pointer;
  border: none; transition: all .15s;
}
.tab.active { background: #c41952; color: #fff; }
.tab:not(.active) {
  background: #fff; border: 1px solid #f3e0e6; color: #a06070;
}
.tab:not(.active):hover { color: #c41952; border-color: #f3c6d4; background: #fff0f4; }

.products-grid {
  display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px;
}
.product-card {
  background: #fff; border: 1px solid #f3e0e6;
  border-radius: 16px; overflow: hidden; cursor: pointer;
  transition: border-color .2s, box-shadow .2s, transform .2s;
}
.product-card:hover {
  border-color: #f3c6d4; box-shadow: 0 8px 24px rgba(196,25,82,0.08);
  transform: translateY(-3px);
}
.product-img {
  aspect-ratio: 1; background: #fdf2f5;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden; position: relative;
}
.product-img img { width: 100%; height: 100%; object-fit: cover; }
.product-badge {
  position: absolute; top: 10px; left: 10px;
  background: #c41952; color: #fff;
  font-size: 10px; font-weight: 700; letter-spacing: 0.05em;
  padding: 3px 8px; border-radius: 6px;
}
.product-badge.new { background: #0369a1; }
.product-badge.hot { background: #b45309; }
.product-body { padding: 14px; }
.product-store { font-size: 11px; color: #a06070; margin-bottom: 3px; font-weight: 500; }
.product-name {
  font-size: 13px; font-weight: 600; color: #1a1a1a; line-height: 1.4; margin-bottom: 6px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.product-rating { display: flex; align-items: center; gap: 2px; margin-bottom: 10px; }
.stars { color: #f59e0b; font-size: 12px; letter-spacing: -1px; }
.stars-empty { color: #f3e0e6; font-size: 12px; }
.rating-count { font-size: 11px; color: #a06070; margin-left: 4px; }
.product-footer { display: flex; align-items: center; justify-content: space-between; }
.product-price { font-size: 15px; font-weight: 800; color: #c41952; }
.product-price-orig { font-size: 11px; color: #c4a8b4; text-decoration: line-through; margin-top: 1px; }
.product-stock { font-size: 11px; color: #a06070; }
.out-of-stock { font-size: 11px; color: #ef4444; font-weight: 600; }
.add-to-cart {
  width: 34px; height: 34px; border-radius: 8px;
  background: #c41952; color: #fff;
  border: none; cursor: pointer; font-size: 20px; font-weight: 300;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; transition: background .15s;
}
.add-to-cart:hover { background: #a0133f; }

/* Skeleton */
.skeleton {
  background: linear-gradient(90deg, #fce4ec 25%, #f9d0dc 50%, #fce4ec 75%);
  background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 6px;
}
.img-skeleton { aspect-ratio: 1; border-radius: 0; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.empty-state {
  grid-column: 1/-1; text-align: center; padding: 48px 24px; color: #a06070;
  display: flex; flex-direction: column; align-items: center; gap: 12px;
}

/* ── FEATURES STRIP ── */
.features-strip {
  padding: 40px 0; background: #fff;
  border-top: 1px solid #f3e0e6; border-bottom: 1px solid #f3e0e6;
}
.features-strip-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.feature-strip-item { display: flex; align-items: center; gap: 14px; }
.feature-strip-icon {
  width: 44px; height: 44px; flex-shrink: 0;
  border-radius: 12px; background: #fce4ec;
  display: flex; align-items: center; justify-content: center; font-size: 20px;
}
.feature-strip-title { font-size: 13px; font-weight: 700; color: #1a1a1a; margin-bottom: 2px; }
.feature-strip-sub { font-size: 12px; color: #a06070; }

/* ── ROLES ── */
.roles-section { padding: 72px 0; background: #fdf2f5; }
.roles-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.role-card {
  background: #fff; border: 1px solid #f3e0e6;
  border-radius: 20px; padding: 32px 24px; text-align: center;
  transition: border-color .2s, transform .2s, box-shadow .2s;
}
.role-card:hover {
  border-color: #f3c6d4; transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(196,25,82,0.08);
}
.role-icon {
  width: 52px; height: 52px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 18px;
}
.role-title { font-size: 16px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
.role-desc { font-size: 13px; color: #a06070; line-height: 1.65; margin-bottom: 16px; }
.role-perks { list-style: none; margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; text-align: left; }
.role-perk-item {
  display: flex; align-items: center; gap: 8px;
  font-size: 12px; font-weight: 500; color: #4b1f30;
}
.perk-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.role-tag {
  display: inline-block; margin-top: 16px;
  padding: 4px 14px; border-radius: 20px;
  font-size: 11px; font-weight: 600; letter-spacing: 0.04em;
}

/* ── REVIEWS ── */
.reviews-section { padding: 72px 0; background: #fff; border-top: 1px solid #f3e0e6; }
.reviews-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 32px; }
.reviews-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.review-card { background: #fdf2f5; border: 1px solid #f3e0e6; border-radius: 16px; padding: 24px; }
.review-stars { display: flex; gap: 2px; margin-bottom: 14px; }
.review-text { font-size: 14px; color: #4b1f30; line-height: 1.7; margin-bottom: 18px; }
.review-author { display: flex; align-items: center; gap: 10px; }
.review-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: #c41952;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.review-name { font-size: 13px; font-weight: 600; color: #1a1a1a; }
.review-role { font-size: 11px; color: #a06070; }

/* ── CTA ── */
.cta-section {
  padding: 80px 0; position: relative; overflow: hidden;
  background: linear-gradient(135deg, #c41952 0%, #8b0f36 100%);
}
.cta-glow {
  position: absolute; width: 600px; height: 600px;
  background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
  top: 50%; left: 50%; transform: translate(-50%,-50%); pointer-events: none;
}
.cta-title {
  font-size: clamp(28px, 4vw, 48px); font-weight: 900; color: #fff;
  letter-spacing: -0.04em; margin-bottom: 16px;
}
.cta-sub {
  font-size: 15px; color: rgba(255,255,255,0.7); margin-bottom: 36px;
  max-width: 500px; margin-left: auto; margin-right: auto;
}
.cta-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.btn-cta-white {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 13px 28px; border-radius: 10px;
  font-size: 14px; font-weight: 700; color: #c41952;
  background: #fff; transition: opacity .15s; text-decoration: none;
}
.btn-cta-white:hover { opacity: .92; }
.btn-cta-outline {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 13px 28px; border-radius: 10px;
  font-size: 14px; font-weight: 600; color: rgba(255,255,255,0.85);
  background: rgba(255,255,255,0.12); border: 1.5px solid rgba(255,255,255,0.25);
  transition: background .15s; text-decoration: none;
}
.btn-cta-outline:hover { background: rgba(255,255,255,0.2); }
</style>
