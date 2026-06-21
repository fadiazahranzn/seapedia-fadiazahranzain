<template>
  <div class="page-wrap">

    <!-- Hero header -->
    <div class="hero">
      <div class="hero-content">
        <div class="hero-left">
          <div class="hero-icon"><Megaphone class="w-6 h-6" /></div>
          <div>
            <h1 class="hero-title">Manajemen Promo</h1>
            <p class="hero-sub">Kelola kode promo diskon untuk pelanggan</p>
          </div>
        </div>
        <button class="btn-create" @click="showForm = true">
          <Plus class="w-4 h-4" /> Buat Promo
        </button>
      </div>

      <!-- Stats -->
      <div class="stat-cards">
        <div class="stat-card">
          <div class="stat-icon all"><Tag class="w-4 h-4" /></div>
          <div>
            <div class="stat-val">{{ promos.length }}</div>
            <div class="stat-lbl">Total Promo</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon active"><CircleCheck class="w-4 h-4" /></div>
          <div>
            <div class="stat-val green">{{ activeCount }}</div>
            <div class="stat-lbl">Aktif</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon expired"><Clock class="w-4 h-4" /></div>
          <div>
            <div class="stat-val muted">{{ expiredCount }}</div>
            <div class="stat-lbl">Kadaluarsa</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="resetForm">
      <div class="modal">
        <div class="modal-head">
          <div class="modal-head-left">
            <div class="modal-icon"><Megaphone class="w-4 h-4" /></div>
            <div>
              <h2 class="modal-title">Buat Promo Baru</h2>
              <p class="modal-sub">Isi detail kode promo di bawah ini</p>
            </div>
          </div>
          <button class="modal-close" @click="resetForm"><X class="w-4 h-4" /></button>
        </div>

        <div class="modal-body">
          <div class="form-grid">
            <div class="field">
              <label>Kode Promo <span class="req">*</span></label>
              <input v-model="form.code" placeholder="Contoh: FLASHSALE" @input="form.code = form.code.toUpperCase()" style="text-transform:uppercase" />
            </div>
            <div class="field">
              <label>Deskripsi</label>
              <input v-model="form.description" placeholder="Deskripsi singkat" />
            </div>
            <div class="field span-2">
              <label>Tipe Diskon <span class="req">*</span></label>
              <div class="type-toggle">
                <button :class="{ active: form.discount_type === 'percentage' }" @click="form.discount_type = 'percentage'">
                  <Percent class="w-3.5 h-3.5" /> Persentase
                </button>
                <button :class="{ active: form.discount_type === 'fixed' }" @click="form.discount_type = 'fixed'">
                  <Banknote class="w-3.5 h-3.5" /> Nominal (Rp)
                </button>
              </div>
            </div>
            <div class="field">
              <label>Nilai Diskon <span class="req">*</span></label>
              <div class="input-adorn">
                <span class="adorn">{{ form.discount_type === 'percentage' ? '%' : 'Rp' }}</span>
                <input v-model="form.discount_value" type="number" min="0" :placeholder="form.discount_type === 'percentage' ? '15' : '10000'" />
              </div>
            </div>
            <div class="field">
              <label>Berlaku Hingga <span class="req">*</span></label>
              <input v-model="form.expires_at" type="datetime-local" />
            </div>
            <div class="field">
              <label>Min. Pembelian</label>
              <div class="input-adorn">
                <span class="adorn">Rp</span>
                <input v-model="form.min_purchase" type="number" min="0" placeholder="Opsional" />
              </div>
            </div>
            <div class="field">
              <label>Maks. Diskon</label>
              <div class="input-adorn">
                <span class="adorn">Rp</span>
                <input v-model="form.max_discount" type="number" min="0" placeholder="Opsional" />
              </div>
            </div>
          </div>

          <div v-if="formError" class="form-error">
            <AlertCircle class="w-3.5 h-3.5 shrink-0" /> {{ formError }}
          </div>
        </div>

        <div class="modal-foot">
          <button class="btn-cancel" @click="resetForm">Batal</button>
          <button class="btn-save" @click="createPromo" :disabled="saving">
            <Loader2 v-if="saving" class="w-3.5 h-3.5 animate-spin" />
            {{ saving ? 'Menyimpan...' : 'Buat Promo' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Filter -->
    <div v-if="!loading && promos.length" class="filter-bar">
      <div class="filter-tabs">
        <button
          v-for="f in statusFilters"
          :key="f.value"
          class="ftab"
          :class="{ active: activeFilter === f.value }"
          @click="activeFilter = f.value"
        >
          {{ f.label }}
          <span class="ftab-count">{{ f.value === 'all' ? promos.length : f.value === 'active' ? activeCount : expiredCount }}</span>
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="promo-list">
      <div v-for="i in 4" :key="i" class="skeleton" />
    </div>

    <!-- Empty -->
    <div v-else-if="!promos.length" class="empty-state">
      <div class="empty-icon"><Megaphone class="w-8 h-8" /></div>
      <p class="empty-title">Belum ada promo</p>
      <p class="empty-sub">Buat kode promo pertama untuk memberikan diskon kepada pelanggan.</p>
      <button class="btn-create" @click="showForm = true"><Plus class="w-4 h-4" /> Buat Promo</button>
    </div>

    <!-- Promo list -->
    <div v-else class="promo-list">
      <div v-for="p in filteredPromos" :key="p.id" class="pcard" :class="{ expired: isExpired(p) }">

        <!-- Left accent -->
        <div class="pcard-accent" />

        <!-- Code + badge -->
        <div class="pcard-main">
          <div class="pcard-top">
            <div class="pcard-code">{{ p.code }}</div>
            <span class="pcard-badge" :class="isExpired(p) ? 'expired' : 'active'">
              {{ isExpired(p) ? 'Kadaluarsa' : 'Aktif' }}
            </span>
          </div>
          <p class="pcard-desc">{{ p.description || 'Tidak ada deskripsi' }}</p>
        </div>

        <!-- Divider -->
        <div class="pcard-div" />

        <!-- Discount value -->
        <div class="pcard-discount">
          <div class="disc-val">
            {{ p.discount_type === 'percentage' ? p.discount_value + '%' : formatPrice(p.discount_value) }}
          </div>
          <div class="disc-lbl">{{ p.discount_type === 'percentage' ? 'potongan' : 'potongan langsung' }}</div>
        </div>

        <!-- Meta -->
        <div class="pcard-meta">
          <div class="meta-item" v-if="p.min_purchase">
            <span class="mk">Min. beli</span>
            <span class="mv">{{ formatPrice(p.min_purchase) }}</span>
          </div>
          <div class="meta-item" v-if="p.max_discount">
            <span class="mk">Maks. diskon</span>
            <span class="mv">{{ formatPrice(p.max_discount) }}</span>
          </div>
          <div class="meta-item">
            <span class="mk">Berlaku hingga</span>
            <span class="mv">{{ formatDate(p.expires_at) }}</span>
          </div>
        </div>

        <!-- Delete -->
        <button class="del-btn" @click="deletePromo(p)" title="Hapus promo">
          <Trash2 class="w-4 h-4" />
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Plus, Trash2, X, Tag, AlertCircle, Loader2, Percent, Banknote, Megaphone, CircleCheck, Clock } from '@lucide/vue'
import { adminApi } from '@/services/admin'
import { toast } from 'vue-sonner'

const promos  = ref([])
const loading = ref(true)
const showForm = ref(false)
const saving   = ref(false)
const formError = ref('')

const form = reactive({
  code: '', description: '', discount_type: 'percentage',
  discount_value: '', min_purchase: '', max_discount: '', expires_at: '',
})

const activeFilter = ref('all')
const statusFilters = [
  { value: 'all',     label: 'Semua' },
  { value: 'active',  label: 'Aktif' },
  { value: 'expired', label: 'Kadaluarsa' },
]
const activeCount  = computed(() => promos.value.filter(p => !isExpired(p)).length)
const expiredCount = computed(() => promos.value.filter(p => isExpired(p)).length)
const filteredPromos = computed(() => {
  if (activeFilter.value === 'active')  return promos.value.filter(p => !isExpired(p))
  if (activeFilter.value === 'expired') return promos.value.filter(p => isExpired(p))
  return promos.value
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
  formError.value = ''
  if (!form.code.trim())                                                             { formError.value = 'Kode promo wajib diisi.'; return }
  if (!form.discount_value || parseFloat(form.discount_value) <= 0)                 { formError.value = 'Nilai diskon harus lebih dari 0.'; return }
  if (form.discount_type === 'percentage' && parseFloat(form.discount_value) > 100) { formError.value = 'Persentase tidak boleh melebihi 100%.'; return }
  if (!form.expires_at)                                                              { formError.value = 'Tanggal kadaluarsa wajib diisi.'; return }
  if (new Date(form.expires_at) <= new Date())                                      { formError.value = 'Tanggal kadaluarsa harus di masa depan.'; return }

  saving.value = true
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

<style scoped>
.page-wrap { display:flex; flex-direction:column; gap:24px; }

/* ── Hero ── */
.hero {
  position:relative; overflow:hidden;
  background:linear-gradient(135deg, #c41952 0%, #e8557a 60%, #f4a0b8 100%);
  border-radius:16px; padding:28px 28px 0;
  box-shadow:0 8px 32px rgba(196,25,82,.25);
}
.hero-content { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
.hero-left    { display:flex; align-items:center; gap:14px; }
.hero-icon    {
  width:44px; height:44px; border-radius:12px;
  background:rgba(255,255,255,.2); backdrop-filter:blur(4px);
  display:flex; align-items:center; justify-content:center; color:#fff;
  border:1px solid rgba(255,255,255,.3);
}
.hero-title { font-size:22px; font-weight:800; color:#fff; letter-spacing:-.4px; }
.hero-sub   { font-size:13px; color:rgba(255,255,255,.75); margin-top:3px; }

/* ── Stat cards ── */
.stat-cards {
  display:flex; gap:0;
  background:rgba(255,255,255,.12); border-radius:12px 12px 0 0;
  backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,.2);
  border-bottom:none; overflow:hidden;
}
.stat-card {
  flex:1; display:flex; align-items:center; gap:12px;
  padding:18px 22px; border-right:1px solid rgba(255,255,255,.15);
}
.stat-card:last-child { border-right:none; }
.stat-icon {
  width:36px; height:36px; border-radius:10px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
}
.stat-icon.all     { background:rgba(255,255,255,.25); color:#fff; }
.stat-icon.active  { background:rgba(134,239,172,.3);  color:#bbf7d0; }
.stat-icon.expired { background:rgba(255,255,255,.15); color:rgba(255,255,255,.7); }
.stat-val  { font-size:26px; font-weight:800; color:#fff; line-height:1; }
.stat-val.green  { color:#bbf7d0; }
.stat-val.muted  { color:rgba(255,255,255,.55); }
.stat-lbl  { font-size:11px; font-weight:600; color:rgba(255,255,255,.65); text-transform:uppercase; letter-spacing:.07em; margin-top:4px; }

/* ── Create button ── */
.btn-create {
  display:inline-flex; align-items:center; gap:7px;
  background:#fff; color:#c41952;
  font-size:13px; font-weight:700; font-family:inherit;
  padding:9px 18px; border-radius:10px; border:none;
  cursor:pointer; transition:all .15s;
  box-shadow:0 2px 8px rgba(0,0,0,.12);
}
.btn-create:hover { background:#fff0f4; transform:translateY(-1px); box-shadow:0 4px 14px rgba(0,0,0,.15); }

/* ── Modal ── */
.modal-overlay {
  position:fixed; inset:0; background:rgba(15,10,20,.45);
  display:flex; align-items:center; justify-content:center;
  z-index:50; padding:16px; backdrop-filter:blur(2px);
}
.modal {
  background:#fff; border-radius:18px; width:100%; max-width:580px;
  border:1px solid #f3e0e6;
  box-shadow:0 30px 80px rgba(196,25,82,.2);
  overflow:hidden;
}
.modal-head {
  display:flex; align-items:center; justify-content:space-between;
  padding:20px 24px; border-bottom:1px solid #f3e0e6;
  background:linear-gradient(135deg,#fdf2f5,#fff);
}
.modal-head-left { display:flex; align-items:center; gap:12px; }
.modal-icon {
  width:36px; height:36px; border-radius:10px;
  background:#c41952; color:#fff;
  display:flex; align-items:center; justify-content:center;
}
.modal-title { font-size:15px; font-weight:800; color:#1a1a1a; }
.modal-sub   { font-size:12px; color:#c41952; opacity:.7; margin-top:2px; }
.modal-close {
  width:32px; height:32px; border-radius:8px; border:1px solid #f3e0e6;
  background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;
  color:#9ca3af; transition:all .15s;
}
.modal-close:hover { background:#fdf2f5; color:#c41952; border-color:#f3c6d4; }
.modal-body { padding:22px 24px; }
.modal-foot { padding:16px 24px; border-top:1px solid #f3e0e6; display:flex; justify-content:flex-end; gap:8px; }

/* ── Form ── */
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.span-2 { grid-column:span 2; }
.field { display:flex; flex-direction:column; gap:6px; }
.field label { font-size:12px; font-weight:600; color:#374151; }
.req { color:#c41952; }
.field input {
  width:100%; height:40px; padding:0 12px;
  font-family:inherit; font-size:13px; color:#1a1a1a;
  background:#fafafa; border:1.5px solid #f3e0e6; border-radius:9px;
  outline:none; transition:all .15s; box-sizing:border-box;
}
.field input:focus { border-color:#c41952; background:#fff; box-shadow:0 0 0 3px rgba(196,25,82,.08); }
.field input::placeholder { color:#f3c6d4; }

.type-toggle { display:flex; background:#fdf2f5; border:1.5px solid #f3e0e6; border-radius:10px; padding:4px; gap:4px; }
.type-toggle button {
  flex:1; display:flex; align-items:center; justify-content:center; gap:6px;
  padding:8px 12px; font-size:12.5px; font-weight:600; font-family:inherit;
  border:none; border-radius:7px; background:transparent; color:#a06070;
  cursor:pointer; transition:all .15s;
}
.type-toggle button.active { background:#c41952; color:#fff; box-shadow:0 2px 8px rgba(196,25,82,.3); }

.input-adorn { position:relative; }
.adorn {
  position:absolute; left:11px; top:50%; transform:translateY(-50%);
  font-size:12px; font-weight:700; color:#a06070; pointer-events:none;
}
.input-adorn input { padding-left:32px !important; }

.form-error {
  display:flex; align-items:center; gap:8px;
  background:#fef2f2; border:1px solid #fecaca; border-radius:9px;
  padding:10px 14px; font-size:12.5px; color:#dc2626; margin-top:16px;
}

.btn-cancel {
  display:inline-flex; align-items:center; gap:6px;
  font-size:13px; font-weight:600; font-family:inherit;
  padding:9px 18px; border-radius:9px;
  border:1.5px solid #f3e0e6; background:#fff; color:#6b7280;
  cursor:pointer; transition:all .15s;
}
.btn-cancel:hover { border-color:#c41952; color:#c41952; background:#fdf2f5; }
.btn-save {
  display:inline-flex; align-items:center; gap:6px;
  font-size:13px; font-weight:700; font-family:inherit;
  padding:9px 20px; border-radius:9px;
  border:none; background:#c41952; color:#fff;
  cursor:pointer; transition:all .15s;
  box-shadow:0 2px 8px rgba(196,25,82,.35);
}
.btn-save:hover:not(:disabled) { background:#a0133f; transform:translateY(-1px); }
.btn-save:disabled { opacity:.6; cursor:not-allowed; }

/* ── Skeleton ── */
.skeleton {
  height:88px; border-radius:14px;
  background:linear-gradient(90deg,#f3e0e6 25%,#fdf2f5 50%,#f3e0e6 75%);
  background-size:200% 100%; animation:shimmer 1.4s infinite;
}
@keyframes shimmer { to { background-position:-200% 0; } }

/* ── Empty ── */
.empty-state { text-align:center; padding:72px 20px; }
.empty-icon  { width:72px; height:72px; background:linear-gradient(135deg,#f3e0e6,#fdf2f5); border-radius:20px; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; color:#c41952; box-shadow:0 4px 16px rgba(196,25,82,.12); }
.empty-title { font-size:18px; font-weight:800; color:#1a1a1a; margin-bottom:8px; }
.empty-sub   { font-size:13px; color:#9ca3af; margin-bottom:24px; line-height:1.6; }

/* ── Promo list ── */
.promo-list { display:flex; flex-direction:column; gap:12px; }

/* ── Promo card ── */
.pcard {
  display:flex; align-items:center; gap:0;
  background:#fff; border-radius:14px;
  border:1.5px solid #f3e0e6;
  box-shadow:0 2px 10px rgba(196,25,82,.06);
  overflow:hidden; transition:box-shadow .2s, transform .15s, border-color .2s;
}
.pcard:hover { box-shadow:0 6px 24px rgba(196,25,82,.13); transform:translateY(-2px); border-color:#f3c6d4; }
.pcard.expired { opacity:.6; border-color:#e5e7eb; }
.pcard.expired:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }

.pcard-accent {
  width:5px; align-self:stretch; flex-shrink:0;
  background:linear-gradient(180deg,#c41952,#e8557a);
  border-radius:0;
}
.pcard.expired .pcard-accent { background:linear-gradient(180deg,#9ca3af,#d1d5db); }

/* code + badge section */
.pcard-main { padding:16px 20px; flex:0 0 220px; }
.pcard-top  { display:flex; align-items:center; gap:10px; margin-bottom:6px; }
.pcard-code {
  font-family:'Courier New', monospace; font-size:15px; font-weight:800;
  color:#c41952; letter-spacing:.06em;
}
.pcard.expired .pcard-code { color:#9ca3af; }
.pcard-badge {
  font-size:10px; font-weight:700; padding:3px 9px; border-radius:99px; letter-spacing:.04em;
}
.pcard-badge.active  { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.pcard-badge.expired { background:#f9fafb; color:#9ca3af; border:1px solid #e5e7eb; }
.pcard-desc { font-size:12px; color:#9ca3af; line-height:1.4; }

/* divider */
.pcard-div { width:1px; align-self:stretch; background:#f3e0e6; margin:12px 0; flex-shrink:0; }
.pcard.expired .pcard-div { background:#f3f4f6; }

/* discount */
.pcard-discount { padding:0 24px; flex:0 0 140px; text-align:center; }
.disc-val { font-size:28px; font-weight:900; color:#c41952; line-height:1; letter-spacing:-1px; }
.pcard.expired .disc-val { color:#9ca3af; }
.disc-lbl { font-size:10px; font-weight:600; color:#a06070; text-transform:uppercase; letter-spacing:.06em; margin-top:4px; }
.pcard.expired .disc-lbl { color:#d1d5db; }

/* meta */
.pcard-meta { flex:1; display:flex; flex-direction:column; gap:4px; padding:16px 20px; border-left:1px solid #f3e0e6; }
.pcard.expired .pcard-meta { border-color:#f3f4f6; }
.meta-item { display:flex; justify-content:space-between; font-size:11.5px; }
.mk { color:#a06070; }
.pcard.expired .mk { color:#d1d5db; }
.mv { font-weight:600; color:#374151; }

/* delete */
.del-btn {
  width:40px; height:40px; border-radius:10px; flex-shrink:0; margin:0 16px;
  border:1.5px solid #fee2e2; background:#fff; color:#f87171;
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; transition:all .15s;
}
.del-btn:hover { background:#fef2f2; border-color:#f87171; color:#dc2626; }

.animate-spin { animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

/* ── Filter bar ── */
.filter-bar { display:flex; align-items:center; }
.filter-tabs { display:flex; flex-wrap:wrap; align-items:center; gap:6px; }
.ftab {
  display:inline-flex; align-items:center; gap:5px;
  font-size:12px; font-weight:600; font-family:inherit;
  padding:6px 14px; border-radius:9999px;
  border:1.5px solid #e2e8f0; background:#fff; color:#6b7280;
  cursor:pointer; transition:all .15s;
}
.ftab.active { border-color:#c41952; color:#c41952; background:#fdf2f5; }
.ftab:not(.active):hover { border-color:#c4a8b4; color:#374151; }
.ftab-count {
  font-size:10px; font-weight:700; opacity:.7;
}
</style>
