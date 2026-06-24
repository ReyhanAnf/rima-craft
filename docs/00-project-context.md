# Project Context

## Nama Proyek

Sistem Manajemen & E-Katalog Rima Craft (ERP Mini & PWA)

## Identitas Bisnis / Brand

*   **Nama Brand:** Rima Craft
*   **Catatan Teknis:** Nama brand dan informasi kontak dasar bisnis **wajib** disimpan di dalam konfigurasi aplikasi (misal: file `.env` atau _settings table_) agar dapat diubah secara dinamis tanpa mengubah _source code_.

## Deskripsi Singkat

Aplikasi berbasis web _Mobile-First_ berwujud Progressive Web App (PWA) untuk UMKM kerajinan lokal (Rima Craft). Aplikasi ini berfungsi ganda: sebagai e-katalog publik standar e-commerce dengan sistem keranjang yang bermuara negosiasi ke WhatsApp, dan sebagai sistem _back-office_ (ERP mini) untuk manajemen stok (bahan baku & produk jadi), kas, dan operasional produksi borongan (_freelancer_).

## Target Pengguna

1.  **Pelanggan (Publik):** Distributor (grosir) dan pembeli eceran yang melihat katalog via HP.
2.  **Owner / Pemilik Bisnis:** Memantau laporan keuangan, neraca kas, dan ringkasan dashboard.
3.  **Admin / Operator:** Menginput penjualan manual, pembelian bahan baku, mengelola alur produksi pengrajin borongan, serta mengelola chat pelanggan.

## Aturan & Karakteristik Bisnis Utama

*   **UI/UX:** 100% Bahasa Indonesia, _Mobile-First_, harus sangat _user-friendly_ untuk orang awam.
*   **Transaksi Publik:** Tidak ada _checkout_ maupun _payment gateway_ otomatis. Keranjang belanja menampilkan harga dasar dan di-_generate_ menjadi pesan WhatsApp terstruktur ke Admin. Negosiasi grosir dilakukan di WA.
*   **Dinamika Bahan Baku:** Bahan baku bersifat dinamis (utama: Pandan Laut dan Eceng Gondok, dsb). Tidak boleh di-_hardcode_, harus bisa dikelola lewat Master Data.
*   **Manajemen Produksi:** Klien menggunakan sistem upah borongan. Sistem harus melacak keluarnya bahan baku ke pengrajin, masuknya produk jadi, serta pencatatan pengeluaran kas untuk upah.
*   **Input Data:** Semua aliran masuk/keluar barang dan uang (termasuk penjualan hasil tawar-menawar WA) diinput secara manual oleh Admin (sentralisasi data).
*   **Skalabilitas:** Arsitektur modular agar siap menampung fitur kompleks di masa depan tanpa merombak sistem dari nol.