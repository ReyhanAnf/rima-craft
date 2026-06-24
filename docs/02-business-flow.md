# Business Flow (Alur Bisnis)

Berikut adalah alur logika bisnis utama yang terjadi di dalam sistem.

## 1\. Alur Transaksi Publik & Portal Pelanggan

*   **Aktor:** Pelanggan (Customer Eceran / Partner Reseller)
*   **Proses:**
    1.  Pelanggan membuka web/PWA, melihat katalog produk (menampilkan foto, harga sesuai *role*, stok tersedia). *Customer* biasa melihat harga standar, *Partner* melihat harga khusus reseller.
    2.  Pelanggan dapat melakukan registrasi/login ke portal mereka untuk melihat riwayat pesanan dan status tagihan.
    3.  Pelanggan menambahkan produk ke keranjang belanja.
    4.  Pelanggan melakukan *checkout* pesanan.
    5.  Sistem otomatis menyimpan pesanan sebagai **Draft (Pending WA)** dan meng-generate Kode Referensi (contoh: `ORD-001`).
    6.  Pelanggan diarahkan ke WhatsApp Admin dengan teks otomatis yang memuat Kode Referensi tersebut untuk negosiasi lanjutan/konfirmasi transfer.

## 2\. Alur Pembelian Bahan Baku (Inbound)

*   **Aktor:** Admin
*   **Proses:**
    1.  Admin membeli bahan baku (Pandan Laut, Eceng Gondok, benang, furing, dll) dari Supplier.
    2.  Admin masuk ke menu "Pembelian Bahan".
    3.  Admin mencatat: Tanggal, Nama Supplier, Item Bahan, Jumlah, Harga Satuan.
    4.  **Dampak Sistem:** Stok Bahan Baku bertambah, Saldo Kas berkurang (tercatat sebagai Pengeluaran Pembelian di Laporan Keuangan).

## 3\. Alur Produksi & Makloon Borongan (Core Business)

*   **Aktor:** Admin & Pengrajin (_Freelancer_)
*   **Proses:**
    1.  **Serah Bahan:** Admin membuat "Surat Tugas Produksi" ke Pengrajin A. Admin menginput bahan apa saja yang diberikan (misal: 10 Kg Pandan Laut).
        *   **Dampak:** Stok Bahan Baku berkurang. Status produksi: _In Progress_.
    2.  **Terima Produk Jadi:** Pengrajin A mengembalikan hasil kerjanya (misal: 20 Tas Anyam). Admin menerima dan memverifikasi kualitas.
    3.  **Penyelesaian:** Admin menekan tombol "Produksi Selesai". Sistem akan meminta input jumlah upah borongan yang dibayarkan ke Pengrajin A.
        *   **Dampak:** Stok Produk Jadi bertambah, Saldo Kas berkurang (tercatat sebagai Biaya Produksi/Upah).

## 4\. Alur Penjualan Produk (Outbound)

*   **Aktor:** Admin
*   **Proses:**
    1.  Admin menerima chat WhatsApp dari pelanggan yang membawa Kode Referensi (`ORD-001`).
    2.  Setelah *deal* harga dan pelanggan mentransfer uang, Admin membuka Dashboard aplikasi.
    3.  Di Dashboard, Admin akan langsung melihat **To-Do List: Pesanan Draft Baru**.
    4.  Admin mengklik pesanan `ORD-001`, menyesuaikan harga jika ada diskon hasil negosiasi, lalu cukup menekan 1 tombol: **"Tandai Lunas & Proses"**.
    5.  **Dampak Sistem (Otomatis):** Tanpa input berulang, sistem otomatis mengubah status pesanan, mengurangi Stok Produk Jadi, dan menambah Saldo Kas (tercatat sebagai Pemasukan di Laporan Keuangan).