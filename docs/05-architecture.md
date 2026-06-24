# System Architecture

## Pola Desain (Design Pattern)

Kita menggunakan pola arsitektur berlapis untuk menjaga agar proyek tetap _scalable_ dan mudah di-_maintain_ dalam jangka panjang. Konsep utamanya adalah **"Fat Model, Thin Controller"** dengan layer tambahan:

`Controller → Action → DTO → Service → Repository`

1.  **Controller:** Sangat tipis. Hanya bertugas menerima _request_, memvalidasi (via `FormRequest`), memanggil _Action_ atau _Service_, dan mengembalikan _Response_ (HTML fragment untuk HTMX atau View penuh).
2.  **DTO (Data Transfer Object):** Membawa data antar layer. Menghindari _passing array_ yang tidak jelas strukturnya.
3.  **Action / Service:** Tempat _Business Logic_ berada (Contoh: `ProcessProductionAction`, `RecordSaleAction`). Wajib digunakan jika logika melibatkan lebih dari 1 tabel atau kalkulasi kompleks.
4.  **Repository (Opsional):** Menangani _query database_ yang kompleks (seperti filter laporan keuangan), agar Model tidak dipenuhi dengan _query scope_ yang terlalu panjang.
5.  **Model:** Mendefinisikan relasi, _mutators_, dan _accessors_.

## Pendekatan API & Client-Server

*   **HTMX:** Digunakan sebagai penggerak utama navigasi dan form. _Error handling_ dilakukan secara global (menangkap `htmx:responseError`).
*   **Fetch API (Alpine):** Dilarang menggunakan `fetch` mentah. Wajib menggunakan global helper `window.apiFetch()` agar HTTP _error_ (4xx, 5xx) selalu masuk ke blok `catch` dan ditangani dengan baik.