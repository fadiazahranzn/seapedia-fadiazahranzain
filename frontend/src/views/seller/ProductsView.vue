<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold">Produk Saya</h1>
        <p class="text-muted-foreground text-sm mt-1">Kelola produk di toko kamu</p>
      </div>
      <Button @click="openForm()">
        <Plus class="w-4 h-4 mr-2" /> Tambah Produk
      </Button>
    </div>

    <!-- No store -->
    <Card v-if="noStore">
      <CardContent class="pt-6 text-center py-12 text-muted-foreground">
        <Store class="w-12 h-12 mx-auto mb-3 opacity-30" />
        <p class="font-medium mb-3">Buat toko terlebih dahulu</p>
        <Button @click="router.push('/seller/store')">Buat Toko</Button>
      </CardContent>
    </Card>

    <template v-else>
      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="h-16 bg-muted rounded-lg animate-pulse" />
      </div>

      <!-- Empty -->
      <Card v-else-if="products.length === 0">
        <CardContent class="pt-6 text-center py-12 text-muted-foreground">
          <Package class="w-12 h-12 mx-auto mb-3 opacity-30" />
          <p class="font-medium mb-3">Belum ada produk</p>
          <Button @click="openForm()">Tambah Produk Pertama</Button>
        </CardContent>
      </Card>

      <!-- Product list -->
      <div v-else class="space-y-3">
        <Card v-for="product in products" :key="product.id">
          <CardContent class="py-4">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-lg bg-muted flex items-center justify-center shrink-0 overflow-hidden">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
                <Package v-else class="w-7 h-7 text-muted-foreground/30" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <p class="font-medium truncate">{{ product.name }}</p>
                  <Badge v-if="product.category" variant="outline" class="text-xs shrink-0">{{ product.category }}</Badge>
                </div>
                <div class="flex items-center gap-3 mt-1 text-sm">
                  <span class="font-bold text-primary">{{ formatPrice(product.price) }}</span>
                  <span class="text-muted-foreground">Stok: {{ product.stock }}</span>
                </div>
              </div>
              <div class="flex gap-2 shrink-0">
                <Button variant="outline" size="sm" @click="openForm(product)">Edit</Button>
                <Button variant="destructive" size="sm" @click="confirmDelete(product)">Hapus</Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </template>

    <!-- Form Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal">
        <div class="modal-head">
          <div class="modal-head-left">
            <div class="modal-icon"><Package class="w-4 h-4" /></div>
            <div>
              <h2 class="modal-title">{{ editingProduct ? 'Edit Produk' : 'Tambah Produk' }}</h2>
              <p class="modal-sub">Isi detail produk di bawah ini</p>
            </div>
          </div>
          <button class="modal-close" @click="showForm = false"><X class="w-4 h-4" /></button>
        </div>

        <div class="modal-body">
          <div class="form-grid">
            <div class="field span-2">
              <label>Nama Produk <span class="req">*</span></label>
              <input v-model="form.name" required placeholder="Nama produk" />
            </div>
            <div class="field">
              <label>Harga (Rp) <span class="req">*</span></label>
              <input v-model="form.price" type="number" min="1" required placeholder="0" />
            </div>
            <div class="field">
              <label>Stok <span class="req">*</span></label>
              <input v-model="form.stock" type="number" min="0" required placeholder="0" />
            </div>
            <div class="field">
              <label>Kategori</label>
              <select v-model="form.category">
                <option value="">Pilih kategori</option>
                <option value="Electronics">Electronics</option>
                <option value="Fashion">Fashion</option>
                <option value="Food">Food</option>
                <option value="Kosmetik">Kosmetik</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="field">
              <label>Brand</label>
              <input v-model="form.brand" placeholder="Merek produk" />
            </div>
            <div class="field">
              <label>Berat (gram)</label>
              <input v-model="form.weight" type="number" min="0" placeholder="0" />
            </div>
            <!-- Foto Produk (multiple) -->
            <div class="field span-2">
              <label>Foto Produk <span style="font-weight:400;color:#9ca3af">(maks. 8 foto, 2MB/foto)</span></label>
              <input ref="photoInput" type="file" accept="image/jpg,image/jpeg,image/png,image/webp" multiple style="display:none" @change="onPhotosChange" />
              <div class="photo-grid">
                <!-- existing + new previews -->
                <div v-for="(src, i) in allPhotoPreviews" :key="'p'+i" class="photo-thumb">
                  <img :src="src" />
                  <button type="button" class="thumb-remove" @click="removePhoto(i)"><X class="w-3 h-3" /></button>
                  <span v-if="i === 0" class="thumb-badge">Utama</span>
                </div>
                <!-- add button -->
                <button v-if="allPhotoPreviews.length < 8" type="button" class="photo-add" @click="$refs.photoInput.click()">
                  <ImagePlus class="w-6 h-6 text-muted-foreground/50" />
                  <span>Tambah</span>
                </button>
              </div>
            </div>

            <!-- Video Produk (opsional) -->
            <div class="field span-2">
              <label>Video Produk <span style="font-weight:400;color:#9ca3af">(opsional, MP4/WebM, maks. 50MB)</span></label>
              <input ref="videoInput" type="file" accept="video/mp4,video/webm,video/quicktime" style="display:none" @change="onVideoChange" />
              <div v-if="videoPreview" class="video-preview-wrap">
                <video :src="videoPreview" controls class="video-preview" />
                <button type="button" class="video-remove" @click="removeVideo"><X class="w-3.5 h-3.5" /></button>
              </div>
              <div v-else class="upload-area video-upload" @click="$refs.videoInput.click()" @dragover.prevent @drop.prevent="onVideoDrop">
                <div class="upload-placeholder">
                  <Video class="w-8 h-8 text-muted-foreground/40" />
                  <p class="upload-hint">Klik atau drag & drop video di sini</p>
                  <p class="upload-hint-sub">MP4, WebM, MOV — maks. 50MB</p>
                </div>
              </div>
            </div>

            <div class="field span-2">
              <label>Deskripsi</label>
              <textarea v-model="form.description" rows="2" placeholder="Deskripsi produk..." class="field-textarea" />
            </div>
          </div>
          <div v-if="formError" class="form-error">{{ formError }}</div>
        </div>

        <div class="modal-foot">
          <button class="btn-cancel" @click="showForm = false">Batal</button>
          <button class="btn-save" @click="submitForm" :disabled="submitting">
            {{ submitting ? 'Menyimpan...' : editingProduct ? 'Simpan Perubahan' : 'Tambah Produk' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete confirm -->
    <div v-if="deletingProduct" class="modal-overlay" @click.self="deletingProduct = null">
      <div class="modal" style="max-width:420px">
        <div class="modal-head">
          <div class="modal-head-left">
            <div class="modal-icon" style="background:#ef4444"><Trash2 class="w-4 h-4" /></div>
            <div>
              <h2 class="modal-title">Hapus Produk?</h2>
              <p class="modal-sub">Tindakan ini tidak dapat dibatalkan</p>
            </div>
          </div>
          <button class="modal-close" @click="deletingProduct = null"><X class="w-4 h-4" /></button>
        </div>
        <div class="modal-body">
          <p class="text-[13px] text-muted-foreground">
            Produk <strong class="text-foreground">"{{ deletingProduct.name }}"</strong> akan dihapus secara permanen.
          </p>
        </div>
        <div class="modal-foot">
          <button class="btn-cancel" @click="deletingProduct = null">Batal</button>
          <button class="btn-delete" @click="deleteProduct" :disabled="submitting">
            {{ submitting ? 'Menghapus...' : 'Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Plus, Package, Store, X, Trash2, ImagePlus, Video } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { sellerApi } from '@/services/seller'
import { toast } from 'vue-sonner'

const router = useRouter()
const products = ref([])
const loading = ref(true)
const noStore = ref(false)
const showForm = ref(false)
const editingProduct = ref(null)
const deletingProduct = ref(null)
const submitting = ref(false)
const formError = ref('')

const emptyForm = { name: '', price: '', stock: '', category: '', brand: '', description: '', weight: '', image_url: '' }
const form = ref({ ...emptyForm })
// existing image URLs (from server, for edit)
const existingPhotos = ref([])
// new File objects to upload
const newPhotoFiles = ref([])
// new photo object-URL previews
const newPhotoPreviews = ref([])
// computed combined previews: existing first, then new
const allPhotoPreviews = computed(() => [...existingPhotos.value, ...newPhotoPreviews.value])

const videoFile = ref(null)
const videoPreview = ref(null)
const removeVideoFlag = ref(false)

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}

async function fetchProducts() {
  loading.value = true
  try {
    const { data } = await sellerApi.getProducts()
    products.value = data.data || data
  } catch (e) {
    if (e.response?.status === 422) noStore.value = true
  } finally {
    loading.value = false
  }
}

function openForm(product = null) {
  editingProduct.value = product
  form.value = product
    ? { name: product.name, price: product.price, stock: product.stock, category: product.category || '', brand: product.brand || '', description: product.description || '', weight: product.weight || '', image_url: product.image_url || '' }
    : { ...emptyForm }
  existingPhotos.value = product?.images?.length ? [...product.images] : (product?.image_url ? [product.image_url] : [])
  newPhotoFiles.value = []
  newPhotoPreviews.value = []
  videoFile.value = null
  videoPreview.value = product?.video_url || null
  removeVideoFlag.value = false
  formError.value = ''
  showForm.value = true
}

function onPhotosChange(e) {
  const files = Array.from(e.target.files)
  const remaining = 8 - allPhotoPreviews.value.length
  const toAdd = files.slice(0, remaining)
  toAdd.forEach(f => {
    newPhotoFiles.value.push(f)
    newPhotoPreviews.value.push(URL.createObjectURL(f))
  })
  e.target.value = ''
}

function removePhoto(index) {
  if (index < existingPhotos.value.length) {
    existingPhotos.value.splice(index, 1)
  } else {
    const ni = index - existingPhotos.value.length
    URL.revokeObjectURL(newPhotoPreviews.value[ni])
    newPhotoFiles.value.splice(ni, 1)
    newPhotoPreviews.value.splice(ni, 1)
  }
}

function onVideoChange(e) {
  const file = e.target.files[0]
  if (!file) return
  videoFile.value = file
  videoPreview.value = URL.createObjectURL(file)
  removeVideoFlag.value = false
}

function onVideoDrop(e) {
  const file = e.dataTransfer.files[0]
  if (!file || !file.type.startsWith('video/')) return
  videoFile.value = file
  videoPreview.value = URL.createObjectURL(file)
  removeVideoFlag.value = false
}

function removeVideo() {
  if (videoPreview.value?.startsWith('blob:')) URL.revokeObjectURL(videoPreview.value)
  videoFile.value = null
  videoPreview.value = null
  removeVideoFlag.value = true
}

async function submitForm() {
  formError.value = ''
  if (Number(form.value.price) < 1) {
    formError.value = 'Harga produk minimal Rp 1.'
    return
  }
  submitting.value = true
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([k, v]) => { if (v !== '' && v != null) fd.append(k, v) })
    newPhotoFiles.value.forEach(f => fd.append('images[]', f))
    fd.append('existing_images', JSON.stringify(existingPhotos.value))
    if (videoFile.value) fd.append('video', videoFile.value)
    if (removeVideoFlag.value) fd.append('remove_video', '1')

    if (editingProduct.value) {
      fd.append('_method', 'PUT')
      await sellerApi.updateProduct(editingProduct.value.id, fd)
      toast.success('Produk berhasil diperbarui!')
    } else {
      await sellerApi.createProduct(fd)
      toast.success('Produk berhasil ditambahkan!')
    }
    showForm.value = false
    fetchProducts()
  } catch (e) {
    const errs = e.response?.data?.errors
    formError.value = errs ? Object.values(errs).flat().join(' ') : e.response?.data?.message || 'Gagal menyimpan produk.'
  } finally {
    submitting.value = false
  }
}

function confirmDelete(product) {
  deletingProduct.value = product
}

async function deleteProduct() {
  submitting.value = true
  try {
    await sellerApi.deleteProduct(deletingProduct.value.id)
    toast.success('Produk dihapus.')
    deletingProduct.value = null
    fetchProducts()
  } catch {
    toast.error('Gagal menghapus produk.')
  } finally {
    submitting.value = false
  }
}

onMounted(fetchProducts)
</script>

<style scoped>
.modal-overlay {
  position:fixed; inset:0; background:rgba(15,10,20,.45);
  display:flex; align-items:center; justify-content:center;
  z-index:50; padding:16px; backdrop-filter:blur(2px);
}
.modal {
  background:#fff; border-radius:18px; width:100%; max-width:580px;
  border:1px solid #f3e0e6;
  box-shadow:0 30px 80px rgba(196,25,82,.2);
  overflow:hidden; max-height:90vh; display:flex; flex-direction:column;
}
.modal-head {
  display:flex; align-items:center; justify-content:space-between;
  padding:20px 24px; border-bottom:1px solid #f3e0e6;
  background:linear-gradient(135deg,#fdf2f5,#fff); flex-shrink:0;
}
.modal-head-left { display:flex; align-items:center; gap:12px; }
.modal-icon {
  width:36px; height:36px; border-radius:10px;
  background:#c41952; color:#fff;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.modal-title { font-size:15px; font-weight:800; color:#1a1a1a; }
.modal-sub   { font-size:12px; color:#a06070; margin-top:2px; }
.modal-close {
  width:32px; height:32px; border-radius:8px; border:1px solid #f3e0e6;
  background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;
  color:#9ca3af; transition:all .15s;
}
.modal-close:hover { background:#fce4ec; color:#c41952; border-color:#f3c6d4; }
.modal-body  { padding:22px 24px; overflow-y:auto; }
.modal-foot  { padding:16px 24px; border-top:1px solid #f3e0e6; display:flex; justify-content:flex-end; gap:8px; flex-shrink:0; }

.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.span-2 { grid-column:span 2; }
.field { display:flex; flex-direction:column; gap:6px; }
.field label { font-size:12px; font-weight:600; color:#374151; }
.field input, .field select {
  height:38px; padding:0 12px; border-radius:10px;
  border:1.5px solid #e5e7eb; font-size:13px; outline:none;
  background:#fff; transition:border-color .15s, box-shadow .15s;
}
.field input:focus, .field select:focus { border-color:#c41952; box-shadow:0 0 0 3px rgba(196,25,82,.08); }
.field-textarea {
  width:100%; border-radius:10px; border:1.5px solid #e5e7eb;
  padding:10px 12px; font-size:13px; outline:none;
  background:#fff; resize:none; font-family:inherit;
  transition:border-color .15s, box-shadow .15s;
}
.field-textarea:focus { border-color:#c41952; box-shadow:0 0 0 3px rgba(196,25,82,.08); }
.req { color:#c41952; }
.form-error { font-size:12px; color:#ef4444; margin-top:12px; }

/* ── Photo grid ── */
.photo-grid {
  display:grid; grid-template-columns:repeat(4,1fr); gap:8px;
}
.photo-thumb {
  position:relative; aspect-ratio:1; border-radius:10px; overflow:hidden;
  border:1.5px solid #e5e7eb;
}
.photo-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.thumb-remove {
  position:absolute; top:4px; right:4px;
  width:22px; height:22px; border-radius:5px;
  background:rgba(0,0,0,.55); color:#fff; border:none;
  cursor:pointer; display:flex; align-items:center; justify-content:center;
}
.thumb-remove:hover { background:rgba(196,25,82,.85); }
.thumb-badge {
  position:absolute; bottom:4px; left:4px;
  font-size:10px; font-weight:700; background:#c41952; color:#fff;
  border-radius:4px; padding:1px 5px;
}
.photo-add {
  aspect-ratio:1; border-radius:10px; border:2px dashed #e5e7eb;
  background:#fafafa; cursor:pointer;
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px;
  font-size:11px; color:#9ca3af; transition:border-color .15s, background .15s;
}
.photo-add:hover { border-color:#c41952; background:#fdf2f5; color:#c41952; }

/* ── Video ── */
.upload-area {
  border:2px dashed #e5e7eb; border-radius:12px; cursor:pointer;
  transition:border-color .15s, background .15s; overflow:hidden;
  min-height:100px; display:flex; align-items:center; justify-content:center;
}
.upload-area:hover { border-color:#c41952; background:#fdf2f5; }
.upload-placeholder { display:flex; flex-direction:column; align-items:center; gap:6px; padding:20px; }
.upload-hint { font-size:13px; font-weight:500; color:#6b7280; }
.upload-hint-sub { font-size:11px; color:#9ca3af; }
.video-preview-wrap { position:relative; border-radius:12px; overflow:hidden; border:1.5px solid #e5e7eb; }
.video-preview { width:100%; max-height:200px; display:block; background:#000; }
.video-remove {
  position:absolute; top:8px; right:8px;
  width:28px; height:28px; border-radius:6px;
  background:rgba(0,0,0,.55); color:#fff; border:none;
  cursor:pointer; display:flex; align-items:center; justify-content:center;
}
.video-remove:hover { background:rgba(196,25,82,.85); }

.btn-cancel {
  height:36px; padding:0 18px; border-radius:9px;
  border:1.5px solid #e5e7eb; background:#fff; font-size:13px; font-weight:600;
  color:#6b7280; cursor:pointer; transition:all .15s;
}
.btn-cancel:hover { border-color:#c41952; color:#c41952; background:#fdf2f5; }
.btn-save {
  height:36px; padding:0 18px; border-radius:9px;
  border:0; background:#c41952; color:#fff; font-size:13px; font-weight:600;
  cursor:pointer; transition:opacity .15s;
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
