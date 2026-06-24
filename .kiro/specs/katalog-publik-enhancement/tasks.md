# Implementation Plan: Katalog Publik Enhancement

## Overview

Implementasi fitur ini dibagi menjadi dua area utama yang saling independen namun diselesaikan secara berurutan:

1. **Settings Hardening** — Menambahkan 5 key baru ke `config/settings.php` dan menampilkan `business_hours` secara kondisional di section kontak katalog.
2. **Search & Filter Produk via HTMX** — Menambahkan route, FormRequest, method controller, dan Blade partial untuk filter produk real-time tanpa full page reload.

Semua file PHP baru wajib menggunakan `declare(strict_types=1)` dan type hinting eksplisit sesuai aturan `docs/09-development-rules.md`.

---

## Tasks

- [x] 1. Config & Infrastructure — Tambah settings key baru
  - [x] 1.1 Update `config/settings.php` dengan 5 key Contact Information
    - Tambahkan blok komentar `// Contact Information` terpisah dari `// Business Identity`
    - Definisikan 5 key: `address`, `email`, `instagram`, `gmaps_iframe`, `business_hours`
    - Setiap key menggunakan pola `env('SETTINGS_*', '')` konsisten dengan key yang ada
    - Pastikan `declare(strict_types=1)` tetap ada di baris pertama file
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.9_

  - [x] 1.2 Tambah route HTMX endpoint ke `routes/web.php`
    - Tambahkan `Route::get('/katalog/filter', [CatalogController::class, 'filter'])->name('catalog.filter');` di blok route publik (tanpa middleware auth)
    - Tambahkan import `use App\Http\Requests\Catalog\FilterProductRequest;` jika perlu (atau pastikan autoload bekerja)
    - _Requirements: 5.1, 5.2, 5.6_

- [x] 2. Backend — FormRequest dan Controller method
  - [x] 2.1 Buat `app/Http/Requests/Catalog/FilterProductRequest.php`
    - Buat direktori `app/Http/Requests/Catalog/` jika belum ada
    - Tambahkan `declare(strict_types=1)` di baris pertama
    - Namespace: `App\Http\Requests\Catalog`
    - Method `authorize()` mengembalikan `true` (endpoint publik)
    - Rules: `search` → `['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-\.,?!]*$/']`
    - Rules: `stock` → `['nullable', 'string', 'in:semua,tersedia,habis']`
    - _Requirements: 3.6, 4.6, 5.2_

  - [x] 2.2 Update `app/Http/Controllers/CatalogController.php` — tambah method `filter()`
    - Tambahkan `declare(strict_types=1)` di baris pertama
    - Tambahkan return type hint eksplisit pada `index()`: `View`
    - Tambahkan import: `FilterProductRequest`, `RedirectResponse`, `View`
    - Implementasikan method `filter(FilterProductRequest $request): View|RedirectResponse`
    - Guard: jika tidak ada header `HX-Request`, redirect ke `route('catalog.index')`
    - Extract `$search = trim((string) ($request->validated('search') ?? ''))` dan `$stock = $request->validated('stock') ?? 'semua'`
    - Build query: `Product::query()->latest()` dengan `where()` Builder, bukan `filter()` Collection
    - Kondisi search: `where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")`
    - Kondisi stock: `where('current_stock', '>', 0)` untuk tersedia, `where('current_stock', '<=', 0)` untuk habis
    - Return: `view('catalog.products-grid', compact('products'))`
    - _Requirements: 3.3, 3.4, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_

- [x] 3. Blade Views — Partial dan komponen baru
  - [x] 3.1 Buat `resources/views/catalog/products-grid.blade.php` (Partial View)
    - Buat direktori `resources/views/catalog/` jika belum ada
    - View ini **tidak** menggunakan layout (`@extends` atau `<x-layouts.*>`) — fragment HTML murni
    - Salin markup kartu produk (termasuk carousel Alpine.js, slide drawer, tombol Beli/Habis) dari `catalog.blade.php` ke dalam loop `@foreach($products as $product)`
    - Tambahkan kondisi empty state: `@if($products->isEmpty())` → `<div class="col-span-full text-center py-16 ...">Tidak ada produk yang sesuai.</div>`
    - Pastikan tidak ada tag `<html>`, `<head>`, `<body>`, atau layout wrapper
    - _Requirements: 5.3, 5.4, 4.7, 4.8, 3.7_

  - [x] 3.2 Buat `resources/views/catalog/partials/product-filter.blade.php` (Komponen Filter)
    - Buat direktori `resources/views/catalog/partials/` jika belum ada
    - Implementasikan `<form x-data="{ activeFilter: 'semua' }" hx-get="/katalog/filter" hx-target="#products-grid" hx-swap="innerHTML">`
    - Input search: `type="search"`, `name="search"`, `hx-trigger="keyup changed delay:400ms from:this"`, `hx-include="closest form"`
    - Tombol filter stok: `@foreach(['semua', 'tersedia', 'habis'] as $filter)` → `<button type="submit" name="stock" value="{{ $filter }}" @click="activeFilter = '{{ $filter }}'">`
    - Kelas aktif Alpine: `activeFilter === '{{ $filter }}' ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300'`
    - Kelas tombol base: `px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest transition-all`
    - Semua elemen menggunakan kelas `dark:` Tailwind
    - _Requirements: 3.1, 3.2, 3.5, 4.1, 4.5, 4.7, 6.1, 6.2, 6.3, 6.6_

  - [x] 3.3 Update `resources/views/catalog.blade.php` — Section `#katalog` (Product Filter + Grid)
    - Di dalam `<section id="katalog">`, setelah header section, tambahkan: `@include('catalog.partials.product-filter')`
    - Ganti blok `@if($products->isEmpty()) ... @else ... @endif` (grid produk) dengan:
      ```html
      <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 [&.htmx-request]:opacity-50 [&.htmx-request]:transition-opacity">
          @include('catalog.products-grid', ['products' => $products])
      </div>
      ```
    - Product_Filter harus ditempatkan sebagai child langsung `<section id="katalog">` sebelum grid
    - _Requirements: 3.1, 6.4, 6.5_

  - [x] 3.4 Update `resources/views/catalog.blade.php` — Section `#kontak` (business_hours + WhatsApp CTA)
    - Tambahkan blok `@php $businessHours = trim((string) config('settings.business_hours', '')); @endphp`
    - Tambahkan conditional rendering jam operasional: `@if($businessHours !== '') ... @endif` setelah blok alamat
    - Label jam operasional menggunakan kelas: `text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2`
    - Value jam operasional menggunakan kelas: `text-gray-700 dark:text-gray-300 font-light`
    - Tambahkan blok formatter WhatsApp: hapus non-digit, konversi leading `0` ke `62`
    - Render `<a href="https://wa.me/{{ $formattedPhone }}" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp dengan Rima Craft">` hanya jika `strlen($formattedPhone) >= 10`
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 7.1, 7.2, 7.3, 7.4, 7.5_

- [x] 4. Settings View — Input field business_hours
  - [x] 4.1 Update `resources/views/settings/index.blade.php` — tambah input `business_hours`
    - Di tab "Data Umum", dalam blok "Informasi Kontak & Lokasi", setelah field `gmaps_iframe`, tambahkan:
      ```html
      <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Jam Operasional</label>
          <input
              type="text"
              name="business_hours"
              value="{{ $settings['business_hours'] ?? '' }}"
              class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none"
              placeholder="Contoh: Senin–Sabtu, 08.00–17.00 WIB"
          />
      </div>
      ```
    - Pastikan input berada di dalam blok `<div class="space-y-4">` bersama field kontak lainnya
    - _Requirements: 1.5, 1.6, 1.7_

- [x] 5. Checkpoint — Verifikasi manual tampilan dan fungsionalitas dasar
  - Jalankan `php artisan serve` dan buka `http://localhost:8000`
  - Verifikasi section `#katalog` menampilkan Product_Filter (input search + 3 tombol stok)
  - Ketik di input search, verifikasi HTMX request terkirim dan grid produk terupdate
  - Klik tombol "Tersedia", verifikasi hanya produk stok > 0 yang tampil
  - Buka `/settings`, verifikasi field "Jam Operasional" ada di tab Data Umum
  - Isi `business_hours`, simpan, buka `/`, verifikasi jam operasional tampil di section kontak
  - Pastikan semua tests pass, tanyakan kepada user jika ada pertanyaan.

- [ ] 6. Tests — Unit Tests (Example-Based)
  - [x] 6.1 Buat `tests/Unit/SettingsConfigTest.php`
    - Test: `it_defines_all_contact_keys` — verifikasi semua 5 key (`address`, `email`, `instagram`, `gmaps_iframe`, `business_hours`) terdefinisi di `config/settings.php`
    - Test: `it_groups_contact_keys_with_empty_default` — verifikasi semua key kontak default ke string kosong `''`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.9_

  - [x] 6.2 Buat `tests/Unit/FilterProductRequestTest.php`
    - Test: `it_rejects_invalid_stock_values` — stock bukan `semua|tersedia|habis` harus gagal validasi (HTTP 422)
    - Test: `it_accepts_valid_stock_values` — verifikasi `semua`, `tersedia`, `habis` lulus validasi
    - Test: `it_rejects_search_with_special_chars` — karakter `<`, `>`, `"`, `'`, `;` harus ditolak
    - Test: `it_accepts_search_with_allowed_chars` — alfanumerik, spasi, `- . , ? !` harus lulus
    - Test: `it_rejects_search_longer_than_100_chars` — string 101 karakter harus ditolak
    - Test: `it_accepts_null_search` — `search` nullable harus lulus validasi
    - _Requirements: 3.6, 4.6_

  - [ ]* 6.3 Buat `tests/Feature/CatalogControllerTest.php`
    - Test: `it_redirects_non_htmx_requests_to_catalog` — `GET /katalog/filter` tanpa header `HX-Request` → redirect 302 ke `/`
    - Test: `it_returns_200_for_valid_htmx_requests` — `GET /katalog/filter` dengan header `HX-Request: true` → HTTP 200
    - Test: `it_returns_html_fragment_not_full_page` — response tidak mengandung `<html`, `<head`, `<body`
    - Test: `it_returns_all_products_when_no_filter` — `search=&stock=semua` mengembalikan semua produk
    - Test: `it_filters_by_search_term` — search mengembalikan hanya produk yang cocok
    - Test: `it_filters_by_stock_tersedia` — `stock=tersedia` mengembalikan hanya produk dengan `current_stock > 0`
    - Test: `it_filters_by_stock_habis` — `stock=habis` mengembalikan hanya produk dengan `current_stock <= 0`
    - Test: `it_returns_empty_state_message_when_no_match` — kombinasi filter yang tidak cocok → "Tidak ada produk yang sesuai."
    - _Requirements: 3.3, 3.4, 4.2, 4.3, 4.4, 5.1, 5.3, 5.4, 5.6_

  - [ ]* 6.4 Buat `tests/Feature/CatalogViewTest.php`
    - Test: `it_renders_filter_component_on_catalog_page` — `GET /` mengandung `hx-get="/katalog/filter"` dan `hx-target="#products-grid"`
    - Test: `it_renders_products_grid_wrapper_with_htmx_indicator` — `GET /` mengandung `id="products-grid"` dengan kelas `htmx-request`
    - Test: `it_renders_business_hours_when_set` — config `business_hours` diisi → blok "Jam Operasional" tampil
    - Test: `it_hides_business_hours_when_empty` — config `business_hours` kosong → tidak ada elemen jam operasional
    - Test: `it_hides_business_hours_when_whitespace_only` — `business_hours` berisi spasi saja → tidak dirender
    - Test: `it_renders_whatsapp_cta_with_valid_phone_08xx` — nomor `08123456789` → href `https://wa.me/628123456789`
    - Test: `it_renders_whatsapp_cta_with_valid_phone_62xx` — nomor `628123456789` → href tetap `https://wa.me/628123456789`
    - Test: `it_hides_whatsapp_cta_when_phone_too_short` — hasil < 10 digit → tidak ada elemen `<a href="https://wa.me/`
    - Test: `it_hides_whatsapp_cta_when_phone_empty` — kosong/null → tidak dirender
    - _Requirements: 2.1, 2.2, 2.3, 6.4, 7.1, 7.2, 7.3, 7.4, 7.5_

  - [ ]* 6.5 Buat `tests/Feature/SettingsIntegrationTest.php`
    - Test: `it_saves_business_hours_to_settings_table` — POST ke `/settings` dengan `business_hours` → verifikasi record di tabel `settings`
    - Test: `it_saves_all_contact_keys` — POST dengan semua 5 key kontak → verifikasi semua tersimpan di DB
    - _Requirements: 1.6, 1.7, 1.8_

- [ ] 7. Tests — Property-Based Tests (PBT)
  - [ ]* 7.1 Tulis property test untuk Property 1: Filter Search — Subset Invariant
    - **Property 1: Filter search mengembalikan subset yang relevan**
    - **Validates: Requirements 3.3, 4.2, 4.3**
    - Gunakan PHPUnit data provider dengan 100+ kombinasi input (search string 1–100 chars + random Product dataset 0–50 rows)
    - Assert: setiap produk dalam hasil filter mengandung `$search` di `name` atau `description` (case-insensitive)
    - Tag komentar: `// Feature: katalog-publik-enhancement, Property 1: Filter search returns only matching products`
    - Minimum 100 iterasi

  - [ ]* 7.2 Tulis property test untuk Property 2: Filter Stok — Subset Invariant
    - **Property 2: Filter stok adalah subset invariant**
    - **Validates: Requirements 4.2, 4.3**
    - Dataset: 0–50 Product factory instances dengan `current_stock` acak (termasuk 0 dan negatif)
    - Assert untuk `tersedia`: semua `p.current_stock > 0`
    - Assert untuk `habis`: semua `p.current_stock <= 0`
    - Tag komentar: `// Feature: katalog-publik-enhancement, Property 2: Stock filter returns only matching products`
    - Minimum 100 iterasi

  - [ ]* 7.3 Tulis property test untuk Property 3: Confluence Filter
    - **Property 3: Urutan penerapan filter tidak mengubah hasil (Confluence)**
    - **Validates: Requirements 3.5, 4.5**
    - Assert: `count(filter(search, stock)) == count(filter_stock(stock, filter_search(search, all)))`
    - Tag komentar: `// Feature: katalog-publik-enhancement, Property 3: Filter order does not affect result`
    - Minimum 100 iterasi

  - [ ]* 7.4 Tulis property test untuk Property 4: Identity Filter
    - **Property 4: Filter kosong + semua mengembalikan semua produk**
    - **Validates: Requirements 3.4, 4.4**
    - Assert: `count(filter('', 'semua')) == count(Product::all())` DAN set sama persis
    - Tag komentar: `// Feature: katalog-publik-enhancement, Property 4: Empty filter returns all products`
    - Minimum 100 iterasi dengan dataset 1–50 Product factory instances

  - [ ]* 7.5 Tulis property test untuk Property 5: HTMX Response Format — Fragment Invariant
    - **Property 5: HTMX response selalu berupa HTML fragment valid**
    - **Validates: Requirements 5.4, 5.6**
    - Untuk setiap kombinasi valid `(search, stock)` dengan header `HX-Request: true`:
    - Assert: `response.status == 200`, `Content-Type` mengandung `text/html`
    - Assert: body tidak mengandung `<html`, `<head`, `<body`
    - Tag komentar: `// Feature: katalog-publik-enhancement, Property 5: HTMX response is always a valid HTML fragment`
    - Minimum 100 iterasi

  - [ ]* 7.6 Tulis property test untuk Property 6: Formatter WA — Idempotence & Prefix Invariant
    - **Property 6: Formatter nomor WhatsApp idempoten dan invariant prefix `62`**
    - **Validates: Requirements 7.2**
    - Extract logika formatter dari `catalog.blade.php` ke helper atau metode yang dapat di-test
    - Assert idempoten: `format_wa(format_wa(phone)) == format_wa(phone)`
    - Assert prefix: `str_starts_with(format_wa(phone), '62')`
    - Input generator: format `08xx...`, `+628x...`, `628x...`, string dengan spasi/tanda hubung
    - Tag komentar: `// Feature: katalog-publik-enhancement, Property 6: WA phone formatter is idempotent with 62 prefix`
    - Minimum 100 iterasi

- [ ] 8. Checkpoint — Semua Tests Harus Hijau
  - Jalankan `php artisan test` dan pastikan semua test pass
  - Jika ada test gagal, perbaiki sebelum lanjut ke langkah docs
  - Pastikan semua tests pass, tanyakan kepada user jika ada pertanyaan.

- [ ] 9. Docs — Update progress dan buat checkpoint
  - [ ] 9.1 Buat `docs/checkpoints/CP-012-katalog-publik-enhancement.md`
    - Gunakan format yang konsisten dengan checkpoint sebelumnya (lihat `CP-011-dashboard-analytics.md`)
    - Dokumentasikan: fitur yang diimplementasikan, file yang dibuat/dimodifikasi, keputusan arsitektur utama
    - Catat: property tests yang dibuat beserta requirement yang dicakup
    - _Requirements: Semua (dokumentasi coverage)_

  - [ ] 9.2 Update `docs/progress/current-status.md`
    - Update `Date Updated` ke tanggal saat ini
    - Pindahkan progress katalog publik ke bagian "Completed"
    - Catat: `[CP-012] Katalog Publik Enhancement`
    - Update "Next Task" sesuai backlog berikutnya
    - _Requirements: Semua (dokumentasi status)_

---

## Notes

- Tasks bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Semua file PHP baru **wajib** menggunakan `declare(strict_types=1)` dan explicit type hints
- Query filter produk **harus** menggunakan Eloquent `where()` Builder — dilarang `filter()` / `reject()` pada Collection PHP
- HTMX adalah satu-satunya mekanisme transport untuk interaksi server; Alpine.js hanya untuk active state visual client-side
- `FilterProductRequest` harus di-type-hint di method `filter()` agar validasi berjalan otomatis
- Property tests: jika library Eris tidak tersedia, gunakan PHPUnit data providers dengan array_map + range untuk 100+ kombinasi
- Setiap property test **harus** menyertakan komentar tag: `// Feature: katalog-publik-enhancement, Property N: ...`
- Checkpoint manual (task 5) dan checkpoint test (task 8) harus diselesaikan sebelum docs update

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2"] },
    { "id": 1, "tasks": ["2.1"] },
    { "id": 2, "tasks": ["2.2"] },
    { "id": 3, "tasks": ["3.1", "4.1"] },
    { "id": 4, "tasks": ["3.2"] },
    { "id": 5, "tasks": ["3.3", "3.4"] },
    { "id": 6, "tasks": ["6.1", "6.2"] },
    { "id": 7, "tasks": ["6.3", "6.4", "6.5"] },
    { "id": 8, "tasks": ["7.1", "7.2", "7.3", "7.4", "7.5", "7.6"] },
    { "id": 9, "tasks": ["9.1", "9.2"] }
  ]
}
```
