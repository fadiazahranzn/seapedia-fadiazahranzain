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
    <div v-if="showForm" class="addr-modal-overlay" @click.self="showForm = false">
      <div class="addr-modal">
        <div class="addr-modal-head">
          <div class="addr-modal-head-left">
            <div class="addr-modal-icon"><MapPin class="w-4 h-4" /></div>
            <div>
              <h2 class="addr-modal-title">{{ editingAddr ? 'Edit Alamat' : 'Tambah Alamat' }}</h2>
              <p class="addr-modal-sub">Isi detail alamat pengiriman di bawah ini</p>
            </div>
          </div>
          <button class="addr-modal-close" @click="showForm = false"><X class="w-4 h-4" /></button>
        </div>

        <div class="addr-modal-body">
          <form @submit.prevent="submitForm">
            <div class="form-grid">

              <div class="field">
                <label>Label <span class="req">*</span></label>
                <input v-model="form.label" placeholder="Rumah / Kantor" required />
              </div>

              <div class="field">
                <label>Nama Penerima <span class="req">*</span></label>
                <input v-model="form.recipient_name" required />
              </div>

              <div class="field">
                <label>No. Telepon <span class="req">*</span></label>
                <input v-model="form.phone" required />
              </div>

              <div class="field">
                <label>Kode Pos <span class="req">*</span></label>
                <input v-model="form.postal_code" required />
              </div>

              <div class="field span-2">
                <label>Alamat Lengkap <span class="req">*</span></label>
                <input v-model="form.full_address" placeholder="Jl. Nama Jalan No. X" required />
              </div>

              <div class="field">
                <label>Provinsi <span class="req">*</span></label>
                <input v-model="form.province" required />
              </div>

              <div class="field">
                <label>Kota <span class="req">*</span></label>
                <input v-model="form.city" required />
              </div>

              <div class="field">
                <label>Kecamatan <span class="req">*</span></label>
                <input v-model="form.district" required />
              </div>

              <div class="field span-2 checkbox-row">
                <input type="checkbox" id="is_default" v-model="form.is_default" />
                <label for="is_default" class="checkbox-label">Jadikan alamat utama</label>
              </div>

            </div>

            <div v-if="formError" class="form-error">{{ formError }}</div>
          </form>
        </div>

        <div class="addr-modal-foot">
          <button class="btn-cancel" @click="showForm = false">Batal</button>
          <button class="btn-save" @click="submitForm" :disabled="submitting">
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete confirm modal -->
    <div v-if="deletingAddr" class="addr-modal-overlay" @click.self="deletingAddr = null">
      <div class="addr-modal" style="max-width:420px">
        <div class="addr-modal-head">
          <div class="addr-modal-head-left">
            <div class="addr-modal-icon" style="background:#ef4444"><Trash2 class="w-4 h-4" /></div>
            <div>
              <h2 class="addr-modal-title">Hapus Alamat?</h2>
              <p class="addr-modal-sub">Tindakan ini tidak dapat dibatalkan</p>
            </div>
          </div>
          <button class="addr-modal-close" @click="deletingAddr = null"><X class="w-4 h-4" /></button>
        </div>

        <div class="addr-modal-body">
          <p class="text-[13px] text-muted-foreground">
            Alamat <strong class="text-foreground">"{{ deletingAddr.label }}"</strong> akan dihapus secara permanen.
          </p>
        </div>

        <div class="addr-modal-foot">
          <button class="btn-cancel" @click="deletingAddr = null">Batal</button>
          <button class="btn-delete" @click="deleteAddr" :disabled="submitting">
            {{ submitting ? 'Menghapus...' : 'Hapus' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Plus, MapPin, Pencil, Trash2, X } from '@lucide/vue'
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

<style scoped>
.addr-modal-overlay {
  position:fixed; inset:0; background:rgba(15,10,20,.45);
  display:flex; align-items:center; justify-content:center;
  z-index:50; padding:16px; backdrop-filter:blur(2px);
}
.addr-modal {
  background:#fff; border-radius:18px; width:100%; max-width:560px;
  border:1px solid #f3e0e6;
  box-shadow:0 30px 80px rgba(196,25,82,.2);
  overflow:hidden;
}
.addr-modal-head {
  display:flex; align-items:center; justify-content:space-between;
  padding:20px 24px; border-bottom:1px solid #f3e0e6;
  background:linear-gradient(135deg,#fdf2f5,#fff);
}
.addr-modal-head-left { display:flex; align-items:center; gap:12px; }
.addr-modal-icon {
  width:36px; height:36px; border-radius:10px;
  background:#c41952; color:#fff;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.addr-modal-title { font-size:15px; font-weight:800; color:#1a1a1a; }
.addr-modal-sub   { font-size:12px; color:#a06070; margin-top:2px; }
.addr-modal-close {
  width:32px; height:32px; border-radius:8px; border:1px solid #f3e0e6;
  background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;
  color:#9ca3af; transition:all .15s;
}
.addr-modal-close:hover { background:#fce4ec; color:#c41952; border-color:#f3c6d4; }
.addr-modal-body  { padding:22px 24px; }
.addr-modal-foot  { padding:16px 24px; border-top:1px solid #f3e0e6; display:flex; justify-content:flex-end; gap:8px; }

.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.span-2 { grid-column:span 2; }
.field { display:flex; flex-direction:column; gap:6px; }
.field label { font-size:12px; font-weight:600; color:#374151; }
.field input {
  height:38px; padding:0 12px; border-radius:10px;
  border:1.5px solid #e5e7eb; font-size:13px; outline:none;
  background:#fff; transition:border-color .15s, box-shadow .15s;
}
.field input:focus { border-color:#c41952; box-shadow:0 0 0 3px rgba(196,25,82,.08); }
.req { color:#c41952; }
.checkbox-row { flex-direction:row; align-items:center; gap:8px; padding-top:4px; }
.checkbox-row input[type="checkbox"] { width:16px; height:16px; accent-color:#c41952; cursor:pointer; }
.checkbox-label { font-size:13px; font-weight:500; color:#374151; cursor:pointer; }
.form-error { font-size:12px; color:#ef4444; margin-top:12px; }

.btn-cancel {
  height:36px; padding:0 18px; border-radius:9px;
  border:1.5px solid #e5e7eb; background:#fff; font-size:13px; font-weight:600;
  color:#6b7280; cursor:pointer; transition:all .15s;
}
.btn-cancel:hover { border-color:#c41952; color:#c41952; background:#fdf2f5; }
.btn-save {
  height:36px; padding:0 18px; border-radius:9px;
  border:0; background:#c41952; color:#fff; font-size:13px; font-weight:600;
  cursor:pointer; transition:opacity .15s; display:flex; align-items:center; gap:6px;
}
.btn-save:hover { opacity:.88; }
.btn-save:disabled { opacity:.5; cursor:not-allowed; }
.btn-delete {
  height:36px; padding:0 18px; border-radius:9px;
  border:0; background:#ef4444; color:#fff; font-size:13px; font-weight:600;
  cursor:pointer; transition:opacity .15s;
}
.btn-delete:hover { opacity:.88; }
.btn-delete:disabled { opacity:.5; cursor:not-allowed; }
</style>
