# \[cover\]

## BAB I: PENDAHULUAN

## Aplikasi Bank Sampah adalah sebuah _platform_ digital terintegrasi yang dirancang untuk mempermudah pengelolaan sampah dan limbah, memungkinkan pengguna untuk dengan mudah melakukan setoran sampah, mengelola data nasabah, dan memantau seluruh transaksi keuangan yang terkait dengan aktivitas bank sampah. Aplikasi ini dibangun menggunakan teknologi _web modern_, memanfaatkan _framework_ Laravel untuk menjamin sistem _backend_ yang kuat dan aman, serta Filament untuk membangun _antarmuka admin_ yang intuitif dan ramah pengguna

## Sistem ini didukung oleh pembagian peran pengguna yang jelas, meliputi Admin, Unit, dan Nasabah Unit. Panel Admin berfungsi sebagai pusat kendali utama , dilengkapi dengan fitur Manajemen Akses yang memungkinkan Admin mengelola Pengguna Admin dan menciptakan Peran & Hak Akses (_roles & permissions_) yang menentukan fitur apa saja yang dapat diakses oleh setiap pengguna. Selain itu, Admin mengelola Unit-Unit Bank Sampah, yang merupakan entitas operasional di lapangan yang dapat memiliki nasabah sendiri

## Inti operasional didukung oleh Manajemen Master yang mengelola data utama seperti Kategori Limbah , Limbah (mencakup jenis, satuan, dan harga beli per satuan) , dan Produk Hasil Produksi (produk hasil pengolahan limbah). Bagian Stok berfungsi secara otomatis, di mana stok limbah bertambah setelah setoran diverifikasi atau produksi dicatat, dan berkurang ketika terjadi penjualan atau limbah digunakan sebagai bahan baku produksi

## Bagian Transaksi adalah pusat akuntansi yang menangani siklus limbah dan keuangan secara menyeluruh. Admin mengelola Setoran dengan memverifikasi status "Pending" untuk mengonfirmasi kuantitas aktual, menyesuaikan jika ada perbedaan, dan memicu penambahan saldo nasabah serta stok limbah setelah status berubah menjadi "Verified". Admin juga memproses Penarikan Saldo yang diajukan nasabah dengan opsi untuk Menyetujui atau Menolak. Siklus limbah dicatat secara lengkap, meliputi Pembelian dari _supplier_ eksternal , Penjualan limbah atau produk , Produksi (mencatat konversi limbah, mengurangi stok bahan baku dan menambah stok produk hasil produksi) , dan pencatatan Residu (limbah sisa yang tidak dapat diolah). Seluruh pengeluaran operasional dicatat dalam fitur Biaya Operasional di bawah menu Akuntansi

## Untuk pengguna lapangan, Dashboard Unit menyediakan ringkasan Saldo Saat Ini, Total Setoran, dan Total Penarikan, dilengkapi dengan grafik tren setoran 6 bulan terakhir. Unit dapat melakukan Aksi Cepat untuk mengajukan setoran limbah, mendaftarkan nasabah baru, dan mengajukan penarikan saldo. Terakhir, Dashboard Nasabah Unit didesain untuk transparansi, menampilkan informasi keuangan unit induk mereka (Saldo, Total Setoran, Total Penarikan) , serta memungkinkan Nasabah melihat daftar nasabah lain di unit yang sama melalui menu _Customer Units_

## BAB II: AUTENTIKASI

### 2.2 Login

Setelah akun Anda aktif, Anda dapat masuk ke dalam aplikasi dengan langkah-langkah berikut:

- Buka halaman login pada aplikasi Anda.
- Masukkan alamat email dan kata sandi yang telah Anda daftarkan.
- Klik tombol "Login".
- Jika data yang Anda masukkan benar, Anda akan diarahkan ke halaman dashboard sesuai dengan peran Anda.

\[gambar\]

### 2.3 Lupa Kata Sandi

Jika Anda lupa kata sandi, Anda dapat mengikuti langkah-langkah berikut untuk meresetnya:

- Buka halaman login dan klik tautan "Forgot your password?".
- Masukkan alamat email yang terdaftar pada akun Anda.
- Klik tombol "Email Password Reset Link".
- Sistem akan mengirimkan email yang berisi tautan untuk mereset kata sandi Anda. Buka email tersebut dan klik tautan yang tersedia.
- Anda akan diarahkan ke halaman untuk membuat kata sandi baru. Masukkan kata sandi baru Anda dan konfirmasikan.
- Setelah itu, Anda dapat login kembali dengan menggunakan kata sandi yang baru.
- Tentu, saya akan membuat gambar yang menunjukkan fokus pada opsi "Lupa Kata Sandi" pada halaman login yang Anda berikan sebelumnya.
- Berikut adalah gambar halaman login Anda, dengan penambahan tautan "Lupa Kata Sandi?" yang terlihat jelas, serta langkah selanjutnya yang akan muncul saat mengkliknya:

\[gambar\]

## BAB III: PANEL ADMIN

Panel admin adalah pusat kendali dari aplikasi Bank Sampah. Di sini, Anda dapat mengelola semua aspek dari aplikasi, mulai dari pengguna, data master, hingga transaksi.

### 3.1 Manajemen Akses

Bagian ini digunakan untuk mengelola siapa saja yang dapat mengakses panel admin dan apa saja yang dapat mereka lakukan.

#### 3.1.1 Pengguna Admin

Fitur ini memungkinkan Anda untuk mengelola pengguna yang memiliki akses ke panel admin. Anda dapat menambah, mengubah, dan menghapus pengguna admin.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Manajemen Akses" > "Pengguna Admin".

\[gambar\]

**Menambah Pengguna Admin Baru:**

- Di halaman daftar pengguna admin, klik tombol "Buat Pengguna Admin".
- Isi formulir dengan data pengguna baru, termasuk nama, email, dan password.
- Pilih peran (role) untuk pengguna tersebut. Peran akan menentukan hak akses pengguna.
- Klik tombol "Buat".

\[gambar\]

**Mengubah Data Pengguna Admin:**

- Di halaman daftar pengguna admin, cari pengguna yang ingin Anda ubah datanya.
- Klik tombol "Ubah" pada baris pengguna tersebut.
- Ubah data yang diinginkan pada formulir.
- Klik tombol "Simpan".

\[gambar\]

**Menghapus Pengguna Admin:**

- Di halaman daftar pengguna admin, cari pengguna yang ingin Anda hapus.
- Klik tombol "Hapus" pada baris pengguna tersebut.  
   \[gambar\]
- Konfirmasikan penghapusan.

#### 3.1.2 Peran & Hak Akses

Fitur ini memungkinkan Anda untuk membuat dan mengelola peran (roles) dan hak akses (permissions). Setiap peran memiliki serangkaian hak akses yang menentukan fitur apa saja yang dapat diakses oleh pengguna dengan peran tersebut.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Manajemen Akses" > "Peran & Hak Akses".

\[gambar\]

**Menambah Peran Baru:**

- Di halaman daftar peran, klik tombol "Buat Peran dan Hak Akses".
- Masukkan nama peran (misalnya: "Operator", "Bendahara").
- Pilih hak akses yang ingin Anda berikan untuk peran tersebut dari daftar yang tersedia.
- Klik tombol "Buat".

\[gambar\]

**Mengubah Hak Akses Peran:**

- Di halaman daftar peran, cari peran yang ingin Anda ubah.
- Klik tombol "Ubah" pada baris peran tersebut.
- Ubah hak akses dengan mencentang atau menghapus centang pada daftar hak akses.
- Klik tombol "Simpan".

### 3.2 Manajemen Pengguna

Bagian ini digunakan untuk mengelola data pengguna yang terdaftar sebagai nasabah unit.

#### 3.2.1 Unit

Fitur ini memungkinkan Anda untuk mengelola data unit-unit bank sampah yang terdaftar. Unit adalah entitas yang dapat memiliki nasabah sendiri.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Manajemen Pengguna" > "Unit".

\[gambar\]

**Menambah Unit Baru:**

- Di halaman daftar unit, klik tombol "Buat Unit".
- Isi formulir dengan data unit baru, termasuk nama unit, email, dan password.
- Klik tombol "Buat".

\[gambar\]

**Melihat Detail Unit:**

Anda dapat melihat detail dari setiap unit, termasuk saldo saat ini dan tanggal bergabung.

**Mengubah Data Unit:**

- Di halaman daftar unit, cari unit yang ingin Anda ubah datanya.
- Klik tombol "Ubah" pada baris unit tersebut.
- Ubah data yang diinginkan pada formulir.
- Klik tombol "Simpan".

**Menghapus Unit:**

- Di halaman daftar unit, cari unit yang ingin Anda hapus.
- Klik tombol "Hapus" pada baris unit tersebut.
- Konfirmasikan penghapusan.

### 3.3 Manajemen Master

Bagian ini digunakan untuk mengelola data-data utama yang akan digunakan di seluruh aplikasi.

#### 3.3.1 Kategori Limbah

Fitur ini digunakan untuk mengelola kategori limbah. Kategori ini akan digunakan untuk mengelompokkan jenis-jenis limbah.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Manajemen Master" > "Kategori Limbah".

\[gambar\]

**Menambah Kategori Limbah Baru:**

- Di halaman daftar kategori limbah, klik tombol "Buat Kategori Limbah".
- Masukkan nama kategori dan deskripsi (opsional).
- Klik tombol "Buat".

\[gambar\]

#### 3.3.2 Limbah

Fitur ini digunakan untuk mengelola jenis-jenis limbah yang dapat diterima oleh bank sampah.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Manajemen Master" > "Limbah".

\[gambar\]

**Menambah Limbah Baru:**

- Di halaman daftar limbah, klik tombol "Buat Limbah".
- Pilih kategori limbah.
- Masukkan nama limbah, satuan (kg, liter, pcs), dan harga beli per satuan.
- Klik tombol "Buat".

\[gambar\]

**Penting:** Anda tidak dapat menghapus jenis limbah yang sudah pernah digunakan dalam transaksi setoran.

#### 3.3.3 Produk Hasil Produksi

Fitur ini digunakan untuk mengelola produk-produk yang dihasilkan dari proses pengolahan limbah.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Manajemen Master" > "Produk Hasil Produksi".

\[gambar\]

**Menambah Produk Baru:**

- Di halaman daftar produk, klik tombol "Buat Produk Hasil Produksi".
- Masukkan nama produk, harga jual, dan satuan.
- Kode produk akan dibuat secara otomatis.
- Klik tombol "Buat".

\[gambar\]

#### 3.3.4 Stok

Fitur ini digunakan untuk melihat jumlah stok dari setiap jenis limbah dan produk hasil produksi.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Manajemen Master" > "Stok".

\[gambar\]

Stok akan bertambah secara otomatis ketika ada setoran limbah yang diverifikasi atau ketika ada produksi yang dicatat. Stok juga akan berkurang secara otomatis ketika ada penjualan atau ketika limbah digunakan sebagai bahan baku produksi.

### 3.4 Transaksi

Bagian ini adalah inti dari operasional bank sampah, di mana semua transaksi dicatat dan dikelola.

#### 3.4.1 Setoran

Fitur ini digunakan untuk mengelola setoran sampah dari nasabah. Admin dapat memverifikasi setoran yang masuk.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Transaksi" > "Setoran".

\[gambar\]

**Verifikasi Setoran:**

Setoran yang dibuat oleh nasabah akan masuk dengan status "Pending". Admin dengan peran "Operator" atau "Director" perlu melakukan verifikasi untuk mengonfirmasi kuantitas dan harga limbah yang disetor.

- Di halaman daftar setoran, cari setoran dengan status "Pending".
- Klik tombol "Verifikasi" pada baris setoran tersebut.
- Akan muncul formulir verifikasi yang menampilkan rincian item yang disetor oleh nasabah.
- Periksa kembali "Kuantitas Aktual (Setelah Verifikasi)" dan sesuaikan jika ada perbedaan.
- Harga final akan diambil dari data master limbah.
- Subtotal dan total nilai verifikasi akan dihitung ulang secara otomatis.
- Setelah semua item diverifikasi, klik tombol "Verifikasi/Kirim".

\[gambar\]

Setelah diverifikasi, status setoran akan berubah menjadi "Verified", saldo nasabah akan bertambah, dan stok limbah akan bertambah.

#### 3.4.2 Penarikan Saldo

Fitur ini digunakan untuk mengelola permintaan penarikan saldo oleh nasabah.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Transaksi" > "Penarikan Saldo".

\[gambar\]

**Menyetujui atau Menolak Penarikan:**

Permintaan penarikan dari nasabah akan masuk dengan status "Pending".

- Di halaman daftar penarikan, cari permintaan yang ingin diproses.
- Untuk menyetujui, klik tombol "Setujui". Pastikan dana sudah ditransfer ke rekening nasabah sebelum menyetujui.
- Untuk menolak, klik tombol "Tolak".
- Status permintaan akan berubah sesuai dengan tindakan yang Anda ambil.

\[gambar\]

#### 3.4.3 Pembelian

Fitur ini digunakan untuk mencatat pembelian limbah dari supplier eksternal.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Transaksi" > "Pembelian".

\[gambar\]

**Menambah Data Pembelian:**

- Di halaman daftar pembelian, klik tombol "Buat Pembelian".
- Isi nama supplier dan tanggal pembelian.
- Pada bagian "Rincian Pembelian", klik "Add Buat item" untuk menambahkan item limbah yang dibeli.
- Pilih jenis limbah, masukkan kuantitas, dan harga beli satuan. Subtotal akan dihitung otomatis.
- Ulangi langkah 4 untuk semua item yang dibeli.
- Total keseluruhan akan dihitung otomatis.
- Klik tombol "Buat".

\[gambar\]

#### 3.4.4 Penjualan

Fitur ini digunakan untuk mencatat penjualan limbah atau produk hasil produksi.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Transaksi" > "Penjualan".

\[gambar\]

**Menambah Data Penjualan:**

- Di halaman daftar penjualan, klik tombol "Buat Penjualan".
- Isi nama pembeli dan tanggal penjualan.
- Pada bagian "Rincian Item", pilih item yang akan dijual (bisa limbah atau produk).
- Masukkan kuantitas dan harga jual satuan. Subtotal akan dihitung otomatis.
- Klik "Tambah Item" untuk menambahkan item lain.
- Total keseluruhan akan dihitung otomatis.
- Klik tombol "Buat".

\[gambar\]

#### 3.4.5 Produksi

Fitur ini digunakan untuk mencatat proses produksi yang mengubah limbah menjadi produk baru.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Transaksi" > "Produksi".

\[gambar\]

**Menambah Data Produksi:**

- Di halaman daftar produksi, klik tombol "Buat Produksi".
- Pada bagian "Informasi Hasil Produksi", pilih produk yang dihasilkan, masukkan jumlahnya, tanggal produksi, dan siapa yang mencatat.
- Pada bagian "Material yang Digunakan", pilih jenis limbah yang digunakan sebagai bahan baku dan masukkan kuantitasnya.
- Klik tombol "Buat".

\[gambar\]

Setelah data produksi disimpan, stok bahan baku (limbah) akan berkurang dan stok produk hasil produksi akan bertambah.

#### 3.4.6 Residu

Fitur ini digunakan untuk mencatat limbah sisa (residu) yang tidak dapat diolah lebih lanjut dan perlu dibuang.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Transaksi" > "Residu".

\[gambar\]

**Menambah Data Residu:**

- Di halaman daftar residu, klik tombol "Buat Residu".
- Masukkan nama residu dan beratnya dalam kilogram (Kg).
- Klik tombol "Buat".

\[gambar\]

### 3.5 Akuntansi

Bagian ini digunakan untuk mengelola aspek keuangan dari operasional bank sampah.

#### 3.5.1 Biaya Operasional

Fitur ini digunakan untuk mencatat semua biaya yang dikeluarkan untuk operasional bank sampah, seperti biaya listrik, air, gaji, dan lain-lain.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Akuntansi" > "Biaya Operasional".

\[gambar\]

**Menambah Biaya Operasional:**

- Di halaman daftar biaya operasional, klik tombol "Buat Biaya Operasional".
- Isi nama biaya, pilih kategori (Biaya Tetap, Biaya Variabel, Lainnya), masukkan jumlah, dan tanggal biaya.
- Anda juga dapat menambahkan deskripsi untuk memperjelas biaya tersebut.
- Klik tombol "Buat".

\[gambar\]

### 3.6 Manajemen Konten

Bagian ini digunakan untuk mengelola konten yang akan ditampilkan kepada nasabah.

#### 3.6.1 Pengumuman

Fitur ini digunakan untuk membuat dan mengelola pengumuman yang akan ditampilkan di dashboard nasabah.

**Cara Mengakses:**

- Login ke panel admin.
- Pada menu navigasi di sebelah kiri, klik "Manajemen Konten" > "Pengumuman".

\[gambar\]

**Membuat Pengumuman Baru:**

- Di halaman daftar pengumuman, klik tombol "Buat Pengumuman".
- Masukkan judul dan isi pengumuman.
- Aktifkan toggle "Tampilkan ke Nasabah" jika Anda ingin pengumuman ini langsung terlihat oleh nasabah.
- Klik tombol "Buat".

\[gambar\]

## BAB IV: DASHBOARD UNIT

Setelah login sebagai pengguna dengan peran "Unit", Anda akan disambut dengan dashboard yang informatif dan mudah digunakan.

### Login

Setelah akun Anda aktif, Anda dapat masuk ke dalam aplikasi dengan langkah-langkah berikut:

- Buka halaman login pada link <https://bsiberseka.com/login>.
- Masukkan alamat email dan kata sandi yang telah Anda daftarkan.
- Klik tombol "Login".
- Jika data yang Anda masukkan benar, Anda akan diarahkan ke halaman dashboard sesuai dengan peran Anda.

\[gambar\]

### 4.1 Ringkasan Akun

Di bagian atas dashboard, Anda akan melihat ringkasan kondisi keuangan akun Anda, yang meliputi:

- **Saldo Saat Ini:** Jumlah uang yang Anda miliki di akun Anda.
- **Total Setoran:** Akumulasi total dari semua setoran yang telah diverifikasi.
- **Total Penarikan:** Akumulasi total dari semua penarikan yang telah disetujui.

\[gambar\]

### 4.2 Grafik Setoran

Dashboard juga menampilkan grafik batang yang menunjukkan tren setoran Anda selama 6 bulan terakhir. Ini membantu Anda untuk memvisualisasikan pendapatan Anda dari waktu ke waktu.

\[gambar\]

### 4.3 Aksi Cepat

Di sisi kanan, terdapat beberapa tombol untuk melakukan aksi cepat:

- **Setor Limbah:** Membawa Anda ke halaman untuk membuat pengajuan setoran baru.
- **Buat Akun Nasabah:** Membawa Anda ke halaman untuk mendaftarkan nasabah baru di bawah unit Anda.
- **Tarik Saldo:** Membawa Anda ke halaman untuk mengajukan permintaan penarikan saldo.

\[gambar\]

### 4.4 Pengumuman

Setiap pengumuman baru yang dibuat oleh admin akan ditampilkan di sini.

### 4.5 Aktivitas Terbaru

Di bagian bawah dashboard, Anda dapat melihat daftar 5 transaksi terakhir (setoran dan penarikan) yang Anda lakukan, beserta statusnya.

\[gambar\]

### 4.6 Membuat Setoran Limbah

Sebagai unit, Anda dapat mengajukan setoran limbah yang telah Anda kumpulkan.

- Dari dashboard, klik tombol "Setor Limbah" atau navigasi ke menu "Setoran".
- Anda akan melihat formulir setoran. Klik tombol "+ Tambah Item Limbah" untuk mulai menambahkan item.
- Pilih "Jenis Limbah" dari daftar yang tersedia. Harga per unit akan muncul secara otomatis.
- Masukkan "Kuantitas" limbah yang Anda setor. Estimasi subtotal akan dihitung secara otomatis.
- Ulangi langkah 3 dan 4 untuk setiap jenis limbah yang berbeda.
- Total estimasi nilai dari setoran Anda akan ditampilkan di bagian bawah.
- Setelah selesai, klik tombol "Ajukan Setoran".

\[gambar\]

Setoran Anda akan masuk ke sistem dengan status "Pending" dan perlu diverifikasi oleh admin sebelum saldo Anda bertambah.

### 4.7 Riwayat Transaksi

Anda dapat melihat seluruh riwayat setoran dan penarikan Anda di halaman "History".

- Klik menu "History" pada navigasi.
- Anda akan melihat dua tabel: "Riwayat Setoran Limbah" dan "Riwayat Penarikan Saldo".
- Anda dapat melihat detail setiap transaksi, termasuk kode, tanggal, jumlah, dan status.

\[gambar\]

### 4.8 Mengelola Rekening Bank

Anda perlu menambahkan rekening bank untuk dapat melakukan penarikan saldo.

- Klik menu "Bank Accounts" pada navigasi.
- Untuk menambah rekening baru, isi formulir "Tambah Rekening Bank Baru" dengan nama bank, nomor rekening, dan nama pemilik rekening.
- Klik "Simpan Rekening".
- Daftar rekening yang sudah Anda tambahkan akan muncul di bawah formulir.
- Anda dapat menghapus rekening dengan mengklik tombol "Hapus".

\[gambar\]

### 4.9 Mengajukan Penarikan Saldo

Setelah memiliki saldo yang cukup dan rekening bank yang terdaftar, Anda dapat mengajukan penarikan.

- Dari dashboard, klik tombol "Tarik Saldo" atau navigasi ke menu "Withdrawal".
- Pilih "Tujuan Penarikan" dari daftar rekening bank Anda.
- Masukkan "Jumlah Penarikan". Minimal penarikan adalah Rp 10.000.
- Klik tombol "Ajukan Penarikan".

\[gambar\]

Permintaan Anda akan masuk ke sistem dengan status "Pending" dan perlu disetujui oleh admin.

### 4.10 Mengelola Nasabah Unit

Sebagai unit, Anda dapat mendaftarkan nasabah di bawah Anda.

- Klik menu "Customer Units" pada navigasi.
- Anda akan melihat daftar nasabah yang sudah terdaftar di bawah unit Anda.
- Untuk menambah nasabah baru, klik tombol "Tambah Akun".
- Isi formulir dengan nama, email, dan password untuk nasabah baru.
- Klik "Buat Akun".

\[gambar\]

## BAB V: DASHBOARD NASABAH UNIT

Jika Anda login sebagai nasabah unit (didaftarkan oleh sebuah unit), Anda akan melihat dashboard yang lebih sederhana yang bertujuan untuk memberikan transparansi atas aktivitas unit induk Anda.

### Login

Setelah akun Anda aktif, Anda dapat masuk ke dalam aplikasi dengan langkah-langkah berikut:

- Buka halaman login pada link <https://bsiberseka.com/login>.
- Masukkan alamat email dan kata sandi yang telah Anda daftarkan.
- Klik tombol "Login".
- Jika data yang Anda masukkan benar, Anda akan diarahkan ke halaman dashboard sesuai dengan peran Anda.

\[gambar\]

### 5.1 Ringkasan Akun Unit Induk

Dashboard ini menampilkan informasi keuangan dari unit yang mendaftarkan Anda, termasuk:

- **Saldo Saat Ini:** Saldo terkini dari unit induk Anda.
- **Total Setoran:** Total nilai setoran yang telah dilakukan oleh unit induk Anda.
- **Total Penarikan:** Total nilai penarikan yang telah dilakukan oleh unit induk Anda.

\[gambar\]

### 5.2 Aktivitas Terbaru Unit Induk

Anda juga dapat melihat daftar transaksi terbaru yang dilakukan oleh unit induk Anda. Ini memberikan gambaran tentang seberapa aktif unit Anda dalam melakukan setoran dan penarikan.

### 5.3 Melihat Daftar Nasabah Lain

Melalui menu "Customer Units", Anda dapat melihat daftar nasabah lain yang juga terdaftar di bawah unit induk yang sama. Ini bertujuan untuk transparansi di dalam komunitas unit tersebut.

\[gambar\]

## BAB VI: PENUTUP

Demikianlah panduan penggunaan aplikasi Bank Sampah ini. Dengan mengikuti panduan ini, diharapkan Anda dapat memanfaatkan semua fitur yang tersedia dengan maksimal untuk mengelola sampah dan keuangan Anda dengan lebih baik. Jika Anda mengalami kesulitan atau memiliki pertanyaan lebih lanjut, jangan ragu untuk menghubungi admin.