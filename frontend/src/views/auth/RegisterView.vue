<template>
  <div class="min-h-screen flex items-center justify-center bg-background px-4">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold tracking-tight">SEAPEDIA</h1>
        <p class="text-muted-foreground mt-1 text-sm">Buat akun baru</p>
      </div>

      <Card>
        <CardContent class="pt-6">
          <form @submit.prevent="handleRegister" class="space-y-4">
            <div class="space-y-2">
              <Label for="name">Nama Lengkap</Label>
              <Input id="name" v-model="form.name" placeholder="Nama kamu" required />
            </div>

            <div class="space-y-2">
              <Label for="username">Username</Label>
              <Input id="username" v-model="form.username" placeholder="username" required />
            </div>

            <div class="space-y-2">
              <Label for="email">Email</Label>
              <Input id="email" v-model="form.email" type="email" placeholder="email@contoh.com" required />
            </div>

            <div class="space-y-2">
              <Label for="password">Password</Label>
              <Input id="password" v-model="form.password" type="password" placeholder="••••••••" required />
            </div>

            <div class="space-y-2">
              <Label for="password_confirmation">Konfirmasi Password</Label>
              <Input id="password_confirmation" v-model="form.password_confirmation" type="password" placeholder="••••••••" required />
            </div>

            <div class="space-y-2">
              <Label for="role">Daftar sebagai</Label>
              <select
                id="role"
                v-model="form.role"
                class="w-full border border-input rounded-md px-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                required
              >
                <option value="">Pilih role</option>
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>
                <option value="driver">Driver</option>
              </select>
            </div>

            <p v-if="error" class="text-destructive text-sm">{{ error }}</p>

            <Button type="submit" class="w-full" :disabled="loading">
              {{ loading ? 'Memproses...' : 'Daftar' }}
            </Button>
          </form>
        </CardContent>
      </Card>

      <p class="text-center text-sm text-muted-foreground mt-4">
        Sudah punya akun?
        <RouterLink to="/login" class="text-primary font-medium hover:underline">Masuk</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import { useAuthStore } from '@/stores/auth'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const auth = useAuthStore()
const router = useRouter()

const form = ref({ name: '', username: '', email: '', password: '', password_confirmation: '', role: '' })
const error = ref('')
const loading = ref(false)

async function handleRegister() {
  error.value = ''
  if (form.value.password.length < 6) {
    error.value = 'Password minimal 6 karakter.'
    return
  }
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Konfirmasi password tidak cocok.'
    return
  }
  loading.value = true
  try {
    await auth.register(form.value)
    toast.success('Registrasi berhasil!')
    router.push(`/${auth.activeRole}`)
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors ? Object.values(errors).flat().join(' ') : 'Registrasi gagal.'
  } finally {
    loading.value = false
  }
}
</script>
