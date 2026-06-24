# UI/UX Guideline

## 1\. Prinsip Desain & Tema

*   **Premium Clean Enterprise Interface:** Menggunakan gaya desain modern (Glassmorphism, soft drop shadows, rounded-3xl corners, dan ruang bernapas yang luas). Transisi warna dan elemen dibuat sangat halus.
*   **Tone Warna (Modern Emerald / Premium Green):**
    *   _Primary:_ Emerald/Teal cerah yang memberikan kesan *fresh* dan profesional.
    *   _Background (Light):_ Off-white dengan gradasi radial ke putih halus.
    *   _Background (Dark):_ Slate/Gray-950 yang sangat elegan dengan *subtle gradient*.
    *   _Gradients & Blur:_ Mengandalkan radial gradients dan *backdrop-blur* untuk kedalaman UI.
*   **Responsive Absolute:** Desain beradaptasi sempurna dengan layar:
    *   _Mobile:_ Lebar penuh, kontrol dengan satu tangan.
    *   _Desktop:_ Memanfaatkan lebar layar (Sidebar navigasi, Bento Grid untuk konten), BUKAN tampilan *mobile* yang dilonggarkan.

## 2\. Komponen UI Utama (Tailwind CSS)

*   **Navigasi Desktop:** Sidebar elegan di sebelah kiri yang mengakomodasi logo dan menu Enterprise.
*   **Navigasi Mobile:** Menggunakan _Bottom Navigation Bar_ dengan desain *Glassmorphism* (Beranda, Transaksi, Master Data, Profil).
*   **Tabel Data & Layout:** Menggunakan pola **Bento Grid** untuk *Dashboard*, dan *Card List* atau modern *Datatable* untuk *desktop*.
*   **Dark Mode Toggle:** Mendukung transisi instan *Light/Dark Mode* yang disinkronisasi dengan `localStorage`.
*   **Form Inputs:** Input modern bergaya *Glass* (latar transparan/blur) dengan fokus *ring* tebal berwarna primary.

## 3\. Interaksi & Notifikasi (Alpine + HTMX)

*   **No-Loading State Flash:** Interaksi seperti simpan/update/delete wajib menggunakan indikator _spinner_ pada tombol (`hx-indicator`) tanpa me-reload halaman.
*   **Toast Notifications:** Muncul dari atas layar dengan animasi halus (_slide in/out_) untuk setiap _action_ sukses.
*   **Modal Dialogs:** Modern modal dengan _backdrop blur_ untuk form kecil (seperti bayar cicilan) atau konfirmasi hapus data.

## 4\. Pendekatan UX untuk Admin Awam (Layperson Experience)

Untuk meminimalisir risiko "lupa mencatat" tanpa menyulitkan admin yang awam, antarmuka menerapkan konsep berikut:
*   **To-Do List Dashboard:** Halaman utama (*dashboard*) admin berfokus pada **"Action Needed"** atau Tugas Tertunda (Misal: *Badge* merah "Ada 3 Pesanan Baru (Draft) Menunggu Konfirmasi"). Ini menuntun admin pada apa yang harus diselesaikan hari itu layaknya aplikasi *to-do list*.
*   **Zero-Input Entry (Auto-Draft):** Pesanan dari pelanggan di web otomatis masuk ke sistem admin dengan status *Draft/Pending*, lengkap dengan Kode Referensi (Ref ID) yang juga dibawa pelanggan saat chat ke WhatsApp. Admin tidak perlu mengetik ulang pesanan dari nol.
*   **1-Click Action:** Untuk menyelesaikan pesanan, admin cukup mencocokkan Ref ID dari chat WA, membuka *Draft* tersebut, dan menekan 1 tombol utama (misal: **"Terima & Tandai Lunas"**). Di belakang layar, sistem otomatis akan mencatat pemasukan kas dan memotong stok tanpa mengharuskan admin mengisi formulir akuntansi yang rumit.