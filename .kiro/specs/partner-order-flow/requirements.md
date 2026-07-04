# Requirements Document: User Order History & Tracking

## Introduction

Fitur ini menambahkan **My Orders** (riwayat pesanan) untuk semua user yang login (customer & partner/reseller),
plus **tracking nomor resi** yang bisa diinput admin dan dilihat user. Partner juga mendapat tambahan opsi
**DP (down payment)** saat checkout karena sifat B2B-nya.

Semua ini menggunakan alur belanja publik yang sudah ada (katalog Vue+Inertia, checkout Vue+Inertia).
Tidak ada portal terpisah — user cukup punya halaman `/my-orders` setelah login.

## Glossary

- **My_Orders**: Halaman daftar pesanan milik user yang sedang login, diakses via `/my-orders`
- **Order_Detail_Page**: Halaman detail satu pesanan user, diakses via `/my-orders/{orderNumber}`
- **Tracking_Number**: Nomor resi pengiriman yang diinput admin di panel admin saat status `shipped`
- **Order**: Model `app/Models/Order.php` dengan relasi `user_id` ke user yang memesan
- **Partner**: User dengan role `partner` yang mendapat harga reseller dan opsi bayar DP
- **DP**: Down Payment — pembayaran sebagian di muka, minimal 30% dari total order
- **remaining_balance**: Sisa pembayaran setelah DP, tercatat sebagai piutang

---

## Requirements

### Requirement 1: Halaman My Orders (Riwayat Pesanan)

**User Story:** Sebagai user yang sudah login, saya ingin melihat semua pesanan saya di satu halaman,
sehingga saya bisa memantau status dan riwayat belanja saya.

#### Acceptance Criteria

1. WHEN user yang terautentikasi mengakses `/my-orders`, THE system SHALL menampilkan semua pesanan
   milik user tersebut (WHERE `orders.user_id = auth()->id()`), diurutkan dari terbaru.
2. THE My_Orders page SHALL menampilkan kolom: nomor pesanan, tanggal, total, status pesanan,
   status pembayaran, dan nomor resi (jika ada).
3. THE My_Orders page SHALL terpaginasi dengan 10 pesanan per halaman.
4. IF user belum pernah memesan, THEN THE system SHALL menampilkan empty state dengan link ke katalog.
5. THE My_Orders page SHALL hanya bisa diakses oleh user yang sudah login; guest diarahkan ke halaman login.
6. WHEN user mengklik baris pesanan, THE system SHALL menampilkan halaman detail pesanan tersebut.

---

### Requirement 2: Halaman Detail Pesanan (User-Facing)

**User Story:** Sebagai user, saya ingin melihat detail lengkap satu pesanan termasuk status terkini
dan nomor resi, sehingga saya tahu kapan barang akan tiba.

#### Acceptance Criteria

1. WHEN user mengakses `/my-orders/{orderNumber}`, THE system SHALL menampilkan detail pesanan:
   nomor pesanan, tanggal, item yang dipesan (nama, qty, harga), subtotal, total, metode pembayaran,
   status pesanan, status pembayaran.
2. THE system SHALL menampilkan timeline status pesanan (dibuat → dikonfirmasi → diproses → dikirim → selesai).
3. IF `tracking_number` tidak null, THEN THE system SHALL menampilkan nomor resi pengiriman di halaman detail.
4. THE system SHALL hanya menampilkan pesanan milik user yang sedang login; jika `order.user_id ≠ auth()->id()`,
   maka return 403.
5. IF order memiliki `down_payment_amount > 0`, THEN THE system SHALL menampilkan ringkasan pembayaran:
   Total, DP Dibayar, dan Sisa Piutang.

---

### Requirement 3: Nomor Resi — Admin Input

**User Story:** Sebagai admin, saya ingin memasukkan nomor resi pengiriman saat order di-ship,
sehingga customer/partner bisa melacak paket mereka.

#### Acceptance Criteria

1. THE Order model SHALL memiliki kolom `tracking_number` bertipe `varchar(100) nullable`.
2. WHEN admin mengubah status order menjadi `shipped` di panel admin, THE system SHALL menampilkan
   field input nomor resi di form update status (`orders-show.blade.php`).
3. WHEN admin mengisi `tracking_number` dan menyimpan, THE system SHALL menyimpan nomor resi ke
   kolom `tracking_number` pada record order tersebut.
4. THE AdminOrderController SHALL menerima field `tracking_number` pada validasi `updateStatus()`.
5. THE `orders-show.blade.php` SHALL menampilkan nomor resi yang sudah disimpan pada section
   "Informasi Pengiriman" jika `tracking_number` tidak null.

---

### Requirement 4: DP Payment untuk Partner

**User Story:** Sebagai partner yang login, saya ingin bisa memilih bayar DP saat checkout,
sehingga saya bisa mengonfirmasi pesanan tanpa harus langsung lunas.

#### Acceptance Criteria

1. WHEN user dengan role `partner` membuka halaman checkout, THE CheckoutPage SHALL menampilkan
   pilihan mode pembayaran tambahan: "Bayar Lunas" (default) atau "Bayar DP".
2. WHEN partner memilih "Bayar DP", THE CheckoutPage SHALL menampilkan input nominal DP dan
   menghitung sisa piutang secara real-time (total - nominal DP).
3. IF nominal DP kurang dari 30% dari total, THEN THE CheckoutPage SHALL menampilkan error validasi
   dan mencegah submit.
4. THE Order model SHALL memiliki kolom `down_payment_amount decimal(15,2) default 0` dan
   `remaining_balance decimal(15,2) default 0`.
5. WHEN order berhasil dibuat dengan DP, THE system SHALL menyimpan `payment_status = 'partial'`,
   `down_payment_amount`, dan `remaining_balance = total - down_payment_amount`.
6. WHEN user tanpa role `partner` membuka checkout, THE CheckoutPage SHALL tidak menampilkan
   opsi DP sama sekali.

---

### Requirement 5: Navigasi & Akses My Orders

**User Story:** Sebagai user yang login, saya ingin bisa mengakses riwayat pesanan dengan mudah
dari halaman publik, sehingga tidak perlu mencari-cari.

#### Acceptance Criteria

1. WHEN user yang terautentikasi berada di halaman katalog publik, THE Navbar SHALL menampilkan
   link "Pesanan Saya" yang mengarah ke `/my-orders`.
2. WHEN user tidak terautentikasi berada di halaman katalog, THE Navbar SHALL menampilkan link
   "Login" seperti sebelumnya (tidak ada link pesanan).
3. WHEN order berhasil dibuat (halaman `order/success`), THE system SHALL menampilkan link
   "Lihat Riwayat Pesanan" yang mengarah ke `/my-orders`.
