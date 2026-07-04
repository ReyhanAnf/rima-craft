# Implementation Plan: User Order History & Tracking

## Overview

Tiga area kerja utama:

1. **Database** — Tambah kolom `tracking_number`, `down_payment_amount`, `remaining_balance` ke tabel `orders`
2. **My Orders (User-facing)** — Halaman `/my-orders` (list + detail) menggunakan Vue+Inertia,
   link di Navbar, link di halaman sukses
3. **Admin Enhancement** — Input nomor resi di panel admin, tampil info DP, badge "DP" di list order
4. **Checkout DP** — Opsi bayar DP untuk partner di `CheckoutPage.vue`

Stack: Vue 3 + Inertia.js untuk halaman user-facing baru; Blade+HTMX untuk panel admin.

---

## Tasks

- [x] 1. Database — Migration & Model Update

  - [x] 1.1 Buat migration baru: `add_tracking_and_dp_fields_to_orders_table`
    - File: `database/migrations/YYYY_MM_DD_xxxxxx_add_tracking_and_dp_fields_to_orders_table.php`
    - Tambahkan kolom setelah `shipping_cost`:
      - `down_payment_amount` — `decimal(15,2)`, default `0`
      - `remaining_balance` — `decimal(15,2)`, default `0`
      - `tracking_number` — `string(100)`, nullable, after `remaining_balance`
    - Jalankan `php artisan migrate` untuk verifikasi tidak ada error

  - [x] 1.2 Update `app/Models/Order.php`
    - Tambahkan ke `$fillable`: `'down_payment_amount'`, `'remaining_balance'`, `'tracking_number'`
    - Tambahkan ke `$casts`: `'down_payment_amount' => 'decimal:2'`, `'remaining_balance' => 'decimal:2'`
    - Tambahkan helper method `isPartiallyPaid(): bool` — `return $this->payment_status === 'partial'`
    - Tambahkan scope `scopePartial($query)` — `return $query->where('payment_status', 'partial')`
    - _Requirements: 3.1, 4.4_

---

- [x] 2. Backend — Routes & Controller

  - [x] 2.1 Tambah route My Orders ke `routes/customer.php`
    - Di dalam blok `Route::middleware('auth')`, tambahkan:
      ```php
      Route::prefix('my-orders')->name('my-orders.')->controller(MyOrderController::class)->group(function () {
          Route::get('/', 'index')->name('index');
          Route::get('/{orderNumber}', 'show')->name('show');
      });
      ```
    - Import: `use App\Http\Controllers\MyOrderController;`
    - _Requirements: 1.1, 1.5, 2.1_

  - [x] 2.2 Buat `app/Http/Controllers/MyOrderController.php`
    - Tambahkan `declare(strict_types=1)` di baris pertama
    - Namespace: `App\Http\Controllers`
    - Method `index(Request $request): InertiaResponse`:
      - Query: `Order::where('user_id', Auth::id())->latest()->paginate(10)->withQueryString()`
      - Return: `Inertia::render('MyOrders/Index', ['orders' => $orders])`
    - Method `show(string $orderNumber): InertiaResponse`:
      - Query: `Order::where('order_number', $orderNumber)->where('user_id', Auth::id())->firstOrFail()`
      - Jika tidak ditemukan (order milik orang lain): `abort(403)`
      - Return: `Inertia::render('MyOrders/Show', ['order' => $order])`
    - _Requirements: 1.1, 1.3, 2.1, 2.4_

  - [x] 2.3 Update `OrderController::checkout()` — pass flag `isPartner`
    - File: `app/Http/Controllers/OrderController.php`
    - Di method `checkout()`, tambahkan ke props Inertia:
      ```php
      'isPartner' => auth()->check() && auth()->user()->hasRole('partner'),
      ```
    - _Requirements: 4.1_

  - [x] 2.4 Update `OrderController::store()` — support DP
    - File: `app/Http/Controllers/OrderController.php`
    - Tambahkan ke `$request->validate()`:
      ```
      'payment_mode'        => 'nullable|in:full,dp',
      'down_payment_amount' => 'nullable|numeric|min:0',
      ```
    - Setelah validasi items, tambahkan logika DP:
      ```php
      $paymentMode = $request->input('payment_mode', 'full');
      $downPayment = 0;
      $remaining   = 0;
      $paymentStatus = 'unpaid';

      if ($paymentMode === 'dp' && auth()->user()?->hasRole('partner')) {
          $total = (float) $validated['total'];
          $dp    = (float) ($validated['down_payment_amount'] ?? 0);
          if ($dp < $total * 0.3) {
              return back()->withErrors(['down_payment_amount' => 'DP minimal 30% dari total order.'])->withInput();
          }
          $downPayment   = min($dp, $total);
          $remaining     = max(0, $total - $downPayment);
          $paymentStatus = $remaining > 0 ? 'partial' : 'unpaid';
      }
      ```
    - Tambahkan ke `Order::create([...])`:
      - `'down_payment_amount' => $downPayment`
      - `'remaining_balance'   => $remaining`
      - `'payment_status'      => $paymentStatus`
    - _Requirements: 4.3, 4.5, 4.6_

  - [x] 2.5 Update `AdminOrderController::updateStatus()` — support resi & partial
    - File: `app/Http/Controllers/AdminOrderController.php`
    - Tambahkan ke validation:
      ```
      'tracking_number' => 'nullable|string|max:100',
      'payment_status'  => 'nullable|in:unpaid,paid,partial,refunded',
      ```
    - Setelah update payment_status, tambahkan:
      ```php
      if ($request->filled('tracking_number')) {
          $order->update(['tracking_number' => $validated['tracking_number']]);
      }
      ```
    - Update case `$newPaymentStatus === 'paid'` — jika `$order->remaining_balance > 0`,
      jumlah kas yang dicatat = `$order->remaining_balance` (bukan `$order->total` untuk
      hindari dobel pencatatan DP yang mungkin sudah dicatat terpisah).
      Setelah update ke `paid`, set `$order->update(['remaining_balance' => 0])`.
    - _Requirements: 3.3, 3.4, 4.5_

---

- [x] 3. Vue Pages — My Orders (User-Facing)

  - [x] 3.1 Buat `resources/js/pages/MyOrders/Index.vue`
    - Props: `orders` (Laravel paginator object)
    - Layout: `PublicLayout` (sudah ada di `resources/js/layouts/PublicLayout.vue`)
    - Tabel pesanan dengan kolom: Nomor Pesanan, Tanggal, Items (count), Total, Status, Status Bayar,
      Resi, Aksi (link detail)
    - Badge status: warna-warni sesuai nilai (`pending` abu, `confirmed` biru, `shipped` cyan,
      `completed` hijau, `cancelled` merah)
    - Badge payment_status: `unpaid` merah, `paid` hijau, `partial` amber dengan label "DP"
    - Jika `tracking_number` ada: tampilkan nomor resi (bisa diklik copy)
    - Pagination: gunakan `<Link>` dari Inertia untuk navigasi halaman
    - Empty state: ilustrasi + "Belum ada pesanan" + tombol "Mulai Belanja" ke katalog
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 3.2 Buat `resources/js/pages/MyOrders/Show.vue`
    - Props: `order` (object Order lengkap)
    - Layout: `PublicLayout`
    - Section 1: **Header** — nomor pesanan, tanggal, badge status
    - Section 2: **Item Pesanan** — tabel produk (nama, qty, harga satuan, subtotal)
    - Section 3: **Ringkasan Pembayaran**
      - Grand Total
      - Jika `order.down_payment_amount > 0`: tampilkan baris "DP Dibayar" (hijau) dan "Sisa Piutang" (amber)
      - Metode pembayaran
    - Section 4: **Nomor Resi** — tampil jika `order.tracking_number` tidak null
      - Tampilkan nomor resi dengan tombol copy-to-clipboard (Alpine.js atau native JS)
      - Link opsional ke Google: `https://www.google.com/search?q={tracking_number}+tracking`
    - Section 5: **Timeline Status** — progress steps visual:
      Dibuat → Dikonfirmasi → Diproses → Dikirim → Selesai
      Tiap step aktif jika timestamp-nya tidak null (`confirmed_at`, `shipped_at`, dll)
    - Tombol "Kembali ke Pesanan Saya" (`Link` ke `/my-orders`)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

---

- [x] 4. Vue Updates — Navbar & Success Page

  - [x] 4.1 Update `resources/js/components/Navbar.vue` — link "Pesanan Saya"
    - Tambahkan prop atau gunakan `usePage().props.auth.user` untuk cek apakah user login
    - Jika `auth.user` tidak null: tampilkan link "Pesanan Saya" di sebelah ikon cart
      ```html
      <Link v-if="page.props.auth?.user" :href="route('my-orders.index')"
            class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 ...">
        Pesanan Saya
      </Link>
      ```
    - Import `Link` dari `@inertiajs/vue3` dan `usePage` jika belum ada
    - _Requirements: 5.1, 5.2_

  - [x] 4.2 Update `resources/js/pages/OrderSuccessPage.vue` — link ke My Orders
    - Di section action buttons (sudah ada "Lanjutkan Belanja" dan "Konfirmasi WA"), tambahkan tombol ketiga:
      ```html
      <Link v-if="page.props.auth?.user" :href="route('my-orders.index')"
            class="flex-1 flex items-center justify-center gap-2 py-4 px-6 bg-gray-100 dark:bg-gray-800 ...">
        Lihat Riwayat Pesanan
      </Link>
      ```
    - _Requirements: 5.3_

---

- [x] 5. Admin Panel — Blade Updates

  - [-] 5.1 Update `resources/views/orders/orders-show.blade.php` — input nomor resi
    - Di form update status (blok `x-data="{ selectedStatus: ... }"`), tambahkan field nomor resi
      yang muncul saat `selectedStatus === 'shipped'` atau sudah ada `tracking_number`:
      ```blade
      <div x-show="selectedStatus === 'shipped' || '{{ $order->tracking_number }}' !== ''"
           x-transition class="col-span-2">
          <label class="block text-xs font-semibold text-gray-500 ...">Nomor Resi</label>
          <input type="text" name="tracking_number"
                 value="{{ $order->tracking_number }}"
                 placeholder="Contoh: JNE123456789"
                 class="w-full text-xs border rounded px-2.5 py-1.5 ...">
      </div>
      ```
    - Di section "Riwayat & Log Status", jika `$order->tracking_number`, tampilkan info resi:
      ```blade
      @if($order->tracking_number)
      <li class="ml-4">
          <span class="absolute -left-[4.5px] mt-1.5 w-2.5 h-2.5 bg-cyan-500 rounded-full"></span>
          <p class="text-xs font-semibold text-gray-900 dark:text-white">Nomor Resi</p>
          <span class="text-[10px] font-mono text-cyan-600">{{ $order->tracking_number }}</span>
      </li>
      @endif
      ```
    - _Requirements: 3.2, 3.3, 3.5_

  - [x] 5.2 Update `resources/views/orders/orders-show.blade.php` — info DP
    - Di section "Rincian Belanja", setelah baris Grand Total, tambahkan:
      ```blade
      @if($order->down_payment_amount > 0)
      <div class="mt-3 pt-3 border-t border-amber-100 dark:border-amber-900/30 space-y-1.5 text-xs">
          <div class="flex justify-end gap-6 font-semibold text-emerald-700 dark:text-emerald-400">
              <span>DP Dibayar:</span>
              <span>Rp {{ number_format($order->down_payment_amount, 0, ',', '.') }}</span>
          </div>
          <div class="flex justify-end gap-6 font-bold text-amber-700 dark:text-amber-400">
              <span>Sisa Piutang:</span>
              <span>Rp {{ number_format($order->remaining_balance, 0, ',', '.') }}</span>
          </div>
      </div>
      @endif
      ```
    - Di dropdown Status Bayar, tambahkan option `partial`:
      ```blade
      <option value="partial" {{ $order->payment_status === 'partial' ? 'selected' : '' }}>DP / Partial</option>
      ```
    - _Requirements: 3.5, 4.5_

  - [x] 5.3 Update `resources/views/orders/orders-list.blade.php` — badge DP
    - Di kolom payment_status, tambahkan case untuk `partial`:
      ```blade
      @elseif($order->payment_status === 'partial')
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 ring-1 ring-amber-300">
              DP
          </span>
      ```
    - _Requirements: (admin visibility)_

  - [x] 5.4 Update `resources/views/orders/orders-index.blade.php` — filter partial
    - Di dropdown filter Status Pembayaran, tambahkan:
      ```blade
      <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>DP / Partial</option>
      ```
    - _Requirements: (admin filter)_

---

- [x] 6. Vue Updates — Checkout DP

  - [x] 6.1 Tambah opsi DP ke `resources/js/pages/CheckoutPage.vue`
    - Tambahkan ke `defineProps`: `isPartner: { type: Boolean, default: false }`
    - Tambahkan reactive state: `const paymentMode = ref('full')`, `const dpAmount = ref(0)`,
      `const dpError = ref('')`
    - Tambahkan ke `useForm`: `payment_mode: 'full'`, `down_payment_amount: 0`
    - Tambahkan computed `remainingBalance`:
      ```js
      const remainingBalance = computed(() =>
        paymentMode.value === 'dp' ? Math.max(0, cart.totalPrice - dpAmount.value) : 0
      )
      ```
    - Tambahkan fungsi `validateDp()`:
      ```js
      function validateDp() {
        const min = cart.totalPrice * 0.3
        dpError.value = dpAmount.value < min
          ? `DP minimal ${formatPrice(min)} (30% dari total)`
          : ''
      }
      ```
    - Update `canSubmit` computed:
      ```js
      const canSubmit = computed(() =>
        cart.items.length > 0 &&
        form.payment_method !== '' &&
        (paymentMode.value !== 'dp' || (dpAmount.value >= cart.totalPrice * 0.3 && !dpError.value))
      )
      ```
    - Di fungsi `submit()`, tambahkan sebelum `form.post()`:
      ```js
      form.payment_mode        = paymentMode.value
      form.down_payment_amount = paymentMode.value === 'dp' ? dpAmount.value : 0
      ```
    - _Requirements: 4.1, 4.2, 4.3, 4.6_

  - [x] 6.2 Tambah UI opsi DP di template `CheckoutPage.vue`
    - Tambahkan section setelah blok "Metode Pembayaran", hanya tampil jika `isPartner`:
      ```html
      <div v-if="isPartner" class="space-y-4 p-5 bg-blue-50 dark:bg-blue-900/10 rounded-xl border border-blue-200 dark:border-blue-800/50">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Opsi Pembayaran Partner</h3>
        <div class="flex gap-3">
          <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 flex-1 transition-all"
                 :class="paymentMode === 'full' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700'">
            <input type="radio" v-model="paymentMode" value="full" class="w-4 h-4 text-amber-600" />
            <div>
              <p class="text-sm font-bold text-gray-900 dark:text-white">Bayar Lunas</p>
              <p class="text-xs text-gray-500">Bayar penuh sekarang</p>
            </div>
          </label>
          <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 flex-1 transition-all"
                 :class="paymentMode === 'dp' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700'">
            <input type="radio" v-model="paymentMode" value="dp" class="w-4 h-4 text-amber-600" />
            <div>
              <p class="text-sm font-bold text-gray-900 dark:text-white">Bayar DP</p>
              <p class="text-xs text-gray-500">Min. 30% di muka</p>
            </div>
          </label>
        </div>

        <Transition name="expand">
          <div v-if="paymentMode === 'dp'" class="space-y-3 pt-3 border-t border-blue-200 dark:border-blue-800/50">
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Nominal DP <span class="text-red-500">*</span>
              </label>
              <input type="number" v-model.number="dpAmount" @input="validateDp" min="0"
                     class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
                     placeholder="Masukkan nominal DP" />
              <p v-if="dpError" class="mt-1 text-xs text-red-500">{{ dpError }}</p>
            </div>
            <div class="flex justify-between text-sm font-semibold p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
              <span class="text-gray-700 dark:text-gray-300">Sisa Piutang:</span>
              <span class="text-amber-700 dark:text-amber-400">{{ formatPrice(remainingBalance) }}</span>
            </div>
          </div>
        </Transition>
      </div>
      ```
    - _Requirements: 4.1, 4.2, 4.3_

---

- [x] 7. Checkpoint — Verifikasi Manual

  - Login sebagai user biasa (customer), buka `/my-orders` → halaman riwayat pesanan tampil
  - Buat pesanan baru, cek riwayat → order baru muncul di list
  - Klik detail order → semua informasi tampil benar termasuk timeline
  - Login sebagai admin, update status order ke "shipped" → input nomor resi → simpan
  - Kembali login sebagai customer, buka detail order → nomor resi tampil
  - Login sebagai partner, buka checkout → opsi DP muncul
  - Pilih DP dengan nominal < 30% → error validasi tampil
  - Pilih DP valid → sisa piutang terhitung → submit → order tersimpan dengan `payment_status = 'partial'`
  - Admin buka order DP → tampil info DP Dibayar & Sisa Piutang
  - Di list order admin → badge "DP" muncul pada order partial

---

- [ ] 8. Docs — Update Progress

  - [x] 8.1 Buat `docs/checkpoints/CP-023-user-order-history-tracking.md`
    - Dokumentasikan: fitur My Orders, tracking resi, DP payment
    - Catat file yang dibuat/dimodifikasi
  - [x] 8.2 Update `docs/progress/current-status.md`

---

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2"] },
    { "id": 1, "tasks": ["2.1", "2.2", "2.3", "2.4", "2.5"] },
    { "id": 2, "tasks": ["3.1", "3.2"] },
    { "id": 3, "tasks": ["4.1", "4.2"] },
    { "id": 4, "tasks": ["5.1", "5.2", "5.3", "5.4"] },
    { "id": 5, "tasks": ["6.1", "6.2"] },
    { "id": 6, "tasks": ["8.1", "8.2"] }
  ]
}
```

## Notes

- `MyOrderController` menggunakan Inertia karena My Orders adalah halaman user-facing yang sudah Vue+Inertia
- Panel admin (tasks 5.x) tetap Blade+HTMX — tidak ada perubahan arsitektur admin
- `tracking_number` tidak diintegrasikan ke API kurir eksternal — hanya input manual, user googling sendiri
- DP hanya berlaku untuk partner, validasi di controller sebagai defense-in-depth (meski Vue sudah validate)
- Halaman My Orders bisa diakses customer biasa DAN partner — semua user login bisa lihat pesanan mereka
