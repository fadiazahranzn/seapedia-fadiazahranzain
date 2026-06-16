<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Dompet Saya</h1>
      <p class="text-muted-foreground text-sm mt-1">Kelola saldo dan riwayat transaksi</p>
    </div>

    <!-- Balance card -->
    <Card class="mb-6 bg-primary text-primary-foreground">
      <CardContent class="pt-6 pb-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-primary-foreground/70 text-sm mb-1">Saldo Kamu</p>
            <p class="text-3xl font-bold">{{ formatPrice(wallet?.balance ?? 0) }}</p>
          </div>
          <Wallet class="w-10 h-10 opacity-50" />
        </div>
      </CardContent>
    </Card>

    <!-- Top up form -->
    <Card class="mb-6">
      <CardContent class="pt-6">
        <h2 class="font-semibold mb-4">Top Up Saldo</h2>
        <div class="flex flex-wrap gap-2 mb-4">
          <button
            v-for="amount in quickAmounts"
            :key="amount"
            type="button"
            class="px-3 py-1.5 rounded-full border text-sm transition-colors"
            :class="topUpAmount == amount ? 'bg-primary text-primary-foreground border-primary' : 'border-border hover:border-primary'"
            @click="topUpAmount = amount"
          >
            {{ formatPrice(amount) }}
          </button>
        </div>
        <div class="flex gap-3">
          <Input v-model="topUpAmount" type="number" placeholder="Nominal (min Rp 10.000)" class="flex-1" />
          <Button :disabled="topping || !topUpAmount" @click="doTopUp">
            {{ topping ? 'Memproses...' : 'Top Up' }}
          </Button>
        </div>
        <p v-if="topUpError" class="text-destructive text-sm mt-2">{{ topUpError }}</p>
      </CardContent>
    </Card>

    <!-- Transactions -->
    <Card>
      <CardContent class="pt-6">
        <h2 class="font-semibold mb-4">Riwayat Transaksi</h2>

        <div v-if="loadingTx" class="space-y-3">
          <div v-for="i in 3" :key="i" class="h-12 bg-muted rounded animate-pulse" />
        </div>

        <div v-else-if="transactions.length === 0" class="text-center py-8 text-muted-foreground text-sm">
          Belum ada transaksi.
        </div>

        <div v-else class="divide-y">
          <div v-for="tx in transactions" :key="tx.id" class="py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full flex items-center justify-center"
                :class="tx.type === 'topup' ? 'bg-green-100' : tx.type === 'refund' ? 'bg-blue-100' : 'bg-red-100'">
                <ArrowDownLeft v-if="tx.type === 'topup' || tx.type === 'refund'" class="w-4 h-4"
                  :class="tx.type === 'topup' ? 'text-green-600' : 'text-blue-600'" />
                <ArrowUpRight v-else class="w-4 h-4 text-red-600" />
              </div>
              <div>
                <p class="text-sm font-medium capitalize">{{ txLabel(tx.type) }}</p>
                <p class="text-xs text-muted-foreground">{{ formatDate(tx.created_at) }}</p>
              </div>
            </div>
            <p class="font-semibold" :class="tx.type === 'payment' ? 'text-red-600' : 'text-green-600'">
              {{ tx.type === 'payment' ? '-' : '+' }}{{ formatPrice(tx.amount) }}
            </p>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Wallet, ArrowDownLeft, ArrowUpRight } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { buyerApi } from '@/services/buyer'
import { toast } from 'vue-sonner'

const wallet = ref(null)
const transactions = ref([])
const topUpAmount = ref('')
const topping = ref(false)
const topUpError = ref('')
const loadingTx = ref(true)
const quickAmounts = [50000, 100000, 200000, 500000, 1000000]

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p)
}
function formatDate(d) {
  return new Date(d).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
}
function txLabel(type) {
  return { topup: 'Top Up', payment: 'Pembayaran', refund: 'Refund' }[type] || type
}

async function loadWallet() {
  const { data } = await buyerApi.getWallet()
  wallet.value = data.data
}

async function loadTransactions() {
  loadingTx.value = true
  try {
    const { data } = await buyerApi.getTransactions()
    transactions.value = data.data || data
  } catch {}
  finally { loadingTx.value = false }
}

async function doTopUp() {
  const amount = Number(topUpAmount.value)
  topUpError.value = ''
  if (!amount || amount < 10000) {
    topUpError.value = 'Minimal top up adalah Rp 10.000.'
    return
  }
  if (amount > 10000000) {
    topUpError.value = 'Maksimal top up adalah Rp 10.000.000.'
    return
  }
  topping.value = true
  topUpError.value = ''
  try {
    const { data } = await buyerApi.topUp(Number(topUpAmount.value))
    wallet.value.balance = data.data.balance
    topUpAmount.value = ''
    toast.success('Top up berhasil!')
    loadTransactions()
  } catch (e) {
    topUpError.value = e.response?.data?.message || e.response?.data?.errors?.amount?.[0] || 'Gagal top up.'
  } finally {
    topping.value = false
  }
}

onMounted(() => { loadWallet(); loadTransactions() })
</script>
