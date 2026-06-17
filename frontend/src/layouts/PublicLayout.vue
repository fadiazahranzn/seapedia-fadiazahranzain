<template>
  <div class="min-h-screen flex flex-col bg-white">
    <!-- Navbar -->
    <header class="navbar">
      <div class="navbar-inner">
        <RouterLink to="/" class="nav-brand">
          <div class="nav-brand-icon">S</div>
          SEAPEDIA
        </RouterLink>

        <nav class="nav-links">
          <RouterLink to="/products">Produk</RouterLink>
          <RouterLink to="/reviews">Ulasan</RouterLink>
        </nav>

        <div class="nav-actions">
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
        <RouterLink to="/products" @click="mobileOpen = false">Produk</RouterLink>
        <RouterLink to="/reviews" @click="mobileOpen = false">Ulasan</RouterLink>
        <template v-if="!auth.isLoggedIn">
          <RouterLink to="/login" @click="mobileOpen = false">Masuk</RouterLink>
          <RouterLink to="/register" @click="mobileOpen = false">Daftar</RouterLink>
        </template>
      </div>
    </header>

    <main class="flex-1">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer-inner">
        <div class="footer-brand">
          <div class="nav-brand-icon" style="width:28px;height:28px;font-size:12px;">S</div>
          SEAPEDIA
        </div>
        <p class="footer-copy">© 2025 SEAPEDIA. Platform marketplace multi-peran.</p>
        <div class="footer-links">
          <RouterLink to="/products">Produk</RouterLink>
          <RouterLink to="/reviews">Ulasan</RouterLink>
          <RouterLink to="/register">Daftar</RouterLink>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView } from 'vue-router'
import { Menu } from '@lucide/vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const mobileOpen = ref(false)
</script>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255,255,255,0.92);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid #e2e8f0;
  height: 64px;
  display: flex;
  align-items: center;
}
.navbar-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}
.nav-brand {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.03em;
  text-decoration: none;
}
.nav-brand-icon {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, #6366f1, #06b6d4);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  flex-shrink: 0;
}
.nav-links {
  display: none;
  align-items: center;
  gap: 4px;
}
@media (min-width: 768px) { .nav-links { display: flex; } }
.nav-links a {
  font-size: 14px;
  font-weight: 500;
  color: #64748b;
  padding: 6px 12px;
  border-radius: 0.5rem;
  transition: color .15s, background .15s;
  text-decoration: none;
}
.nav-links a:hover, .nav-links a.router-link-active { color: #0f172a; background: #f1f5f9; }

.nav-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-ghost {
  padding: 7px 16px;
  border-radius: 0.5rem;
  font-size: 14px;
  font-weight: 500;
  color: #334155;
  background: none;
  text-decoration: none;
  transition: background .15s;
}
.btn-ghost:hover { background: #f1f5f9; }
.btn-primary-sm {
  padding: 7px 16px;
  border-radius: 0.5rem;
  font-size: 14px;
  font-weight: 600;
  color: #fff;
  background: #6366f1;
  text-decoration: none;
  transition: background .15s, box-shadow .15s;
  box-shadow: 0 1px 3px rgb(99 102 241 / .3);
}
.btn-primary-sm:hover { background: #4f46e5; }
.role-badge {
  font-size: 12px;
  font-weight: 600;
  color: #6366f1;
  background: #eef2ff;
  border: 1px solid #c7d2fe;
  border-radius: 9999px;
  padding: 3px 10px;
  display: none;
}
@media (min-width: 640px) { .role-badge { display: inline-block; } }
.mobile-menu-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
  border-radius: 0.5rem;
  background: none;
  border: none;
  cursor: pointer;
  color: #64748b;
  transition: background .15s;
}
.mobile-menu-btn:hover { background: #f1f5f9; }
@media (min-width: 768px) { .mobile-menu-btn { display: none; } }

.mobile-nav {
  border-top: 1px solid #e2e8f0;
  padding: 12px 24px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  background: #fff;
}
.mobile-nav a {
  display: block;
  padding: 8px 12px;
  font-size: 14px;
  color: #64748b;
  text-decoration: none;
  border-radius: 0.5rem;
  transition: background .15s, color .15s;
}
.mobile-nav a:hover { background: #f1f5f9; color: #0f172a; }

/* Footer */
.footer {
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  padding: 32px 24px;
}
.footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.footer-brand {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  font-size: 15px;
  color: #0f172a;
}
.footer-copy { font-size: 13px; color: #64748b; }
.footer-links {
  display: flex;
  gap: 20px;
}
.footer-links a {
  font-size: 13px;
  color: #64748b;
  text-decoration: none;
  transition: color .15s;
}
.footer-links a:hover { color: #0f172a; }
</style>
