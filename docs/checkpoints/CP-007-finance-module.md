# Finance & Kas Besar Module (CP-005) Roadmap

Berdasarkan kesepakatan di CP-004, modul ini difokuskan untuk menangani aliran kas dan pembayaran parsial (Cicilan/Hutang/Piutang) dari transaksi yang ada.

## Target Implementasi
1. Tabel `payments` untuk mencatat setiap termin pembayaran (Cicilan). Polymorphic ke `Purchase`, `Sale`, `Production`.
2. Tabel `cash_ledgers` (Buku Kas) untuk mencatat semua kas masuk dan keluar secara terpusat.
3. Sinkronisasi status `unpaid` -> `partial` -> `paid` secara otomatis ketika jumlah pembayaran mencapai `total_amount`.
