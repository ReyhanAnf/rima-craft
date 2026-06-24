# Requirements Document

## Introduction

Fitur **Katalog Publik Enhancement** meningkatkan halaman katalog publik Rima Craft (`catalog.blade.php`) pada dua area utama:

1. **Search & Filter Produk** — pengunjung dapat menyaring koleksi produk berdasarkan kata kunci dan status stok secara real-time via HTMX tanpa full page reload.
2. **Hardening Section Kontak** — section `#kontak` yang sudah ada di view perlu didukung oleh settings key yang lengkap dan terstandarisasi (termasuk `address`, `email`, `instagram`, `gmaps_iframe`, dan `business_hours` yang saat ini **belum terdaftar** di `config/settings.php`), serta penambahan tampilan jam operasional yang belum ada di view.

Proyek ini berjalan di atas Laravel 13 + BHA Stack (Blade + HTMX + Alpine.js + Tailwind CSS) dengan arsitektur Controller → Action → DTO → Service → Repository.

---

## Glossary

- **Catalog**: Halaman publik utama Rima Craft (`/`), dirender oleh `CatalogController`.
- **Product**: Model Eloquent `App\Models\Product` dengan kolom `name`, `description`, `base_price`, `current_stock`, `image_path`, `media_assets`.
- **Product_Grid**: Area `<section id="katalog">` di `catalog.blade.php` yang menampilkan kartu produk.
- **Product_Filter**: Komponen HTMX yang menangani input pencarian dan filter stok di dalam Product_Grid.
- **Partial_View**: Blade view parsial yang dirender `CatalogController` sebagai respons HTMX (bukan full page), berisi HTML kartu produk saja (`resources/views/catalog/products-grid.blade.php`).
- **Filter_Request**: `App\Http\Requests\Catalog\FilterProductRequest` — FormRequest untuk validasi parameter pencarian dan filter.
- **Settings**: Tabel `settings` (model `App\Models\Setting`) yang menyimpan konfigurasi bisnis sebagai pasangan `key`/`value`, dibaca via `config('settings.*')`.
- **Settings_Config**: File `config/settings.php` yang memetakan environment variable ke default value untuk setiap setting key.
- **Kontak_Section**: Section `<section id="kontak">` di `catalog.blade.php` yang menampilkan informasi kontak bisnis.
- **WhatsApp_CTA**: Tombol/tautan di Kontak_Section yang membuka `https://wa.me/{nomor}` di tab baru.
- **business_hours**: Setting key baru untuk jam operasional bisnis (contoh: "Senin–Sabtu, 08.00–17.00 WIB").
- **HTMX_Endpoint**: Route `GET /katalog/filter` yang merespons request HTMX dengan Partial_View.
- **Stok_Filter**: Nilai enum string untuk filter stok: `semua`, `tersedia`, `habis`.
- **Formatted_Phone**: Nomor telepon yang telah diproses: karakter non-digit dihapus, dan jika dimulai dengan `0` maka dikonversi menjadi `62` (format E.164 Indonesia tanpa tanda `+`).

---

## Requirements

### Kebutuhan 1: Infrastruktur Settings Key untuk Kontak

**User Story:** Sebagai Owner, saya ingin semua informasi kontak bisnis (alamat, email, Instagram, Google Maps, jam operasional) dapat dikelola melalui halaman Settings, sehingga perubahan informasi kontak langsung terefleksi di halaman katalog publik tanpa menyentuh kode.

#### Acceptance Criteria

1. THE Settings_Config SHALL mendefinisikan key `address` menggunakan pola `env('SETTINGS_ADDRESS', '')`, konsisten dengan key yang sudah ada seperti `business_name`.
2. THE Settings_Config SHALL mendefinisikan key `email` menggunakan pola `env('SETTINGS_EMAIL', '')`.
3. THE Settings_Config SHALL mendefinisikan key `instagram` menggunakan pola `env('SETTINGS_INSTAGRAM', '')`.
4. THE Settings_Config SHALL mendefinisikan key `gmaps_iframe` menggunakan pola `env('SETTINGS_GMAPS_IFRAME', '')`.
5. THE Settings_Config SHALL mendefinisikan key `business_hours` menggunakan pola `env('SETTINGS_BUSINESS_HOURS', '')`.
6. WHEN admin menyimpan nilai untuk key `business_hours` melalui `SettingController@update`, THE Settings SHALL menyimpan record ke tabel `settings` dengan `key = 'business_hours'` dan `value` sesuai input.
7. WHEN halaman katalog dirender dan tabel `settings` memiliki record dengan `key = 'business_hours'`, THE Kontak_Section SHALL menampilkan nilai `value` tersebut (bukan nilai default dari `config/settings.php`).
8. IF penyimpanan ke tabel `settings` gagal karena error database, THEN THE SettingController SHALL mengembalikan respons error dengan status HTTP 500 dan tidak mengubah state settings yang sudah ada.
9. THE Settings_Config SHALL mengelompokkan semua key kontak (`address`, `email`, `instagram`, `gmaps_iframe`, `business_hours`) dalam satu blok komentar `// Contact Information` yang terpisah dari blok `// Business Identity`.

---

### Kebutuhan 2: Tampilan Jam Operasional di Section Kontak

**User Story:** Sebagai pengunjung katalog, saya ingin melihat jam operasional workshop pada section kontak, sehingga saya tahu waktu terbaik untuk menghubungi atau mengunjungi Rima Craft.

#### Acceptance Criteria

1. IF `trim(config('settings.business_hours'))` tidak menghasilkan string kosong, THEN THE Kontak_Section SHALL merender blok jam operasional tepat di bawah blok informasi alamat, sebelum elemen kontak berikutnya.
2. IF `trim(config('settings.business_hours'))` menghasilkan string kosong atau nilai adalah `null`, THEN THE Kontak_Section SHALL tidak merender elemen HTML apapun untuk blok jam operasional — tidak ada placeholder, tidak ada teks "Belum diatur".
3. THE Kontak_Section SHALL merender label "Jam Operasional" menggunakan kelas Tailwind yang identik dengan label kontak lain yang sudah ada (misalnya `text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500`).
4. THE Kontak_Section SHALL merender nilai `business_hours` menggunakan kelas teks yang identik dengan nilai kontak lain yang sudah ada (misalnya `text-gray-700 dark:text-gray-300 font-light`).

---

### Kebutuhan 3: Input Pencarian Produk via HTMX

**User Story:** Sebagai pengunjung katalog (calon pembeli atau distributor), saya ingin dapat mencari produk berdasarkan nama atau deskripsi, sehingga saya dapat menemukan produk yang saya inginkan dengan cepat tanpa harus menggulir seluruh katalog.

#### Acceptance Criteria

1. THE Product_Filter SHALL menyediakan elemen `<input type="search" name="search">` di dalam section `#katalog`, sebelum Product_Grid, dengan atribut `hx-get="/katalog/filter"`, `hx-target="#products-grid"`, dan `hx-trigger`.
2. WHEN pengunjung mengetik atau menghapus karakter pada input pencarian, THE Product_Filter SHALL mengirim request HTMX ke HTMX_Endpoint dengan debounce 400ms (`hx-trigger="keyup changed delay:400ms"`), tidak lebih cepat dari itu.
3. WHEN HTMX_Endpoint menerima parameter `search` dengan panjang 1–100 karakter, THE Catalog SHALL mengembalikan Partial_View berisi hanya produk yang `name` atau `description`-nya mengandung string tersebut (LIKE query, case-insensitive). IF tidak ada produk yang cocok, Partial_View berisi pesan kosong (lihat Kebutuhan 5, Kriteria 4).
4. WHEN HTMX_Endpoint menerima parameter `search` berupa string kosong, string berisi hanya whitespace, atau parameter `search` tidak ada dalam request, THE Catalog SHALL mengembalikan Partial_View berisi semua produk tanpa filter nama.
5. THE Product_Filter SHALL menggunakan mekanisme form enclosing atau `hx-include` sehingga nilai Stok_Filter yang sedang aktif selalu dikirimkan bersamaan dengan parameter `search` dalam satu request HTMX.
6. IF parameter `search` mengandung karakter di luar set alfanumerik, spasi, tanda hubung (`-`), titik (`.`), koma (`,`), tanda tanya (`?`), dan tanda seru (`!`), THEN THE Filter_Request SHALL mengembalikan HTTP 422 dan Product_Grid SHALL tetap menampilkan hasil filter sebelumnya tanpa perubahan.
7. WHEN HTMX_Endpoint dipanggil dengan parameter `search` yang valid namun tidak ada produk yang cocok dengan kombinasi filter aktif, THE Partial_View SHALL merender elemen dengan teks "Tidak ada produk yang sesuai." dan tidak merender elemen kartu produk apapun.

---

### Kebutuhan 4: Filter Status Stok Produk via HTMX

**User Story:** Sebagai pengunjung katalog, saya ingin dapat menyaring produk berdasarkan ketersediaan stok (Semua / Tersedia / Habis), sehingga saya tidak membuang waktu melihat produk yang tidak dapat dibeli.

#### Acceptance Criteria

1. THE Product_Filter SHALL menyediakan tiga elemen interaktif bertanda teks "Semua", "Tersedia", dan "Habis" yang masing-masing mengirimkan nilai `semua`, `tersedia`, dan `habis` sebagai parameter `stock` via HTMX ke HTMX_Endpoint.
2. WHEN pengunjung mengaktifkan Stok_Filter `tersedia`, THE Catalog SHALL mengembalikan Partial_View berisi hanya produk dengan nilai kolom `current_stock > 0`.
3. WHEN pengunjung mengaktifkan Stok_Filter `habis`, THE Catalog SHALL mengembalikan Partial_View berisi hanya produk dengan nilai kolom `current_stock <= 0`.
4. WHEN pengunjung mengaktifkan Stok_Filter `semua`, THE Catalog SHALL mengembalikan Partial_View berisi semua produk tanpa kondisi filter stok.
5. WHEN Stok_Filter aktif berubah, THE Product_Filter SHALL mengirim request HTMX ke HTMX_Endpoint dengan menyertakan parameter `stock` (nilai filter baru) DAN parameter `search` (nilai input pencarian yang sedang aktif saat itu) dalam satu request yang sama.
6. IF parameter `stock` dalam request tidak bernilai `semua`, `tersedia`, atau `habis`, THEN THE Filter_Request SHALL mengembalikan HTTP 422.
7. WHEN halaman pertama kali dimuat (`GET /`), THE Product_Filter SHALL merender elemen Stok_Filter `semua` dalam kondisi aktif secara visual (misalnya kelas CSS aktif berbeda dengan yang non-aktif).
8. WHEN Stok_Filter aktif menghasilkan nol produk, THE Partial_View SHALL merender elemen dengan teks "Tidak ada produk yang sesuai." dan tidak merender elemen kartu produk apapun.

---

### Kebutuhan 5: HTMX Endpoint untuk Filter Produk

**User Story:** Sebagai pengunjung katalog, saya ingin hasil filter produk muncul langsung tanpa reload halaman, sehingga pengalaman browsing terasa cepat dan mulus di HP maupun desktop.

#### Acceptance Criteria

1. IF request ke `GET /katalog/filter` tidak menyertakan header `HX-Request: true`, THEN THE Catalog SHALL mengembalikan redirect HTTP 302 ke `/`.
2. WHEN `GET /katalog/filter` menerima request dengan header `HX-Request: true`, THE Catalog SHALL memproses parameter `search` dan `stock` melalui Filter_Request sebelum query database dieksekusi.
3. WHEN kombinasi filter `search` dan `stock` tidak menghasilkan produk apapun, THE Catalog SHALL mengembalikan Partial_View berisi elemen pesan "Tidak ada produk yang sesuai." tanpa elemen kartu produk, dengan HTTP status 200.
4. THE Catalog SHALL mengembalikan Partial_View yang hanya berisi HTML kartu produk (fragment HTML tanpa tag `<html>`, `<head>`, `<body>`, atau layout `public.blade.php`).
5. THE Catalog SHALL membangun query produk menggunakan Eloquent `where()` clause pada kolom database, bukan dengan `filter()` atau `reject()` pada Collection PHP.
6. WHEN HTMX_Endpoint dipanggil dengan request valid, THE Catalog SHALL selalu mengembalikan HTTP 200 dengan header `Content-Type: text/html`.

---

### Kebutuhan 6: Konsistensi UI Filter dengan Design System

**User Story:** Sebagai pengunjung katalog, saya ingin tampilan komponen filter terasa menyatu dengan halaman katalog yang sudah ada, sehingga pengalaman visual tetap premium dan konsisten.

#### Acceptance Criteria

1. THE elemen Stok_Filter yang sedang aktif SHALL memiliki background `bg-amber-500` atau `bg-amber-600` dan teks berwarna putih; elemen yang tidak aktif SHALL memiliki background transparan atau `bg-gray-100 dark:bg-gray-800`.
2. THE Product_Filter SHALL menerapkan kelas `dark:` Tailwind pada semua elemen interaktif sehingga tampilan tetap terbaca pada dark mode.
3. THE elemen tombol Stok_Filter SHALL menggunakan kelas `rounded-full` untuk border radius, konsisten dengan elemen pill/badge lain di katalog.
4. WHEN request HTMX sedang dalam perjalanan (in-flight), THE elemen Product_Grid SHALL menampilkan perubahan visual (misalnya opacity berkurang menjadi `opacity-50`) sebagai sinyal loading, dan kembali normal ketika respons diterima.
5. THE Product_Filter (input search + tombol stok) SHALL ditempatkan sebagai child langsung dari `<section id="katalog">`, sebelum elemen grid produk.
6. WHEN lebar viewport adalah 320px, THE Product_Filter SHALL merender input pencarian dan tombol Stok_Filter tanpa overflow horizontal atau elemen yang terpotong.

---

### Kebutuhan 7: Kelengkapan Data WhatsApp CTA

**User Story:** Sebagai pengunjung katalog, saya ingin tombol "Chat WhatsApp" di section kontak membuka percakapan WhatsApp yang valid, sehingga saya dapat langsung menghubungi Rima Craft tanpa perlu menyalin nomor secara manual.

#### Acceptance Criteria

1. WHEN `config('settings.business_phone')` menghasilkan Formatted_Phone dengan panjang minimal 10 digit, THE Kontak_Section SHALL merender WhatsApp_CTA sebagai tag `<a href="https://wa.me/{Formatted_Phone}" target="_blank" rel="noopener noreferrer">`.
2. THE Formatted_Phone SHALL dihitung dari `config('settings.business_phone')` dengan urutan: (a) hapus semua karakter non-digit, (b) jika hasilnya dimulai dengan `0`, ganti `0` terdepan dengan `62`.
3. WHEN hasil kalkulasi Formatted_Phone setelah strip non-digit menghasilkan string dengan panjang kurang dari 10 digit, THE Kontak_Section SHALL tidak merender WhatsApp_CTA.
4. WHEN `config('settings.business_phone')` bernilai `null`, string kosong, atau string berisi hanya whitespace, THE Kontak_Section SHALL tidak merender WhatsApp_CTA — tidak ada elemen `<a>` maupun tombol disabled.
5. THE WhatsApp_CTA SHALL menyertakan atribut `aria-label="Chat WhatsApp dengan Rima Craft"` untuk aksesibilitas screen reader.

---

## Correctness Properties untuk Property-Based Testing

### Property 1: Filter Tidak Menambah Produk (Metamorphic)

```
∀ (search, stock): count(filter(search, stock)) ≤ count(Product::all())
```

### Property 2: Filter Stok adalah Subset yang Konsisten (Invariant)

```
∀ p ∈ filter('*', 'tersedia'): p.current_stock > 0
∀ p ∈ filter('*', 'habis'):    p.current_stock == 0
```

### Property 3: Filter `semua` adalah Identity (Idempotence Baseline)

```
filter('', 'semua') ≡ Product::latest()->get()
```

### Property 4: Filter Pencarian adalah Subset dengan Kandidat yang Valid (Invariant)

```
∀ p ∈ filter(q, '*'), q ≠ '':
  str_contains(strtolower(p.name), strtolower(q))
  OR str_contains(strtolower(p.description ?? ''), strtolower(q))
```

### Property 5: Filter Kombinasi adalah Irisan (Confluence)

```
filter(search, stock) ≡ filter_stock(stock, filter_search(search, products))
                      ≡ filter_search(search, filter_stock(stock, products))
```

### Property 6: Formatter Nomor WA Idempoten dan Deterministik (Round-Trip)

```
∀ phone ∈ valid_phone_strings:
  format_wa(format_wa(phone)) == format_wa(phone)   // idempoten
  str_starts_with(format_wa(phone), '62')           // invariant prefix
```
