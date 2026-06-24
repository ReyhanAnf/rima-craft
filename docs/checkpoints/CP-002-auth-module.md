# CP-002: Auth Module

**Status:** ✅ Completed **Target:** Membangun sistem otentikasi login dan manajemen role/permission dasar.

## Task List:

*   [x] Buat migration & Model untuk `roles`, `permissions`, `role_user`, `permission_role` (Spatie-like Custom).
*   [x] Konfigurasi relasi Many-to-Many di model `User`, `Role`, `Permission`.
*   [x] Buat `AuthController` dan `LoginRequest` (Fat Model/Request, Thin Controller).
*   [x] Buat view `resources/views/auth/login.blade.php` (Mobile-first, HTMX hx-post, styling Earth Tones).
*   [x] Siapkan route `/login`, `/logout` dan middleware proteksi dasar.
