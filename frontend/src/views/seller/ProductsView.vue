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
    <div v-if="showForm" class="fixed inset-0 bg-black/50 flex items-start justify-center z-50 p-4 overflow-y-auto">
      <Card class="w-full max-w-lg my-8">
        <CardContent class="pt-6">
          <h2 class="font-semibold text-lg mb-4">{{ editingProduct ? 'Edit Produk' : 'Tambah Produk' }}</h2>
          <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2 space-y-2">
                <Label>Nama Produk *</Label>
                <Input v-model="form.name" required placeholder="Nama produk" />
              </div>
              <div class="space-y-2">
                <Label>Harga (Rp) *</Label>
                <Input v-model="form.price" type="number" min="0" required placeholder="0" />
              </div>
              <div class="space-y-2">
                <Label>Stok *</Label>
                <Input v-model="form.stock" type="number" min="0" required placeholder="0" />
              </div>
              <div class="space-y-2">
                <Label>Kategori</Label>
                <select v-model="form.category" class="w-full border border-input rounded-md px-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                  <option value="">Pilih kategori</option>
                  <option value="Electronics">Electronics</option>
                  <option value="Fashion">Fashion</option>
                  <option value="Food">Food</option>
                  <option value="Kosmetik">Kosmetik</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="space-y-2">
                <Label>Brand</Label>
                <Input v-model="form.brand" placeholder="Merek produk" />
              </div>
              <div class="space-y-2">
                <Label>Berat (gram)</Label>
                <Input v-model="form.weight" type="number" min="0" placeholder="0" />
              </div>
              <div class="space-y-2">
                <Label>URL Gambar</Label>
                <Input v-model="form.image_url" type="url" placeholder="https://..." />
              </div>
              <div class="col-span-2 space-y-2">
                <Label>Deskripsi</Label>
                <textarea v-model="form.description" rows="2" placeholder="Deskripsi produk..." class="w-full border border-input rounded-md px-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring resize-none" />
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
    <div v-if="deletingProduct" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <Card class="w-full max-w-sm">
        <CardContent class="pt-6">
          <h3 class="font-semibold mb-2">Hapus Produk?</h3>
          <p class="text-sm text-muted-foreground mb-4">Produk "<span class="font-medium text-foreground">{{ deletingProduct.name }}</span>" akan dihapus permanen.</p>
          <div class="flex gap-3">
            <Button variant="destructive" :disabled="submitting" @click="deleteProduct">{{ submitting ? 'Menghapus...' : 'Hapus' }}</Button>
            <Button variant="outline" @click="deletingProduct = null">Batal</Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Plus, Package, Store } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
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
  formError.value = ''
  showForm.value = true
}

async function submitForm() {
  submitting.value = true
  formError.value = ''
  try {
    if (editingProduct.value) {
      await sellerApi.updateProduct(editingProduct.value.id, form.value)
      toast.success('Produk berhasil diperbarui!')
    } else {
      await sellerApi.createProduct(form.value)
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
