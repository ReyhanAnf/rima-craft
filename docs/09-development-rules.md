# Development Rules

Dokumen ini berisi hukum mutlak yang HARUS diikuti oleh pengembang (terutama AI Agent) selama proses penulisan kode.

## 1\. Aturan Workflow AI (Wajib)

*   **TIDAK BOLEH menulis kode secara membabi buta.** Mulai dari inisialisasi, routing, logika, baru UI.
*   **UPDATE CHECKPOINT:** Setiap kali AI menyelesaikan satu tugas fungsional atau modul, AI **DIWAJIBKAN** untuk memperbarui/membuat file di folder `docs/checkpoints/` dan mengupdate `docs/progress/current-status.md`.

## 2\. Standar Koding PHP & Laravel

*   Gunakan `declare(strict_types=1);` di baris pertama setiap file PHP.
*   Tulis _Type Hinting_ secara eksplisit (parameter dan return type).
*   **Fat Model, Thin Controller:** Validasi harus di `FormRequest`. Logika bisnis yang melibatkan lebih dari 1 tabel harus dipindah ke `Action` (misal: `app/Actions/RecordProductionAction.php`).

## 3\. Frontend BHA Stack Rules

*   **Dilarang keras** menggunakan jQuery atau Vue/React.
*   **Alpine.js:** Untuk _state_ UI _client-side_ (buka-tutup modal, notifikasi, hitung keranjang belanja offline).
*   **HTMX:** Untuk form submission, paginasi, pencarian data, dan navigasi halaman (menggunakan `hx-boost="true"`).

## 4\. Keamanan

*   Gunakan `@csrf` di setiap form.
*   Dilarang keras melakukan _Mass Assignment_ tanpa mendefinisikan `$fillable` di Model.