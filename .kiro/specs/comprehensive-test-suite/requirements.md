# Requirements Document

## Introduction

Rima Craft adalah aplikasi ERP Mini UMKM berbasis Laravel 13 + Vue 3 + Inertia.js yang saat ini berjalan di versi v0.2.0. Proyek ini memerlukan sebuah _test suite_ komprehensif untuk memvalidasi kebenaran logika bisnis kritis, integritas data, keamanan akses berbasis peran (RBAC), dan perilaku HTTP endpoint.

Test suite ini akan mencakup:
- **Unit Tests** untuk Action classes, Model methods, dan FormRequest validation
- **Feature Tests** untuk HTTP endpoint dan alur pengguna end-to-end
- **Property-Based Testing** untuk memverifikasi invariant kalkulasi finansial
- **Database Factories** untuk semua model yang belum memiliki factory

Seluruh kode test harus menggunakan `declare(strict_types=1)`, atribut `#[Test]`, dan `DatabaseTransactions` trait untuk semua test yang menyentuh database.

---

## Glossary

- **TestSuite**: Keseluruhan kumpulan test yang dibangun dalam spesifikasi ini
- **Action**: Kelas di `app/Actions/` yang mengenkapsulasi logika bisnis multi-tabel
- **RecordSaleAction**: Action untuk mencatat transaksi penjualan beserta efek sampingnya
- **RecordPurchaseAction**: Action untuk mencatat transaksi pembelian beserta efek sampingnya
- **RecordProductionAction**: Action untuk mencatat proses produksi beserta perhitungan HPP
- **RecordPaymentAction**: Action untuk mencatat pembayaran polimorfik
- **RecordTransactionAction**: Action untuk mencatat transaksi kas manual
- **AdjustStockAction**: Action untuk mencatat penyesuaian stok
- **RBAC**: Role-Based Access Control; sistem peran dan izin aplikasi
- **Factory**: Kelas `Database\Factories\*` untuk menghasilkan data model tiruan saat testing
- **DatabaseTransactions**: Trait PHPUnit/Laravel yang membungkus setiap test dalam transaksi database yang di-rollback otomatis setelah test selesai — tidak me-reset atau drop tabel
- **PBT**: Property-Based Testing; teknik pengujian dengan data input yang digenerate secara acak
- **grand_total**: Total akhir transaksi = `total_amount + shipping_fee - discount`
- **grand_total_cost**: Total biaya produksi = `total_material_cost + labor_cost + overhead_cost + additional_cost`
- **payment_status**: Status pembayaran: `unpaid`, `partial`, atau `paid`
- **HPP**: Harga Pokok Produksi; biaya total untuk menghasilkan suatu produk
- **CashLedger**: Model buku kas untuk mencatat setiap perubahan saldo akun
- **order_number**: Nomor unik pesanan berformat `ORD-YYYYMMDD-NNN`

---

## Requirements

### Requirement 1: Infrastruktur dan Standar Penulisan Test

**User Story:** Sebagai developer, saya ingin memiliki standar penulisan test yang konsisten, sehingga seluruh test suite mudah dibaca, dipelihara, dan dijalankan.

#### Acceptance Criteria

1. THE TestSuite SHALL menggunakan `declare(strict_types=1)` di setiap file test PHP.
2. THE TestSuite SHALL menggunakan atribut `#[Test]` (PHPUnit modern style) pada setiap metode test, bukan anotasi `@test`.
3. WHEN test yang menyentuh database dijalankan, THE TestSuite SHALL menggunakan trait `DatabaseTransactions` untuk membungkus setiap test dalam transaksi yang di-rollback otomatis, tanpa me-reset atau drop tabel database.
4. THE TestSuite SHALL menyediakan Factory untuk setiap Model yang digunakan dalam test: `Product`, `Material`, `Sale`, `Purchase`, `Production`, `Order`, `Contact`, `Role`, `Permission`, `Account`, `CashLedger`, `Payment`, `StockAdjustment`.
5. THE TestSuite SHALL mengorganisasi test dalam direktori `tests/Unit/Actions/`, `tests/Unit/Models/`, `tests/Unit/Requests/`, dan `tests/Feature/`.
6. WHEN sebuah Factory dipanggil, THE Factory SHALL menghasilkan data yang valid sesuai constraint database (tipe data, panjang field, relasi foreign key).

---

### Requirement 2: Unit Test — RecordSaleAction

**User Story:** Sebagai developer, saya ingin RecordSaleAction diuji secara menyeluruh, sehingga saya yakin bahwa transaksi penjualan selalu mencatat data dengan benar dan menjaga integritas stok serta keuangan.

#### Acceptance Criteria

1. WHEN `RecordSaleAction::handle()` dipanggil dengan data valid dan stok mencukupi, THE Action SHALL membuat satu record `Sale` dengan nilai `grand_total` yang sama dengan `total_amount + shipping_fee - discount`.
2. WHEN `RecordSaleAction::handle()` dipanggil dengan data valid, THE Action SHALL membuat `SaleItem` untuk setiap item di dalam array `items`.
3. WHEN `RecordSaleAction::handle()` dipanggil dengan data valid, THE Action SHALL mengurangi `current_stock` setiap `Product` terkait sebesar jumlah `qty` yang dipesan.
4. WHEN `RecordSaleAction::handle()` dipanggil dengan `payment_status = 'paid'`, THE Action SHALL membuat satu record `Payment` dan satu record `CashLedger` bertipe `in`.
5. WHEN `RecordSaleAction::handle()` dipanggil dengan `payment_status = 'paid'`, THE Action SHALL menambah `balance` pada `Account` sebesar nilai `grand_total`.
6. WHEN `RecordSaleAction::handle()` dipanggil dengan `payment_status = 'unpaid'`, THE Action SHALL TIDAK membuat record `Payment` maupun record `CashLedger`.
7. IF stok `Product` tidak mencukupi untuk memenuhi `qty` yang diminta, THEN THE Action SHALL melempar `Exception` dengan pesan yang menyebut nama produk.
8. WHEN `RecordSaleAction::handle()` dipanggil dengan `save_customer = true` dan `customer_name` diisi, THE Action SHALL membuat record `Contact` baru bertipe `customer`.
9. FOR ALL kombinasi nilai `shipping_fee` dan `discount` yang valid (≥ 0), THE Action SHALL memastikan `grand_total = total_amount + shipping_fee - discount` (property-based invariant).

---

### Requirement 3: Unit Test — RecordPurchaseAction

**User Story:** Sebagai developer, saya ingin RecordPurchaseAction diuji, sehingga saya yakin bahwa transaksi pembelian selalu memperbarui stok bahan baku dan harga beli terakhir secara akurat.

#### Acceptance Criteria

1. WHEN `RecordPurchaseAction::handle()` dipanggil dengan data valid, THE Action SHALL membuat satu record `Purchase` beserta `PurchaseItem` untuk setiap item.
2. WHEN `RecordPurchaseAction::handle()` dipanggil dengan data valid, THE Action SHALL menambah `current_stock` setiap `Material` terkait sebesar nilai `qty`.
3. WHEN `RecordPurchaseAction::handle()` dipanggil dengan `price > 0`, THE Action SHALL memperbarui `last_buy_price` pada `Material` terkait.
4. WHEN `RecordPurchaseAction::handle()` dipanggil dengan `payment_status = 'paid'`, THE Action SHALL membuat satu record `Payment` dan satu record `CashLedger` bertipe `out`.
5. WHEN `RecordPurchaseAction::handle()` dipanggil dengan `payment_status = 'paid'`, THE Action SHALL mengurangi `balance` pada `Account` sebesar nilai `total_amount`.
6. WHEN `RecordPurchaseAction::handle()` dipanggil dengan `payment_status = 'unpaid'`, THE Action SHALL TIDAK membuat record `Payment` maupun record `CashLedger`.
7. WHEN `RecordPurchaseAction::handle()` dipanggil dengan `save_supplier = true` dan `supplier_name` diisi, THE Action SHALL membuat record `Contact` baru bertipe `supplier`.
8. FOR ALL kombinasi `qty` dan `price` item yang valid (> 0), THE Action SHALL memastikan `total_amount = SUM(qty_i * price_i)` untuk semua item (property-based invariant).

---

### Requirement 4: Unit Test — RecordProductionAction

**User Story:** Sebagai developer, saya ingin RecordProductionAction diuji, sehingga saya yakin bahwa proses produksi menghitung HPP dengan benar dan memperbarui stok bahan baku serta produk secara akurat.

#### Acceptance Criteria

1. WHEN `RecordProductionAction::handle()` dipanggil dengan stok bahan baku mencukupi, THE Action SHALL membuat record `Production`, `ProductionMaterial`, dan `ProductionResult`.
2. WHEN `RecordProductionAction::handle()` dipanggil dengan data valid, THE Action SHALL mengurangi `current_stock` setiap `Material` yang digunakan sebesar nilai `qty`.
3. WHEN `RecordProductionAction::handle()` dipanggil dengan data valid, THE Action SHALL menambah `current_stock` setiap `Product` yang dihasilkan sebesar nilai `qty`.
4. WHEN `RecordProductionAction::handle()` dipanggil dengan data valid, THE Action SHALL memastikan `grand_total_cost = total_material_cost + labor_cost + overhead_cost + additional_cost`.
5. WHEN `RecordProductionAction::handle()` dipanggil dengan `labor_cost > 0`, THE Action SHALL membuat entry `CashLedger` dengan `category = 'production_labor'` bertipe `out`.
6. WHEN `RecordProductionAction::handle()` dipanggil dengan `overhead_cost > 0`, THE Action SHALL membuat entry `CashLedger` dengan `category = 'production_overhead'` bertipe `out`.
7. WHEN `RecordProductionAction::handle()` dipanggil dengan `total_material_cost > 0`, THE Action SHALL membuat entry `CashLedger` dengan `category = 'production_material'` bertipe `out`.
8. IF stok `Material` tidak mencukupi untuk memenuhi `qty` yang diminta, THEN THE Action SHALL melempar `Exception` dengan pesan yang menyebut nama material.
9. FOR ALL nilai biaya yang valid (≥ 0), THE Action SHALL memastikan jumlah semua entry `CashLedger` yang dibuat sama dengan `grand_total_cost` (property-based invariant).

---

### Requirement 5: Unit Test — RecordPaymentAction

**User Story:** Sebagai developer, saya ingin RecordPaymentAction diuji, sehingga saya yakin bahwa pembayaran polimorfik selalu memperbarui saldo akun dan status pembayaran entitas yang dibayar dengan benar.

#### Acceptance Criteria

1. WHEN `RecordPaymentAction::handle()` dipanggil untuk `Sale` dengan `amount` valid, THE Action SHALL membuat satu record `Payment` dan satu `CashLedger` bertipe `in`.
2. WHEN `RecordPaymentAction::handle()` dipanggil untuk `Purchase` dengan `amount` valid, THE Action SHALL membuat satu record `Payment` dan satu `CashLedger` bertipe `out`.
3. WHEN `RecordPaymentAction::handle()` dipanggil dan total pembayaran telah mencapai `grand_total` entitas, THE Action SHALL memperbarui `payment_status` entitas menjadi `paid`.
4. WHEN `RecordPaymentAction::handle()` dipanggil dan total pembayaran masih kurang dari `grand_total` entitas, THE Action SHALL memperbarui `payment_status` entitas menjadi `partial`.
5. WHEN `RecordPaymentAction::handle()` dipanggil untuk `Purchase` bertipe `out`, THE Action SHALL mengurangi `balance` pada `Account` sebesar nilai `amount`.
6. WHEN `RecordPaymentAction::handle()` dipanggil untuk `Sale` bertipe `in`, THE Action SHALL menambah `balance` pada `Account` sebesar nilai `amount`.
7. IF `balance` `Account` tidak mencukupi saat memproses pembayaran tipe `out` (Purchase/Production), THEN THE Action SHALL melempar `Exception` dengan pesan "Saldo kas ... tidak mencukupi!".

---

### Requirement 6: Unit Test — RecordTransactionAction

**User Story:** Sebagai developer, saya ingin RecordTransactionAction diuji, sehingga saya yakin bahwa transaksi kas manual mencatat perubahan saldo dengan benar dan menolak pengeluaran yang melebihi saldo.

#### Acceptance Criteria

1. WHEN `RecordTransactionAction::handle()` dipanggil dengan `type = 'in'`, THE Action SHALL membuat satu record `CashLedger` bertipe `in` dan menambah `balance` pada `Account`.
2. WHEN `RecordTransactionAction::handle()` dipanggil dengan `type = 'out'` dan `balance` mencukupi, THE Action SHALL membuat satu record `CashLedger` bertipe `out` dan mengurangi `balance` pada `Account`.
3. WHEN `RecordTransactionAction::handle()` dipanggil dengan `type = 'in'`, THE Action SHALL memastikan `balance_after = balance_sebelumnya + amount` pada record `CashLedger` yang dibuat.
4. WHEN `RecordTransactionAction::handle()` dipanggil dengan `type = 'out'`, THE Action SHALL memastikan `balance_after = balance_sebelumnya - amount` pada record `CashLedger` yang dibuat.
5. IF `type = 'out'` dan nilai `amount` melebihi `balance` `Account`, THEN THE Action SHALL melempar `Exception` dengan pesan "Saldo kas ... tidak mencukupi!".
6. IF `type = 'out'` dan `amount` melebihi `balance`, THEN THE Action SHALL TIDAK mengubah nilai `balance` pada `Account`.

---

### Requirement 7: Unit Test — AdjustStockAction

**User Story:** Sebagai developer, saya ingin AdjustStockAction diuji, sehingga saya yakin bahwa penyesuaian stok selalu mencatat log perubahan dengan akurat dan menolak input yang tidak menghasilkan perubahan.

#### Acceptance Criteria

1. WHEN `AdjustStockAction::handle()` dipanggil dengan `actual_stock` berbeda dari `current_stock` untuk `Material`, THE Action SHALL membuat satu record `StockAdjustment` dan memperbarui `current_stock` material menjadi `actual_stock`.
2. WHEN `AdjustStockAction::handle()` dipanggil dengan `actual_stock` berbeda dari `current_stock` untuk `Product`, THE Action SHALL membuat satu record `StockAdjustment` dan memperbarui `current_stock` produk menjadi `actual_stock`.
3. WHEN `AdjustStockAction::handle()` dipanggil, THE Action SHALL memastikan `quantity_difference = actual_stock - previous_stock` pada record `StockAdjustment` yang dibuat.
4. IF `actual_stock` sama dengan `current_stock` (tidak ada perubahan), THEN THE Action SHALL melempar `Exception` dengan pesan "Tidak ada perubahan stok.".
5. IF `actual_stock` sama dengan `current_stock`, THEN THE Action SHALL TIDAK membuat record `StockAdjustment` apapun.

---

### Requirement 8: Unit Test — Product::getPriceForUser()

**User Story:** Sebagai developer, saya ingin metode `getPriceForUser()` diuji dengan semua skenario prioritas harga, sehingga saya yakin bahwa harga yang ditampilkan ke setiap pengguna selalu akurat sesuai hirarki yang ditetapkan.

#### Acceptance Criteria

1. WHEN `getPriceForUser()` dipanggil untuk user reseller yang memiliki harga khusus (`product_user_prices`), THE Product SHALL mengembalikan harga dari `product_user_prices` (prioritas tertinggi).
2. WHEN `getPriceForUser()` dipanggil untuk user reseller tanpa harga khusus, dan kota user memiliki harga wilayah dengan `reseller_price > 0`, THE Product SHALL mengembalikan `reseller_price` dari `product_region_prices` kota tersebut.
3. WHEN `getPriceForUser()` dipanggil untuk user reseller tanpa harga khusus, kota tidak memiliki harga wilayah tetapi provinsi memilikinya, THE Product SHALL mengembalikan harga dari `product_region_prices` provinsi.
4. WHEN `getPriceForUser()` dipanggil untuk user reseller tanpa harga khusus dan tanpa harga wilayah, THE Product SHALL mengembalikan `reseller_price` default produk jika `reseller_price > 0`.
5. WHEN `getPriceForUser()` dipanggil untuk user customer biasa (bukan reseller), THE Product SHALL mengembalikan `base_price` produk.
6. WHEN `getPriceForUser()` dipanggil tanpa user (`null`), THE Product SHALL mengembalikan `base_price` produk.

---

### Requirement 9: Unit Test — Order Model (Lifecycle & Auto-generate)

**User Story:** Sebagai developer, saya ingin Order Model diuji, sehingga saya yakin bahwa nomor pesanan digenerate secara otomatis dan metode transisi status berfungsi dengan benar.

#### Acceptance Criteria

1. WHEN sebuah `Order` dibuat (`creating` event), THE Order SHALL menghasilkan `order_number` secara otomatis dengan format `ORD-YYYYMMDD-NNN` (contoh: `ORD-20260101-001`).
2. WHEN dua `Order` dibuat pada hari yang sama, THE Order SHALL menghasilkan `order_number` yang berbeda dan unik.
3. WHEN `Order::confirm()` dipanggil, THE Order SHALL memperbarui `status` menjadi `confirmed` dan mengisi kolom `confirmed_at` dengan waktu saat ini.
4. WHEN `Order::markProcessing()` dipanggil, THE Order SHALL memperbarui `status` menjadi `processing`.
5. WHEN `Order::markShipped()` dipanggil, THE Order SHALL memperbarui `status` menjadi `shipped` dan mengisi kolom `shipped_at` dengan waktu saat ini.
6. WHEN `Order::complete()` dipanggil, THE Order SHALL memperbarui `status` menjadi `completed` dan mengisi kolom `completed_at` dengan waktu saat ini.
7. WHEN `Order::cancel()` dipanggil dengan alasan, THE Order SHALL memperbarui `status` menjadi `cancelled`, mengisi `cancelled_at`, dan menyimpan `cancellation_reason`.
8. WHILE `status = 'pending'`, THE Order SHALL mengembalikan `true` dari metode `isPending()`.
9. WHILE `status = 'confirmed'`, THE Order SHALL mengembalikan `true` dari metode `isConfirmed()`.
10. WHILE `payment_status = 'paid'`, THE Order SHALL mengembalikan `true` dari metode `isPaid()`.
11. WHILE `payment_status = 'partial'`, THE Order SHALL mengembalikan `true` dari metode `isPartiallyPaid()`.

---

### Requirement 10: Unit Test — RBAC (User Model)

**User Story:** Sebagai developer, saya ingin metode RBAC pada User Model diuji, sehingga saya yakin bahwa sistem otorisasi bekerja sesuai aturan yang ditetapkan untuk semua peran.

#### Acceptance Criteria

1. WHEN `User::hasRole()` dipanggil dengan nama peran yang dimiliki user, THE User SHALL mengembalikan `true`.
2. WHEN `User::hasRole()` dipanggil dengan nama peran yang tidak dimiliki user, THE User SHALL mengembalikan `false`.
3. WHEN `User::hasPermission()` dipanggil untuk user dengan peran `dev-admin`, THE User SHALL mengembalikan `true` untuk izin apapun (bypass semua permission checks).
4. WHEN `User::hasPermission()` dipanggil untuk user non-dev-admin dengan izin yang dimilikinya melalui peran, THE User SHALL mengembalikan `true`.
5. WHEN `User::hasPermission()` dipanggil untuk user non-dev-admin dengan izin yang tidak dimilikinya, THE User SHALL mengembalikan `false`.
6. WHEN `User::hasAnyRole()` dipanggil dengan array yang berisi setidaknya satu peran yang dimiliki user, THE User SHALL mengembalikan `true`.
7. WHEN `User::hasAnyRole()` dipanggil dengan array yang tidak berisi peran manapun yang dimiliki user, THE User SHALL mengembalikan `false`.
8. WHEN `User::hasAllPermissions()` dipanggil dengan array izin yang semuanya dimiliki user, THE User SHALL mengembalikan `true`.
9. WHEN `User::hasAllPermissions()` dipanggil dengan array izin yang salah satunya tidak dimiliki user, THE User SHALL mengembalikan `false`.

---

### Requirement 11: Unit Test — FormRequest Validation

**User Story:** Sebagai developer, saya ingin FormRequest validation classes diuji secara unit, sehingga saya yakin bahwa input yang tidak valid selalu ditolak sebelum mencapai Action.

#### Acceptance Criteria

1. WHEN `StoreSaleRequest` divalidasi dengan semua field wajib terisi dan bertipe benar, THE Request SHALL lulus validasi.
2. WHEN `StoreSaleRequest` divalidasi tanpa `date`, tanpa `items`, atau `items` berupa array kosong, THE Request SHALL gagal validasi.
3. WHEN `StoreSaleRequest` divalidasi dengan `items[*].qty` bernilai negatif atau nol, THE Request SHALL gagal validasi.
4. WHEN `StoreSaleRequest` divalidasi dengan `payment_status` yang bukan `unpaid`, `partial`, atau `paid`, THE Request SHALL gagal validasi.
5. WHEN `StorePurchaseRequest` divalidasi dengan semua field wajib terisi dan bertipe benar, THE Request SHALL lulus validasi.
6. WHEN `StorePurchaseRequest` divalidasi dengan `items[*].material_id` yang tidak ada di tabel `materials`, THE Request SHALL gagal validasi.
7. WHEN `StoreProductRequest` divalidasi dengan `name`, `base_price`, dan `current_stock` terisi dengan tipe benar, THE Request SHALL lulus validasi.
8. WHEN `StoreProductRequest` divalidasi tanpa `name` atau dengan `base_price` bernilai negatif, THE Request SHALL gagal validasi.
9. WHEN `StoreProductRequest` divalidasi dengan `current_stock` bertipe non-integer (misal: string), THE Request SHALL gagal validasi.

---

### Requirement 12: Feature Test — Autentikasi

**User Story:** Sebagai pengguna, saya ingin fitur login, logout, dan registrasi bekerja dengan benar, sehingga saya dapat mengakses aplikasi sesuai peran saya.

#### Acceptance Criteria

1. WHEN request `POST /login` dikirim dengan kredensial admin yang valid, THE AuthController SHALL mengarahkan pengguna ke halaman dashboard admin (HTTP 302).
2. WHEN request `POST /login` dikirim dengan kredensial yang salah, THE AuthController SHALL mengembalikan respons dengan error validasi (HTTP 422 atau redirect dengan errors).
3. WHEN request `POST /logout` dikirim oleh pengguna yang sudah login, THE AuthController SHALL menghapus sesi dan mengarahkan ke halaman login (HTTP 302).
4. WHEN request `POST /register/customer` dikirim dengan data valid, THE AuthController SHALL membuat `User` baru, menetapkan peran `customer`, membuat `Contact` terkait, dan mengarahkan ke customer dashboard.
5. WHEN request `POST /register/customer` dikirim dengan email yang sudah terdaftar, THE AuthController SHALL mengembalikan respons dengan error validasi untuk field `email`.
6. WHEN request `POST /login` dikirim untuk user customer/reseller, THE AuthController SHALL mengarahkan ke portal yang sesuai (customer dashboard atau reseller dashboard).

---

### Requirement 13: Feature Test — Katalog Publik

**User Story:** Sebagai pengunjung, saya ingin dapat melihat dan mencari produk di katalog publik, sehingga saya dapat menemukan produk yang saya inginkan.

#### Acceptance Criteria

1. WHEN request `GET /catalog` dikirim (tanpa autentikasi), THE CatalogController SHALL mengembalikan respons HTTP 200 beserta data produk yang aktif.
2. WHEN request `GET /catalog?search=kerajinan` dikirim, THE CatalogController SHALL mengembalikan hanya produk yang namanya mengandung kata "kerajinan".
3. WHEN request `GET /catalog?stock=tersedia` dikirim, THE CatalogController SHALL mengembalikan hanya produk dengan `current_stock > 0`.
4. WHEN request `GET /catalog?stock=habis` dikirim, THE CatalogController SHALL mengembalikan hanya produk dengan `current_stock = 0`.
5. WHEN request `GET /catalog?search=<script>alert(1)</script>` dikirim, THE CatalogController SHALL mengembalikan respons HTTP 422 (validasi gagal).

---

### Requirement 14: Feature Test — Checkout dan Pembuatan Order

**User Story:** Sebagai pembeli (guest maupun user login), saya ingin dapat membuat pesanan melalui checkout, sehingga pesanan saya tercatat dengan benar di sistem.

#### Acceptance Criteria

1. WHEN request `POST /order` dikirim oleh guest dengan data valid dan `order_method = 'form'`, THE OrderController SHALL membuat `Order` baru dengan `status = 'pending'` dan mengarahkan ke halaman sukses.
2. WHEN request `POST /order` dikirim oleh user yang sudah login, THE OrderController SHALL membuat `Order` baru yang terhubung ke `user_id` pengguna tersebut.
3. WHEN request `POST /order` dikirim dengan `create_account = true` oleh guest, THE OrderController SHALL membuat `User` baru dengan peran `customer`, membuat `Contact` terkait, dan mengisi `user_id` pada `Order`.
4. WHEN request `POST /order` dikirim oleh reseller dengan `payment_mode = 'dp'` dan `down_payment_amount >= 30%` dari total, THE OrderController SHALL membuat `Order` dengan `payment_status = 'partial'` dan nilai `remaining_balance` yang benar.
5. WHEN request `POST /order` dikirim oleh reseller dengan `down_payment_amount < 30%` dari total, THE OrderController SHALL mengembalikan respons error validasi untuk field `down_payment_amount`.
6. WHEN request `POST /order` dikirim tanpa item atau dengan item kosong, THE OrderController SHALL mengembalikan respons error dengan pesan "Keranjang belanja kosong.".
7. WHEN `Order` berhasil dibuat, THE Order SHALL memiliki `order_number` dengan format `ORD-YYYYMMDD-NNN`.

---

### Requirement 15: Feature Test — Admin Order Management

**User Story:** Sebagai admin, saya ingin dapat mengelola seluruh pesanan (lihat, update status, hapus), sehingga saya dapat memproses pesanan pelanggan dengan efisien.

#### Acceptance Criteria

1. WHEN request `GET /admin/orders` dikirim oleh user dengan izin `view-orders`, THE AdminOrderController SHALL mengembalikan respons HTTP 200 beserta daftar semua pesanan.
2. WHEN request `GET /admin/orders` dikirim oleh user tanpa izin `view-orders`, THE AdminOrderController SHALL mengembalikan respons HTTP 403.
3. WHEN request `PATCH /admin/orders/{id}/status` dikirim oleh admin dengan status yang valid, THE AdminOrderController SHALL memperbarui `status` pesanan dan mengembalikan redirect atau HTTP 200.
4. WHEN request `DELETE /admin/orders/{id}` dikirim oleh user dengan izin `manage-orders`, THE AdminOrderController SHALL menghapus pesanan (soft delete) dan mengembalikan redirect.
5. WHEN request `DELETE /admin/orders/{id}` dikirim oleh user tanpa izin `manage-orders`, THE AdminOrderController SHALL mengembalikan respons HTTP 403.

---

### Requirement 16: Feature Test — Modul Penjualan (Sales)

**User Story:** Sebagai admin/operator, saya ingin endpoint modul penjualan diuji, sehingga saya yakin bahwa pembuatan transaksi penjualan melalui HTTP berfungsi dengan benar.

#### Acceptance Criteria

1. WHEN request `GET /sales` dikirim oleh user dengan izin `view-sales`, THE SaleController SHALL mengembalikan respons HTTP 200.
2. WHEN request `GET /sales` dikirim oleh user tanpa autentikasi, THE SaleController SHALL mengembalikan redirect ke halaman login (HTTP 302).
3. WHEN request `POST /sales` dikirim oleh admin dengan data valid dan stok mencukupi, THE SaleController SHALL membuat `Sale` baru, mengurangi stok produk, dan mengarahkan ke halaman index sales.
4. WHEN request `POST /sales` dikirim dengan stok produk yang tidak mencukupi, THE SaleController SHALL mengembalikan respons dengan error yang menyebut nama produk.
5. WHEN request `POST /sales` dikirim tanpa field wajib (`date`, `items`), THE SaleController SHALL mengembalikan respons dengan error validasi.

---

### Requirement 17: Feature Test — Modul Pembelian (Purchases)

**User Story:** Sebagai admin/operator, saya ingin endpoint modul pembelian diuji, sehingga saya yakin bahwa pembuatan transaksi pembelian melalui HTTP memperbarui stok bahan baku dengan benar.

#### Acceptance Criteria

1. WHEN request `GET /purchases` dikirim oleh user dengan izin `view-purchases`, THE PurchaseController SHALL mengembalikan respons HTTP 200.
2. WHEN request `POST /purchases` dikirim oleh admin dengan data valid, THE PurchaseController SHALL membuat `Purchase` baru, menambah `current_stock` material, dan mengarahkan ke halaman index purchases.
3. WHEN request `POST /purchases` dikirim tanpa field wajib (`date`, `items`), THE PurchaseController SHALL mengembalikan respons dengan error validasi.

---

### Requirement 18: Feature Test — Modul Keuangan (Finance)

**User Story:** Sebagai admin/owner, saya ingin endpoint modul keuangan diuji, sehingga saya yakin bahwa dashboard keuangan dan transaksi manual berfungsi dengan benar.

#### Acceptance Criteria

1. WHEN request `GET /finance` dikirim oleh user dengan izin `view-finance`, THE FinanceController SHALL mengembalikan respons HTTP 200.
2. WHEN request `GET /finance` dikirim oleh user tanpa izin `view-finance`, THE FinanceController SHALL mengembalikan respons HTTP 403.
3. WHEN request `POST /finance/transactions` dikirim oleh admin dengan data valid dan `type = 'in'`, THE FinanceController SHALL membuat `CashLedger` bertipe `in` dan memperbarui `balance` akun.
4. WHEN request `POST /finance/transactions` dikirim dengan `type = 'out'` dan saldo tidak mencukupi, THE FinanceController SHALL mengembalikan respons dengan error saldo tidak mencukupi.

---

### Requirement 19: Feature Test — CRUD Produk (Admin)

**User Story:** Sebagai admin, saya ingin endpoint CRUD produk diuji, sehingga saya yakin bahwa pengelolaan data produk berjalan dengan benar dan hanya dapat diakses oleh pengguna yang berwenang.

#### Acceptance Criteria

1. WHEN request `GET /products` dikirim oleh admin, THE ProductController SHALL mengembalikan respons HTTP 200 beserta daftar produk.
2. WHEN request `POST /products` dikirim oleh admin dengan data valid, THE ProductController SHALL membuat `Product` baru dan mengarahkan ke halaman index produk.
3. WHEN request `POST /products` dikirim tanpa `name` atau dengan `base_price` negatif, THE ProductController SHALL mengembalikan respons dengan error validasi.
4. WHEN request `PATCH /products/{id}` dikirim oleh admin dengan data valid, THE ProductController SHALL memperbarui data `Product` terkait.
5. WHEN request `DELETE /products/{id}` dikirim oleh admin, THE ProductController SHALL menghapus `Product` terkait (soft delete) dan mengarahkan ke halaman index produk.
6. WHEN request `POST /products` dikirim oleh user tanpa izin `manage-products`, THE ProductController SHALL mengembalikan respons HTTP 403.

---

### Requirement 20: Feature Test — Penyesuaian Stok (Stock Adjustment)

**User Story:** Sebagai admin/operator, saya ingin endpoint penyesuaian stok diuji, sehingga saya yakin bahwa proses stock opname mencatat perubahan stok dengan benar.

#### Acceptance Criteria

1. WHEN request `POST /stock-adjustments` dikirim oleh admin dengan `actual_stock` berbeda dari stok saat ini, THE StockAdjustmentController SHALL membuat `StockAdjustment` dan memperbarui `current_stock` item terkait.
2. WHEN request `POST /stock-adjustments` dikirim dengan `actual_stock` sama dengan stok saat ini, THE StockAdjustmentController SHALL mengembalikan respons dengan error "Tidak ada perubahan stok.".
3. WHEN request `POST /stock-adjustments` dikirim tanpa autentikasi, THE StockAdjustmentController SHALL mengembalikan redirect ke halaman login (HTTP 302).

---

### Requirement 21: Property-Based Testing — Invariant Kalkulasi Finansial

**User Story:** Sebagai developer, saya ingin invariant kalkulasi finansial diverifikasi dengan data input acak, sehingga saya yakin bahwa formula perhitungan selalu benar untuk rentang nilai input apapun.

#### Acceptance Criteria

1. FOR ALL kombinasi nilai `total_amount`, `shipping_fee` (≥ 0), dan `discount` (0 ≤ discount ≤ total_amount) yang valid, THE TestSuite SHALL memverifikasi bahwa `grand_total = total_amount + shipping_fee - discount` selalu berlaku.
2. FOR ALL kombinasi `qty` dan `price` item penjualan yang valid (qty ≥ 1, price ≥ 0), THE TestSuite SHALL memverifikasi bahwa `total_amount = SUM(qty_i * price_i)` untuk semua item selalu berlaku.
3. FOR ALL kombinasi biaya produksi yang valid (`total_material_cost`, `labor_cost`, `overhead_cost`, `additional_cost` ≥ 0), THE TestSuite SHALL memverifikasi bahwa `grand_total_cost = total_material_cost + labor_cost + overhead_cost + additional_cost` selalu berlaku.
4. FOR ALL urutan pembayaran parsial yang valid hingga melunasi `grand_total`, THE TestSuite SHALL memverifikasi bahwa `payment_status` berubah dari `unpaid` → `partial` → `paid` secara monoton dan tidak pernah mundur.
5. FOR ALL nilai `actual_stock` yang valid, THE TestSuite SHALL memverifikasi bahwa `quantity_difference = actual_stock - previous_stock` pada `StockAdjustment` yang dibuat selalu berlaku.
