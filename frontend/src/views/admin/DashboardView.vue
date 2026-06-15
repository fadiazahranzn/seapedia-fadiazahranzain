<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Dashboard Admin</h1>
      <p class="text-muted-foreground text-sm mt-1">Monitor seluruh aktivitas SEAPEDIA</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
      <Card v-for="stat in stats" :key="stat.label">
        <CardContent class="pt-6">
          <div class="w-9 h-9 rounded-lg flex items-center justify-center mb-3" :class="stat.bg">
            <component :is="stat.icon" class="w-5 h-5" :class="stat.color" />
          </div>
          <p class="text-sm text-muted-foreground">{{ stat.label }}</p>
          <p class="font-bold text-lg">coming soon</p>
        </CardContent>
      </Card>
    </div>

    <Card>
      <CardContent class="pt-6 text-center py-12 text-muted-foreground">
        <ShieldCheck class="w-12 h-12 mx-auto mb-3 opacity-30" />
        <p class="font-medium">Admin Monitoring — Level 6</p>
        <p class="text-sm mt-1">Monitoring lengkap dan manajemen voucher/promo akan tersedia di level berikutnya.</p>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { Users, Package, ClipboardList, Tag, Truck, ShieldCheck } from '@lucide/vue'
import { Card, CardContent } from '@/components/ui/card'

const stats = [
  { label: 'Pengguna', icon: Users, bg: 'bg-blue-100', color: 'text-blue-600' },
  { label: 'Produk', icon: Package, bg: 'bg-green-100', color: 'text-green-600' },
  { label: 'Pesanan', icon: ClipboardList, bg: 'bg-orange-100', color: 'text-orange-600' },
  { label: 'Voucher & Promo', icon: Tag, bg: 'bg-purple-100', color: 'text-purple-600' },
]
</script>
