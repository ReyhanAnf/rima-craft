# Checkpoint: CP-021-fix-orders-detail-drawer

## Deskripsi
Memperbaiki tombol **Detail** pada halaman daftar pesanan online yang tidak merespons karena bundle JavaScript admin belum mengaktifkan Alpine.js dan HTMX.

## Perubahan Utama
1. **JavaScript Entry (`resources/js/app.js`):**
   - Menambahkan import dan inisialisasi `alpinejs`.
   - Menambahkan import `htmx.org` dan mengeksposnya ke `window.htmx`.
   - Mendaftarkan ulang `Alpine.store('cart')` untuk kompatibilitas halaman Blade checkout yang masih menggunakan `$store.cart`.
   - Menjalankan `Alpine.start()` agar event seperti `@click="$dispatch('open-drawer')"` pada tombol Detail dapat membuka drawer.
   - Membatasi inisialisasi Inertia/Vue hanya saat root Inertia (`#app[data-page]`) tersedia, sehingga halaman Blade admin tidak gagal saat memuat bundle.

## Dampak
- Tombol Detail pada `resources/views/orders/orders-list.blade.php` kembali memproses `hx-get` ke `orders.show`.
- Drawer global pada `resources/views/components/layouts/app.blade.php` dapat terbuka lewat event Alpine `open-drawer`.
- Form dan interaksi HTMX lain di halaman admin kembali aktif.

## Verifikasi
- `npm run build` sukses.
- Build menghasilkan warning dari dependency `htmx.org` terkait direct `eval`, tetapi tidak menghentikan build.
