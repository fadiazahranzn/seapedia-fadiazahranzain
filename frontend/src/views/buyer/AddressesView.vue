<template>
  <div class="max-w-[760px] mx-auto">

    <!-- Header -->
    <div class="flex items-start justify-between gap-4 mb-5">
      <div>
        <h1 class="text-[22px] font-bold tracking-[-0.03em]">Alamat Pengiriman</h1>
        <p class="text-[13px] text-muted-foreground mt-0.5">Kelola alamat pengiriman kamu</p>
      </div>
      <button
        class="inline-flex items-center gap-1.5 px-4 py-[9px] rounded-[10px] border-0 text-[13px] font-semibold text-white bg-primary hover:opacity-90 transition-opacity cursor-pointer shrink-0 shadow-[0_2px_8px_rgba(99,102,241,.25)]"
        @click="openForm()"
      >
        <Plus class="w-3.5 h-3.5" /> Tambah Alamat
      </button>
    </div>

    <!-- Count -->
    <p v-if="!loading" class="text-[13px] text-muted-foreground mb-4">
      {{ addresses.length }} alamat tersimpan
    </p>

    <!-- Skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 2" :key="i" class="h-36 bg-muted rounded-xl animate-pulse" />
    </div>

    <!-- Empty -->
    <div v-else-if="addresses.length === 0" class="flex flex-col items-center gap-3 py-20 text-center bg-card border rounded-xl">
      <MapPin class="w-12 h-12 text-muted-foreground/25" />
      <p class="text-[15px] font-medium">Belum ada alamat</p>
      <p class="text-[13px] text-muted-foreground">Tambah alamat untuk bisa checkout</p>
      <button
        class="mt-1 inline-flex items-center gap-1.5 px-4 py-2 rounded-[10px] border-0 text-[13px] font-semibold text-white bg-primary hover:opacity-90 cursor-pointer"
        @click="openForm()"
      >
        <Plus class="w-3.5 h-3.5" /> Tambah Alamat
      </button>
    </div>

    <!-- Address cards -->
    <div v-else class="space-y-3">
      <div
        v-for="addr in addresses"
        :key="addr.id"
        class="bg-card border-[1.5px] rounded-xl p-5 transition-shadow hover:shadow-md"
        :class="addr.is_default ? 'border-primary' : 'border-border'"
      >
        <div class="flex items-start gap-3.5">
          <!-- icon -->
          <div
            class="w-10 h-10 rounded-[10px] flex items-center justify-center shrink-0 mt-0.5"
            :class="addr.is_default ? 'bg-primary/10' : 'bg-muted'"
          >
            <MapPin class="w-[18px] h-[18px]" :class="addr.is_default ? 'text-primary' : 'text-muted-foreground'" />
          </div>

          <!-- info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-0.5">
              <span class="text-[14px] font-bold">{{ addr.label }}</span>
              <span v-if="addr.is_default" class="text-[11px] font-semibold text-primary bg-primary/10 border border-primary/20 rounded-full px-2 py-0.5">Utama</span>
            </div>
            <p class="text-[13px] font-medium text-slate-700">{{ addr.recipient_name }}</p>
            <p class="text-[12px] text-muted-foreground mt-0.5">{{ addr.phone }}</p>
            <p class="text-[12px] text-muted-foreground mt-1.5 leading-relaxed">
              {{ addr.full_address }}, {{ addr.district }}, {{ addr.city }}, {{ addr.province }} {{ addr.postal_code }}
            </p>
          </div>
        </div>

        <!-- action row -->
        <div class="flex items-center gap-2 mt-4 pt-4 border-t">
          <button
            class="h-8 px-3 rounded-lg border-[1.5px] border-border text-[12px] font-semibold text-slate-600 bg-background hover:border-primary hover:text-primary hover:bg-primary/5 transition-colors cursor-pointer inline-flex items-center gap-1.5"
            @click="openForm(addr)"
          >
            <Pencil class="w-3 h-3" /> Edit
          </button>
          <button
            v-if="!addr.is_default"
            class="h-8 px-3 rounded-lg border-[1.5px] border-border text-[12px] font-medium text-muted-foreground bg-background hover:border-primary hover:text-primary hover:bg-primary/5 transition-colors cursor-pointer"
            @click="setDefault(addr)"
          >
            Jadikan Utama
          </button>
          <button
            class="h-8 px-3 rounded-lg border-0 text-[12px] font-medium text-red-500 bg-transparent hover:bg-red-50 transition-colors cursor-pointer ml-auto inline-flex items-center gap-1.5"
            @click="confirmDelete(addr)"
          >
            <Trash2 class="w-3 h-3" /> Hapus
          </button>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div
      v-if="showForm"
      class="fixed inset-0 bg-black/50 flex items-start justify-center z-50 p-4 overflow-y-auto"
      @click.self="showForm = false"
    >
      <div class="bg-card border rounded-xl w-full max-w-[520px] my-8 p-7">
        <h2 class="font-bold text-[17px] mb-5">{{ editingAddr ? 'Edit Alamat' : 'Tambah Alamat' }}</h2>
        <form @submit.prevent="submitForm">
          <div class="grid grid-cols-2 gap-3">

            <div class="space-y-1.5">
              <label class="text-[12px] font-semibold">Label *</label>
              <input v-model="form.label" placeholder="Rumah / Kantor" required
                class="w-full h-[38px] px-3 rounded-[10px] border-[1.5px] text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-background font-sans transition-all" />
            </div>

            <div class="space-y-1.5">
              <label class="text-[12px] font-semibold">Nama Penerima *</label>
              <input v-model="form.recipient_name" required
                class="w-full h-[38px] px-3 rounded-[10px] border-[1.5px] text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-background font-sans transition-all" />
            </div>

            <div class="space-y-1.5">
              <label class="text-[12px] font-semibold">No. Telepon *</label>
              <input v-model="form.phone" required
                class="w-full h-[38px] px-3 rounded-[10px] border-[1.5px] text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-background font-sans transition-all" />
            </div>

            <div class="space-y-1.5">
              <label class="text-[12px] font-semibold">Kode Pos *</label>
              <input v-model="form.postal_code" required
                class="w-full h-[38px] px-3 rounded-[10px] border-[1.5px] text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-background font-sans transition-all" />
            </div>

            <div class="col-span-2 space-y-1.5">
              <label class="text-[12px] font-semibold">Alamat Lengkap *</label>
              <input v-model="form.full_address" placeholder="Jl. Nama Jalan No. X" required
                class="w-full h-[38px] px-3 rounded-[10px] border-[1.5px] text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-background font-sans transition-all" />
            </div>

            <div class="space-y-1.5">
              <label class="text-[12px] font-semibold">Provinsi *</label>
              <input v-model="form.province" required
                class="w-full h-[38px] px-3 rounded-[10px] border-[1.5px] text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-background font-sans transition-all" />
            </div>

            <div class="space-y-1.5">
              <label class="text-[12px] font-semibold">Kota *</label>
              <input v-model="form.city" required
                class="w-full h-[38px] px-3 rounded-[10px] border-[1.5px] text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-background font-sans transition-all" />
            </div>

            <div class="space-y-1.5">
              <label class="text-[12px] font-semibold">Kecamatan *</label>
              <input v-model="form.district" required
                class="w-full h-[38px] px-3 rounded-[10px] border-[1.5px] text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-background font-sans transition-all" />
            </div>

            <div class="col-span-2 flex items-center gap-2 pt-1">
              <input type="checkbox" id="is_default" v-model="form.is_default" class="w-4 h-4 rounded accent-primary cursor-pointer" />
              <label for="is_default" class="text-[13px] font-medium cursor-pointer">Jadikan alamat utama</label>
            </div>

          </div>

          <p v-if="formError" class="text-red-500 text-[12px] mt-3">{{ formError }}</p>

          <div class="flex gap-2.5 mt-5">
            <button
              type="submit"
              :disabled="submitting"
              class="h-[38px] px-5 rounded-[10px] border-0 text-[13px] font-semibold text-white bg-primary hover:opacity-90 transition-opacity cursor-pointer disabled:opacity-50"
            >
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <button
              type="button"
              class="h-[38px] px-5 rounded-[10px] border-[1.5px] text-[13px] font-semibold text-slate-600 bg-background hover:border-primary hover:text-primary hover:bg-primary/5 transition-colors cursor-pointer"
              @click="showForm = false"
            >
              Batal
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete confirm modal -->
    <div
      v-if="deletingAddr"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="deletingAddr = null"
    >
      <div class="bg-card border rounded-xl w-full max-w-sm p-6">
        <div class="w-11 h-11 rounded-full bg-red-50 flex items-center justify-center mb-4">
          <Trash2 class="w-5 h-5 text-red-500" />
        </div>
        <h3 class="font-bold text-[16px] mb-1.5">Hapus Alamat?</h3>
        <p class="text-[13px] text-muted-foreground mb-5">
          Alamat <strong class="text-foreground">"{{ deletingAddr.label }}"</strong> akan dihapus secara permanen.
        </p>
        <div class="flex gap-2.5">
          <button
            :disabled="submitting"
            class="h-[38px] px-5 rounded-[10px] border-0 text-[13px] font-semibold text-white bg-red-600 hover:opacity-90 transition-opacity cursor-pointer disabled:opacity-50"
            @click="deleteAddr"
          >
            {{ submitting ? 'Menghapus...' : 'Hapus' }}
          </button>
          <button
            class="h-[38px] px-5 rounded-[10px] border-[1.5px] text-[13px] font-semibold text-slate-600 bg-background hover:border-primary hover:text-primary hover:bg-primary/5 transition-colors cursor-pointer"
            @click="deletingAddr = null"
          >
            Batal
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Plus, MapPin, Pencil, Trash2 } from '@lucide/vue'
import { buyerApi } from '@/services/buyer'
import { toast } from 'vue-sonner'

const addresses   = ref([])
const loading     = ref(true)
const showForm    = ref(false)
const editingAddr = ref(null)
const deletingAddr = ref(null)
const submitting  = ref(false)
const formError   = ref('')

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
  submitting.value = true; formError.value = ''
  try {
    if (editingAddr.value) {
      await buyerApi.updateAddress(editingAddr.value.id, form.value)
      toast.success('Alamat diperbarui!')
    } else {
      await buyerApi.createAddress(form.value)
      toast.success('Alamat ditambahkan!')
    }
    showForm.value = false; load()
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
    deletingAddr.value = null; load()
  } finally { submitting.value = false }
}

onMounted(load)
</script>
