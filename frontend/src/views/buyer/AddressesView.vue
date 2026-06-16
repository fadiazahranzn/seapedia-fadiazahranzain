<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold">Alamat Pengiriman</h1>
        <p class="text-muted-foreground text-sm mt-1">Kelola alamat pengiriman kamu</p>
      </div>
      <Button @click="openForm()"><Plus class="w-4 h-4 mr-2" />Tambah</Button>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 2" :key="i" class="h-24 bg-muted rounded-lg animate-pulse" />
    </div>

    <div v-else-if="addresses.length === 0" class="text-center py-12 text-muted-foreground">
      <MapPin class="w-12 h-12 mx-auto mb-3 opacity-30" />
      <p>Belum ada alamat. Tambah alamat untuk bisa checkout.</p>
    </div>

    <div v-else class="space-y-3">
      <Card v-for="addr in addresses" :key="addr.id" :class="addr.is_default ? 'border-primary' : ''">
        <CardContent class="py-4">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <p class="font-semibold">{{ addr.label }}</p>
                <Badge v-if="addr.is_default" class="text-xs">Utama</Badge>
              </div>
              <p class="text-sm">{{ addr.recipient_name }} · {{ addr.phone }}</p>
              <p class="text-sm text-muted-foreground">{{ addr.full_address }}, {{ addr.district }}, {{ addr.city }}, {{ addr.province }} {{ addr.postal_code }}</p>
            </div>
            <div class="flex flex-col gap-1 shrink-0">
              <Button variant="outline" size="sm" @click="openForm(addr)">Edit</Button>
              <Button v-if="!addr.is_default" variant="ghost" size="sm" @click="setDefault(addr)">Jadikan Utama</Button>
              <Button variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="confirmDelete(addr)">Hapus</Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/50 flex items-start justify-center z-50 p-4 overflow-y-auto">
      <Card class="w-full max-w-lg my-8">
        <CardContent class="pt-6">
          <h2 class="font-semibold text-lg mb-4">{{ editingAddr ? 'Edit Alamat' : 'Tambah Alamat' }}</h2>
          <form @submit.prevent="submitForm" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <Label>Label *</Label>
                <Input v-model="form.label" placeholder="Rumah / Kantor" required />
              </div>
              <div class="space-y-1">
                <Label>Nama Penerima *</Label>
                <Input v-model="form.recipient_name" required />
              </div>
              <div class="space-y-1">
                <Label>No. Telepon *</Label>
                <Input v-model="form.phone" required />
              </div>
              <div class="space-y-1">
                <Label>Kode Pos *</Label>
                <Input v-model="form.postal_code" required />
              </div>
              <div class="col-span-2 space-y-1">
                <Label>Alamat Lengkap *</Label>
                <Input v-model="form.full_address" placeholder="Jl. Nama Jalan No. X" required />
              </div>
              <div class="space-y-1">
                <Label>Provinsi *</Label>
                <Input v-model="form.province" required />
              </div>
              <div class="space-y-1">
                <Label>Kota *</Label>
                <Input v-model="form.city" required />
              </div>
              <div class="space-y-1">
                <Label>Kecamatan *</Label>
                <Input v-model="form.district" required />
              </div>
              <div class="flex items-center gap-2 pt-4">
                <input type="checkbox" id="is_default" v-model="form.is_default" class="rounded" />
                <Label for="is_default">Jadikan alamat utama</Label>
              </div>
            </div>
            <p v-if="formError" class="text-destructive text-sm">{{ formError }}</p>
            <div class="flex gap-3 pt-2">
              <Button type="submit" :disabled="submitting">{{ submitting ? 'Menyimpan...' : 'Simpan' }}</Button>
              <Button type="button" variant="outline" @click="showForm = false">Batal</Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>

    <!-- Delete confirm -->
    <div v-if="deletingAddr" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <Card class="w-full max-w-sm">
        <CardContent class="pt-6">
          <h3 class="font-semibold mb-2">Hapus Alamat?</h3>
          <p class="text-sm text-muted-foreground mb-4">Alamat "<strong>{{ deletingAddr.label }}</strong>" akan dihapus.</p>
          <div class="flex gap-3">
            <Button variant="destructive" :disabled="submitting" @click="deleteAddr">Hapus</Button>
            <Button variant="outline" @click="deletingAddr = null">Batal</Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Plus, MapPin } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { buyerApi } from '@/services/buyer'
import { toast } from 'vue-sonner'

const addresses = ref([])
const loading = ref(true)
const showForm = ref(false)
const editingAddr = ref(null)
const deletingAddr = ref(null)
const submitting = ref(false)
const formError = ref('')

const emptyForm = { label: '', recipient_name: '', phone: '', full_address: '', province: '', city: '', district: '', postal_code: '', is_default: false }
const form = ref({ ...emptyForm })

async function load() {
  loading.value = true
  try {
    const { data } = await buyerApi.getAddresses()
    addresses.value = data.data
  } finally { loading.value = false }
}

function openForm(addr = null) {
  editingAddr.value = addr
  form.value = addr ? { ...addr } : { ...emptyForm }
  formError.value = ''
  showForm.value = true
}

async function submitForm() {
  submitting.value = true
  formError.value = ''
  try {
    if (editingAddr.value) {
      await buyerApi.updateAddress(editingAddr.value.id, form.value)
      toast.success('Alamat diperbarui!')
    } else {
      await buyerApi.createAddress(form.value)
      toast.success('Alamat ditambahkan!')
    }
    showForm.value = false
    load()
  } catch (e) {
    const errs = e.response?.data?.errors
    formError.value = errs ? Object.values(errs).flat().join(' ') : e.response?.data?.message || 'Gagal menyimpan.'
  } finally { submitting.value = false }
}

async function setDefault(addr) {
  await buyerApi.setDefault(addr.id)
  toast.success('Alamat utama diperbarui.')
  load()
}

function confirmDelete(addr) { deletingAddr.value = addr }

async function deleteAddr() {
  submitting.value = true
  try {
    await buyerApi.deleteAddress(deletingAddr.value.id)
    toast.success('Alamat dihapus.')
    deletingAddr.value = null
    load()
  } finally { submitting.value = false }
}

onMounted(load)
</script>
