<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <h1 class="text-[22px] font-bold tracking-[-0.03em]">Keranjang</h1>
      <button
        v-if="cart?.items?.length"
        class="text-[12px] font-semibold text-red-600 bg-transparent border-none cursor-pointer px-2.5 py-1.5 rounded-[10px] hover:bg-red-50 transition-colors"
        @click="doClear"
      >
        Kosongkan
      </button>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 items-start">
      <div class="space-y-3">
        <div v-for="i in 2" :key="i" class="h-24 bg-muted rounded-xl animate-pulse" />
      </div>
      <div class="h-96 bg-muted rounded-xl animate-pulse" />
    </div>

    <div v-else-if="!cart?.items?.length" class="flex flex-col items-center gap-3 py-16 text-center bg-card border rounded-xl">
      <ShoppingCart class="w-12 h-12 text-slate-300" />
      <p class="text-[15px] font-medium">Keranjang kosong</p>
      <small class="text-[13px] text-muted-foreground">Tambahkan produk untuk mulai belanja</small>
      <button
        class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-[10px] text-[13px] font-semibold text-white bg-primary hover:opacity-90 transition-opacity cursor-pointer border-0"
        @click="router.push('/buyer/products')"
      >
        Belanja Sekarang
      </button>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 items-start">
      <!-- Cart items -->
      <div class="space-y-3">
        <!-- Store banner -->
        <div class="bg-card border rounded-xl px-4 py-3.5 flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
            <Store class="w-4 h-4 text-primary" />
          </div>
          <div>
            <p class="text-[14px] font-semibold">{{ cart.store?.name }}</p>
          </div>
          <p class="ml-auto text-[11px] text-muted-foreground">Keranjang hanya bisa dari 1 toko</p>
        </div>

        <!-- Item cards -->
        <div v-for="item in cart.items" :key="item.id" class="bg-card border rounded-xl p-4">
          <div class="flex items-center gap-3.5">
            <div class="w-16 h-16 rounded-[10px] bg-muted flex items-center justify-center shrink-0 overflow-hidden">
              <img v-if="item.product?.image_url" :src="item.product.image_url" :alt="item.product?.name" class="w-full h-full object-cover" />
              <Package v-else class="w-7 h-7 text-muted-foreground/30" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-[14px] font-semibold truncate">{{ item.product?.name }}</p>
              <p class="text-[13px] font-bold text-primary mt-0.5">{{ formatPrice(item.product?.price) }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <!-- qty control -->
              <div class="flex items-center border rounded-[10px] overflow-hidden">
                <button class="w-[30px] h-[30px] flex items-center justify-center hover:bg-muted text-slate-600 text-base border-0 bg-transparent cursor-pointer transition-colors" @click="updateQty(item, item.quantity - 1)">−</button>
                <div class="w-9 h-[30px] flex items-center justify-center text-[14px] font-semibold border-x text-foreground">{{ item.quantity }}</div>
                <button class="w-[30px] h-[30px] flex items-center justify-center hover:bg-muted text-slate-600 text-base border-0 bg-transparent cursor-pointer transition-colors" @click="updateQty(item, item.quantity + 1)">+</button>
              </div>
              <button
                class="w-[30px] h-[30px] flex items-center justify-center rounded-[10px] border-0 bg-transparent text-muted-foreground cursor-pointer hover:text-red-600 hover:bg-red-50 transition-colors"
                @click="removeItem(item)"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
          <p class="text-right text-[12px] text-muted-foreground mt-2.5">
            Subtotal: <strong class="text-foreground">{{ formatPrice(item.product?.price * item.quantity) }}</strong>
          </p>
        </div>
      </div>

      <!-- Checkout panel -->
      <div class="bg-card border rounded-xl p-6 sticky top-[76px]">
        <h2 class="text-[16px] font-bold mb-5">Ringkasan Pesanan</h2>

        <!-- Delivery method -->
        <div class="mb-5">
          <label class="text-[12px] font-semibold mb-2 block tracking-[0.01em]">Metode Pengiriman</label>
          <div class="space-y-2">
            <label
              v-for="method in deliveryMethods"
              :key="method.value"
              class="flex items-center justify-between px-3 py-2.5 rounded-[10px] border-[1.5px] cursor-pointer transition-colors"
              :class="selectedMethod === method.value ? 'border-primary bg-primary/5' : 'border-border hover:border-muted-foreground/40'"
            >
              <div class="flex items-center gap-2">
                <input type="radio" v-model="selectedMethod" :value="method.value" @change="loadPreview" class="accent-primary w-3.5 h-3.5" />
                <div>
                  <p class="text-[13px] font-medium">{{ method.label }}</p>
                  <p class="text-[11px] text-muted-foreground">{{ method.desc }}</p>
                </div>
              </div>
              <span class="text-[13px] font-semibold text-slate-600">{{ formatPrice(method.fee) }}</span>
            </label>
          </div>
        </div>

        <!-- Voucher -->
        <div class="mb-5">
          <label class="text-[12px] font-semibold mb-2 block tracking-[0.01em]">
            Kode Voucher <span class="font-normal text-muted-foreground">(opsional)</span>
          </label>
          <div class="flex gap-2">
            <input
              v-model="voucherCode"
              type="text"
              placeholder="Contoh: SEAPEDIA10"
              class="flex-1 h-9 px-3 rounded-[10px] border text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 uppercase font-sans bg-background transition-colors"
              @keyup.enter="applyVoucher"
            />
            <button
              class="h-9 px-3.5 rounded-[10px] border text-[12px] font-semibold text-slate-600 bg-background hover:border-primary hover:text-primary hover:bg-primary/5 transition-colors cursor-pointer whitespace-nowrap"
              @click="applyVoucher"
              :disabled="applyingVoucher"
            >
              {{ appliedVoucher ? 'Hapus' : 'Pakai' }}
            </button>
          </div>
          <p v-if="voucherError" class="flex items-center gap-1 text-[11px] text-red-600 mt-1.5">
            <X class="w-3 h-3" /> {{ voucherError }}
          </p>
          <p v-if="appliedVoucher" class="flex items-center gap-1 text-[11px] text-green-600 mt-1.5">
            <Check class="w-3 h-3" /> Voucher "{{ appliedVoucher.code }}" berhasil dipakai
          </p>
        </div>

        <!-- Promo -->
        <div class="mb-5">
          <label class="text-[12px] font-semibold mb-2 block tracking-[0.01em]">
            Kode Promo <span class="font-normal text-muted-foreground">(opsional)</span>
          </label>
          <div class="flex gap-2">
            <input
              v-model="promoCode"
              type="text"
              placeholder="Contoh: FLASHSALE"
              class="flex-1 h-9 px-3 rounded-[10px] border text-[13px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 uppercase font-sans bg-background transition-colors"
              @keyup.enter="applyPromo"
            />
            <button
              class="h-9 px-3.5 rounded-[10px] border text-[12px] font-semibold text-slate-600 bg-background hover:border-primary hover:text-primary hover:bg-primary/5 transition-colors cursor-pointer whitespace-nowrap"
              @click="applyPromo"
              :disabled="applyingPromo"
            >
              {{ appliedPromo ? 'Hapus' : 'Pakai' }}
            </button>
          </div>
          <p v-if="promoError" class="flex items-center gap-1 text-[11px] text-red-600 mt-1.5">
            <X class="w-3 h-3" /> {{ promoError }}
          </p>
          <p v-if="appliedPromo" class="flex items-center gap-1 text-[11px] text-green-600 mt-1.5">
            <Check class="w-3 h-3" /> Promo "{{ appliedPromo.code }}" berhasil dipakai
          </p>
        </div>

        <!-- Price breakdown -->
        <div v-if="preview" class="border-y py-3.5 mb-4 space-y-2">
          <div class="flex justify-between text-[13px]">
            <span class="text-muted-foreground">Subtotal</span>
            <span>{{ formatPrice(preview.subtotal) }}</span>
          </div>
          <div v-if="(preview.voucher_discount ?? 0) + (preview.promo_discount ?? 0) > 0" class="flex justify-between text-[13px] text-green-600">
            <span>Diskon</span>
            <span>−{{ formatPrice((preview.voucher_discount ?? 0) + (preview.promo_discount ?? 0)) }}</span>
          </div>
          <div class="flex justify-between text-[13px]">
            <span class="text-muted-foreground">Ongkir ({{ deliveryMethods.find(m => m.value === selectedMethod)?.label }})</span>
            <span>{{ formatPrice(preview.delivery_fee) }}</span>
          </div>
          <div class="flex justify-between text-[13px]">
            <span class="text-muted-foreground">PPN 12%</span>
            <span>{{ formatPrice(preview.ppn_amount) }}</span>
          </div>
          <div class="flex justify-between text-[15px] font-bold mt-1 pt-1">
            <span>Total</span>
            <span class="text-primary">{{ formatPrice(preview.total) }}</span>
          </div>
        </div>

        <!-- Address -->
        <div class="mb-0">
          <label class="text-[12px] font-semibold mb-2 block tracking-[0.01em]">Alamat Pengiriman</label>
          <Select v-model="selectedAddress">
            <SelectTrigger class="w-full h-9 text-[13px] rounded-[10px]">
              <SelectValue placeholder="Pilih alamat" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="addr in addresses" :key="addr.id" :value="String(addr.id)">
                <span class="font-medium">{{ addr.label }}</span>
                <span class="text-muted-foreground"> — {{ addr.full_address }}, {{ addr.city }}</span>
              </SelectItem>
            </SelectContent>
          </Select>
          <RouterLink to="/buyer/addresses" class="text-[11px] text-primary hover:underline mt-1 inline-block">+ Tambah alamat baru</RouterLink>
        </div>

        <p v-if="checkoutError" class="text-[13px] text-red-600 mt-3">{{ checkoutError }}</p>

        <button
          class="w-full h-11 mt-4 rounded-[10px] border-0 text-[14px] font-bold text-white flex items-center justify-center gap-2 cursor-pointer transition-all bg-gradient-to-br from-primary to-cyan-500 shadow-[0_4px_14px_rgba(99,102,241,0.3)] hover:opacity-90 hover:-translate-y-px disabled:opacity-50 disabled:cursor-not-allowed disabled:translate-y-0"
          :disabled="!preview || !selectedAddress || checkingOut"
          @click="doCheckout"
        >
          <Check v-if="!checkingOut" class="w-4 h-4" />
          {{ checkingOut ? 'Memproses...' : 'Checkout Sekarang' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { ShoppingCart, Store, Package, Trash2, Check, X } from '@lucide/vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
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
    if (def) selectedAddress.value = String(def.id)
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
    appliedVoucher.value = null; voucherCode.value = ''; voucherError.value = ''
    loadPreview(); return
  }
  if (!voucherCode.value) return
  applyingVoucher.value = true; voucherError.value = ''
  try {
    const { data } = await buyerApi.validateVoucher(voucherCode.value)
    appliedVoucher.value = data.data
    loadPreview(); toast.success('Voucher berhasil dipakai!')
  } catch (e) {
    voucherError.value = e.response?.data?.message || 'Voucher tidak valid.'
  } finally { applyingVoucher.value = false }
}

async function applyPromo() {
  if (appliedPromo.value) {
    appliedPromo.value = null; promoCode.value = ''; promoError.value = ''
    loadPreview(); return
  }
  if (!promoCode.value) return
  applyingPromo.value = true; promoError.value = ''
  try {
    const { data } = await buyerApi.validatePromo(promoCode.value)
    appliedPromo.value = data.data
    loadPreview(); toast.success('Promo berhasil dipakai!')
  } catch (e) {
    promoError.value = e.response?.data?.message || 'Promo tidak valid.'
  } finally { applyingPromo.value = false }
}

async function updateQty(item, qty) {
  if (qty < 1) { removeItem(item); return }
  try {
    await buyerApi.updateItem(item.id, qty)
    item.quantity = qty; loadPreview()
  } catch (e) { toast.error(e.response?.data?.message || 'Gagal update.') }
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
  cart.value.store_id = null; cart.value.store = null; preview.value = null
  toast.success('Keranjang dikosongkan.')
}

async function doCheckout() {
  if (!selectedAddress.value || !selectedMethod.value) return
  checkingOut.value = true; checkoutError.value = ''
  try {
    const { data } = await buyerApi.checkout({
      address_id: Number(selectedAddress.value),
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
