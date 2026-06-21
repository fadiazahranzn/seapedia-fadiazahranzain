<template>
  <div class="layout">
    <!-- TOPBAR -->
    <div class="topbar">
      <strong>🎉 Flash Sale!</strong> Diskon hingga 50% untuk produk pilihan hari ini.
    </div>

    <!-- NAVBAR -->
    <header class="navbar">
      <div class="navbar-inner">
        <RouterLink to="/" class="nav-brand">
          <div class="nav-brand-icon">S</div>
          <span class="brand-text">SEAPEDIA</span>
        </RouterLink>

        <nav class="nav-links">
          <RouterLink to="/">Beranda</RouterLink>
          <RouterLink to="/products">Produk</RouterLink>
        </nav>

        <div class="nav-actions">
          <NotificationDropdown v-if="auth.isLoggedIn" :iconSize="16" />

          <RouterLink to="/buyer/cart" class="nav-icon-btn" v-if="auth.isLoggedIn && auth.activeRole === 'buyer'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          </RouterLink>

          <template v-if="!auth.isLoggedIn">
            <RouterLink to="/login" class="btn-ghost">Masuk</RouterLink>
            <RouterLink to="/register" class="btn-primary-sm">Daftar</RouterLink>
          </template>
          <template v-else>
            <span class="role-badge capitalize">{{ auth.activeRole }}</span>
            <RouterLink :to="`/${auth.activeRole}`" class="btn-primary-sm">Dashboard</RouterLink>
          </template>

          <button class="mobile-menu-btn" @click="mobileOpen = !mobileOpen">
            <Menu :size="20" />
          </button>
        </div>
      </div>

      <!-- Mobile nav -->
      <div v-if="mobileOpen" class="mobile-nav">
        <RouterLink to="/" @click="mobileOpen = false">Beranda</RouterLink>
        <RouterLink to="/products" @click="mobileOpen = false">Produk</RouterLink>
        <template v-if="!auth.isLoggedIn">
          <RouterLink to="/login" @click="mobileOpen = false">Masuk</RouterLink>
          <RouterLink to="/register" @click="mobileOpen = false">Daftar</RouterLink>
        </template>
      </div>
    </header>

    <main class="main-content">
      <RouterView />
    </main>

    <!-- FOOTER -->
    <footer class="footer">
      <div class="footer-inner">
        <div class="footer-col-brand">
          <div class="footer-brand">
            <div class="nav-brand-icon" style="width:28px;height:28px;font-size:11px;">S</div>
            <span class="brand-text" style="font-size:15px;">SEAPEDIA</span>
          </div>
          <p class="footer-desc">Platform marketplace multi-peran yang menghubungkan pembeli, penjual, dan driver.</p>
        </div>
        <div class="footer-col">
          <div class="footer-heading">Platform</div>
          <div class="footer-links">
            <RouterLink to="/">Beranda</RouterLink>
            <RouterLink to="/products">Produk</RouterLink>
          </div>
        </div>
        <div class="footer-col">
          <div class="footer-heading">Bergabung</div>
          <div class="footer-links">
            <RouterLink to="/register?role=buyer">Daftar Pembeli</RouterLink>
            <RouterLink to="/register?role=seller">Buka Toko</RouterLink>
            <RouterLink to="/register?role=driver">Jadi Driver</RouterLink>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2025 SEAPEDIA. All rights reserved.</span>
        <span>Built with ❤️ for COMPFEST SEA</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView } from 'vue-router'
import { Menu } from '@lucide/vue'
import { useAuthStore } from '@/stores/auth'
import NotificationDropdown from '@/components/shared/NotificationDropdown.vue'

const auth = useAuthStore()
const mobileOpen = ref(false)
</script>

<style scoped>
.layout { min-height: 100vh; display: flex; flex-direction: column; background: #fdf2f5; }

/* ── TOPBAR ── */
.topbar {
  background: #c41952; text-align: center;
  padding: 8px 24px; font-size: 12px; font-weight: 500; color: #fff;
}
.topbar strong { font-weight: 700; }

/* ── NAVBAR ── */
.navbar {
  position: sticky; top: 0; z-index: 50;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid #f3e0e6;
  display: flex; flex-direction: column;
}
.navbar-inner {
  max-width: 1200px; margin: 0 auto; padding: 0 24px;
  width: 100%; height: 64px;
  display: flex; align-items: center; justify-content: space-between; gap: 24px;
}
.nav-brand {
  display: flex; align-items: center; gap: 8px;
  text-decoration: none; flex-shrink: 0;
}
.brand-text {
  font-family: 'Brawler', serif;
  font-size: 18px; font-weight: 700; color: #1a1a1a; letter-spacing: 0.01em;
}
.nav-brand-icon {
  width: 32px; height: 32px; background: #c41952;
  border-radius: 8px; display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 13px; font-weight: 900; flex-shrink: 0;
}
.nav-links {
  display: none; align-items: center; gap: 2px;
}
@media (min-width: 768px) { .nav-links { display: flex; } }
.nav-links a {
  font-size: 13px; font-weight: 500; color: #a06070;
  padding: 6px 12px; border-radius: 8px;
  transition: color .15s, background .15s; text-decoration: none;
}
.nav-links a:hover,
.nav-links a.router-link-exact-active { color: #c41952; background: #fce4ec; }

.nav-actions { display: flex; align-items: center; gap: 8px; }

.nav-icon-btn {
  width: 36px; height: 36px; border-radius: 8px;
  background: #fdf2f5; border: 1px solid #f3e0e6;
  display: flex; align-items: center; justify-content: center;
  color: #a06070; transition: background .15s, color .15s; text-decoration: none;
}
.nav-icon-btn:hover { background: #fce4ec; color: #c41952; }

.btn-ghost {
  padding: 7px 16px; border-radius: 8px;
  font-size: 13px; font-weight: 500; color: #4b1f30;
  background: none; text-decoration: none; transition: background .15s;
}
.btn-ghost:hover { background: #fce4ec; color: #c41952; }

.btn-primary-sm {
  padding: 7px 16px; border-radius: 8px;
  font-size: 13px; font-weight: 600; color: #fff;
  background: #c41952; text-decoration: none;
  transition: background .15s;
}
.btn-primary-sm:hover { background: #a0133f; }

.role-badge {
  font-size: 12px; font-weight: 600; color: #c41952;
  background: #fce4ec; border: 1px solid #f3c6d4;
  border-radius: 9999px; padding: 3px 10px; display: none;
}
@media (min-width: 640px) { .role-badge { display: inline-block; } }

.mobile-menu-btn {
  display: flex; align-items: center; justify-content: center;
  padding: 6px; border-radius: 8px;
  background: none; border: none; cursor: pointer;
  color: #a06070; transition: background .15s;
}
.mobile-menu-btn:hover { background: #fce4ec; color: #c41952; }
@media (min-width: 768px) { .mobile-menu-btn { display: none; } }

.mobile-nav {
  border-top: 1px solid #f3e0e6; padding: 12px 24px;
  display: flex; flex-direction: column; gap: 4px; background: #fff;
}
.mobile-nav a {
  display: block; padding: 8px 12px; font-size: 14px;
  color: #a06070; text-decoration: none; border-radius: 8px;
  transition: background .15s, color .15s;
}
.mobile-nav a:hover { background: #fce4ec; color: #c41952; }

/* ── MAIN ── */
.main-content { flex: 1; }

/* ── FOOTER ── */
.footer { background: #fff; border-top: 1px solid #f3e0e6; padding: 48px 24px 0; }
.footer-inner {
  max-width: 1200px; margin: 0 auto;
  display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px;
  padding-bottom: 40px;
}
.footer-brand {
  display: flex; align-items: center; gap: 8px; margin-bottom: 12px;
}
.footer-desc { font-size: 13px; color: #a06070; line-height: 1.7; max-width: 280px; }
.footer-heading {
  font-size: 11px; font-weight: 700; color: #1a1a1a;
  text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 14px;
}
.footer-links { display: flex; flex-direction: column; gap: 10px; }
.footer-links a { font-size: 13px; color: #a06070; text-decoration: none; transition: color .15s; }
.footer-links a:hover { color: #c41952; }
.footer-bottom {
  max-width: 1200px; margin: 0 auto;
  padding: 20px 0; border-top: 1px solid #f3e0e6;
  display: flex; justify-content: space-between; align-items: center;
  font-size: 12px; color: #a06070;
}
</style>
