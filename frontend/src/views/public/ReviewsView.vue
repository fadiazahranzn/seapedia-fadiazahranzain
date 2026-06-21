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
    <div v-if="editingReview" class="rev-modal-overlay" @click.self="editingReview = null">
      <div class="rev-modal">
        <div class="rev-modal-head">
          <div class="rev-modal-head-left">
            <div class="rev-modal-icon"><Star class="w-4 h-4" /></div>
            <div>
              <h2 class="rev-modal-title">Edit Ulasan</h2>
              <p class="rev-modal-sub">Perbarui rating dan komentarmu</p>
            </div>
          </div>
          <button class="rev-modal-close" @click="editingReview = null"><X class="w-4 h-4" /></button>
        </div>

        <div class="rev-modal-body">
          <div class="field">
            <label class="field-label">Rating</label>
            <div class="star-row">
              <button v-for="i in 5" :key="i" type="button" @click="editForm.rating = i" class="star-btn">
                <Star class="w-6 h-6" :class="i <= editForm.rating ? 'text-yellow-400 fill-yellow-400' : 'text-muted-foreground'" />
              </button>
            </div>
          </div>
          <div class="field" style="margin-top:14px">
            <label class="field-label">Komentar <span class="req">*</span></label>
            <textarea
              v-model="editForm.comment"
              rows="4"
              required
              class="rev-textarea"
            />
          </div>
        </div>

        <div class="rev-modal-foot">
          <button class="btn-cancel" @click="editingReview = null">Batal</button>
          <button class="btn-save" @click="saveEdit" :disabled="submitting">
            {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Star, X } from '@lucide/vue'
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

<style scoped>
.rev-modal-overlay {
  position:fixed; inset:0; background:rgba(15,10,20,.45);
  display:flex; align-items:center; justify-content:center;
  z-index:50; padding:16px; backdrop-filter:blur(2px);
}
.rev-modal {
  background:#fff; border-radius:18px; width:100%; max-width:480px;
  border:1px solid #f3e0e6;
  box-shadow:0 30px 80px rgba(196,25,82,.2);
  overflow:hidden;
}
.rev-modal-head {
  display:flex; align-items:center; justify-content:space-between;
  padding:20px 24px; border-bottom:1px solid #f3e0e6;
  background:linear-gradient(135deg,#fdf2f5,#fff);
}
.rev-modal-head-left { display:flex; align-items:center; gap:12px; }
.rev-modal-icon {
  width:36px; height:36px; border-radius:10px;
  background:#c41952; color:#fff;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.rev-modal-title { font-size:15px; font-weight:800; color:#1a1a1a; }
.rev-modal-sub   { font-size:12px; color:#a06070; margin-top:2px; }
.rev-modal-close {
  width:32px; height:32px; border-radius:8px; border:1px solid #f3e0e6;
  background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;
  color:#9ca3af; transition:all .15s;
}
.rev-modal-close:hover { background:#fce4ec; color:#c41952; border-color:#f3c6d4; }
.rev-modal-body  { padding:22px 24px; }
.rev-modal-foot  { padding:16px 24px; border-top:1px solid #f3e0e6; display:flex; justify-content:flex-end; gap:8px; }

.field { display:flex; flex-direction:column; gap:6px; }
.field-label { font-size:12px; font-weight:600; color:#374151; }
.req { color:#c41952; }
.star-row { display:flex; gap:2px; }
.star-btn { padding:2px; background:none; border:none; cursor:pointer; line-height:0; }
.rev-textarea {
  width:100%; border-radius:10px; border:1.5px solid #e5e7eb;
  padding:10px 12px; font-size:13px; outline:none;
  background:#fff; resize:none; font-family:inherit;
  transition:border-color .15s, box-shadow .15s;
}
.rev-textarea:focus { border-color:#c41952; box-shadow:0 0 0 3px rgba(196,25,82,.08); }

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
</style>
