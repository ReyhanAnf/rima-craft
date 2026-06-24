# Checkpoint 012: Customer & Partner Roles + UX Guidelines

## Status: Completed ✅
**Date:** 2026-06-24

## Deskripsi
Pembaharuan pada dokumentasi proyek untuk mengakomodasi penambahan _role_ baru (Customer dan Partner/Reseller) serta pendekatan UX/alur bisnis baru untuk mengutamakan kemudahan Admin yang masih awam.

## Perubahan yang Dilakukan
1.  **Docs: Product Vision & Requirements**
    *   Menambahkan _role_ `Customer` (eceran) dan `Partner` (reseller/B2B).
    *   Menambahkan rencana fitur Portal Pelanggan ke _Pending Backlog_.
2.  **Docs: Business Flow & Database Design**
    *   Mengubah alur transaksi agar terintegrasi dengan Portal Pelanggan (sistem Auto-Draft) dan harga yang dibedakan (Standar vs Reseller).
    *   Memperbarui entitas rancangan tabel `roles` dan menambah `user_id` pada tabel `contacts`.
3.  **Docs: UI/UX Guidelines**
    *   Mendokumentasikan konsep "To-Do List Dashboard" dan eksekusi "1-Click Action" untuk admin.
    *   Menekankan proses "Zero-Input Entry" untuk menekan human error dan lupa mencatat pesanan.

## Langkah Selanjutnya
*   Melanjutkan _Architecture Refactoring_ Phase 2, khususnya mengimplementasikan RBAC Middleware & Gates yang akan menangani otorisasi dari _role-role_ yang baru didefinisikan ini.
