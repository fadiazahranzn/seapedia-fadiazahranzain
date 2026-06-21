<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold">{{ store ? 'Edit Toko' : 'Buat Toko' }}</h1>
      <p class="text-muted-foreground text-sm mt-1">
        {{ store ? 'Perbarui informasi toko kamu' : 'Buat toko untuk mulai berjualan di SEAPEDIA' }}
      </p>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 h-80 bg-muted rounded-2xl animate-pulse" />
      <div class="h-48 bg-muted rounded-2xl animate-pulse" />
    </div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Form -->
        <Card class="lg:col-span-2">
          <CardContent class="pt-6">
            <!-- Store identity preview -->
            <div class="flex items-center gap-4 mb-6 pb-5 border-b">
              <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center shrink-0">
                <StoreIcon class="w-7 h-7 text-primary" />
              </div>
              <div>
                <p class="font-semibold text-lg leading-tight">{{ form.name || 'Nama Toko' }}</p>
                <p v-if="store" class="text-xs text-muted-foreground font-mono mt-0.5">/{{ store.slug }}</p>
                <span v-if="store" class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-100 px-2 py-0.5 rounded-full mt-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-green-500" />
                  Toko Aktif
                </span>
              </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
              <!-- Nama Toko -->
              <div class="space-y-1.5">
                <Label for="name" class="text-sm font-medium">
                  Nama Toko <span class="text-destructive">*</span>
                </Label>
                <div class="relative">
                  <StoreIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                  <Input
                    id="name"
                    v-model="form.name"
                    placeholder="Nama toko unik kamu"
                    required
                    class="pl-9"
                  />
                </div>
                <p v-if="errors.name" class="text-destructive text-xs flex items-center gap-1">
                  <AlertCircle class="w-3 h-3" /> {{ errors.name }}
                </p>
              </div>

              <!-- Deskripsi -->
              <div class="space-y-1.5">
                <Label for="description" class="text-sm font-medium">Deskripsi Toko</Label>
                <div class="relative">
                  <FileText class="absolute left-3 top-3 w-4 h-4 text-muted-foreground" />
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="5"
                    placeholder="Ceritakan tentang toko kamu, produk yang dijual, keunggulan, dll..."
                    class="w-full border border-input rounded-md pl-9 pr-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring resize-none"
                  />
                </div>
                <p class="text-xs text-muted-foreground text-right">{{ form.description.length }} karakter</p>
              </div>

              <!-- Error -->
              <div v-if="error" class="flex items-center gap-2 p-3 bg-destructive/10 text-destructive rounded-lg text-sm">
                <AlertCircle class="w-4 h-4 shrink-0" />
                {{ error }}
              </div>

              <!-- Actions -->
              <div class="flex gap-3 pt-1">
                <Button type="submit" :disabled="submitting" class="flex items-center gap-2">
                  <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
                  <Save v-else class="w-4 h-4" />
                  {{ submitting ? 'Menyimpan...' : store ? 'Simpan Perubahan' : 'Buat Toko' }}
                </Button>
                <Button type="button" variant="outline" @click="router.push('/seller/dashboard')">
                  Batal
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <!-- Right: Info sidebar -->
        <div class="space-y-4">
          <!-- Store stats -->
          <Card v-if="store">
            <CardContent class="pt-5 pb-5">
              <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-4">Info Toko</p>
              <div class="space-y-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                    <Link class="w-4 h-4 text-blue-600" />
                  </div>
                  <div>
                    <p class="text-xs text-muted-foreground">URL Toko</p>
                    <p class="font-mono text-xs text-foreground">/{{ store.slug }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                    <Package class="w-4 h-4 text-orange-600" />
                  </div>
                  <div>
                    <p class="text-xs text-muted-foreground">Total Produk</p>
                    <p class="font-semibold text-sm">{{ store.products?.length ?? 0 }}</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Tips card -->
          <Card class="bg-primary/5 border-primary/20">
            <CardContent class="pt-5 pb-5">
              <p class="text-xs font-semibold text-primary uppercase tracking-wide mb-3">Tips</p>
              <ul class="space-y-2 text-xs text-muted-foreground">
                <li class="flex items-start gap-2">
                  <span class="text-primary mt-0.5">•</span>
                  Gunakan nama toko yang mudah diingat pembeli.
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-primary mt-0.5">•</span>
                  Tulis deskripsi yang jelas tentang produk yang kamu jual.
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-primary mt-0.5">•</span>
                  Deskripsi lengkap membantu pembeli lebih percaya.
                </li>
              </ul>
            </CardContent>
          </Card>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Store as StoreIcon, FileText, AlertCircle, Save, Loader2, Link, Package } from '@lucide/vue'
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
