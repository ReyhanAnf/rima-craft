# Technology Stack & Infrastructure

## Backend & Framework

*   **Framework:** Laravel 13 (Memanfaatkan fitur terbaru untuk kesiapan integrasi AI Agent di masa depan).
*   **Bahasa:** PHP 8.3+ (Strict Types diwajibkan).
*   **Database:** MySQL 8.x atau PostgreSQL 15+ (Mendukung JSON kolom dengan baik jika diperlukan untuk log/history).

## Frontend (UI & Reaktivitas)

*   **Pendekatan:** Vue.js 3 (SPA dengan Vue Router + Pinia).
*   **Templating:** Single File Components (SFC) dengan Vue Template.
*   **State Management:** Pinia (Untuk state UI global dan _offline cart_).
*   **HTTP Client:** Vue Query untuk caching dan server-state.
*   **Styling:** Tailwind CSS v3/v4 (Desain modern, bersih, palet _Earth Tones_).

## Progressive Web App (PWA) & Offline Support

*   **Service Worker:** Kustom berbasis Workbox.
*   **Offline State:** Pinia + `localStorage` (untuk keranjang belanja publik agar tidak hilang saat sinyal putus).

## Deployment & Infrastruktur

*   **Environment:** Shared Hosting (cPanel / CyberPanel).
*   **Keterbatasan:** Tidak menggunakan _daemon/worker_ antrian yang berjalan terus-menerus. Menggunakan Task Scheduler (Cron) atau penanganan sinkron.