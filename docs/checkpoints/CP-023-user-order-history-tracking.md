# Checkpoint: CP-023 — User Order History & Tracking

## Deskripsi

Menambahkan fitur riwayat pesanan untuk semua user yang login (customer & partner), tracking nomor resi pengiriman, dan opsi pembayaran DP (down payment) khusus untuk partner/reseller.

## Perubahan Utama

### 1. Database
- **Migration baru**: `add_tracking_and_dp_fields_to_orders_table.php`
  - Kolom `down_payment_amount decimal(15,2) default 0`
  - Kolom `remaining_balance decimal(15,2) default 0`
  - Kolom `tracking_number varchar(100) nullable`
- **Model `Order`**: tambah 3 kolom ke `$fillable` dan `$casts`, tambah method `isPartiallyPaid()`, tambah scope `scopePartial()`

### 2. Backend — Controller & Routes
- **`MyOrderController`** (baru): `index()` dan `show()` via Inertia untuk halaman My Orders user-facing
- **`routes/customer.php`**: tambah route `/my-orders` dan `/my-orders/{orderNumber}`
- **`OrderController::checkout()`**: pass `isPartner` flag ke Inertia props
- **`OrderController::store()`**: logic DP — validasi min 30%, simpan `down_payment_amount`, `remaining_balance`, `payment_status = 'partial'`
- **`AdminOrderController::updateStatus()`**: terima `tracking_number` dan `payment_status = 'partial'`; kas saat pelunasan menggunakan `remaining_balance` (bukan total) untuk hindari dobel pencatatan DP

### 3. Vue Pages (User-Facing)
- **`MyOrders/Index.vue`** (baru): list pesanan dengan tabel desktop + card mobile, badge status berwarna, pagination Inertia
- **`MyOrders/Show.vue`** (baru): detail pesanan dengan 6 section — header, items, ringkasan pembayaran (termasuk DP/piutang), nomor resi + copy + Google search, timeline status, info pengiriman
- **`Navbar.vue`**: link "Pesanan Saya" tampil saat user login
- **`OrderSuccessPage.vue`**: tombol "Lihat Riwayat Pesanan" saat user login
- **`CheckoutPage.vue`**: opsi "Bayar Lunas" / "Bayar DP" tampil khusus partner, validasi min 30%, tampil sisa piutang real-time

### 4. Admin Panel Blade
- **`orders-show.blade.php`**: field input nomor resi (muncul saat status = shipped), info DP Dibayar + Sisa Piutang, option `partial` di dropdown Status Bayar, nomor resi di timeline
- **`orders-list.blade.php`**: badge "DP" amber untuk `payment_status = 'partial'`
- **`orders-index.blade.php`**: option `partial` di filter Status Pembayaran

## Keputusan Arsitektur

- **My Orders menggunakan Inertia/Vue** — konsisten dengan halaman publik yang sudah Vue+Inertia
- **Admin panel tetap Blade+HTMX** — tidak ada perubahan arsitektur admin
- **DP hanya untuk partner** — validasi di controller (backend) sebagai defense-in-depth, bukan hanya di Vue
- **Tracking number = manual input** — tidak ada integrasi API kurir eksternal; user bisa google sendiri dari link yang disediakan
- **Kas saat pelunasan DP** — `amountToRecord = remaining_balance` untuk hindari pencatatan ganda

## File yang Dibuat/Dimodifikasi

**Baru:**
- `database/migrations/*_add_tracking_and_dp_fields_to_orders_table.php`
- `app/Http/Controllers/MyOrderController.php`
- `resources/js/pages/MyOrders/Index.vue`
- `resources/js/pages/MyOrders/Show.vue`

**Dimodifikasi:**
- `app/Models/Order.php`
- `routes/customer.php`
- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/AdminOrderController.php`
- `resources/js/components/Navbar.vue`
- `resources/js/pages/OrderSuccessPage.vue`
- `resources/js/pages/CheckoutPage.vue`
- `resources/views/orders/orders-show.blade.php`
- `resources/views/orders/orders-list.blade.php`
- `resources/views/orders/orders-index.blade.php`
