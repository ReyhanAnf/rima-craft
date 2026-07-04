# Checkpoint: CP-024 — Portal Autentikasi Customer & Partner

## Deskripsi

Menambahkan halaman login khusus pengguna non-admin (Customer & Partner) dengan tata letak visual *split-pane* (formulir di satu sisi dan ilustrasi gambar di sisi lain), serta mengintegrasikan menu masuk, daftar, dasbor, dan keluar (logout) pada *navbar* dan *footer* website utama, lengkap dengan panel dashboard, profil, dan navigasi seluler adaptif yang terintegrasi dengan layout admin (menu terbatas).

## Perubahan Utama

### 1. Backend Controller & Middleware
- **`AuthController::create()`**: Diperbarui untuk merender langsung component `Auth/Login` dengan data `isAdmin = false`. Sebelumnya, metode ini hanya mengalihkan (redirect) user ke `/admin/login`.
- **`AuthController::createAdminLogin()`**: Diperbarui untuk mengirimkan prop `isAdmin = true` saat merender component `Auth/Login`.
- **`RoleMiddleware.php` & `PermissionMiddleware.php`**: Menambahkan `$user->unsetRelation('roles')` sebelum melakukan check role/permission untuk menghindari *cache stale relationship* pada Eloquent segera setelah registrasi selesai.

### 2. Frontend Auth — `Login.vue`
- Mendukung prop `isAdmin` (boolean, default: `false`) untuk membedakan mode login admin vs non-admin.
- **Layout Split-Pane**:
  - Pada layar besar (desktop `lg` ke atas), layar dibagi menjadi dua sisi: form login dan image visual ilustrasi.
  - Untuk **Customer**: Gambar berada di kiri, Form berada di kanan (`order-1` pada image, `order-2` pada form).
  - Untuk **Admin**: Form berada di kiri, Gambar berada di kanan (`order-1` pada form, `order-2` pada image).
  - Pada layar mobile (di bawah `lg`), panel gambar disembunyikan otomatis untuk kenyamanan satu tangan dan target sentuh yang optimal.
- **Links Pendaftaran**: Menampilkan opsi pendaftaran cepat jika diakses dari portal non-admin ("Daftar Customer" dan "Daftar Partner").
- **Submit Route Dinamis**: Mengarahkan form submit secara tepat ke `admin.login.store` jika admin, atau `login.store` jika non-admin.

### 3. Layout Admin Terpadu — `AdminLayout.vue`
- Menyediakan sidebar desktop adaptif untuk peran `customer` dan `partner` yang secara otomatis membatasi menu navigasi sesuai hak akses (Dashboard, Pesanan Saya, Profil).
- **Mobile Bottom Navigation Bar Dinamis**:
  - Mengubah bottom bar statis admin (Dashboard, Penjualan, Buku Kas) menjadi dinamis berdasarkan *computed property* `mobileBottomItems`.
  - Untuk **Customer/Partner**: Tampil dengan navigasi Dashboard, Pesanan, Profil, dan Menu Drawer.
  - Untuk **Admin/Operator/Owner**: Tetap dengan navigasi Dashboard, Penjualan, Buku Kas, dan Menu Drawer.
- Memperbaiki pengalihan tautan logo di bagian kiri atas agar adaptif sesuai peran masing-masing.

### 4. Frontend Navigation — `Navbar.vue`
- Menampilkan menu dinamis berdasarkan status autentikasi:
  - **Sudah Login**: Menampilkan tombol "Pesanan Saya", "Portal Saya" (mengarahkan otomatis ke dashboard masing-masing berdasarkan peran user seperti `customer.dashboard` atau `partner.dashboard`), dan tombol "Keluar" (Logout).
  - **Belum Login**: Menampilkan tombol "Masuk".
- Mendukung tampilan mobile: Jika di layar mobile, menu digantikan dengan ikon minimalis yang elegan berdampingan dengan keranjang belanja.

### 5. Layout Publik — `PublicLayout.vue`
- Menghubungkan link "Login" di bagian footer secara dinamis. Jika user sudah login, link akan berubah menjadi "Portal Saya" dan mengarahkan mereka ke dasbor masing-masing.

---

## File yang Dibuat/Dimodifikasi

**Baru (Assets Gambar):**
- `public/assets/customer-login-side.png` (Gambar ilustrasi kerajinan)
- `public/assets/admin-login-side.png` (Gambar ilustrasi meja kerja pengrajin & manajemen)

**Dimodifikasi:**
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Middleware/RoleMiddleware.php`
- `app/Http/Middleware/PermissionMiddleware.php`
- `resources/js/pages/Auth/Login.vue`
- `resources/js/layouts/AdminLayout.vue`
- `resources/js/components/Navbar.vue`
- `resources/js/layouts/PublicLayout.vue`
