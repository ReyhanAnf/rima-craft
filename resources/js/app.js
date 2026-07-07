import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import Alpine from 'alpinejs';
import htmx from 'htmx.org';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ToastService from 'primevue/toastservice';
import 'primeicons/primeicons.css';

window.Alpine = Alpine;
window.htmx = htmx;

if (typeof window !== 'undefined') {
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        window.deferredInstallPrompt = e;
        window.isAppInstallable = true;
        window.dispatchEvent(new CustomEvent('pwa-installable-status', { detail: true }));
    });

    window.addEventListener('appinstalled', () => {
        window.deferredInstallPrompt = null;
        window.isAppInstallable = false;
        window.dispatchEvent(new CustomEvent('pwa-installable-status', { detail: false }));
    });
}

Alpine.store('cart', {
    items: JSON.parse(localStorage.getItem('rimacraft_cart') || '[]'),
    notes: localStorage.getItem('rimacraft_cart_notes') || '',
    showOrderForm: false,
    checkoutMethod: 'form',

    save() {
        localStorage.setItem('rimacraft_cart', JSON.stringify(this.items));
        localStorage.setItem('rimacraft_cart_notes', this.notes);
    },

    totalItems() {
        return this.items.reduce((sum, item) => sum + item.qty, 0);
    },

    totalPrice() {
        return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
    },

    add(product) {
        const existing = this.items.find((item) => item.id === product.id);

        if (existing) {
            if (existing.qty >= product.stock) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Stok tidak mencukupi!', type: 'error' },
                }));

                return;
            }

            existing.qty += 1;
        } else if (product.stock > 0) {
            this.items.push({
                id: product.id,
                name: product.name,
                price: Number.parseFloat(product.price) || 0,
                qty: 1,
                stock: product.stock,
                image: product.image || null,
            });
        } else {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message: 'Produk sedang habis.', type: 'error' },
            }));

            return;
        }

        this.save();
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message: `${product.name} ditambahkan ke keranjang!`, type: 'success' },
        }));
    },

    remove(id) {
        this.items = this.items.filter((item) => item.id !== id);
        this.save();
    },

    clear() {
        this.items = [];
        this.notes = '';
        this.save();
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message: 'Keranjang telah dibersihkan!', type: 'success' },
        }));
    },

    increment(id) {
        const item = this.items.find((cartItem) => cartItem.id === id);

        if (item && item.qty < item.stock) {
            item.qty += 1;
            this.save();

            return;
        }

        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message: 'Maksimal stok tercapai!', type: 'error' },
        }));
    },

    decrement(id) {
        const item = this.items.find((cartItem) => cartItem.id === id);

        if (item && item.qty > 1) {
            item.qty -= 1;
            this.save();
        } else if (item && item.qty === 1) {
            this.remove(id);
        }
    },

    checkout(businessPhone) {
        if (this.items.length === 0) {
            return;
        }

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

        text += '\nApakah produk tersebut tersedia dan bisa dikirim ke alamat saya?';

        let phone = (businessPhone || window.rimacraft?.businessPhone || '6281234567890').replace(/\D/g, '');
        if (phone.startsWith('0')) {
            phone = `62${phone.substring(1)}`;
        }

        const waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
        window.open(waUrl, '_blank');
    },
});

Alpine.start();

document.body.addEventListener('htmx:responseError', (evt) => {
    const status = evt.detail.xhr.status;

    if (status === 422) {
        console.warn('Validasi form gagal.');
    } else if (status === 403 || status === 401) {
        alert('Akses Ditolak atau Sesi Habis. Silakan login kembali.');
    } else if (status >= 500) {
        alert('Terjadi kesalahan pada server. Silakan coba lagi.');
    }
});

window.apiFetch = async function apiFetch(url, options = {}) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const defaultHeaders = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
    };

    options.headers = { ...defaultHeaders, ...options.headers };

    try {
        const response = await fetch(url, options);

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `HTTP Error: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('apiFetch Error:', error.message);
        throw error;
    }
};

// Simple route helper to replace Ziggy without extra packages
window.route = function(name, params = {}) {
    if (name === undefined) {
        return {
            current: (routeName) => {
                const path = window.route(routeName);
                if (!path || path === '/') {
                    return window.location.pathname === '/';
                }
                return window.location.pathname === path || window.location.pathname.startsWith(path + '/');
            }
        };
    }

    const routes = {
        'catalog.index': '/',
        'catalog.shop': '/katalog',
        'login': '/login',
        'admin.login': '/admin/login',
        'admin.login.store': '/admin/login',
        'logout': '/logout',
        'dashboard': '/dashboard',
        'products.index': '/products',
        'materials.index': '/materials',
        'sales.index': '/sales',
        'purchases.index': '/purchases',
        'finance.index': '/finance',
        'finance.accounts.store': '/finance/accounts',
        'finance.transactions.store': '/finance/transactions',
        'finance.print': '/finance/print',
        'settings.index': '/settings',
        'settings.update': '/settings',
        'contacts.index': '/contacts',
        'stock-adjustments.index': '/stock-adjustments',
        'productions.index': '/productions',
        'galleries.index': '/galleries',
        'users.index': '/users',
        'users.store': '/users',
        'users.update': '/users/{id}',
        'users.destroy': '/users/{id}',
        'users.verify-reseller': '/users/{id}/verify-reseller',
        'users.reject-reseller': '/users/{id}/reject-reseller',
        'roles.index': '/roles',
        'orders.index': '/orders',
        'regions.index': '/regions',
        'regions.store': '/regions',
        'regions.update': '/regions/{id}',
        'regions.destroy': '/regions/{id}',
        'payment-methods.index': '/payment-methods',
        
        // Auth routes
        'auth.google.redirect': '/auth/google/redirect',
        'auth.google.callback': '/auth/google/callback',
        'auth.google.complete': '/auth/google/complete',
        'auth.google.complete.store': '/auth/google/complete',
        'login.store': '/login',
        'register.show': '/register',
        'register.submit': '/register',
        'customer.login': '/customer/login',
        'customer.login.store': '/customer/login',
        'customer.register': '/customer/register',
        'customer.register.submit': '/customer/register',
        'reseller.login': '/reseller/login',
        'reseller.login.store': '/reseller/login',
        'reseller.register': '/reseller/register',
        'reseller.register.submit': '/reseller/register',

        // Password reset
        'password.request': '/forgot-password',
        'password.email': '/forgot-password',
        'password.reset': '/reset-password/{token}',
        'password.update': '/reset-password',

        // Static pages
        'page.terms': '/syarat-ketentuan',
        'page.privacy': '/kebijakan-privasi',
        'page.shipping': '/pengiriman-retur',

        // Parameterized routes
        'orders.show': '/orders/{id}',
        'orders.update-status': '/orders/{id}/status',
        'orders.destroy': '/orders/{id}',
        'purchases.show': '/purchases/{id}',
        'sales.show': '/sales/{id}',
        'sales.update-status': '/sales/{id}/status',
        'productions.show': '/productions/{id}',
        'roles.edit': '/roles/{role}/edit',
        'roles.update': '/roles/{role}',
        'roles.destroy': '/roles/{role}',
        
        // Portals
        'customer.dashboard': '/customer/dashboard',
        'customer.orders': '/customer/orders',
        'customer.profile': '/customer/profile',
        'customer.profile.update': '/customer/profile/update',
        'reseller.dashboard': '/reseller/dashboard',
        'reseller.orders': '/reseller/orders',
        'reseller.billing': '/reseller/billing',
        'reseller.profile': '/reseller/profile',
        'reseller.profile.update': '/reseller/profile/update',
    };

    let path = routes[name];
    if (!path && name.includes('.')) {
        const parts = name.split('.');
        const resource = parts[0];
        const action = parts[1];
        const resourcePath = '/' + resource;

        if (action === 'index' || action === 'store') {
            path = resourcePath;
        } else if (action === 'create') {
            path = resourcePath + '/create';
        } else if (action === 'show' || action === 'update' || action === 'destroy') {
            path = resourcePath + '/{id}';
        } else if (action === 'edit') {
            path = resourcePath + '/{id}/edit';
        }
    }

    if (!path) {
        console.warn(`Route "${name}" not found in custom helper.`);
        return '';
    }

    if (typeof params !== 'object') {
        path = path.replace(/\{[a-zA-Z0-9_-]+\}/, params);
    } else {
        Object.keys(params).forEach(key => {
            // Replace key placeholder like {id} or {role}
            path = path.replace(/\{[a-zA-Z0-9_-]+\}/, params[key]);
        });
    }

    return path;
};

if (document.getElementById('app')) {
    createInertiaApp({
        title: (title) => title ? `${title} — Rima Craft` : 'Rima Craft',

        resolve: (name) =>
            resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),

        setup({ el, App, props, plugin }) {
            const pinia = createPinia();

            const app = createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(pinia)
                .use(PrimeVue, {
                    theme: {
                        preset: Aura,
                        options: {
                            darkModeSelector: '.dark',
                        }
                    }
                })
                .use(ToastService);

            // Register global route function for templates
            app.config.globalProperties.route = window.route;
            app.mount(el);
        },

        progress: {
            color: '#d97706',
        },
    });
}
