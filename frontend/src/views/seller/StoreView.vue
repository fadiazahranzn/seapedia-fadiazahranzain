<template>
  <div class="max-w-lg">
    <div class="mb-6">
      <h1 class="text-2xl font-bold">{{ store ? 'Edit Toko' : 'Buat Toko' }}</h1>
      <p class="text-muted-foreground text-sm mt-1">{{ store ? 'Perbarui informasi toko kamu' : 'Buat toko untuk mulai berjualan' }}</p>
    </div>

    <Card v-if="loading" class="animate-pulse h-48" />

    <Card v-else>
      <CardContent class="pt-6">
        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-2">
            <Label for="name">Nama Toko <span class="text-destructive">*</span></Label>
            <Input id="name" v-model="form.name" placeholder="Nama toko unik kamu" required />
            <p v-if="errors.name" class="text-destructive text-xs">{{ errors.name }}</p>
          </div>

          <div class="space-y-2">
            <Label for="description">Deskripsi</Label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              placeholder="Ceritakan tentang toko kamu..."
              class="w-full border border-input rounded-md px-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring resize-none"
            />
          </div>

          <div v-if="store" class="p-3 bg-muted/50 rounded-md text-sm text-muted-foreground">
            Slug: <span class="font-mono">/{{ store.slug }}</span>
          </div>

          <p v-if="error" class="text-destructive text-sm">{{ error }}</p>

          <div class="flex gap-3">
            <Button type="submit" :disabled="submitting">
              {{ submitting ? 'Menyimpan...' : store ? 'Simpan Perubahan' : 'Buat Toko' }}
            </Button>
            <Button type="button" variant="outline" @click="router.push('/seller/dashboard')">Batal</Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent } from '@/components/ui/card'
import { sellerApi } from '@/services/seller'
import { toast } from 'vue-sonner'

const router = useRouter()
const store = ref(null)
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const errors = ref({})
const form = ref({ name: '', description: '' })

onMounted(async () => {
  try {
    const { data } = await sellerApi.getStore()
    store.value = data.data
    form.value.name = data.data.name
    form.value.description = data.data.description || ''
  } catch {}
  finally { loading.value = false }
})

async function submit() {
  submitting.value = true
  error.value = ''
  errors.value = {}
  try {
    if (store.value) {
      await sellerApi.updateStore(form.value)
      toast.success('Toko berhasil diperbarui!')
    } else {
      await sellerApi.createStore(form.value)
      toast.success('Toko berhasil dibuat!')
    }
    router.push('/seller/dashboard')
  } catch (e) {
    if (e.response?.data?.errors) {
      errors.value = Object.fromEntries(
        Object.entries(e.response.data.errors).map(([k, v]) => [k, v[0]])
      )
    } else {
      error.value = e.response?.data?.message || 'Gagal menyimpan toko.'
    }
  } finally {
    submitting.value = false
  }
}
</script>
