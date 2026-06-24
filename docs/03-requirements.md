# Product Requirements (Kebutuhan Sistem)

Disusun berdasarkan prioritas pengembangan (MoSCoW Method) untuk memastikan MVP (Minimum Viable Product) tepat sasaran.

## 1\. Must Have (Fase 1 - MVP Core)

### A. Konfigurasi Sistem (Global)

*   Nama brand (Rima Craft), logo, dan nomor WhatsApp Admin diatur melalui konfigurasi terpusat (tidak di-_hardcode_ di _views_).

### B. Modul Autentikasi & Pengguna

*   Login Admin, Owner, Customer (Eceran), dan Partner (Reseller).
*   Role Management:
    *   **Owner:** Hanya read/view laporan.
    *   **Admin:** Bisa input data operasional.
    *   **Partner:** Pelanggan B2B/Reseller, dapat melihat harga khusus, riwayat transaksi, dan status tagihan.
    *   **Customer:** Pelanggan B2C/Eceran, dapat melihat harga standar, riwayat pesanan, dan profil mereka.

### C. Modul Katalog Publik (Front-End)

*   Halaman Home (Landing page sederhana).
*   Halaman Katalog Produk dengan filter pencarian. Harga yang ditampilkan menyesuaikan role pengguna yang sedang login (Standar vs Reseller).
*   Fitur Keranjang Belanja dan Checkout pesanan mandiri.
*   Portal Pelanggan terintegrasi untuk melihat riwayat pesanan, status pengiriman, dan tagihan.
*   Redirect opsi chat WhatsApp Admin dengan format pesanan terstruktur untuk negosiasi lanjutan.

### D. Modul Master Data (Back-Office)

*   **Manajemen Bahan Baku:** CRUD data bahan (Pandan laut, Eceng gondok, dll), satuan, dan alert stok minimum.
*   **Manajemen Produk Jadi:** CRUD produk jualan, foto, harga dasar eceran, deskripsi.
*   **Manajemen Mitra/Kontak:** Buku alamat untuk Supplier, Pelanggan (Customer & Partner), dan Pengrajin (_freelancer_). Terintegrasi dengan akun User yang login.

### E. Modul Inventori & Produksi

*   **Pembelian Bahan:** Form pencatatan masuknya bahan dari supplier.
*   **Produksi Borongan:**
    *   Form serah-terima bahan ke Pengrajin.
    *   Form penerimaan produk jadi dari Pengrajin beserta input pembayaran upah.
*   Penyesuaian Stok (Stock Opname) manual jika ada barang hilang/rusak.

### F. Modul Penjualan

*   Pencatatan transaksi penjualan manual (pemotongan stok produk jadi & pencatatan harga final kesepakatan).
*   Riwayat transaksi (Status: Lunas, Dikirim, Selesai).

### G. Modul Keuangan / Kas Sederhana

*   Buku Kas Umum (Pemasukan vs Pengeluaran).
*   Pencatatan otomatis dari modul Pembelian (minus), Produksi/Upah (minus), dan Penjualan (plus).
*   Pencatatan manual untuk biaya lain-lain (misal: listrik, bayar kurir, snack).

## 2\. Should Have (Fase 2)

*   Fitur Chat Terpusat di dalam dashboard Admin (seperti WhatsApp Web) jika ingin beralih dari WA reguler.
*   Laporan Laba/Rugi Bulanan yang rapi dan bisa diekspor ke PDF/Excel.

## 3\. Could Have (Fase 3 - Opsional & Ekspansi)

*   Multi-gudang (jika usaha semakin besar).
*   Integrasi ongkos kirim otomatis (RajaOngkir).
*   Modul _Invoice_ digital dengan link ke pelanggan.

## 4\. Batasan Non-Fungsional

*   **Teknologi:** BHA Stack (Blade, HTMX, Alpine.js, Laravel).
*   **Kinerja:** Harus sangat responsif di HP (PWA ready, manifest disiapkan untuk bisa "Add to Homescreen").
*   **Hosting:** Dioptimalkan untuk Shared Hosting (tanpa antrian daemon yang memberatkan).