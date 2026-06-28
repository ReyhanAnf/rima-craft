import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    const items = ref(JSON.parse(localStorage.getItem('rimacraft_cart') || '[]'));
    const notes = ref(localStorage.getItem('rimacraft_cart_notes') || '');

    function save() {
        localStorage.setItem('rimacraft_cart', JSON.stringify(items.value));
        localStorage.setItem('rimacraft_cart_notes', notes.value);
    }

    const totalItems = computed(() =>
        items.value.reduce((sum, item) => sum + item.qty, 0)
    );

    const totalPrice = computed(() =>
        items.value.reduce((sum, item) => sum + item.price * item.qty, 0)
    );

    function add(product, qty = 1) {
        const existing = items.value.find(i => i.id === product.id && i.variantLabel === (product.variantLabel ?? null));
        if (existing) {
            const newQty = existing.qty + qty;
            if (newQty <= product.stock) {
                existing.qty = newQty;
            } else {
                return { error: 'Stok tidak mencukupi!' };
            }
        } else {
            if (product.stock > 0) {
                if (qty > product.stock) {
                    return { error: 'Stok tidak mencukupi!' };
                }
                items.value.push({
                    id:           product.id,
                    name:         product.name,
                    price:        parseFloat(product.price) || 0,
                    qty:          qty,
                    stock:        product.stock,
                    image:        product.image ?? null,
                    variantLabel: product.variantLabel ?? null,
                });
            } else {
                return { error: 'Produk sedang habis.' };
            }
        }
        save();
        const label = product.variantLabel ? ` (${product.variantLabel})` : '';
        return { success: `${product.name}${label} ditambahkan ke keranjang!` };
    }

    function remove(id) {
        items.value = items.value.filter(i => i.id !== id);
        save();
    }

    function clear() {
        items.value = [];
        notes.value = '';
        save();
    }

    function increment(id) {
        const item = items.value.find(i => i.id === id);
        if (!item) return;
        if (item.qty < item.stock) {
            item.qty++;
            save();
            return { success: true };
        }
        return { error: 'Maksimal stok tercapai!' };
    }

    function decrement(id) {
        const item = items.value.find(i => i.id === id);
        if (!item) return;
        if (item.qty > 1) {
            item.qty--;
            save();
        } else {
            remove(id);
        }
    }

    function buildWhatsAppMessage(businessName) {
        let text = `Halo *${businessName}*,\nSaya ingin memesan:\n\n`;
        items.value.forEach((item, index) => {
            text += `${index + 1}. *${item.name}*\n`;
            text += `   Jumlah: ${item.qty} x Rp ${item.price.toLocaleString('id-ID')} = Rp ${(item.qty * item.price).toLocaleString('id-ID')}\n`;
        });
        text += `\n*Total Pesanan: Rp ${totalPrice.value.toLocaleString('id-ID')}*\n`;
        if (notes.value.trim()) {
            text += `\n*Catatan Tambahan:*\n_${notes.value.trim()}_\n`;
        }
        text += `\nApakah produk tersebut tersedia dan bisa dikirim ke alamat saya?`;
        return text;
    }

    function checkout(businessPhone, businessName = 'Rima Craft') {
        if (items.value.length === 0) return;
        const text = buildWhatsAppMessage(businessName);
        let phone = (businessPhone || '6281234567890').replace(/\D/g, '');
        if (phone.startsWith('0')) phone = '62' + phone.substring(1);
        window.open(`https://wa.me/${phone}?text=${encodeURIComponent(text)}`, '_blank');
    }

    return {
        items,
        notes,
        totalItems,
        totalPrice,
        add,
        remove,
        clear,
        increment,
        decrement,
        checkout,
        save,
    };
});
