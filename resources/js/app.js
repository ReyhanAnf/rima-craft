import 'htmx.org';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Register Alpine Cart Store
Alpine.store('cart', {
    items: JSON.parse(localStorage.getItem('rimacraft_cart') || '[]'),
    notes: localStorage.getItem('rimacraft_cart_notes') || '',
    showOrderForm: false,
    checkoutMethod: 'form',
    
    save() {
        localStorage.setItem('rimacraft_cart', JSON.stringify(this.items));
        localStorage.setItem('rimacraft_cart_notes', this.notes);
    },

    add(product) {
        const existing = this.items.find(i => i.id === product.id);
        if (existing) {
            if (existing.qty < product.stock) {
                existing.qty++;
            } else {
                window.dispatchEvent(new CustomEvent('toast', { 
                    detail: { message: 'Stok tidak mencukupi!', type: 'error' } 
                }));
                return;
            }
        } else {
            if (product.stock > 0) {
                this.items.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    qty: 1,
                    stock: product.stock,
                    image: product.image
                });
            } else {
                window.dispatchEvent(new CustomEvent('toast', { 
                    detail: { message: 'Produk sedang habis.', type: 'error' } 
                }));
                return;
            }
        }
        this.save();
        window.dispatchEvent(new CustomEvent('toast', { 
            detail: { message: product.name + ' ditambahkan ke keranjang!', type: 'success' } 
        }));
    },

    remove(id) {
        this.items = this.items.filter(i => i.id !== id);
        this.save();
    },

    clear() {
        this.items = [];
        this.notes = '';
        this.save();
        window.dispatchEvent(new CustomEvent('toast', { 
            detail: { message: 'Keranjang telah dibersihkan!', type: 'success' } 
        }));
    },

    increment(id) {
        const item = this.items.find(i => i.id === id);
        if (item && item.qty < item.stock) {
            item.qty++;
            this.save();
        } else {
            window.dispatchEvent(new CustomEvent('toast', { 
                detail: { message: 'Maksimal stok tercapai!', type: 'error' } 
            }));
        }
    },

    decrement(id) {
        const item = this.items.find(i => i.id === id);
        if (item && item.qty > 1) {
            item.qty--;
            this.save();
        } else if (item && item.qty === 1) {
            this.remove(id);
        }
    },

    totalItems() {
        return this.items.reduce((sum, item) => sum + item.qty, 0);
    },

    totalPrice() {
        return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
    },

    checkout(businessPhone) {
        if (this.items.length === 0) return;
        
        const businessName = window.rimacraft?.businessName || 'Rima Craft';
        let text = `Halo *${businessName}*,\nSaya ingin memesan:\n\n`;
        
        this.items.forEach((item, index) => {
            text += `${index + 1}. *${item.name}*\n`;
            text += `   Jumlah: ${item.qty} x Rp ${item.price.toLocaleString('id-ID')} = Rp ${(item.qty * item.price).toLocaleString('id-ID')}\n`;
        });
        
        text += `\n*Total Pesanan: Rp ${this.totalPrice().toLocaleString('id-ID')}*\n`;
        
        if (this.notes.trim()) {
            text += `\n*Catatan Tambahan:*\n_${this.notes.trim()}_\n`;
        }
        
        text += `\nApakah produk tersebut tersedia dan bisa dikirim ke alamat saya?`;

        // Ensure phone is formatted properly
        let phone = (businessPhone || window.rimacraft?.businessPhone || '6281234567890').replace(/\D/g, '');
        if (phone.startsWith('0')) {
            phone = '62' + phone.substring(1);
        }

        const waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
        window.open(waUrl, '_blank');
    }
});

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
