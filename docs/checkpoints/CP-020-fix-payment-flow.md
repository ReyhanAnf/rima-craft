# Checkpoint: CP-020-fix-payment-flow

## Deskripsi
Penyelesaian perbaikan alur (flow) pembayaran pada form checkout pelanggan. Perubahan mencakup relaksasi tipe data kolom pembayaran di database, penyesuaian validasi email secara dinamis (tergantung status auth dan pilihan buat akun), serta penyempurnaan UI Blade untuk pengguna yang sudah terotentikasi.

## Perubahan Utama
1. **Database Schema:**
   - Membuat file migrasi baru [2026_06_26_071000_change_payment_method_in_orders_table.php](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/database/migrations/2026_06_26_071000_change_payment_method_in_orders_table.php).
   - Mengubah kolom `payment_method` pada tabel `orders` dari tipe `enum` menjadi `string` agar mendukung kode pembayaran dari tabel `payment_methods` (misalnya `bca`, `mandiri`, `gopay`, `qris`, dll.).
2. **Controller Logic (`OrderController`):**
   - Memodifikasi aturan validasi `customer_email` di [OrderController.php](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/app/Http/Controllers/OrderController.php#L49-L57).
   - Validasi `unique:users,email` kini hanya diterapkan saat checkout sebagai tamu dengan mencentang "Buat Akun Sekarang".
   - Jika sudah login, validasi email mengabaikan ID user bersangkutan agar tidak memicu error email duplikat.
3. **Blade Template (`checkout`):**
   - Membungkus card "Buat Akun Sekarang" di [checkout.blade.php](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/views/orders/checkout.blade.php) dengan direktif `@guest` untuk menyembunyikannya apabila pengguna sudah login.
   - Memperbaiki syntax error parser HTML di mana karakter `>` pada expression Alpine.js `this.cartItems.length > 0` memotong tag `<form>`. Karakter ini diganti dengan check truthy `this.cartItems.length` sehingga inisialisasi Alpine.js berjalan normal dan ringkasan belanja di sebelah kanan dapat dirender dengan benar.
   - Memperbaiki ReferenceError di method `init()` di mana pemanggilan `$store` dan `$watch` diubah menjadi `this.$store` dan `this.$watch` agar dapat diakses dengan benar di dalam scope JavaScript komponen.
   - Memperbaiki masalah urutan inisialisasi Alpine.js dengan memindahkan deklarasi `Alpine.store('cart', ...)` dari layout [public.blade.php](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/views/components/layouts/public.blade.php) ke dalam bundle [app.js](file:///d:/01_WORK/_Active/PKM/Rima_Craft/rima-craft/resources/js/app.js) sebelum pemanggilan `Alpine.start()`. Hal ini menjamin store sudah terdaftar saat parser memproses elemen `x-data` di form checkout. Konfigurasi dinamis Laravel dibagikan ke `window.rimacraft` global object sebelum memuat bundle asset.

## Verifikasi
- Menjalankan migrasi database sukses.
- Pengecekan sintaks PHP pada Controller sukses.
