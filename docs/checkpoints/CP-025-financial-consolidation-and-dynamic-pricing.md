# Checkpoint [CP-025]: Konsolidasi Keuangan & Dinamisasi Harga Katalog Produk

**Status:** Selesai ✅
**Tanggal:** 2026-07-06

---

## 1. Deskripsi Pekerjaan
Checkpoint ini mencakup konsolidasi keuangan ERP Rima Craft ke rekening tunggal (Kas Utama), pembentukan tampilan dashboard analitis keuangan yang premium, pemisahan form tambah/edit produk jadi ke halaman mandiri terpisah, serta penyempurnaan sistem penentuan harga produk (tingkat wilayah berhirarki dan harga khusus reseller spesifik).

---

## 2. Rincian Implementasi

### A. Konsolidasi Keuangan (Finance Module)
1. **Rekening Tunggal & Label Transaksi**:
   - Menambahkan migrasi database untuk mengarahkan seluruh transaksi masa lalu ke `account_id = 1` (Kas Utama) dan menambahkan kolom `payment_label` ke tabel ledger.
   - Memodifikasi `RecordTransactionAction`, `RecordPaymentAction`, `RecordSaleAction`, `RecordPurchaseAction`, dan `RecordProductionAction` agar menyematkan label transaksi (seperti BCA, Cash, COD, dll.) ke mutasi ledger.
2. **Dashboard Analitik Keuangan**:
   - Memperbarui halaman [Finance/Index.vue](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/js/pages/Finance/Index.vue) dengan visual dashboard yang premium (semi-glassmorphic total balance card, grafik tren mutasi bulanan, breakdown metode pembayaran, dan tabel mutasi).
3. **Penyatuan Komponen Filter Tanggal**:
   - Mengganti input tanggal ganda (start & end date) menjadi satu input range DatePicker terintegrasi di modul Penjualan, Pembelian, dan Keuangan.
4. **Perubahan Nama Menu**:
   - Mengubah nama navigasi menu "Buku Kas" menjadi "Keuangan" di sidebar [AdminLayout.vue](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/js/layouts/AdminLayout.vue) dan berkas konfigurasi menu.

### B. Pemisahan Halaman Form Produk & Dinamisasi Harga
1. **Pemisahan Form ke Halaman Mandiri**:
   - Menghapus form dari modal/drawer di [Index.vue](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/js/pages/Products/Index.vue).
   - Membuat halaman baru [Create.vue](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/js/pages/Products/Create.vue) dan [Edit.vue](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/js/pages/Products/Edit.vue) untuk pembuatan dan pembaruan produk secara lebih leluasa.
   - Menambahkan metode rute Inertia `create()` dan `edit()` pada `ProductController`.
2. **Pencarian Wilayah Berhirarki**:
   - Menampilkan Provinsi dan Kota/Kabupaten secara tergabung dan searchable pada seleksi wilayah.
   - Memperbarui logika pencarian harga wilayah di model `Product.php`: Sistem otomatis menggunakan harga Kota/Kab jika tersedia, atau beralih ke harga Provinsi sebagai fallback.
3. **Harga Khusus Reseller Spesifik**:
   - Membuat tabel migrasi database `product_user_prices` dan model `ProductUserPrice`.
   - Menambahkan section khusus reseller di form Create/Edit untuk mengkonfigurasi harga jual eksklusif per reseller.
   - Menjadikan harga khusus reseller individu sebagai prioritas penentuan harga tertinggi mengesampingkan wilayah.

---

## 3. Berkas yang Diubah / Ditambahkan
* **Migrasi**:
  - `database/migrations/2026_07_06_100000_create_product_user_prices_table.php` (Baru)
* **Model**:
  - [Product.php](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/app/Models/Product.php) (Diperbarui)
  - [ProductUserPrice.php](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/app/Models/ProductUserPrice.php) (Baru)
* **Kontroler & Request**:
  - [ProductController.php](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/app/Http/Controllers/ProductController.php) (Diperbarui)
  - [StoreProductRequest.php](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/app/Http/Requests/Product/StoreProductRequest.php) (Diperbarui)
  - [UpdateProductRequest.php](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/app/Http/Requests/Product/UpdateProductRequest.php) (Diperbarui)
* **Views**:
  - [Create.vue](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/js/pages/Products/Create.vue) (Baru)
  - [Edit.vue](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/js/pages/Products/Edit.vue) (Baru)
  - [Index.vue](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/js/pages/Products/Index.vue) (Diperbarui)
