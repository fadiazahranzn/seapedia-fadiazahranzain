<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <div class="mb-8">
      <h1 class="text-2xl font-bold mb-1">Ulasan Pengguna</h1>
      <p class="text-muted-foreground text-sm">Ceritakan pengalamanmu menggunakan SEAPEDIA</p>
    </div>

    <!-- Form -->
    <Card class="mb-8">
      <CardContent class="pt-6">
        <h2 class="font-semibold mb-4">Tulis Ulasan</h2>
        <form @submit.prevent="submitReview" class="space-y-4">
          <div class="space-y-2">
            <Label for="reviewer_name">Nama</Label>
            <Input id="reviewer_name" v-model="form.reviewer_name" placeholder="Nama kamu" required :disabled="!!auth.user" />
          </div>

          <div class="space-y-2">
            <Label>Rating</Label>
            <div class="flex gap-1">
              <button
                v-for="i in 5"
                :key="i"
                type="button"
                @click="form.rating = i"
                class="p-1 rounded hover:scale-110 transition-transform"
              >
                <Star class="w-7 h-7" :class="i <= form.rating ? 'text-yellow-400 fill-yellow-400' : 'text-muted-foreground'" />
              </button>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="comment">Komentar</Label>
            <textarea
              id="comment"
              v-model="form.comment"
              rows="3"
              placeholder="Bagaimana pengalamanmu menggunakan SEAPEDIA?"
              required
              class="w-full border border-input rounded-md px-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring resize-none"
            />
          </div>

          <p v-if="error" class="text-destructive text-sm">{{ error }}</p>

          <Button type="submit" :disabled="submitting || form.rating === 0">
            {{ submitting ? 'Mengirim...' : 'Kirim Ulasan' }}
          </Button>
        </form>
      </CardContent>
    </Card>

    <!-- Reviews list -->
    <div v-if="loading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="h-24 bg-muted rounded-lg animate-pulse" />
    </div>

    <div v-else-if="reviews.length === 0" class="text-center py-12 text-muted-foreground">
      Belum ada ulasan. Jadilah yang pertama!
    </div>

    <div v-else class="space-y-4">
      <Card v-for="review in reviews" :key="review.id">
        <CardContent class="pt-4 pb-4">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="font-medium text-sm">{{ review.reviewer_name }}</span>
                <span v-if="review.updated_at" class="text-xs text-muted-foreground">(diedit)</span>
              </div>
              <div class="flex gap-0.5 mb-2">
                <Star v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-muted'" />
              </div>
              <p class="text-sm text-muted-foreground">{{ review.comment }}</p>
            </div>
            <div class="flex flex-col items-end gap-1 shrink-0">
              <span class="text-xs text-muted-foreground">{{ formatDate(review.created_at) }}</span>
              <Button
                v-if="auth.user && review.user_id === auth.user.id"
                variant="ghost"
                size="sm"
                class="text-xs h-7"
                @click="startEdit(review)"
              >Edit</Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Edit modal -->
    <div v-if="editingReview" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <Card class="w-full max-w-md">
        <CardContent class="pt-6">
          <h3 class="font-semibold mb-4">Edit Ulasan</h3>
          <form @submit.prevent="saveEdit" class="space-y-4">
            <div class="flex gap-1">
              <button v-for="i in 5" :key="i" type="button" @click="editForm.rating = i" class="p-1">
                <Star class="w-6 h-6" :class="i <= editForm.rating ? 'text-yellow-400 fill-yellow-400' : 'text-muted-foreground'" />
              </button>
            </div>
            <textarea
              v-model="editForm.comment"
              rows="3"
              required
              class="w-full border border-input rounded-md px-3 py-2 text-sm bg-background focus:outline-none focus:ring-2 focus:ring-ring resize-none"
            />
            <div class="flex gap-2">
              <Button type="submit" :disabled="submitting">Simpan</Button>
              <Button type="button" variant="outline" @click="editingReview = null">Batal</Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Star } from '@lucide/vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent } from '@/components/ui/card'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue-sonner'
import api from '@/services/api'

const auth = useAuthStore()
const reviews = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const editingReview = ref(null)

const form = ref({ reviewer_name: auth.user?.name || '', rating: 0, comment: '' })
const editForm = ref({ rating: 0, comment: '' })

function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function fetchReviews() {
  loading.value = true
  try {
    const { data } = await api.get('/reviews')
    reviews.value = data.data || data
  } catch {}
  finally { loading.value = false }
}

async function submitReview() {
  if (form.value.rating === 0) return
  submitting.value = true
  error.value = ''
  try {
    await api.post('/reviews', form.value)
    toast.success('Ulasan berhasil dikirim!')
    form.value = { reviewer_name: auth.user?.name || '', rating: 0, comment: '' }
    fetchReviews()
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal mengirim ulasan.'
  } finally {
    submitting.value = false
  }
}

function startEdit(review) {
  editingReview.value = review
  editForm.value = { rating: review.rating, comment: review.comment }
}

async function saveEdit() {
  submitting.value = true
  try {
    await api.put(`/reviews/${editingReview.value.id}`, editForm.value)
    toast.success('Ulasan diperbarui!')
    editingReview.value = null
    fetchReviews()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal memperbarui ulasan.')
  } finally {
    submitting.value = false
  }
}

onMounted(fetchReviews)
</script>
