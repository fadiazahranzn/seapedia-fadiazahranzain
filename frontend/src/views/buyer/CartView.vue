<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Keranjang</h1>
      <Button v-if="cart?.items?.length" variant="ghost" size="sm" class="text-destructive" @click="doClear">Kosongkan</Button>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 2" :key="i" class="h-20 bg-muted rounded-lg animate-pulse" />
    </div>

    <div v-else-if="!cart?.items?.length" class="text-center py-16 text-muted-foreground">
      <ShoppingCart class="w-14 h-14 mx-auto mb-3 opacity-30" />
      <p class="font-medium">Keranjang kosong</p>
      <Button class="mt-4" as-child><RouterLink to="/products">Belanja Sekarang</RouterLink></Button>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Items -->
      <div class="lg:col-span-2 space-y-3">
        <Card>
          <CardContent class="py-3 flex items-center gap-2 text-sm text-muted-foreground">
            <Store class="w-4 h-4" />
            <span>Toko: <strong class="text-foreground">{{ cart.store?.name }}</strong></span>
            <span class="ml-auto text-xs">Keranjang hanya bisa dari 1 toko</span>
          </CardContent>
        </Card>

        <Card v-for="item in cart.items" :key="item.id">
          <CardContent class="py-4">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 bg-muted rounded-lg flex items-center justify-center shrink-0 overflow-hidden">
                <img v-if="item.product?.image_url" :src="item.product.image_url" :alt="item.product.name" class="w-full h-full object-cover" />
                <Package v-else class="w-7 h-7 text-muted-foreground/30" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium text-sm truncate">{{ item.product?.name }}</p>
                <p class="text-primary font-bold text-sm mt-0.5">{{ formatPrice(item.product?.price) }}</p>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <button class="w-7 h-7 rounded-full border flex items-center justify-center hover:bg-muted" @click="updateQty(item, item.quantity - 1)">
                  <Minus class="w-3 h-3" />
                </button>
                <span class="w-8 text-center font-medium text-sm">{{ item.quantity }}</span>
                <button class="w-7 h-7 rounded-full border flex items-center justify-center hover:bg-muted" @click="updateQty(item, item.quantity + 1)">
                  <Plus class="w-3 h-3" />
                </button>
                <button class="ml-2 text-muted-foreground hover:text-destructive" @click="removeItem(item)">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div class="mt-2 text-right text-sm font-medium">
              Subtotal: {{ formatPrice(item.product?.price * item.quantity) }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Checkout summary -->
      <div>
        <Card class="sticky top-20">
          <CardContent class="pt-6 space-y-4">
            <h2 class="font-semibold">Ringkasan Pesanan</h2>

            <!-- Delivery Method -->
            <div class="space-y-2">
              <Label>Metode Pengiriman</Label>
              <div class="space-y-2">
                <label v-for="method in deliveryMethods" :key="method.value"
                  class="flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-colors"
                  :class="selectedMethod === method.value ? 'border-primary bg-primary/5' : 'border-border hover:border-muted-foreground'">
                  <div class="flex items-center gap-2">
                    <input type="radio" v-model="selectedMethod" :value="method.value" @change="loadPreview" class="accent-primary" />
                    <div>
                      <p class="text-sm font-medium">{{ method.label }}</p>
                      <p class="text-xs text-muted-foreground">{{ method.desc }}</p>
                    </div>
                  </div>
                  <span class="text-sm font-medium">{{ formatPrice(method.fee) }}</span>
                </label>
              </div>
            </div>

            <!-- Voucher -->
            <div class="space-y-2">
              <Label>Kode Voucher <span class="text-muted-foreground font-normal">(opsional)</span></Label>
              <div class="flex gap-2">
                <Input v-model="voucherCode" placeholder="Contoh: SEAPEDIA10" class="uppercase" @keyup.enter="applyVoucher" />
                <Button variant="outline" size="sm" @click="applyVoucher" :disabled="applyingVoucher">
                  {{ appliedVoucher ? 'Hapus' : 'Pakai' }}
                </Button>
              </div>
              <p v-if="voucherError" class="text-destructive text-xs">{{ voucherError }}</p>
              <p v-if="appliedVoucher" class="text-green-600 text-xs flex items-center gap-1">
                <CheckCircle class="w-3 h-3" /> Voucher "{{ appliedVoucher.code }}" berhasil dipakai
              </p>
            </div>

            <!-- Promo -->
            <div class="space-y-2">
              <Label>Kode Promo <span class="text-muted-foreground font-normal">(opsional)</span></Label>
              <div class="flex gap-2">
                <Input v-model="promoCode" placeholder="Contoh: FLASHSALE" class="uppercase" @keyup.enter="applyPromo" />
                <Button variant="outline" size="sm" @click="applyPromo" :disabled="applyingPromo">
                  {{ appliedPromo ? 'Hapus' : 'Pakai' }}
                </Button>
              </div>
              <p v-if="promoError" class="text-destructive text-xs">{{ promoError }}</p>
              <p v-if="appliedPromo" class="text-green-600 text-xs flex items-center gap-1">
                <CheckCircle class="w-3 h-3" /> Promo "{{ appliedPromo.code }}" berhasil dipakai
              </p>
            </div>

            <!-- Price breakdown -->
            <div v-if="preview" class="divide-y text-sm space-y-2">
              <div class="pb-2 space-y-1">
                <div class="flex justify-between"><span class="text-muted-foreground">Subtotal</span><span>{{ formatPrice(preview.subtotal) }}</span></div>
                <div v-if="preview.voucher_discount > 0" class="flex justify-between text-green-600">
                  <span>Diskon Voucher</span><span>-{{ formatPrice(preview.voucher_discount) }}</span>
                </div>
                <div v-if="preview.promo_discount > 0" class="flex justify-between text-green-600">
                  <span>Diskon Promo</span><span>-{{ formatPrice(preview.promo_discount) }}</span>
                </div>
                <div class="flex justify-between"><span class="text-muted-foreground">Ongkir</span><span>{{ formatPrice(preview.delivery_fee) }}</span></div>
                <div class="flex justify-between text-muted-foreground text-xs">
                  <span>Dasar PPN</span><span>{{ formatPrice(preview.tax_base) }}</span>
                </div>
                <div class="flex justify-between"><span class="text-muted-foreground">PPN 12%</span><span>{{ formatPrice(preview.ppn_amount) }}</span></div>
              </div>
              <div class="pt-2 flex justify-between font-bold">
                <span>Total</span><span class="text-primary">{{ formatPrice(preview.total) }}</span>
              </div>
            </div>

            <!-- Address -->
            <div class="space-y-2">
              <Label>Alamat Pengiriman</Label>
              <select v-model="selectedAddress" class="w-full border border-input rounded-md px-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                <option value="">Pilih alamat</option>
                <option v-for="addr in addresses" :key="addr.id" :value="addr.id">
                  {{ addr.label }} - {{ addr.city }}
                </option>
              </select>
              <RouterLink to="/buyer/addresses" class="text-xs text-primary hover:underline">+ Tambah alamat baru</RouterLink>
            </div>

            <p v-if="checkoutError" class="text-destructive text-sm">{{ checkoutError }}</p>

            <Button class="w-full" :disabled="!preview || !selectedAddress || checkingOut" @click="doCheckout">
              {{ checkingOut ? 'Memproses...' : 'Checkout Sekarang' }}
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { ShoppingCart, Store, Package, Plus, Minus, Trash2, CheckCircle } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { buyerApi } from '@/services/buyer'
import { toast } from 'vue-sonner'

const router = useRouter()
const cart = ref(null)
const addresses = ref([])
const preview = ref(null)
const loading = ref(true)
const selectedMethod = ref('regular')
const selectedAddress = ref('')
const checkingOut = ref(false)
const checkoutError = ref('')

const voucherCode = ref('')
const promoCode = ref('')
const appliedVoucher = ref(null)
const appliedPromo = ref(null)
const voucherError = ref('')
const promoError = ref('')
const applyingVoucher = ref(false)
const applyingPromo = ref(false)

const deliveryMethods = [
  { value: 'instant', label: 'Instant', desc: 'Hari yang sama', fee: 25000 },
  { value: 'next_day', label: 'Next Day', desc: '1 hari kerja', fee: 15000 },
  { value: 'regular', label: 'Regular', desc: '2-3 hari kerja', fee: 10000 },
]

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}

async function loadCart() {
  loading.value = true
  try {
    const { data } = await buyerApi.getCart()
    cart.value = data.data
    if (cart.value?.items?.length) loadPreview()
  } finally { loading.value = false }
}

async function loadAddresses() {
  try {
    const { data } = await buyerApi.getAddresses()
    addresses.value = data.data
    const def = data.data.find(a => a.is_default)
    if (def) selectedAddress.value = def.id
  } catch {}
}

async function loadPreview() {
  if (!cart.value?.items?.length) return
  try {
    const { data } = await buyerApi.previewCheckout({
      delivery_method: selectedMethod.value,
      voucher_code: appliedVoucher.value?.code || undefined,
      promo_code: appliedPromo.value?.code || undefined,
    })
    preview.value = data.data
  } catch {}
}

async function applyVoucher() {
  if (appliedVoucher.value) {
    appliedVoucher.value = null
    voucherCode.value = ''
    voucherError.value = ''
    loadPreview()
    return
  }
  if (!voucherCode.value) return
  applyingVoucher.value = true
  voucherError.value = ''
  try {
    const { data } = await buyerApi.validateVoucher(voucherCode.value)
    appliedVoucher.value = data.data
    loadPreview()
    toast.success('Voucher berhasil dipakai!')
  } catch (e) {
    voucherError.value = e.response?.data?.message || 'Voucher tidak valid.'
  } finally { applyingVoucher.value = false }
}

async function applyPromo() {
  if (appliedPromo.value) {
    appliedPromo.value = null
    promoCode.value = ''
    promoError.value = ''
    loadPreview()
    return
  }
  if (!promoCode.value) return
  applyingPromo.value = true
  promoError.value = ''
  try {
    const { data } = await buyerApi.validatePromo(promoCode.value)
    appliedPromo.value = data.data
    loadPreview()
    toast.success('Promo berhasil dipakai!')
  } catch (e) {
    promoError.value = e.response?.data?.message || 'Promo tidak valid.'
  } finally { applyingPromo.value = false }
}

async function updateQty(item, qty) {
  if (qty < 1) { removeItem(item); return }
  try {
    await buyerApi.updateItem(item.id, qty)
    item.quantity = qty
    loadPreview()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal update.')
  }
}

async function removeItem(item) {
  try {
    await buyerApi.removeItem(item.id)
    cart.value.items = cart.value.items.filter(i => i.id !== item.id)
    if (!cart.value.items.length) { cart.value.store_id = null; cart.value.store = null; preview.value = null }
    else loadPreview()
    toast.success('Item dihapus.')
  } catch {}
}

async function doClear() {
  await buyerApi.clearCart()
  cart.value.items = []
  cart.value.store_id = null
  cart.value.store = null
  preview.value = null
  toast.success('Keranjang dikosongkan.')
}

async function doCheckout() {
  if (!selectedAddress.value || !selectedMethod.value) return
  checkingOut.value = true
  checkoutError.value = ''
  try {
    const { data } = await buyerApi.checkout({
      address_id: selectedAddress.value,
      delivery_method: selectedMethod.value,
      voucher_code: appliedVoucher.value?.code || undefined,
      promo_code: appliedPromo.value?.code || undefined,
    })
    toast.success('Checkout berhasil!')
    router.push(`/buyer/orders/${data.data.id}`)
  } catch (e) {
    checkoutError.value = e.response?.data?.message || 'Checkout gagal.'
  } finally { checkingOut.value = false }
}

onMounted(() => { loadCart(); loadAddresses() })
</script>
