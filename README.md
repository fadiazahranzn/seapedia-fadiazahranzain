# SEAPEDIA

Platform e-commerce multi-role berbasis web yang memungkinkan Buyer berbelanja, Seller berjualan, dan Driver mengantarkan pesanan — semuanya dalam satu ekosistem.

## Daftar Isi

- [Fitur](#fitur)
- [Tech Stack](#tech-stack)
- [Struktur Project](#struktur-project)
- [Cara Menjalankan](#cara-menjalankan)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Role & Akses](#role--akses)
- [Alur Penggunaan](#alur-penggunaan)
- [API Overview](#api-overview)

---

## Fitur

### Buyer
- Registrasi & login multi-role
- Jelajahi & cari produk (search, filter kategori, sort harga)
- Keranjang belanja (single-store rule)
- Checkout dengan pilihan metode pengiriman (Instant / Next Day / Regular)
- Voucher & kode promo
- Dompet digital (top up & riwayat transaksi)
- Manajemen alamat pengiriman
- Tracking pesanan real-time
- Laporan pengeluaran

### Seller
- Buat & kelola toko
- Manajemen produk (tambah, edit, hapus)
- Proses pesanan masuk
- Laporan pendapatan

### Driver
- Lihat job pengiriman yang tersedia
- Ambil & selesaikan job
- Riwayat job & total penghasilan (80% dari delivery fee)

### Admin
- Dashboard statistik (user, toko, produk, pesanan, pengiriman)
- Manajemen voucher & promo
- Monitoring overdue order dengan sistem refund & reversal otomatis

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12, PHP 8.3 |
| Auth | JWT (tymon/jwt-auth) |
| Database | SQLite (dev) |
| Frontend | Vue 3, Vite, Pinia |
| UI | Tailwind CSS v4, shadcn-vue, Lucide Icons |
| HTTP Client | Axios |

---

## Struktur Project

```
seapedia/
├── backend/          # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   ├── SellerProductController.php
│   │   │   ├── StoreController.php
│   │   │   ├── CartController.php
│   │   │   ├── OrderController.php
│   │   │   ├── DeliveryController.php
│   │   │   ├── WalletController.php
│   │   │   ├── AddressController.php
│   │   │   ├── VoucherController.php
│   │   │   ├── PromoController.php
│   │   │   ├── ReviewController.php
│   │   │   ├── AdminController.php
│   │   │   └── OverdueController.php
│   │   └── Models/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
│       └── api.php
└── frontend/         # Vue 3 SPA
    └── src/
        ├── views/
        │   ├── auth/
        │   ├── buyer/
        │   ├── seller/
        │   ├── driver/
        │   ├── admin/
        │   └── public/
        ├── stores/       # Pinia stores
        ├── services/     # API services (axios)
        └── router/
```

---

## Cara Menjalankan

### Prasyarat
- PHP 8.3+
- Composer
- Node.js 18+
- npm

### Backend

```bash
cd backend

# Install dependencies
composer install

# Salin file environment
cp .env.example .env

# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Jalankan migrasi & seeder
php artisan migrate --seed

# Jalankan server
php artisan serve
```

API akan berjalan di `http://localhost:8000`

### Frontend

```bash
cd frontend

# Install dependencies
npm install

# Jalankan dev server
npm run dev
```

Aplikasi akan berjalan di `http://localhost:5173`

---

## Konfigurasi Environment

Salin `backend/.env.example` ke `backend/.env` dan sesuaikan:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# Untuk MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=seapedia
# DB_USERNAME=root
# DB_PASSWORD=

JWT_SECRET=       # diisi otomatis oleh php artisan jwt:secret
JWT_TTL=1440      # token lifetime dalam menit (default 24 jam)
```

---

## Role & Akses

Satu user dapat memiliki lebih dari satu role. Role aktif ditentukan saat login dan tersimpan di JWT payload.

| Role | Deskripsi |
|---|---|
| `buyer` | Berbelanja, mengelola alamat, dompet, dan pesanan |
| `seller` | Mengelola toko, produk, dan memproses pesanan |
| `driver` | Mengambil dan menyelesaikan job pengiriman |
| `admin` | Monitoring platform, manajemen voucher/promo, penanganan overdue |

---

## Alur Penggunaan

### Alur Checkout (Buyer)
1. Tambah produk ke keranjang
2. Pilih metode pengiriman & opsional voucher/promo
3. Preview total biaya (subtotal + ongkir − diskon + PPN 12%)
4. Pilih alamat pengiriman
5. Checkout — saldo wallet dipotong otomatis

### Alur Pengiriman
1. **Seller** menerima pesanan → klik "Proses" → status: `menunggu_pengirim`
2. **Driver** melihat job tersedia → ambil job → status: `sedang_dikirim`
3. **Driver** konfirmasi selesai → status: `pesanan_selesai`

### Alur Overdue
Jika pesanan melewati batas waktu SLA:

| Metode | SLA |
|---|---|
| Instant | 1 hari |
| Next Day | 2 hari |
| Regular | 5 hari |

Admin menjalankan `POST /api/admin/overdue/process`:
- Status pesanan berubah ke `dikembalikan`
- Buyer mendapat refund penuh ke wallet
- Pendapatan seller di-reversal

---

## API Overview

Base URL: `http://localhost:8000/api`

### Autentikasi

Semua endpoint terproteksi membutuhkan header:
```
Authorization: Bearer <token>
```

| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/auth/register` | Registrasi user baru |
| POST | `/auth/login` | Login & dapatkan token JWT |
| GET | `/auth/me` | Info user yang sedang login |
| POST | `/auth/logout` | Logout & invalidate token |
| POST | `/auth/switch-role` | Ganti role aktif |

### Produk (Public)

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/products` | Daftar produk |
| GET | `/products/{id}` | Detail produk |
| GET | `/stores/{store}` | Profil toko publik |

Query params `/products`: `search`, `category`, `sort` (`terbaru`/`harga_asc`/`harga_desc`), `store_id`, `per_page`, `page`

### Buyer

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/buyer/wallet` | Info saldo |
| POST | `/buyer/wallet/topup` | Top up saldo (min: 10.000, max: 10.000.000) |
| GET | `/buyer/wallet/transactions` | Riwayat transaksi |
| GET | `/buyer/addresses` | Daftar alamat |
| POST | `/buyer/addresses` | Tambah alamat |
| PUT | `/buyer/addresses/{id}` | Edit alamat |
| DELETE | `/buyer/addresses/{id}` | Hapus alamat |
| PATCH | `/buyer/addresses/{id}/default` | Set alamat utama |
| GET | `/buyer/cart` | Isi keranjang |
| POST | `/buyer/cart/items` | Tambah item ke keranjang |
| PUT | `/buyer/cart/items/{id}` | Update jumlah item |
| DELETE | `/buyer/cart/items/{id}` | Hapus item |
| DELETE | `/buyer/cart` | Kosongkan keranjang |
| POST | `/buyer/checkout/preview` | Preview total biaya |
| POST | `/buyer/checkout` | Proses checkout |
| GET | `/buyer/orders` | Daftar pesanan |
| GET | `/buyer/orders/{id}` | Detail pesanan |
| GET | `/buyer/orders/{id}/tracking` | Tracking pengiriman |
| GET | `/buyer/report` | Laporan pengeluaran |

### Seller

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/seller/store` | Info toko |
| POST | `/seller/store` | Buat toko |
| PUT | `/seller/store` | Update toko |
| GET | `/seller/products` | Daftar produk toko |
| POST | `/seller/products` | Tambah produk |
| PUT | `/seller/products/{id}` | Edit produk |
| DELETE | `/seller/products/{id}` | Hapus produk |
| GET | `/seller/orders` | Daftar pesanan masuk |
| PATCH | `/seller/orders/{id}/process` | Proses pesanan |
| GET | `/seller/orders/{id}/tracking` | Tracking pesanan |
| GET | `/seller/report` | Laporan pendapatan |

### Driver

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/driver/jobs` | Job tersedia |
| GET | `/driver/jobs/{id}` | Detail job |
| PATCH | `/driver/jobs/{id}/take` | Ambil job |
| PATCH | `/driver/jobs/{id}/complete` | Selesaikan job |
| GET | `/driver/active-job` | Job aktif saat ini |
| GET | `/driver/my-jobs` | Riwayat job & total penghasilan |

### Admin

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/admin/dashboard` | Statistik platform |
| GET | `/admin/users` | Daftar user |
| GET | `/admin/stores` | Daftar toko |
| GET | `/admin/products` | Daftar produk |
| GET | `/admin/orders` | Daftar pesanan |
| GET | `/admin/deliveries` | Daftar pengiriman |
| GET | `/admin/overdue` | Daftar pesanan overdue |
| POST | `/admin/overdue/process` | Proses semua pesanan overdue |
| POST | `/admin/simulate-next-day` | Simulasi hari berikutnya (testing) |
| GET | `/admin/vouchers` | Daftar voucher |
| POST | `/admin/vouchers` | Buat voucher |
| DELETE | `/admin/vouchers/{id}` | Hapus voucher |
| GET | `/admin/promos` | Daftar promo |
| POST | `/admin/promos` | Buat promo |
| DELETE | `/admin/promos/{id}` | Hapus promo |

### Ulasan & Diskon

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/reviews` | Daftar ulasan |
| POST | `/reviews` | Tulis ulasan |
| PUT | `/reviews/{id}` | Edit ulasan (pemilik saja) |
| POST | `/vouchers/validate` | Validasi kode voucher |
| POST | `/promos/validate` | Validasi kode promo |
