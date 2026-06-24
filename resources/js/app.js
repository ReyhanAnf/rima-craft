import 'htmx.org';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// 1. GLOBAL HTMX ERROR HANDLING
document.body.addEventListener('htmx:responseError', function (evt) {
    const status = evt.detail.xhr.status;
    // TODO: Ganti alert ini dengan Toast Notification modern (Alpine) nantinya
    if (status === 422) {
        console.warn('Validasi form gagal.');
    } else if (status === 403 || status === 401) {
        alert('Akses Ditolak atau Sesi Habis. Silakan login kembali.');
    } else if (status >= 500) {
        alert('Terjadi kesalahan pada server. Silakan coba lagi.');
    }
});

// 2. HELPER FETCH API (Dilarang pakai fetch mentah!)
window.apiFetch = async function(url, options = {}) {
    // Otomatis tambahkan header JSON dan CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken
    };

    options.headers = { ...defaultHeaders, ...options.headers };

    try {
        const response = await fetch(url, options);
        
        // HUKUM MUTLAK: Tangkap error HTTP
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `HTTP Error: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('apiFetch Error:', error.message);
        throw error; // Lempar ulang agar bisa ditangkap oleh blok pemanggil
    }
};
