<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-[22px] font-bold tracking-[-0.03em]">Keranjang</h1>
        <p v-if="cart?.items?.length" class="text-[13px] text-muted-foreground mt-0.5">
          {{ cart.items.length }} item{{ cart.items.length > 1 ? '' : '' }} dari
          <span class="font-medium text-foreground">{{ cart.store?.name }}</span>
        </p>
      </div>
      <button
        v-if="cart?.items?.length"
        class="text-[12px] font-semibold text-red-500 bg-transparent border-none cursor-pointer px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors"
        @click="doClear"
      >
        Kosongkan
      </button>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-6 items-start">
      <div class="space-y-3">
        <div v-for="i in 2" :key="i" class="h-28 bg-muted rounded-2xl animate-pulse" />
      </div>
      <div class="h-[500px] bg-muted rounded-2xl animate-pulse" />
    </div>

    <!-- Empty state -->
    <div
      v-else-if="!cart?.items?.length"
      class="flex flex-col items-center gap-4 py-20 text-center bg-card border rounded-2xl"
    >
      <div class="w-16 h-16 rounded-2xl bg-primary/8 flex items-center justify-center">
        <ShoppingCart class="w-7 h-7 text-primary/50" />
      </div>
      <div>
        <p class="text-[15px] font-semibold">Keranjang masih kosong</p>
        <small class="text-[13px] text-muted-foreground">Tambahkan produk untuk mulai belanja</small>
      </div>
      <button
        class="mt-1 inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-[13px] font-semibold text-white bg-primary hover:opacity-90 transition-opacity cursor-pointer border-0 shadow-sm"
        @click="router.push('/buyer/products')"
      >
        Belanja Sekarang
      </button>
    </div>

    <!-- Cart content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-6 items-start">

      <!-- Left: items -->
      <div class="space-y-3">

        <!-- Store info strip -->
        <div class="bg-card border rounded-2xl px-4 py-3 flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
            <Store class="w-4.5 h-4.5 text-primary" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[13px] font-semibold truncate">{{ cart.store?.name }}</p>
            <p class="text-[11px] text-muted-foreground">Keranjang hanya bisa dari 1 toko</p>
          </div>
        </div>

        <!-- Item cards -->
        <div
          v-for="item in cart.items"
          :key="item.id"
          class="group bg-card border rounded-2xl p-4 transition-shadow hover:shadow-sm"
        >
          <div class="flex items-start gap-4">
            <!-- Product image -->
            <div class="w-[72px] h-[72px] rounded-xl bg-muted flex items-center justify-center shrink-0 overflow-hidden border border-border/60">
              <img
                v-if="item.product?.image_url"
                :src="item.product.image_url"
                :alt="item.product?.name"
                class="w-full h-full object-cover"
              />
              <Package v-else class="w-7 h-7 text-muted-foreground/25" />
            </div>

            <!-- Product info -->
            <div class="flex-1 min-w-0 pt-0.5">
              <p class="text-[14px] font-semibold leading-snug line-clamp-2">{{ item.product?.name }}</p>
              <p class="text-[14px] font-bold text-primary mt-1">{{ formatPrice(item.product?.price) }}</p>
            </div>

            <!-- Delete button -->
            <button
              class="w-8 h-8 flex items-center justify-center rounded-lg border-0 bg-transparent text-muted-foreground/50 cursor-pointer hover:text-red-500 hover:bg-red-50 transition-colors shrink-0 mt-0.5"
              @click="removeItem(item)"
              title="Hapus item"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Subtotal + qty row -->
          <div class="flex items-center justify-between mt-3.5 pt-3 border-t border-border/60">
            <p class="text-[12px] text-muted-foreground">
              Subtotal:
              <strong class="text-foreground font-semibold ml-0.5">{{ formatPrice(item.product?.price * item.quantity) }}</strong>
            </p>

            <!-- Qty stepper -->
            <div class="flex items-center gap-0.5 bg-muted/60 rounded-xl p-0.5">
              <button
                class="w-7 h-7 flex items-center justify-center rounded-lg text-foreground text-[15px] font-medium border-0 bg-transparent cursor-pointer hover:bg-background hover:shadow-sm transition-all leading-none"
                @click="updateQty(item, item.quantity - 1)"
              >−</button>
              <div class="w-8 h-7 flex items-center justify-center text-[13px] font-bold text-foreground">
                {{ item.quantity }}
              </div>
              <button
                class="w-7 h-7 flex items-center justify-center rounded-lg text-foreground text-[15px] font-medium border-0 bg-transparent cursor-pointer hover:bg-background hover:shadow-sm transition-all leading-none"
                @click="updateQty(item, item.quantity + 1)"
              >+</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: checkout panel -->
      <div ref="checkoutPanelRef" class="bg-card border rounded-2xl overflow-hidden sticky top-[76px]">

        <!-- Panel header -->
        <div class="px-5 py-4 border-b bg-muted/30">
          <h2 class="text-[15px] font-bold">Ringkasan Pesanan</h2>
        </div>

        <div class="px-5 py-5 space-y-5">

          <!-- Delivery method -->
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground mb-2.5">Metode Pengiriman</p>
            <div class="space-y-2">
              <label
                v-for="method in deliveryMethods"
                :key="method.value"
                class="flex items-center justify-between px-3.5 py-3 rounded-xl border-[1.5px] cursor-pointer transition-all"
                :class="selectedMethod === method.value
                  ? 'border-primary bg-primary/5 shadow-[0_0_0_1px_rgb(var(--primary)/0.15)]'
                  : 'border-border hover:border-primary/40 hover:bg-muted/40'"
              >
                <div class="flex items-center gap-2.5">
                  <input
                    type="radio"
                    v-model="selectedMethod"
                    :value="method.value"
                    @change="loadPreview"
                    class="accent-primary w-3.5 h-3.5 shrink-0"
                  />
                  <div>
                    <p class="text-[13px] font-semibold">{{ method.label }}</p>
                    <p class="text-[11px] text-muted-foreground">{{ method.desc }}</p>
                  </div>
                </div>
                <span
                  class="text-[12px] font-bold tabular-nums"
                  :class="selectedMethod === method.value ? 'text-primary' : 'text-muted-foreground'"
                >{{ formatPrice(method.fee) }}</span>
              </label>
            </div>
          </div>

          <!-- Voucher + Promo codes in a compact grid -->
          <div class="space-y-3">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Kode Diskon</p>

            <!-- Voucher -->
            <div>
              <div class="flex gap-2">
                <div class="relative flex-1">
                  <Tag class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground/50" />
                  <input
                    v-model="voucherCode"
                    type="text"
                    placeholder="Kode Voucher"
                    :disabled="!!appliedVoucher"
                    class="w-full h-9 pl-8 pr-3 rounded-xl border text-[12px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 uppercase font-sans bg-background transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    @keyup.enter="applyVoucher"
                  />
                </div>
                <button
                  class="h-9 px-3.5 rounded-xl border text-[12px] font-semibold transition-colors cursor-pointer whitespace-nowrap"
                  :class="appliedVoucher
                    ? 'border-red-200 text-red-600 bg-red-50 hover:bg-red-100'
                    : 'border-border text-muted-foreground bg-background hover:border-primary hover:text-primary hover:bg-primary/5'"
                  @click="applyVoucher"
                  :disabled="applyingVoucher"
                >
                  {{ appliedVoucher ? 'Hapus' : 'Pakai' }}
                </button>
              </div>
              <p v-if="voucherError" class="flex items-center gap-1 text-[11px] text-red-500 mt-1.5">
                <X class="w-3 h-3 shrink-0" /> {{ voucherError }}
              </p>
              <p v-if="appliedVoucher" class="flex items-center gap-1 text-[11px] text-green-600 mt-1.5">
                <Check class="w-3 h-3 shrink-0" /> Voucher "{{ appliedVoucher.code }}" berhasil dipakai
              </p>
            </div>

            <!-- Promo -->
            <div>
              <div class="flex gap-2">
                <div class="relative flex-1">
                  <Percent class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground/50" />
                  <input
                    v-model="promoCode"
                    type="text"
                    placeholder="Kode Promo"
                    :disabled="!!appliedPromo"
                    class="w-full h-9 pl-8 pr-3 rounded-xl border text-[12px] outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 uppercase font-sans bg-background transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    @keyup.enter="applyPromo"
                  />
                </div>
                <button
                  class="h-9 px-3.5 rounded-xl border text-[12px] font-semibold transition-colors cursor-pointer whitespace-nowrap"
                  :class="appliedPromo
                    ? 'border-red-200 text-red-600 bg-red-50 hover:bg-red-100'
                    : 'border-border text-muted-foreground bg-background hover:border-primary hover:text-primary hover:bg-primary/5'"
                  @click="applyPromo"
                  :disabled="applyingPromo"
                >
                  {{ appliedPromo ? 'Hapus' : 'Pakai' }}
                </button>
              </div>
              <p v-if="promoError" class="flex items-center gap-1 text-[11px] text-red-500 mt-1.5">
                <X class="w-3 h-3 shrink-0" /> {{ promoError }}
              </p>
              <p v-if="appliedPromo" class="flex items-center gap-1 text-[11px] text-green-600 mt-1.5">
                <Check class="w-3 h-3 shrink-0" /> Promo "{{ appliedPromo.code }}" berhasil dipakai
              </p>
            </div>
          </div>

          <!-- Price breakdown -->
          <div v-if="preview" class="bg-muted/40 rounded-xl px-4 py-3.5 space-y-2.5">
            <div class="flex justify-between text-[12px]">
              <span class="text-muted-foreground">Subtotal</span>
              <span class="font-medium tabular-nums">{{ formatPrice(preview.subtotal) }}</span>
            </div>
            <div
              v-if="(preview.voucher_discount ?? 0) + (preview.promo_discount ?? 0) > 0"
              class="flex justify-between text-[12px] text-green-600"
            >
              <span class="flex items-center gap-1"><Sparkles class="w-3 h-3" /> Diskon</span>
              <span class="font-semibold tabular-nums">−{{ formatPrice((preview.voucher_discount ?? 0) + (preview.promo_discount ?? 0)) }}</span>
            </div>
            <div class="flex justify-between text-[12px]">
              <span class="text-muted-foreground">Ongkir ({{ deliveryMethods.find(m => m.value === selectedMethod)?.label }})</span>
              <span class="font-medium tabular-nums">{{ formatPrice(preview.delivery_fee) }}</span>
            </div>
            <div class="flex justify-between text-[12px]">
              <span class="text-muted-foreground">PPN 12%</span>
              <span class="font-medium tabular-nums">{{ formatPrice(preview.ppn_amount) }}</span>
            </div>
            <div class="flex justify-between text-[14px] font-bold pt-2 border-t border-border/60 mt-1">
              <span>Total</span>
              <span class="text-primary tabular-nums">{{ formatPrice(preview.total) }}</span>
            </div>
          </div>

          <!-- Address -->
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground mb-2.5">Alamat Pengiriman</p>
            <Select v-model="selectedAddress">
              <SelectTrigger class="w-full h-10 text-[13px] rounded-xl">
                <SelectValue placeholder="Pilih alamat pengiriman" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="addr in addresses" :key="addr.id" :value="String(addr.id)">
                  <span class="font-medium">{{ addr.label }}</span>
                  <span class="text-muted-foreground"> — {{ addr.full_address }}, {{ addr.city }}</span>
                </SelectItem>
              </SelectContent>
            </Select>
            <RouterLink
              to="/buyer/addresses"
              class="text-[11px] text-primary hover:underline mt-1.5 inline-flex items-center gap-0.5"
            >
              <Plus class="w-3 h-3" /> Tambah alamat baru
            </RouterLink>
          </div>

        </div>

        <!-- Checkout footer -->
        <div class="px-5 pb-5">
          <p v-if="checkoutError" class="text-[12px] text-red-500 mb-3 flex items-center gap-1.5">
            <X class="w-3.5 h-3.5 shrink-0" /> {{ checkoutError }}
          </p>
          <button
            class="w-full h-12 rounded-xl border-0 text-[14px] font-bold text-white flex items-center justify-center gap-2 cursor-pointer transition-all bg-primary shadow-[0_4px_16px_hsl(var(--primary)/0.3)] hover:opacity-90 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:translate-y-0 disabled:shadow-none"
            :disabled="!preview || !selectedAddress || checkingOut"
            @click="doCheckout"
          >
            <Check v-if="!checkingOut" class="w-4 h-4" />
            <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ checkingOut ? 'Memproses...' : 'Checkout Sekarang' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { ShoppingCart, Store, Package, Trash2, Check, X, Tag, Percent, Sparkles, Plus } from '@lucide/vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { buyerApi } from '@/services/buyer'
import { toast } from 'vue-sonner'

const router = useRouter()
const route = useRoute()
const checkoutPanelRef = ref(null)
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

onMounted(async () => {
  await Promise.all([loadCart(), loadAddresses()])
  if (route.query.buyNow) {
    await nextTick()
    checkoutPanelRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    router.replace({ query: {} })
  }
})
</script>
