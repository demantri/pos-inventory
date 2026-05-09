# POS Inventory System

Sistem kasir dan manajemen inventori berbasis web yang dibangun dengan Laravel 12. Mendukung pencatatan stok FIFO, multi-role pengguna, laporan keuangan, dan integrasi payment gateway Midtrans.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Akun Demo](#akun-demo)
- [Roles & Hak Akses](#roles--hak-akses)
- [Fitur per Modul](#fitur-per-modul)
- [Integrasi Midtrans](#integrasi-midtrans)
- [Struktur Database](#struktur-database)
- [Struktur Proyek](#struktur-proyek)

---

## Fitur Utama

- **Kasir / POS** — Transaksi penjualan dengan multiple metode pembayaran (tunai, QRIS, transfer, kartu)
- **Payment Gateway** — Integrasi Midtrans Snap untuk pembayaran digital
- **Manajemen Inventori** — Pencatatan stok berbasis lot dengan metode FIFO
- **Alur Pembelian** — Purchase Order → Penerimaan Barang → Update Stok otomatis
- **Laporan Keuangan** — Laporan HPP, penjualan, laba kotor, dan valuasi inventori
- **Multi-Role** — Super Admin, Admin, Kasir, Gudang dengan hak akses berbeda
- **Pengaturan Admin** — Manajemen user, role & permission, info toko, konfigurasi payment gateway

---

## Tech Stack

| Kategori | Teknologi |
|----------|-----------|
| Backend | Laravel 12, PHP ^8.2 |
| Frontend | Blade, Tailwind CSS 3, Alpine.js 3 |
| Database | MySQL / SQLite |
| Auth & Permission | Laravel Breeze, Spatie Permission ^6.25 |
| Payment Gateway | Midtrans (midtrans/midtrans-php ^2.6) |
| Export | Maatwebsite Excel ^3.1, barryvdh/laravel-dompdf ^3.1 |
| Build Tool | Vite 7 |

---

## Persyaratan Sistem

- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 18.x & npm
- MySQL 8.x (atau SQLite untuk development)
- Extension PHP: `pdo`, `mbstring`, `openssl`, `json`, `bcmath`, `gd`

---

## Instalasi

### 1. Clone repositori

```bash
git clone <repository-url> pos-inventory
cd pos-inventory
```

### 2. Install dependensi

```bash
composer install
npm install
```

### 3. Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` sesuai kebutuhan (lihat bagian [Konfigurasi](#konfigurasi)).

### 4. Migrasi dan seeding database

```bash
php artisan migrate
php artisan db:seed
```

### 5. Storage link

```bash
php artisan storage:link
```

### 6. Build frontend assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Jalankan server

```bash
php artisan serve
```

Akses aplikasi di `http://localhost:8000`

---

### Cara Cepat (via Composer script)

```bash
composer setup   # install + key generate + migrate + npm install
composer dev     # jalankan server, queue, log, dan vite secara bersamaan
```

---

## Konfigurasi

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_pos_inventory
DB_USERNAME=root
DB_PASSWORD=
```

Untuk development menggunakan SQLite:

```env
DB_CONNECTION=sqlite
# DB_DATABASE akan otomatis menggunakan database/database.sqlite
```

### Midtrans Payment Gateway

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
```

> **Catatan:** API key Midtrans juga dapat dikonfigurasi langsung dari halaman **Pengaturan → Payment Gateway** pada aplikasi tanpa perlu mengedit `.env`.

### Konfigurasi Tambahan

```env
APP_NAME="POS Inventory"
APP_URL=http://localhost:8000
APP_LOCALE=id

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## Akun Demo

Setelah menjalankan `php artisan db:seed`, akun berikut tersedia:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@pos.test | password |
| Admin | admin@pos.test | password |
| Kasir | kasir@pos.test | password |
| Gudang | gudang@pos.test | password |

---

## Roles & Hak Akses

### Daftar Role

| Role | Deskripsi |
|------|-----------|
| **super_admin** | Akses penuh ke seluruh fitur |
| **admin** | Akses penuh kecuali hapus user |
| **kasir** | Kasir POS, data pelanggan, laporan penjualan |
| **gudang** | Purchase order, penerimaan barang, inventori |

### Daftar Permission

| Grup | Permission |
|------|-----------|
| Dashboard | `dashboard.view` |
| Kategori | `categories.view` `create` `edit` `delete` |
| Satuan | `units.view` `create` `edit` `delete` |
| Produk | `products.view` `create` `edit` `delete` |
| Supplier | `suppliers.view` `create` `edit` `delete` |
| Pelanggan | `customers.view` `create` `edit` `delete` |
| Purchase Order | `purchase_orders.view` `create` `edit` `delete` |
| Penerimaan Barang | `goods_receipts.view` `create` `confirm` |
| Inventori | `inventory.view` `adjustment` · `stock_lots.view` |
| POS / Kasir | `pos.access` `pos.void` |
| Laporan | `reports.hpp` `sales` `inventory` `profit` |
| User | `users.view` `create` `edit` `delete` |
| Pengaturan | `settings.view` `store` `payment_gateway` `roles` |

> Permission tiap role dapat diubah sewaktu-waktu melalui **Pengaturan → Role & Hak Akses**.

---

## Fitur per Modul

### Dashboard
- Ringkasan penjualan hari ini (omzet, transaksi, laba kotor)
- Produk dengan stok di bawah minimum
- Transaksi terbaru
- Grafik penjualan bulanan

### Kasir / POS
- Pencarian produk dan scan barcode
- Filter produk per kategori
- Keranjang belanja dengan ubah qty dan diskon per item
- Diskon total dan pengaturan pajak
- Pembayaran tunai dengan shortcut nominal
- Pembayaran digital via Midtrans Snap (QRIS, transfer bank, kartu kredit/debit, e-wallet)
- Cetak struk transaksi

### Payment Gateway (Midtrans)
- Popup Snap Midtrans langsung di halaman kasir
- Resume pembayaran jika popup ditutup secara tidak sengaja
- Notifikasi webhook untuk konfirmasi pembayaran pending (transfer VA)
- Pembatalan transaksi yang aman tanpa konflik order ID

### Master Data
- **Kategori** & **Satuan** — CRUD dengan toggle aktif/nonaktif
- **Produk** — CRUD dengan gambar, barcode, stok minimum, toggle aktif
- **Supplier** — Data supplier untuk pembelian
- **Pelanggan** — Riwayat total transaksi dan kunjungan

### Manajemen Inventori
- **Purchase Order** — Buat PO ke supplier, ubah status (draft → dikirim → diterima)
- **Penerimaan Barang** — Konfirmasi barang masuk, update stok otomatis per lot
- **Stok & Lot FIFO** — Lihat stok per produk, kartu stok, riwayat pergerakan
- Setiap penjualan otomatis mengambil stok dari lot tertua (FIFO)

### Laporan
- **Laporan HPP** — Harga Pokok Penjualan per produk/periode
- **Laporan Penjualan** — Rekap transaksi dengan detail metode pembayaran
- **Laporan Inventori** — Valuasi stok saat ini berdasarkan harga perolehan

### Pengaturan (Admin)

| Menu | Deskripsi |
|------|-----------|
| Informasi Toko | Nama, alamat, telepon, logo, catatan struk |
| Payment Gateway | API key Midtrans, mode sandbox/production |
| Manajemen User | Tambah/edit/hapus user, assign role, aktif/nonaktif |
| Role & Hak Akses | Edit permission per role via checkbox |

---

## Integrasi Midtrans

### Cara Mendapatkan API Key

1. Daftar di [dashboard.midtrans.com](https://dashboard.midtrans.com)
2. Buka menu **Settings → Access Keys**
3. Salin **Server Key** dan **Client Key**
4. Untuk testing, gunakan key dari tab **Sandbox**

### Konfigurasi di Aplikasi

1. Login sebagai **Admin** atau **Super Admin**
2. Buka **Pengaturan → Payment Gateway**
3. Aktifkan toggle **Aktifkan Midtrans**
4. Isi **Server Key** dan **Client Key**
5. Pastikan **Mode Production** nonaktif untuk sandbox
6. Klik **Simpan Pengaturan**

### Alur Pembayaran

```
Kasir pilih metode non-tunai
        │
        ▼
Backend buat Sale (PENDING) + minta Snap Token ke Midtrans
        │
        ▼
Frontend buka popup Snap Midtrans
        │
   ┌────┴──────────────────────┬───────────────────┐
   ▼                           ▼                   ▼
Bayar sukses              Popup ditutup        Bayar gagal
   │                           │                   │
   ▼                           ▼                   ▼
onSuccess               onClose / onPending      onError
   │                           │                   │
   ▼                     Modal muncul:             ▼
Konfirmasi ke         "Lanjutkan atau         Cancel + hapus
backend, proses        Batalkan?"             transaksi pending
FIFO + HPP                 │
   │                ┌───────┴────────┐
   ▼                ▼                ▼
Sale COMPLETED   Lanjutkan       Batalkan
              (buka Snap lagi) (hapus transaksi)
```

### Kartu Test Midtrans (Sandbox)

| Kartu | Nomor | CVV | Exp |
|-------|-------|-----|-----|
| Visa (sukses) | `4811 1111 1111 1114` | `123` | `01/26` |
| Mastercard (sukses) | `5211 1111 1111 1117` | `123` | `01/26` |
| Visa (gagal) | `4911 1111 1111 1113` | `123` | `01/26` |

### Webhook Notification

Untuk menerima notifikasi pembayaran dari Midtrans (diperlukan untuk transfer VA / pembayaran async), daftarkan URL berikut di dashboard Midtrans:

```
https://yourdomain.com/payment/notification
```

Menu: **Settings → Configuration → Payment Notification URL**

---

## Struktur Database

```
users                    — Akun pengguna
categories               — Kategori produk
units                    — Satuan pengukuran
products                 — Master produk
suppliers                — Data supplier
customers                — Data pelanggan
purchase_orders          — Header purchase order
purchase_order_items     — Detail item PO
goods_receipts           — Header penerimaan barang
goods_receipt_items      — Detail item penerimaan
stock_lots               — Lot stok per penerimaan (FIFO)
stock_movements          — Riwayat pergerakan stok (audit trail)
sales                    — Header transaksi penjualan
sale_items               — Detail item penjualan
sale_item_lots           — Lot yang dikonsumsi per item penjualan
hpp_records              — Catatan harga pokok per transaksi
payment_transactions     — Log transaksi payment gateway
settings                 — Konfigurasi aplikasi (key-value)
roles                    — Role pengguna (Spatie)
permissions              — Permission (Spatie)
model_has_roles          — Relasi user ↔ role
role_has_permissions     — Relasi role ↔ permission
```

---

## Struktur Proyek

```
pos-inventory/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php
│   │   │   ├── Settings/
│   │   │   │   ├── UserManagementController.php
│   │   │   │   ├── RoleManagementController.php
│   │   │   │   ├── StoreSettingController.php
│   │   │   │   └── PaymentGatewayController.php
│   │   │   ├── PosController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── ReportController.php
│   │   │   └── ...
│   │   └── Middleware/
│   │       └── EnsureUserIsActive.php
│   ├── Models/
│   │   ├── Sale.php, SaleItem.php, SaleItemLot.php
│   │   ├── Product.php, Category.php, Unit.php
│   │   ├── StockLot.php, StockMovement.php
│   │   ├── PurchaseOrder.php, GoodsReceipt.php
│   │   ├── PaymentTransaction.php
│   │   ├── Setting.php
│   │   └── ...
│   └── Services/
│       ├── FifoService.php      — Konsumsi stok FIFO
│       ├── HppService.php       — Kalkulasi Harga Pokok Penjualan
│       └── MidtransService.php  — Integrasi Midtrans API
├── config/
│   └── midtrans.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RolePermissionSeeder.php
│       ├── MasterDataSeeder.php
│       └── UserSeeder.php
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php        — Layout utama (sidebar)
│   │   └── pos.blade.php        — Layout khusus kasir
│   ├── pos/
│   ├── settings/
│   │   ├── users/
│   │   └── roles/
│   ├── reports/
│   └── ...
└── routes/
    └── web.php
```

---

## Lisensi

Proyek ini dibuat untuk keperluan pembelajaran. Silakan digunakan dan dimodifikasi sesuai kebutuhan.
#   p o s - i n v e n t o r y  
 