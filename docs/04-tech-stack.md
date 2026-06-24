# Technology Stack & Infrastructure

## Backend & Framework

*   **Framework:** Laravel 13 (Memanfaatkan fitur terbaru untuk kesiapan integrasi AI Agent di masa depan).
*   **Bahasa:** PHP 8.3+ (Strict Types diwajibkan).
*   **Database:** MySQL 8.x atau PostgreSQL 15+ (Mendukung JSON kolom dengan baik jika diperlukan untuk log/history).

## Frontend (UI & Reaktivitas)

*   **Pendekatan:** BHA Stack (Blade + HTMX + Alpine.js).
*   **Templating:** Laravel Blade (Komponen UI modular).
*   **Interaktivitas Server-Side:** [HTMX](https://htmx.org/) (Tidak ada _full page reload_).
*   **Interaktivitas Client-Side:** Alpine.js (Untuk _state_ UI lokal dan _offline cart_).
*   **Styling:** Tailwind CSS v3/v4 (Desain modern, bersih, palet _Earth Tones_).

## Progressive Web App (PWA) & Offline Support

*   **Service Worker:** Kustom berbasis Workbox.
*   **Offline State:** Alpine.js + `localStorage` (untuk keranjang belanja publik agar tidak hilang saat sinyal putus).

## Deployment & Infrastruktur

*   **Environment:** Shared Hosting (cPanel / CyberPanel).
*   **Keterbatasan:** Tidak menggunakan _daemon/worker_ antrian yang berjalan terus-menerus. Menggunakan Task Scheduler (Cron) atau penanganan sinkron.