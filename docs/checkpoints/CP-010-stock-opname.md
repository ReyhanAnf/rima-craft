# CP-010: Stock Opname (Penyesuaian Stok)

Fitur ini melengkapi Modul Inventori & Produksi pada MVP. Digunakan ketika stok tercatat di sistem berbeda dengan stok fisik (karena hilang, rusak, salah hitung, atau sisa potong).

## Kebutuhan Data (Schema)
Model `StockAdjustment` dengan Polymorphic relation (`adjustable_type`, `adjustable_id`) karena bisa menargetkan `Material` (Bahan Baku) atau `Product` (Produk Jadi).
- `quantity` (decimal, bisa positif untuk penambahan, negatif untuk pengurangan).
- `reason` (string, alasan penyesuaian seperti 'Hilang', 'Rusak', 'Salah Hitung').
- `user_id` (Admin yang melakukan opname).

## UI/UX
- Halaman dedicated "Penyesuaian Stok" (`/stock-adjustments`) yang menampilkan riwayat penyesuaian.
- Form (Slide-over drawer) untuk melakukan opname.
- Pilihan dinamis (Dropdown): Memilih kategori (Bahan / Produk) lalu memuat daftar itemnya.
- Penambahan menu baru di Sidebar Desktop dan "Menu Lainnya" di Mobile.
