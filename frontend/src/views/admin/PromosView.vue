<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Manajemen Promo</h1>
      <Button @click="showForm = true">+ Buat Promo</Button>
    </div>

    <!-- Create form -->
    <Card v-if="showForm" class="mb-6">
      <CardContent class="pt-6">
        <h2 class="font-semibold mb-4">Buat Promo Baru</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <Label>Kode Promo</Label>
            <Input v-model="form.code" placeholder="Contoh: FLASHSALE" class="uppercase" />
          </div>
          <div class="space-y-1">
            <Label>Deskripsi</Label>
            <Input v-model="form.description" placeholder="Deskripsi singkat" />
          </div>
          <div class="space-y-1">
            <Label>Tipe Diskon</Label>
            <select v-model="form.discount_type" class="w-full border border-input rounded-md px-3 py-2 text-sm bg-background">
              <option value="percentage">Persentase (%)</option>
              <option value="fixed">Nominal (Rp)</option>
            </select>
          </div>
          <div class="space-y-1">
            <Label>Nilai Diskon</Label>
            <Input v-model="form.discount_value" type="number" min="0" :placeholder="form.discount_type === 'percentage' ? '15 (= 15%)' : '10000'" />
          </div>
          <div class="space-y-1">
            <Label>Min. Pembelian (Rp)</Label>
            <Input v-model="form.min_purchase" type="number" min="0" placeholder="Opsional" />
          </div>
          <div class="space-y-1">
            <Label>Maks. Diskon (Rp)</Label>
            <Input v-model="form.max_discount" type="number" min="0" placeholder="Opsional" />
          </div>
          <div class="space-y-1 md:col-span-2">
            <Label>Kadaluarsa</Label>
            <Input v-model="form.expires_at" type="datetime-local" class="max-w-xs" />
          </div>
        </div>
        <p v-if="formError" class="text-destructive text-sm mt-3">{{ formError }}</p>
        <div class="flex gap-2 mt-4">
          <Button @click="createPromo" :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan Promo' }}</Button>
          <Button variant="outline" @click="resetForm">Batal</Button>
        </div>
      </CardContent>
    </Card>

    <!-- List -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="h-20 bg-muted rounded-xl animate-pulse" />
    </div>
    <div v-else-if="!promos.length" class="text-center py-16 text-muted-foreground">
      <p>Belum ada promo. Buat yang pertama!</p>
    </div>
    <div v-else class="space-y-3">
      <Card v-for="p in promos" :key="p.id">
        <CardContent class="py-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span class="font-mono font-bold text-purple-600">{{ p.code }}</span>
                <span :class="isExpired(p) ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                  class="text-xs px-2 py-0.5 rounded-full font-medium">
                  {{ isExpired(p) ? 'Kadaluarsa' : 'Aktif' }}
                </span>
              </div>
              <p class="text-sm text-muted-foreground">{{ p.description }}</p>
              <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-muted-foreground">
                <span>Diskon: <strong class="text-foreground">{{ p.discount_type === 'percentage' ? p.discount_value + '%' : formatPrice(p.discount_value) }}</strong></span>
                <span v-if="p.min_purchase">Min. beli: {{ formatPrice(p.min_purchase) }}</span>
                <span v-if="p.max_discount">Maks. diskon: {{ formatPrice(p.max_discount) }}</span>
                <span>Kadaluarsa: {{ formatDate(p.expires_at) }}</span>
              </div>
            </div>
            <button class="text-muted-foreground hover:text-destructive shrink-0" @click="deletePromo(p)">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Trash2 } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent } from '@/components/ui/card'
import { adminApi } from '@/services/admin'
import { toast } from 'vue-sonner'

const promos = ref([])
const loading = ref(true)
const showForm = ref(false)
const saving = ref(false)
const formError = ref('')

const form = reactive({
  code: '', description: '', discount_type: 'percentage',
  discount_value: '', min_purchase: '', max_discount: '', expires_at: '',
})

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}
function formatDate(d) {
  return new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
function isExpired(p) { return new Date(p.expires_at) < new Date() }

function resetForm() {
  showForm.value = false
  formError.value = ''
  Object.assign(form, { code: '', description: '', discount_type: 'percentage', discount_value: '', min_purchase: '', max_discount: '', expires_at: '' })
}

async function createPromo() {
  saving.value = true
  formError.value = ''
  try {
    const payload = {
      code: form.code,
      description: form.description || undefined,
      discount_type: form.discount_type,
      discount_value: parseFloat(form.discount_value),
      min_purchase: form.min_purchase ? parseFloat(form.min_purchase) : undefined,
      max_discount: form.max_discount ? parseFloat(form.max_discount) : undefined,
      expires_at: form.expires_at,
    }
    const { data } = await adminApi.createPromo(payload)
    promos.value.unshift(data.data)
    resetForm()
    toast.success('Promo berhasil dibuat!')
  } catch (e) {
    const errors = e.response?.data?.errors
    formError.value = errors ? Object.values(errors).flat().join(', ') : e.response?.data?.message || 'Gagal membuat promo.'
  } finally { saving.value = false }
}

async function deletePromo(p) {
  if (!confirm(`Hapus promo "${p.code}"?`)) return
  try {
    await adminApi.deletePromo(p.id)
    promos.value = promos.value.filter(x => x.id !== p.id)
    toast.success('Promo dihapus.')
  } catch { toast.error('Gagal menghapus.') }
}

onMounted(async () => {
  try {
    const { data } = await adminApi.getPromos()
    promos.value = data.data
  } finally { loading.value = false }
})
</script>
