# API & Interactivity Contract

Karena proyek ini menggunakan HTMX, arsitektur komunikasi utamanya BUKAN JSON API RESTful standar, melainkan **Hypermedia-Driven Application (HDA)**.

## 1\. HTMX Endpoints (HTML Responses)

Endpoint yang dipanggil oleh HTMX harus mengembalikan potongan HTML (_fragments_).

**Contoh Aturan Backend (Laravel):**

```
public function show(Product $product, Request $request) {    if ($request->header('HX-Request')) {        // Mengembalikan HANYA fragment modal/card produk        return view('components.product-card', compact('product'));    }    // Jika user me-refresh halaman (non-HTMX), kembalikan layout utuh    return view('pages.product-show', compact('product'));}
```

## 2\. JSON Endpoints (Khusus Alpine.js / PWA Offline Data)

Hanya buat _endpoint_ JSON jika benar-benar dibutuhkan oleh _client-side logic_ (misal sinkronisasi katalog untuk _offline mode_ PWA).

*   `GET /api/v1/katalog/sync` -> Mengembalikan _array_ ID produk, nama, dan harga dasar untuk di-cache oleh Alpine.js.

## 3\. Standar Penamaan Route

*   Publik: `katalog.index`, `katalog.show`
*   Admin Master: `admin.materials.index`, `admin.products.index`
*   Admin Transaksi: `admin.purchases.create`, `admin.productions.create`