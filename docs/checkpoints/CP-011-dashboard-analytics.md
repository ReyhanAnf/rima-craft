# CP-011: Dashboard Analytics

Fokus pada penggantian halaman statis "Segera" di Dashboard menjadi panel metrik dan analitik interaktif yang cantik.

## Metrik Utama yang Ditampilkan
1. Saldo Kas Besar Saat Ini.
2. Total Pendapatan Bulan Ini.
3. Total Produk Terjual Bulan Ini.
4. Total Produksi Sedang Berjalan (Pending).

## Peringatan / Notifikasi Singkat
- Bahan Baku yang Menipis (Stok <= Stok Minimal).

## Visualisasi Data (Grafik)
- Grafik Tren Pendapatan Penjualan 7 Hari Terakhir (Menggunakan ApexCharts via CDN atau Alpine).

## Implementasi Teknis
- Membuat `DashboardController` untuk mengekstrak Query berat dari `routes/web.php`.
- Memperbarui `resources/views/dashboard.blade.php`.
