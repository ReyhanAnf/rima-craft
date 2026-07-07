# [cover]

## BAB I: PENDAHULUAN

Aplikasi **Rima Craft** adalah sebuah platform digital terintegrasi yang dirancang khusus untuk mempermudah operasional bisnis kerajinan anyaman tradisional bercita rasa modern. Aplikasi ini memadukan transaksi ritel umum (B2C), pengelolaan kemitraan keagenan (B2B/Reseller), manajemen stok bahan baku, konversi proses produksi, pencatatan keuangan Buku Kas (Ledger), hingga monitoring logistik pengiriman. Aplikasi ini dibangun menggunakan teknologi *web modern* berbasis Laravel sebagai sistem backend, dipadukan dengan HTMX untuk interaksi yang cepat tanpa *reload* halaman penuh, serta Alpine.js dan Tailwind CSS untuk menyajikan antarmuka pengguna yang dinamis, cepat, dan responsif.

Sistem Rima Craft didukung oleh pembagian peran pengguna (roles & permissions) yang terstruktur rapi untuk menjaga keamanan data dan alur kerja bisnis. Peran-peran tersebut meliputi:

- **Pelanggan Umum (Customer):** Pengguna retail yang dapat menjelajahi katalog produk, melakukan checkout pesanan, melacak pengiriman, serta mengelola profil belanja pribadinya secara online.
- **Reseller (B2B Partner):** Mitra agen terverifikasi yang berhak mendapatkan harga khusus (harga reseller/grosir), melakukan pembayaran berjangka menggunakan uang muka (Down Payment - DP minimal 30%), serta mengakses dasbor khusus untuk memantau ringkasan tagihan piutang belanja keagenan.
- **Operator:** Staf operasional gudang dan penjualan yang berwenang mencatat transaksi offline (pembelian bahan baku, penjualan retail toko, pencatatan produksi, dan penyesuaian stok fisik) serta mengelola status pesanan pelanggan.
- **Owner (Pemilik Usaha):** Manajemen tingkat atas yang memiliki hak penuh memantau buku keuangan kas, mencatat jurnal manual, menganalisis laba kotor operasional, memantau kinerja supplier/pelanggan, serta mengelola pengaturan konten landing page.
- **Super Admin:** Pusat kendali utama administrasi sistem yang berwenang memverifikasi pendaftaran akun reseller baru, mengelola hak akses peran (roles & permissions), serta menambahkan pengguna admin/staf operasional.

Dengan adanya panduan tutorial detail ini, setiap pengguna diharapkan dapat memahami dan mengoperasikan fitur-fitur aplikasi Rima Craft sesuai wewenang perannya dari awal hingga akhir.

---

## BAB II: AUTENTIKASI & REGISTRASI

Autentikasi di Rima Craft terbagi menjadi dua jalur utama, yaitu jalur publik untuk pelanggan dan reseller, serta jalur khusus untuk staf administrasi.

### 2.1 Login Pelanggan & Reseller
Pelanggan retail maupun reseller menggunakan halaman masuk yang sama untuk mengakses dasbor portal mereka.

**Langkah-langkah Login:**
- Buka halaman login publik melalui menu navigasi atas atau URL `/login`.
- Masukkan alamat **Email** dan **Password** yang telah terdaftar.
- Klik tombol **Login**.
- Jika data yang Anda masukkan benar, sistem akan mendeteksi peran akun Anda secara otomatis:
  - Pelanggan retail akan diarahkan ke **Customer Dashboard Portal**.
  - Reseller terverifikasi akan diarahkan ke **Reseller Dashboard Portal**.
  - Reseller yang masih dalam status peninjauan (*pending/rejected*) akan diarahkan ke halaman dasbor pelanggan dengan notifikasi status verifikasi akun.

[gambar]

### 2.2 Lupa Kata Sandi
Jika Anda lupa kata sandi untuk masuk ke aplikasi, Anda dapat meresetnya dengan mudah.

**Langkah-langkah Reset Password:**
- Buka halaman login dan klik tautan **Lupa Kata Sandi? (Forgot Password?)**.
- Masukkan alamat email yang terdaftar pada akun Anda.
- Klik tombol **Kirim Tautan Reset Password**.
- Sistem akan mengirimkan email yang berisi tautan untuk mereset kata sandi Anda. Buka email tersebut dan klik tautan yang tersedia.
- Anda akan diarahkan ke halaman untuk membuat kata sandi baru. Masukkan kata sandi baru Anda dan konfirmasikan.
- Setelah itu, Anda dapat login kembali dengan menggunakan kata sandi yang baru.

[gambar]

### 2.3 Registrasi Akun Pelanggan (B2C)
Pengunjung baru yang ingin bertransaksi secara online dan melacak riwayat belanjanya dapat mendaftarkan akun pelanggan retail.

**Langkah-langkah Mendaftar:**
- Akses halaman daftar melalui URL `/register` atau tombol pendaftaran di halaman login.
- Isi formulir registrasi pelanggan dengan lengkap:
  - **Nama Lengkap**
  - **Alamat Email**
  - **Nomor Telepon/WhatsApp**
  - **Password** (minimal 8 karakter)
  - **Konfirmasi Password**
  - **Alamat Lengkap**
  - **Provinsi** (pilih dari menu dropdown)
  - **Kota/Kabupaten** (pilih dari menu dropdown yang muncul setelah memilih provinsi)
- Klik tombol **Daftar Akun**.
- Akun pelanggan Anda akan langsung aktif dan Anda akan diarahkan ke portal dasbor pelanggan.

[gambar]

### 2.4 Registrasi Kemitraan Reseller (B2B)
Bagi pelaku usaha atau grosir yang ingin mendapatkan potongan harga khusus reseller dan fasilitas pembayaran DP, dapat mengajukan akun reseller.

**Langkah-langkah Pengajuan Reseller:**
- Pada halaman `/register`, aktifkan pilihan opsi **Daftar sebagai Reseller** (atau akses langsung `/register/reseller`).
- Selain kolom identitas pribadi, Anda wajib melengkapi data bisnis keagenan berikut:
  - **Nama Perusahaan/Toko (Company Name)**
  - **Jenis Bisnis (Business Type)** (misalnya: Retailer, Dropshipper, Grosir, dll.)
- Klik tombol **Ajukan Kemitraan Reseller**.
- Setelah mendaftar, akun Anda berstatus **Pending**. Anda akan masuk ke dasbor mode pelanggan dengan notifikasi: *"Akun reseller Anda sedang dalam proses verifikasi."*
- Hubungi admin untuk mempercepat proses persetujuan dokumen kemitraan.

[gambar]

### 2.5 Login Staf Admin & Manajemen
Bagi operator gudang, owner, dan administrator, masuk ke sistem menggunakan panel masuk khusus guna mengamankan hak akses backend.

**Langkah-langkah Login Admin:**
- Akses halaman masuk admin melalui URL `/admin/login`.
- Masukkan **Email** dan **Password** staf Anda.
- Klik tombol **Login Admin**.
- Staf yang berhasil masuk akan langsung diarahkan ke **Dashboard Admin** utama.

[gambar]

### 2.6 Keluar dari Sistem (Logout)
- Untuk menjaga keamanan akun, buka menu samping (sidebar) dan pilih form/tombol **Keluar / Logout** yang berwarna merah.
- Sistem akan menghapus sesi login dan mengarahkan Anda kembali ke halaman publik.

---

## BAB III: ALUR TRANSAKSI PELANGGAN (B2C) & RESELLER (B2B)

Bab ini menjelaskan langkah-langkah bertransaksi secara online dari sudut pandang pembeli (pelanggan umum maupun reseller terverifikasi).

### 3.1 Eksplorasi Katalog Produk & Pencarian
Aplikasi menyediakan halaman galeri produk interaktif agar pembeli dapat menemukan kerajinan anyaman yang diinginkan.

**Cara Mengeksplorasi Produk:**
- Buka halaman depan Rima Craft atau akses menu **Katalog Produk**.
- Sistem akan memuat seluruh daftar produk kerajinan yang aktif.
- Gunakan fitur **Pencarian** untuk mencari nama produk tertentu.
- **Informasi Harga Dinamis:**
  - Jika Anda pengunjung umum atau pelanggan biasa (B2C), harga yang tertera adalah **Harga Retail (Base Price)**.
  - Jika Anda login sebagai Reseller terverifikasi, harga yang ditampilkan otomatis berubah menjadi **Harga Reseller (Reseller Price)** yang lebih murah.

[gambar]

### 3.2 Memasukkan Produk ke Keranjang Belanja
**Langkah-langkah Memilih Barang:**
- Klik produk yang diminati untuk melihat deskripsi detail, stok yang tersedia, serta variasi produk (jika ada).
- Masukkan kuantitas pembelian yang diinginkan.
- Klik tombol keranjang. Keranjang belanja akan memunculkan pop-up yang menampilkan daftar belanjaan sementara Anda.

[gambar]

### 3.3 Proses Checkout Pemesanan
Setelah selesai memilih produk, lakukan langkah checkout untuk memproses pesanan.

**Langkah-langkah Checkout:**
- Klik tombol **Checkout** pada panel keranjang belanja.
- Anda akan diarahkan ke halaman checkout.
- **Pengisian Data Pengiriman:**
  - Jika sudah login, data Nama, Email, Telepon, dan Alamat Anda otomatis terisi dari profil akun Anda.
  - Jika Anda bertransaksi sebagai tamu (*guest checkout*), isi formulir identitas penerima secara manual. Anda dapat mengisi kolom *Buat Akun Sekarang* dengan password baru agar sistem otomatis membuatkan akun pelanggan setelah pemesanan selesai.
- **Pemilihan Lokasi & Ongkir Otomatis:**
  - Pilih **Provinsi** dan **Kota/Kabupaten** tujuan pengiriman. Sistem secara otomatis akan menampilkan perhitungan tarif ongkos kirim.
- **Fasilitas Down Payment (DP) Khusus Reseller:**
  - Jika Anda login sebagai Reseller terverifikasi, sistem menampilkan opsi **Bayar Lunas** atau pembayaran uang muka (**DP minimal 30%**).
- Tinjau ringkasan belanja dan pastikan seluruh pesanan sudah benar.
- Klik tombol **Buat Pesanan**.

[gambar]

### 3.4 Halaman Sukses & Instruksi Pembayaran
Setelah pesanan berhasil dibuat, pembeli akan dialihkan ke halaman sukses.

**Langkah-langkah Konfirmasi Pembayaran:**
- Halaman sukses menampilkan **Nomor Pesanan** unik Anda.
- **Instruksi Transfer:** Sistem menampilkan instruksi rekening tujuan.
- Lakukan transfer bank sejumlah total pembayaran Anda.
- **Konfirmasi WhatsApp:** Klik tombol konfirmasi untuk mengirim bukti transfer via WhatsApp ke admin.

[gambar]

### 3.5 Riwayat Pesanan & Dasbor
Pembeli terdaftar dapat memantau perjalanan paket pesanan mereka secara mandiri.

**Cara Memantau Pesanan:**
- Buka menu **Pesanan Saya** (My Orders) dari portal pelanggan.
- Klik pesanan yang diinginkan untuk memantau status secara *real-time*.
- Anda juga dapat menyalin nomor resi pada kolom yang disediakan admin setelah status berubah menjadi **Dikirim**.

[gambar]

---

## BAB IV: PANEL ADMIN (BACKEND SYSTEM)

Panel admin merupakan pusat kendali operasional Rima Craft yang diakses oleh Operator, Owner, dan Super Admin.

### 4.1 Navigasi Utama Panel Admin
Setelah login, Anda akan masuk ke halaman Admin. Pada sisi sebelah kiri layar, terdapat **Menu Sidebar** yang memuat seluruh modul backend: Dashboard, Bahan Baku, Katalog Produk, Penjualan, Pesanan Online, Produksi, Stok, Buku Kontak, Buku Kas, Pembelian, Galeri, dan Pengaturan Web. Klik ikon garis tiga (burger menu) jika Anda mengakses melalui *smartphone* untuk membuka navigasi ini.

[gambar]

### 4.2 Penggunaan Fitur Pencarian & Filter Data (Universal)
Hampir seluruh modul tabel data (Bahan Baku, Penjualan, Pesanan, Pembelian, dll) dilengkapi dengan fitur penyaringan untuk memudahkan pencarian data secara spesifik.
**Langkah-langkah Memfilter Tabel:**
- Di bagian atas setiap tabel data, klik tombol **Filter**.
- Sebuah panel filter *(Filter Panel)* akan terbuka ke bawah (*dropdown*).
- Anda bisa menyaring data berdasarkan parameter tertentu sesuai modul, misalnya:
  - **Rentang Tanggal (Date Range):** Mencari transaksi dari tanggal A sampai tanggal B.
  - **Status:** (Contoh: Menampilkan pesanan yang hanya berstatus *Lunas* atau *Diproses*).
  - **Tipe / Role:** (Contoh: Menampilkan transaksi pemasukan saja pada Buku Kas).
- Setelah parameter dipilih, tabel otomatis memuat ulang data yang sesuai.
- Informasi "Filter aktif:" akan muncul di atas tabel. Untuk menghapus semua saringan dan mengembalikan tabel seperti semula, klik teks **Reset Filter**.

[gambar]

### 4.3 Dashboard
Dashboard menyajikan visualisasi data performa usaha Rima Craft secara *real-time*.

**Cara Menggunakan Dashboard:**
- Saat pertama kali login, Anda otomatis berada di halaman **Dashboard**.
- Di bagian atas, terdapat opsi **Filter Rentang Waktu** khusus analitik (Hari Ini, 7 Hari, 30 Hari, Bulan Ini). Klik salah satu filter untuk mengubah rentang grafik statistik.
- Anda dapat memantau kotak ringkasan metrik: **Total Pendapatan**, **Belanja Bahan**, **Biaya Produksi**, dan **Laba Kotor (Gross Profit)**.
- Gulir ke bawah untuk melihat grafik garis tren penjualan dan pembelian.
- Di bagian bawah, terdapat tabel ringkasan aksi cepat, termasuk kemampuan untuk melakukan *Approval* / Penyetujuan langsung terhadap pesanan tertunda atau pendaftaran reseller baru.

[gambar]

### 4.4 Bahan Baku
Modul ini digunakan untuk mengelola inventaris bahan baku mentah (material) yang disimpan di gudang produksi.

**Langkah-langkah Menambah Bahan Baku:**
- Pada sidebar, klik menu **Bahan Baku**. Anda akan melihat tabel daftar material yang ada.
- Klik tombol **Tambah Bahan Baku** di pojok kanan atas tabel.
- Halaman formulir bahan baku akan terbuka. Isi kolom-kolom berikut:
  - **Nama Bahan Baku** (Contoh: Rotan Sintetis, Pandan Kering).
  - **Satuan** (Pilih satuan ukur seperti Kg, Meter, Lembar).
  - **Harga Beli** (Estimasi harga dasar bahan per satuan).
  - **Stok Minimal** (Masukkan angka batas peringatan jika stok menipis).
- Setelah semua terisi, klik tombol **Simpan**.
- *Catatan: Untuk mengedit bahan baku yang sudah ada, klik ikon pensil (Perbarui) pada baris tabel material tersebut. Untuk menghapus, klik ikon tempat sampah (Delete).*

[gambar]

### 4.5 Katalog Produk
Menu ini digunakan untuk mengelola data master kerajinan anyaman yang akan ditampilkan kepada pelanggan publik.

**Langkah-langkah Membuat Produk Baru:**
- Pada sidebar, klik menu **Katalog Produk**.
- Klik tombol **Tambah Produk**.
- Di dalam formulir produk, lengkapi informasi berikut:
  - **Nama Produk** dan **Deskripsi Singkat**.
  - **Harga Retail** (Harga untuk pelanggan umum) dan **Harga Reseller** (Harga khusus grosir).
  - **Stok Awal** (Kuantitas fisik produk saat ini).
- Pada bagian media, Anda wajib mengunggah foto utama di kolom **Preview Baru**.
- Jika Anda memiliki foto dari berbagai sudut, klik area **Tambah Foto Galeri** untuk mengunggah lebih dari satu gambar pendukung.
- Anda juga dapat menambahkan link video YouTube/TikTok dengan menekan **Tambah Kolom Video**.
- Klik tombol **Simpan** untuk menerbitkan produk tersebut ke halaman depan.

[gambar]

### 4.6 Penjualan (Offline)
Pencatatan nota penjualan untuk pelanggan yang datang dan bertransaksi langsung di workshop/toko fisik.

**Langkah-langkah Mencatat Penjualan:**
- Klik menu **Penjualan**.
- Klik tombol **Catat Penjualan Baru**.
- Pilih nama pelanggan dari *dropdown* Kontak, lalu tentukan **Tanggal Transaksi**.
- Di bagian *Item Penjualan*, klik tombol **+ Tambah Baris** untuk memasukkan produk apa saja yang dibeli oleh pelanggan.
- Pilih nama produk dan masukkan **Kuantitas** (Jumlah beli). Subtotal akan dihitung otomatis oleh sistem.
- Di bagian bawah, pilih **Rekening Kas** tujuan (Misal: Laci Kasir Utama) sebagai tempat uang tunai/transfer diterima.
- Klik **Simpan**. Sistem akan memotong stok produk dan menambahkan saldo rekening kas secara otomatis.

[gambar]

### 4.7 Pesanan Online
Mengelola dan memproses semua orderan masuk yang dibuat oleh pelanggan/reseller melalui website e-commerce.

**Langkah-langkah Memproses Pesanan Masuk:**
- Klik menu **Pesanan Online**. Pesanan baru akan berstatus **Pending**.
- Klik ikon mata (Detail) pada pesanan yang ingin diproses.
- Cek bagian detail pembayaran. Jika pembeli telah mengunggah bukti transfer, verifikasi mutasi rekening bank Anda.
- Jika pembayaran valid, ubah **Status Pembayaran (Payment Status)** menjadi **Lunas** (atau **DP Terbayar** untuk reseller). Tekan **Simpan Pembayaran**.
- Selanjutnya, ubah **Status Pesanan (Order Status)** menjadi **Diproses** saat Anda mulai mengemas pesanan.
- Saat paket sudah diserahkan ke jasa ekspedisi, ubah lagi statusnya menjadi **Dikirim**. Kotak *input* **Nomor Resi** akan muncul, masukkan nomor resi dari kurir agar pelanggan bisa melacak paketnya.

[gambar]

### 4.8 Produksi
Mencatat proses perakitan, konversi, atau pengolahan bahan baku menjadi produk anyaman siap jual. Modul ini penting untuk HPP (Harga Pokok Produksi).

**Langkah-langkah Mencatat Produksi:**
- Klik menu **Produksi**.
- Klik tombol **Proses Produksi Baru**.
- Di bagian *Material Terpakai*, klik **+ Tambah Bahan** lalu pilih bahan mentah apa saja dan berapa jumlah yang dikonsumsi (Stok bahan otomatis berkurang).
- Di bagian *Hasil Produksi*, klik **+ Tambah Produk** lalu tentukan produk jadi apa yang dihasilkan dan berapa unit jadinya (Stok produk otomatis bertambah).
- Pada bagian bawah, masukkan nominal **Biaya Tenaga Kerja** (Upah pengrajin) dan **Biaya Overhead** (Listrik, lem, kemasan tambahan).
- Klik **Simpan**. Sistem akan menjurnal HPP secara otomatis ke Buku Kas.

[gambar]

### 4.9 Stok (Penyesuaian Stok Gudang)
Digunakan untuk mencocokkan jumlah stok fisik di gudang dengan catatan di aplikasi (Stock Opname) jika terjadi selisih, barang rusak, atau hilang.

**Langkah-langkah Menyesuaikan Stok:**
- Klik menu **Stok**.
- Klik tombol **Buat Penyesuaian**.
- Pada formulir penyesuaian, pilih apakah Anda ingin menyesuaikan **Bahan Baku** atau **Produk Jadi**.
- Pilih nama barang secara spesifik.
- Pada kolom *Kuantitas Penyesuaian*:
  - Masukkan angka positif (contoh: `5`) jika stok fisik di gudang **lebih banyak** dari di sistem.
  - Masukkan angka negatif (contoh: `-3`) jika ada barang **rusak/hilang** sehingga stok fisik berkurang.
- Wajib memberikan alasan pada kolom *Keterangan*.
- Klik **Simpan**. Riwayat penyesuaian akan tercatat di log (*audit trail*).

[gambar]

### 4.10 Buku Kontak
Mengelola basis data pihak eksternal agar mudah dipilih saat melakukan transaksi.

**Langkah-langkah Menambah Kontak:**
- Klik menu **Buku Kontak**, lalu klik **Tambah Kontak**.
- Pada formulir, masukkan **Nama Lengkap** kontak.
- Masukkan **Nomor Handphone/WhatsApp** dan **Alamat Lengkap**.
- Pilih Jenis/Tipe Kontak (Pelanggan, Supplier, Pengrajin, Reseller).
- Klik **Simpan**.

[gambar]

### 4.11 Buku Kas (Keuangan)
Mengelola pembukuan jurnal keuangan arus kas (*Cash Flow*) Rima Craft.

**Langkah-langkah Mengelola Rekening & Jurnal:**
- Klik menu **Buku Kas**. Halaman ini memuat daftar semua rekening dan riwayat transaksinya.
- **Membuat Rekening Baru:** Klik tombol **Tambah Rekening Baru** (biasanya untuk membuat partisi uang seperti "Kas Bank BCA", "Kas Kecil Tunai"). Masukkan nama dan saldo awalnya lalu **Simpan**.
- **Mencatat Kas Masuk / Keluar Manual:**
  - Klik tombol **Catat Transaksi Kas**.
  - Pilih Jenis (Pemasukan / Pengeluaran).
  - Pilih Rekening yang terlibat.
  - Masukkan Nominal uang dan Keterangan (Contoh: "Beli Alat Tulis", "Bayar Listrik").
  - Klik **Simpan**. Saldo akhir rekening tersebut akan ter-*update* otomatis.

[gambar]

### 4.12 Pembelian
Menu untuk mencatat pengadaan (kulakan) bahan baku dari pemasok (Supplier).

**Langkah-langkah Mencatat Pembelian:**
- Klik menu **Pembelian**, lalu klik **Catat Pembelian Baru**.
- Pilih nama *Supplier* pemasok dan tentukan **Tanggal Pembelian**.
- Di bagian detail, klik **+ Tambah Baris**.
- Pilih nama bahan baku yang baru saja dibeli, tentukan jumlah (kuantitas) dan harga belinya per satuan.
- Pilih metode pembayaran menggunakan Rekening Kas mana.
- Klik **Simpan**. Sistem otomatis memotong saldo Rekening Kas yang dipilih dan menambahkan stok Bahan Baku di gudang Anda.

[gambar]

### 4.13 Galeri & Pengaturan Web
Digunakan untuk mengelola etalase visual (*Landing Page*) agar selalu menarik dan *up-to-date*.

**Langkah-langkah Mengatur Halaman Depan:**
- **Mengelola Gambar Slide (Carousel):**
  - Klik menu **Galeri**.
  - Klik **Tambah Foto** dan unggah *banner* promosi terbaru. Tentukan urutan tampilannya. Foto ini akan meluncur (*slide*) secara bergantian di beranda pembeli.
- **Konfigurasi Identitas Toko:**
  - Klik menu **Pengaturan Web**. Di sini Anda akan melihat beberapa *Tab* menu.
  - Pada *Tab Umum*, Anda bisa mengubah Nama Toko, Nomor WA untuk konfirmasi pemesanan, dan *link* Google Maps.
  - Pada *Tab SEO & Teks*, Anda dapat memperbarui judul tulisan besar (*Hero Banner*) dan deskripsi kebijakan toko (Syarat & Ketentuan).
  - Pastikan selalu mengklik tombol **Perbarui** (Simpan) di bagian bawah setelah melakukan perubahan pada masing-masing *Tab*.

[gambar]

---

## BAB V: MANAJEMEN AKSES & VERIFIKASI RESELLER (SUPER ADMIN)

Fitur spesifik yang dirancang khusus untuk administrator sistem guna mengamankan arus wewenang operasional.

**1. Manajemen Pengguna (Users & Roles):**
- Untuk mendelegasikan tugas ke staf baru, Super Admin dapat mengakses pengaturan pengguna rahasia.
- Klik opsi pengguna, lalu pilih **Tambah User Baru**.
- Isi profil staf (Nama, Email, Password).
- Pada bagian *Role* (Peran), pilih jabatan spesifik staf tersebut (misalnya: *Operator Gudang* hanya bisa akses menu bahan baku dan produksi; *Kasir* hanya bisa akses menu penjualan).
- Klik **Perbarui / Simpan** untuk mengaktifkan akun tersebut.

**2. Langkah Verifikasi Kemitraan Reseller:**
- Setiap kali ada calon agen yang mendaftar via web, akun mereka akan masuk dengan status *Pending*.
- Terdapat dua cara memverifikasinya:
  - **Persetujuan Cepat via Dashboard:** Admin dapat memantau kotak notifikasi pesanan dan *user* baru langsung dari halaman utama **Dashboard**. Klik tombol **Approve (Setujui)** pada baris nama pendaftar reseller tersebut.
  - **Melalui Tabel Pengguna:** Masuk ke daftar pengguna, *filter* pendaftar berstatus *Pending*, cek kelengkapan data usahanya, lalu klik tombol ubah status menjadi *Verified*.
- Setelah *Verified*, reseller tersebut resmi dapat melakukan *Login*, menikmati Harga Grosir Otomatis, dan mengaktifkan mode cicilan (DP).

---

## BAB VI: PENUTUP

Panduan penggunaan aplikasi **Rima Craft** ini dirancang untuk memberikan kemudahan bagi setiap peran pengguna, mulai dari pembeli retail hingga pemilik usaha (owner) dan staf lapangan. Melalui integrasi e-commerce yang solid dengan HTMX, manajemen gudang bahan baku, kalkulasi biaya produksi, pelaporan kas ledger keuangan, serta sistem keagenan B2B reseller, operasional bisnis Rima Craft dapat berjalan dengan lebih terstruktur, efisien, dan transparan.

Apabila Anda mengalami kendala teknis dalam pengoperasian aplikasi, silakan menghubungi Tim Administrator Rima Craft atau Developer Sistem guna penanganan lebih lanjut. Selamat bekerja dan mari kembangkan kerajinan anyaman nusantara ke tingkat dunia!
