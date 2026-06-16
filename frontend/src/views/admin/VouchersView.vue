<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Manajemen Voucher</h1>
      <Button @click="showForm = true">+ Buat Voucher</Button>
    </div>

    <!-- Create form -->
    <Card v-if="showForm" class="mb-6">
      <CardContent class="pt-6">
        <h2 class="font-semibold mb-4">Buat Voucher Baru</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <Label>Kode Voucher</Label>
            <Input v-model="form.code" placeholder="Contoh: HEMAT20" class="uppercase" />
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
            <Input v-model="form.discount_value" type="number" min="0" :placeholder="form.discount_type === 'percentage' ? '10 (= 10%)' : '20000'" />
          </div>
          <div class="space-y-1">
            <Label>Min. Pembelian (Rp)</Label>
            <Input v-model="form.min_purchase" type="number" min="0" placeholder="Opsional" />
          </div>
          <div class="space-y-1">
            <Label>Maks. Diskon (Rp)</Label>
            <Input v-model="form.max_discount" type="number" min="0" placeholder="Opsional" />
          </div>
          <div class="space-y-1">
            <Label>Batas Penggunaan</Label>
            <Input v-model="form.usage_limit" type="number" min="1" placeholder="100" />
          </div>
          <div class="space-y-1">
            <Label>Kadaluarsa</Label>
            <Input v-model="form.expires_at" type="datetime-local" />
          </div>
        </div>
        <p v-if="formError" class="text-destructive text-sm mt-3">{{ formError }}</p>
        <div class="flex gap-2 mt-4">
          <Button @click="createVoucher" :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan Voucher' }}</Button>
          <Button variant="outline" @click="resetForm">Batal</Button>
        </div>
      </CardContent>
    </Card>

    <!-- List -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="h-20 bg-muted rounded-xl animate-pulse" />
    </div>
    <div v-else-if="!vouchers.length" class="text-center py-16 text-muted-foreground">
      <p>Belum ada voucher. Buat yang pertama!</p>
    </div>
    <div v-else class="space-y-3">
      <Card v-for="v in vouchers" :key="v.id">
        <CardContent class="py-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span class="font-mono font-bold text-primary">{{ v.code }}</span>
                <span :class="isExpired(v) || isUsedUp(v) ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                  class="text-xs px-2 py-0.5 rounded-full font-medium">
                  {{ isExpired(v) ? 'Kadaluarsa' : isUsedUp(v) ? 'Habis' : 'Aktif' }}
                </span>
              </div>
              <p class="text-sm text-muted-foreground">{{ v.description }}</p>
              <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-muted-foreground">
                <span>Diskon: <strong class="text-foreground">{{ v.discount_type === 'percentage' ? v.discount_value + '%' : formatPrice(v.discount_value) }}</strong></span>
                <span v-if="v.min_purchase">Min. beli: {{ formatPrice(v.min_purchase) }}</span>
                <span v-if="v.max_discount">Maks. diskon: {{ formatPrice(v.max_discount) }}</span>
                <span>Dipakai: <strong class="text-foreground">{{ v.used_count }}/{{ v.usage_limit }}</strong></span>
                <span>Kadaluarsa: {{ formatDate(v.expires_at) }}</span>
              </div>
            </div>
            <button class="text-muted-foreground hover:text-destructive shrink-0" @click="deleteVoucher(v)">
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

const vouchers = ref([])
const loading = ref(true)
const showForm = ref(false)
const saving = ref(false)
const formError = ref('')

const form = reactive({
  code: '', description: '', discount_type: 'percentage',
  discount_value: '', min_purchase: '', max_discount: '',
  usage_limit: '', expires_at: '',
})

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}
function formatDate(d) {
  return new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
function isExpired(v) { return new Date(v.expires_at) < new Date() }
function isUsedUp(v) { return v.used_count >= v.usage_limit }

function resetForm() {
  showForm.value = false
  formError.value = ''
  Object.assign(form, { code: '', description: '', discount_type: 'percentage', discount_value: '', min_purchase: '', max_discount: '', usage_limit: '', expires_at: '' })
}

async function createVoucher() {
  formError.value = ''
  if (!form.code.trim()) {
    formError.value = 'Kode voucher wajib diisi.'
    return
  }
  if (!form.discount_value || parseFloat(form.discount_value) <= 0) {
    formError.value = 'Nilai diskon wajib diisi dan harus lebih dari 0.'
    return
  }
  if (form.discount_type === 'percentage' && parseFloat(form.discount_value) > 100) {
    formError.value = 'Nilai diskon persentase tidak boleh melebihi 100%.'
    return
  }
  if (!form.usage_limit || parseInt(form.usage_limit) < 1) {
    formError.value = 'Batas penggunaan minimal 1.'
    return
  }
  if (!form.expires_at) {
    formError.value = 'Tanggal kadaluarsa wajib diisi.'
    return
  }
  if (new Date(form.expires_at) <= new Date()) {
    formError.value = 'Tanggal kadaluarsa harus di masa depan.'
    return
  }
  saving.value = true
  try {
    const payload = {
      code: form.code,
      description: form.description || undefined,
      discount_type: form.discount_type,
      discount_value: parseFloat(form.discount_value),
      min_purchase: form.min_purchase ? parseFloat(form.min_purchase) : undefined,
      max_discount: form.max_discount ? parseFloat(form.max_discount) : undefined,
      usage_limit: parseInt(form.usage_limit),
      expires_at: form.expires_at,
    }
    const { data } = await adminApi.createVoucher(payload)
    vouchers.value.unshift(data.data)
    resetForm()
    toast.success('Voucher berhasil dibuat!')
  } catch (e) {
    const errors = e.response?.data?.errors
    formError.value = errors ? Object.values(errors).flat().join(', ') : e.response?.data?.message || 'Gagal membuat voucher.'
  } finally { saving.value = false }
}

async function deleteVoucher(v) {
  if (!confirm(`Hapus voucher "${v.code}"?`)) return
  try {
    await adminApi.deleteVoucher(v.id)
    vouchers.value = vouchers.value.filter(x => x.id !== v.id)
    toast.success('Voucher dihapus.')
  } catch { toast.error('Gagal menghapus.') }
}

onMounted(async () => {
  try {
    const { data } = await adminApi.getVouchers()
    vouchers.value = data.data
  } finally { loading.value = false }
})
</script>
