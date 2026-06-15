<template>
  <div class="min-h-screen flex flex-col bg-background">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <RouterLink to="/" class="flex items-center gap-2 font-bold text-xl text-primary">
          <ShoppingBag class="w-6 h-6" />
          SEAPEDIA
        </RouterLink>

        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-6 text-sm">
          <RouterLink to="/products" class="text-muted-foreground hover:text-foreground transition-colors">Produk</RouterLink>
          <RouterLink to="/reviews" class="text-muted-foreground hover:text-foreground transition-colors">Ulasan</RouterLink>
        </nav>

        <div class="flex items-center gap-2">
          <template v-if="!auth.isLoggedIn">
            <Button variant="ghost" size="sm" as-child>
              <RouterLink to="/login">Masuk</RouterLink>
            </Button>
            <Button size="sm" as-child>
              <RouterLink to="/register">Daftar</RouterLink>
            </Button>
          </template>
          <template v-else>
            <Badge variant="outline" class="hidden sm:flex capitalize">{{ auth.activeRole }}</Badge>
            <Button size="sm" as-child>
              <RouterLink :to="`/${auth.activeRole}`">Dashboard</RouterLink>
            </Button>
          </template>

          <!-- Mobile menu -->
          <button class="md:hidden p-2 rounded-md hover:bg-muted" @click="mobileOpen = !mobileOpen">
            <Menu class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Mobile nav -->
      <div v-if="mobileOpen" class="md:hidden border-t px-4 py-3 space-y-2 bg-background">
        <RouterLink to="/products" class="block py-2 text-sm text-muted-foreground hover:text-foreground" @click="mobileOpen = false">Produk</RouterLink>
        <RouterLink to="/reviews" class="block py-2 text-sm text-muted-foreground hover:text-foreground" @click="mobileOpen = false">Ulasan</RouterLink>
      </div>
    </header>

    <main class="flex-1">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="border-t bg-muted/30 py-8 mt-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-2 font-bold text-primary">
            <ShoppingBag class="w-5 h-5" />
            SEAPEDIA
          </div>
          <p class="text-sm text-muted-foreground">© 2025 SEAPEDIA. Platform marketplace multi-peran.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView } from 'vue-router'
import { ShoppingBag, Menu } from '@lucide/vue'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

const auth = useAuthStore()
const mobileOpen = ref(false)
</script>
