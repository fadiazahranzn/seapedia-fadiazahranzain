<template>
  <div class="min-h-screen flex items-center justify-center bg-background px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold">Pilih Peran Aktif</h1>
        <p class="text-muted-foreground text-sm mt-1">Kamu memiliki beberapa peran. Pilih yang ingin digunakan sekarang.</p>
      </div>

      <div class="space-y-3">
        <button
          v-for="role in auth.user?.roles"
          :key="role"
          class="w-full flex items-center gap-4 p-4 rounded-lg border-2 transition-all hover:border-primary hover:bg-primary/5"
          :class="role === auth.activeRole ? 'border-primary bg-primary/5' : 'border-border'"
          @click="selectRole(role)"
          :disabled="switching"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="roleConfig[role]?.bg">
            <component :is="roleConfig[role]?.icon" class="w-5 h-5" :class="roleConfig[role]?.color" />
          </div>
          <div class="text-left">
            <p class="font-semibold capitalize">{{ role }}</p>
            <p class="text-xs text-muted-foreground">{{ roleConfig[role]?.desc }}</p>
          </div>
          <Badge v-if="role === auth.activeRole" class="ml-auto">Aktif</Badge>
        </button>
      </div>

      <Button variant="ghost" size="sm" class="w-full mt-4" @click="handleLogout">Keluar</Button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ShoppingCart, Store, Truck, ShieldCheck } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue-sonner'

const auth = useAuthStore()
const router = useRouter()
const switching = ref(false)

const roleConfig = {
  buyer: { icon: ShoppingCart, bg: 'bg-blue-100', color: 'text-blue-600', desc: 'Belanja produk dari berbagai toko' },
  seller: { icon: Store, bg: 'bg-green-100', color: 'text-green-600', desc: 'Kelola toko dan produkmu' },
  driver: { icon: Truck, bg: 'bg-orange-100', color: 'text-orange-600', desc: 'Ambil dan antar pesanan' },
  admin: { icon: ShieldCheck, bg: 'bg-purple-100', color: 'text-purple-600', desc: 'Monitor seluruh marketplace' },
}

async function selectRole(role) {
  if (role === auth.activeRole) { router.push(`/${role}`); return }
  switching.value = true
  try {
    await auth.switchRole(role)
    toast.success(`Peran aktif: ${role}`)
    router.push(`/${role}`)
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal mengganti peran.')
  } finally {
    switching.value = false
  }
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
