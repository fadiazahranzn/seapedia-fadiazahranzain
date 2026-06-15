<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Job Tersedia</h1>
      <p class="text-muted-foreground text-sm mt-1">Cari dan ambil job pengiriman</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
      <Card>
        <CardContent class="pt-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
              <Truck class="w-5 h-5 text-orange-600" />
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Job Aktif</p>
              <p class="font-bold text-lg">coming soon</p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="pt-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
              <DollarSign class="w-5 h-5 text-green-600" />
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Total Penghasilan</p>
              <p class="font-bold text-lg">coming soon</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card>
      <CardContent class="pt-6 text-center py-12 text-muted-foreground">
        <Truck class="w-12 h-12 mx-auto mb-3 opacity-30" />
        <p class="font-medium">Fitur Driver — Level 5</p>
        <p class="text-sm mt-1">Pencarian job, ambil job, dan konfirmasi pengiriman akan tersedia di level berikutnya.</p>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { Truck, DollarSign } from '@lucide/vue'
import { Card, CardContent } from '@/components/ui/card'
</script>
