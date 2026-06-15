<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Selamat datang, {{ auth.user?.name }}!</h1>
      <p class="text-muted-foreground text-sm mt-1">Anda masuk sebagai <span class="font-medium text-primary">Buyer</span></p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <Card class="cursor-pointer hover:shadow-md transition-shadow" @click="router.push('/buyer/wallet')">
        <CardContent class="pt-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
              <Wallet class="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Saldo</p>
              <p class="font-bold text-lg">{{ walletLoading ? '...' : formatPrice(wallet?.balance ?? 0) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="cursor-pointer hover:shadow-md transition-shadow" @click="router.push('/buyer/cart')">
        <CardContent class="pt-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
              <ShoppingCart class="w-5 h-5 text-green-600" />
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Keranjang</p>
              <p class="font-bold text-lg">{{ cartCount }} item</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="cursor-pointer hover:shadow-md transition-shadow" @click="router.push('/buyer/orders')">
        <CardContent class="pt-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
              <ClipboardList class="w-5 h-5 text-orange-600" />
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Pesanan</p>
              <p class="font-bold text-lg">Lihat Semua</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="flex gap-3">
      <Button as-child><RouterLink to="/products">Jelajahi Produk</RouterLink></Button>
      <Button variant="outline" as-child><RouterLink to="/buyer/wallet">Top Up Saldo</RouterLink></Button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { Wallet, ShoppingCart, ClipboardList } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { useAuthStore } from '@/stores/auth'
import { buyerApi } from '@/services/buyer'

const auth = useAuthStore()
const router = useRouter()
const wallet = ref(null)
const cart = ref(null)
const walletLoading = ref(true)

const cartCount = computed(() => cart.value?.items?.reduce((s, i) => s + i.quantity, 0) ?? 0)

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p)
}

onMounted(async () => {
  try {
    const [w, c] = await Promise.allSettled([buyerApi.getWallet(), buyerApi.getCart()])
    if (w.status === 'fulfilled') wallet.value = w.value.data.data
    if (c.status === 'fulfilled') cart.value = c.value.data.data
  } finally { walletLoading.value = false }
})
</script>
