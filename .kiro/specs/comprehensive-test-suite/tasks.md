# Implementation Plan: Comprehensive Test Suite

## Overview

Implementasi test suite komprehensif untuk proyek Rima Craft (Laravel 13 + PHPUnit 11) yang mencakup 15 database factory baru, 1 trait helper, 6 file Unit/Actions, 3 file Unit/Models, 3 file Unit/Requests, dan 9 file Feature Tests. Tidak ada dependency eksternal baru — murni menggunakan PHPUnit bawaan Laravel.

---

## Tasks

- [x] 1. Buat database factories untuk model-model core (tanpa relasi polimorfik)
  - [x] 1.1 Buat `AccountFactory`
    - Buat `database/factories/AccountFactory.php` dengan default state: `name='Kas Utama'`, `type='cash'`, `balance=1_000_000.00`
    - _Requirements: 1.4, 1.6_

  - [x] 1.2 Buat `ContactFactory`
    - Buat `database/factories/ContactFactory.php` dengan default state: `type='customer'`, `name=fake()->name()`, `email=fake()->unique()->safeEmail()`, `phone=fake()->numerify('08##########')`
    - _Requirements: 1.4, 1.6_

  - [x] 1.3 Buat `ProductFactory`
    - Buat `database/factories/ProductFactory.php` dengan default state: `name`, `base_price`, `reseller_price`, `current_stock=10..100`
    - Tambahkan state `out_of_stock()` → `current_stock = 0`
    - Tambahkan state `with_reseller_price($price)` → `reseller_price = $price`
    - _Requirements: 1.4, 1.6_

  - [x] 1.4 Buat `MaterialFactory`
    - Buat `database/factories/MaterialFactory.php` dengan default state: `name`, `unit` (random dari 'kg','meter','lembar','pcs'), `current_stock=20..200`, `last_buy_price`, `min_stock=5`
    - _Requirements: 1.4, 1.6_

  - [x] 1.5 Buat `RoleFactory` dan `PermissionFactory`
    - Buat `database/factories/RoleFactory.php` — tidak pakai random name, harus selalu di-override saat create (default `name='customer'`)
    - Buat `database/factories/PermissionFactory.php` dengan `name=fake()->unique()->slug(2)`
    - _Requirements: 1.4, 1.6_

- [x] 2. Buat database factories untuk model transaksi dan model polimorfik
  - [x] 2.1 Buat `SaleFactory` dan `SaleItemFactory`
    - Buat `database/factories/SaleFactory.php` dengan `payment_status='unpaid'`, `shipping_status='pending'`, `shipping_fee=0`, `discount=0`, `grand_total=fn(array $a) => $a['total_amount']`
    - Buat `database/factories/SaleItemFactory.php` dengan `qty`, `price`, `subtotal`
    - _Requirements: 1.4, 1.6_

  - [x] 2.2 Buat `PurchaseFactory` dan `PurchaseItemFactory`
    - Buat `database/factories/PurchaseFactory.php` dengan `payment_status='unpaid'`, `total_amount`
    - Buat `database/factories/PurchaseItemFactory.php` dengan `qty`, `price`, `subtotal`
    - _Requirements: 1.4, 1.6_

  - [x] 2.3 Buat `ProductionFactory`
    - Buat `database/factories/ProductionFactory.php` dengan semua cost fields default 0, `status='completed'`
    - _Requirements: 1.4, 1.6_

  - [x] 2.4 Buat `OrderFactory`
    - Buat `database/factories/OrderFactory.php` dengan `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `items` (array JSON default), `subtotal=100_000`, `shipping_cost=0`, `total=100_000`, `status='pending'`, `payment_status='unpaid'`, `payment_method='transfer'`, `order_method='form'`
    - Catatan: `province_id` dan `city_id` harus diisi manual via `Region::create()` atau seeder di test
    - _Requirements: 1.4, 1.6_

  - [x] 2.5 Buat `PaymentFactory`, `CashLedgerFactory`, dan `StockAdjustmentFactory`
    - Buat `database/factories/PaymentFactory.php` — polimorfik: `payable_type=Sale::class`, `payable_id=null` (SELALU di-override saat digunakan)
    - Buat `database/factories/CashLedgerFactory.php` dengan `type='in'`, `category='manual'`, `payment_label='Cash'`
    - Buat `database/factories/StockAdjustmentFactory.php` — polimorfik: `adjustable_type=Material::class`, `adjustable_id=null` (SELALU di-override)
    - _Requirements: 1.4, 1.6_

- [x] 3. Buat trait `CreatesTestData`
  - [x] 3.1 Implementasi trait `tests/Traits/CreatesTestData.php`
    - Buat direktori `tests/Traits/`
    - Implementasi method `createAdminUser(): User` — buat user + role `dev-admin`, attach via pivot
    - Implementasi method `createUserWithRole(string $roleName): User` — buat user + role via `Role::firstOrCreate`
    - Implementasi method `createUserWithPermission(string $permissionName): User` — buat user + role unik + permission via `Permission::firstOrCreate`, attach pivot
    - Implementasi method `createAccount(float $balance = 1_000_000): Account` — buat `Account` dengan nama 'Kas Utama'
    - _Requirements: 1.5_

- [x] 4. Implementasi Unit Tests — RecordSaleAction
  - [x] 4.1 Buat `tests/Unit/Actions/RecordSaleActionTest.php` dengan setup dan helper method
    - Tambahkan `declare(strict_types=1)`, `use RefreshDatabase, CreatesTestData`
    - Buat `setUp()`: instantiate `RecordSaleAction`, buat `Account` (balance=1_000_000), buat 2 `Product` (stock=10)
    - Buat private method `validSaleData(array $overrides = []): array` sebagai helper
    - _Requirements: 1.1, 1.2, 2.1_

  - [x] 4.2 Implementasi 8 test method utama di `RecordSaleActionTest`
    - `it_creates_sale_with_correct_grand_total` — assert `grand_total == total_amount + shipping_fee - discount`
    - `it_creates_sale_items_for_each_item_in_array` — assert `SaleItem::count() == count(items)`
    - `it_reduces_product_stock_after_sale` — assert stock berkurang sejumlah qty
    - `it_creates_payment_and_ledger_when_paid` — assert `Payment::count()==1`, `CashLedger::where('type','in')->count()==1`
    - `it_increases_account_balance_when_paid` — assert `$account->fresh()->balance == 1_000_000 + grand_total`
    - `it_does_not_create_payment_or_ledger_when_unpaid` — assert count == 0 untuk keduanya
    - `it_throws_exception_when_stock_is_insufficient` — `expectException(\Exception::class)`, assert pesan mengandung nama produk
    - `it_creates_customer_contact_when_save_customer_is_true` — assert `Contact::where('type','customer')->count()==1`
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_

  - [ ]* 4.3 Implementasi PBT method di `RecordSaleActionTest`
    - `it_grand_total_formula_always_holds` — loop 50 iterasi dengan kombinasi acak `total_amount`, `shipping_fee`, `discount`; assert formula selalu terpenuhi
    - **Property 1: `grand_total = total_amount + shipping_fee - discount`**
    - **Validates: Requirements 21.1**
    - _Requirements: 2.9, 21.1_

- [x] 5. Implementasi Unit Tests — RecordPurchaseAction
  - [x] 5.1 Buat `tests/Unit/Actions/RecordPurchaseActionTest.php`
    - `setUp()`: buat `Account`, 2 `Material` (stock=10), instantiate `RecordPurchaseAction`
    - Buat helper `validPurchaseData(array $overrides = []): array`
    - Implementasi 7 test method: create + items, stock increase, last_buy_price update, payment+ledger paid, balance decrease, no payment unpaid, supplier contact
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

  - [ ]* 5.2 Implementasi PBT method di `RecordPurchaseActionTest`
    - `it_total_amount_equals_sum_of_items` — loop 50 iterasi, generate random qty+price per item, assert `total_amount = SUM(qty_i * price_i)`
    - **Property 2: `total_amount = SUM(qty_i × price_i)`**
    - **Validates: Requirements 21.2**
    - _Requirements: 3.8, 21.2_

- [x] 6. Implementasi Unit Tests — RecordProductionAction
  - [x] 6.1 Buat `tests/Unit/Actions/RecordProductionActionTest.php`
    - `setUp()`: buat `Account`, 2 `Material` (stock=50, last_buy_price=5000), 1 `Product`, instantiate `RecordProductionAction`
    - Implementasi 8 test method: create records, material stock reduce, product stock increase, grand_total_cost formula, ledger `production_labor`, ledger `production_overhead`, ledger `production_material`, exception material insufficient
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8_

  - [ ]* 6.2 Implementasi PBT method di `RecordProductionActionTest`
    - `it_total_ledger_amount_equals_grand_total_cost` — loop 50 iterasi, generate random biaya, assert `SUM(CashLedger.amount) == grand_total_cost`
    - **Property 3: `SUM(ledger.amount) == grand_total_cost`**
    - **Validates: Requirements 21.3**
    - _Requirements: 4.9, 21.3_

- [x] 7. Implementasi Unit Tests — RecordPaymentAction
  - [x] 7.1 Buat `tests/Unit/Actions/RecordPaymentActionTest.php`
    - `setUp()`: buat `Account` (balance=2_000_000), `Sale` (grand_total=500_000, payment_status='unpaid'), `Purchase` (total_amount=300_000, payment_status='unpaid')
    - Implementasi 7 test method: payment+ledger-in for Sale, payment+ledger-out for Purchase, status-paid ketika lunas, status-partial ketika cicilan, balance decrease for Purchase, balance increase for Sale, exception saldo kurang
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7_

- [x] 8. Implementasi Unit Tests — RecordTransactionAction & AdjustStockAction
  - [x] 8.1 Buat `tests/Unit/Actions/RecordTransactionActionTest.php`
    - `setUp()`: buat `Account` (balance=500_000, name='Kas Utama')
    - Implementasi 6 test method: in ledger + balance increase, out ledger + balance decrease, balance_after correct for in, balance_after correct for out, exception saldo kurang, no balance change saat exception
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

  - [x] 8.2 Buat `tests/Unit/Actions/AdjustStockActionTest.php`
    - `setUp()`: buat `Material` (stock=10), `Product` (stock=20)
    - Implementasi 5 test method: adjustment + update material stock, adjustment + update product stock, quantity_difference = actual - previous, exception no change, no record created when unchanged
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

- [x] 9. Checkpoint — pastikan semua Unit Tests Actions lulus
  - Jalankan `php artisan test tests/Unit/Actions/ --stop-on-failure`
  - Pastikan semua test lulus, tidak ada error pada factory maupun trait
  - Tanyakan ke user jika ada masalah konfigurasi environment

- [x] 10. Implementasi Unit Tests — Model Methods
  - [x] 10.1 Buat `tests/Unit/Models/ProductGetPriceForUserTest.php`
    - Gunakan `RefreshDatabase` — test menyentuh tabel `product_user_prices` dan `product_region_prices`
    - `setUp()`: buat `Product` (base_price=100_000, reseller_price=80_000), buat `Region` province + city
    - Implementasi 6 test method: user-specific price (prioritas tertinggi), reseller + region kota reseller_price, reseller + fallback ke provinsi, reseller default price, customer biasa → base_price, null user → base_price
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_

  - [x] 10.2 Buat `tests/Unit/Models/OrderLifecycleTest.php`
    - Gunakan `RefreshDatabase`
    - Implementasi 11 test method: auto-generate order_number dengan format `ORD-YYYYMMDD-NNN`, uniqueness hari sama, `confirm()` → status+confirmed_at, `markProcessing()` → status, `markShipped()` → status+shipped_at, `complete()` → status+completed_at, `cancel(reason)` → status+cancelled_at+reason, `isPending()`, `isConfirmed()`, `isPaid()`, `isPartiallyPaid()`
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 9.8, 9.9, 9.10, 9.11_

  - [x] 10.3 Buat `tests/Unit/Models/UserRbacTest.php`
    - Gunakan `RefreshDatabase`
    - Implementasi 9 test method: hasRole true, hasRole false, dev-admin bypass all, hasPermission via role true, hasPermission false, hasAnyRole true, hasAnyRole false, hasAllPermissions true, hasAllPermissions false
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7, 10.8, 10.9_

- [x] 11. Implementasi Unit Tests — FormRequest Validation
  - [x] 11.1 Buat `tests/Unit/Requests/StoreSaleRequestTest.php`
    - Instantiate `StoreSaleRequest`, gunakan `Validator::make($data, $request->rules())` — tanpa HTTP request atau DB
    - Implementasi 4 test method: passes with valid data, fails without date or items, fails when qty is zero or negative, fails with invalid payment_status
    - _Requirements: 11.1, 11.2, 11.3, 11.4_

  - [x] 11.2 Buat `tests/Unit/Requests/StorePurchaseRequestTest.php`
    - Gunakan `RefreshDatabase` untuk test `it_fails_when_material_id_does_not_exist` (query `exists:materials,id`)
    - Implementasi 3 test method: passes with valid data, fails when material_id does not exist, fails without required fields
    - _Requirements: 11.5, 11.6_

  - [x] 11.3 Buat `tests/Unit/Requests/StoreProductRequestTest.php`
    - Instantiate `StoreProductRequest`, gunakan `Validator::make` langsung — tidak butuh DB
    - Implementasi 3 test method: passes with valid name+base_price+stock, fails without name or with negative base_price, fails when current_stock is non-integer
    - _Requirements: 11.7, 11.8, 11.9_

- [x] 12. Implementasi Feature Tests — Auth & Catalog
  - [x] 12.1 Buat `tests/Feature/Auth/AuthenticationTest.php`
    - Gunakan `RefreshDatabase, CreatesTestData`
    - Setup: buat admin user (`createAdminUser()`), buat customer user (`createUserWithRole('customer')`)
    - Implementasi 6 test scenario via `$this->post()`:
      - `POST /admin/login` kredensial valid admin → redirect `/dashboard` (302)
      - `POST /admin/login` password salah → redirect back dengan `errors.email`
      - `POST /logout` user autentikasi → redirect `/login`
      - `POST /register/customer` data valid → User+Contact dibuat, redirect customer dashboard
      - `POST /register/customer` email duplikat → `errors.email`
      - `POST /admin/login` user customer → redirect ke customer portal
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6_

  - [x] 12.2 Buat `tests/Feature/Catalog/CatalogPublicTest.php`
    - Gunakan `RefreshDatabase`
    - Setup: buat beberapa `Product` via factory termasuk state `out_of_stock()`
    - Implementasi 5 test method:
      - `GET /catalog` → 200, ada data products (tanpa autentikasi)
      - `GET /catalog?search=<nama_produk>` → hanya produk yang match (endpoint filter JSON)
      - `GET /catalog?stock=tersedia` → hanya produk `current_stock > 0`
      - `GET /catalog?stock=habis` → hanya produk `current_stock == 0`
      - `GET /catalog?search=<script>alert(1)</script>` → 422
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5_

- [ ] 13. Implementasi Feature Tests — Orders
  - [-] 13.1 Buat `tests/Feature/Orders/CheckoutOrderTest.php`
    - Gunakan `RefreshDatabase, CreatesTestData`
    - Setup: buat `PaymentMethod` (code='transfer'), buat `Region` (province + city via `Region::create()`), buat `Product`
    - Implementasi 7 test method:
      - `POST /order` guest + form → redirect sukses, `Order` dibuat status=pending
      - `POST /order` user login → `Order` terhubung `user_id`
      - `POST /order` dengan `create_account=true` → User+Contact dibuat, login otomatis
      - `POST /order` reseller + dp >= 30% → `payment_status=partial`, `remaining_balance` benar
      - `POST /order` reseller + dp < 30% → error `down_payment_amount`
      - `POST /order` items kosong → error "Keranjang belanja kosong."
      - Assert `order_number` format `ORD-YYYYMMDD-NNN` pada order berhasil
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5, 14.6, 14.7_

  - [ ] 13.2 Buat `tests/Feature/Orders/AdminOrderManagementTest.php`
    - Gunakan `RefreshDatabase, CreatesTestData`
    - Setup: buat user dengan permission `view-orders` dan `manage-orders`, buat beberapa `Order` via factory
    - Implementasi 5 test method:
      - `GET /admin/orders` dengan `view-orders` → 200
      - `GET /admin/orders` tanpa permission → 403
      - `PATCH /admin/orders/{id}/status` admin + status valid → redirect / 200
      - `DELETE /admin/orders/{id}` dengan `manage-orders` → redirect, soft deleted
      - `DELETE /admin/orders/{id}` tanpa permission → 403
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5_

- [~] 14. Checkpoint — pastikan semua Unit Tests dan Feature Auth/Catalog/Orders lulus
  - Jalankan `php artisan test tests/Unit/ tests/Feature/Auth/ tests/Feature/Catalog/ tests/Feature/Orders/ --stop-on-failure`
  - Pastikan semua test hijau sebelum lanjut ke Feature Tests modul lainnya
  - Tanyakan ke user jika ada masalah route, middleware, atau seeder

- [ ] 15. Implementasi Feature Tests — Sales & Purchases
  - [~] 15.1 Buat `tests/Feature/Sales/SaleModuleTest.php`
    - Gunakan `RefreshDatabase, CreatesTestData`
    - Setup: buat admin user dengan permission `view-sales` dan `manage-sales`, buat `Account`, buat `Product` (stock=10)
    - Implementasi 5 test method:
      - `GET /sales` dengan `view-sales` → 200
      - `GET /sales` tanpa autentikasi → 302 redirect ke login
      - `POST /sales` data valid + stok cukup → redirect `/sales`, `Sale` dibuat, stok berkurang
      - `POST /sales` stok kurang → error mengandung nama produk
      - `POST /sales` tanpa date/items → error validasi
    - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5_

  - [~] 15.2 Buat `tests/Feature/Purchases/PurchaseModuleTest.php`
    - Gunakan `RefreshDatabase, CreatesTestData`
    - Setup: buat admin user dengan permission `view-purchases` dan `manage-purchases`, buat `Account`, buat `Material` (stock=10)
    - Implementasi 3 test method:
      - `GET /purchases` dengan `view-purchases` → 200
      - `POST /purchases` data valid → redirect `/purchases`, `Purchase` dibuat, `current_stock` material bertambah
      - `POST /purchases` tanpa date/items → error validasi
    - _Requirements: 17.1, 17.2, 17.3_

- [ ] 16. Implementasi Feature Tests — Finance & Products
  - [~] 16.1 Buat `tests/Feature/Finance/FinanceModuleTest.php`
    - Gunakan `RefreshDatabase, CreatesTestData`
    - Setup: buat `Account` (balance=500_000, name='Kas Utama'), buat admin dengan permission `view-finance` dan `manage-finance`
    - Implementasi 4 test method:
      - `GET /finance` dengan `view-finance` → 200
      - `GET /finance` tanpa permission → 403
      - `POST /finance/transactions` type=in valid → `CashLedger` dibuat, balance naik
      - `POST /finance/transactions` type=out saldo kurang → error saldo tidak mencukupi
    - _Requirements: 18.1, 18.2, 18.3, 18.4_

  - [~] 16.2 Buat `tests/Feature/Products/ProductCrudTest.php`
    - Gunakan `RefreshDatabase, CreatesTestData`
    - Setup: buat admin dengan permission `manage-products`
    - Implementasi 6 test method:
      - `GET /products` admin → 200
      - `POST /products` data valid → redirect `/products`, `Product` dibuat
      - `POST /products` tanpa name / base_price negatif → error validasi
      - `PATCH /products/{id}` data valid → `Product` diperbarui
      - `DELETE /products/{id}` admin → soft deleted, redirect `/products`
      - `POST /products` tanpa `manage-products` → 403
    - _Requirements: 19.1, 19.2, 19.3, 19.4, 19.5, 19.6_

- [ ] 17. Implementasi Feature Tests — Stock Adjustment
  - [~] 17.1 Buat `tests/Feature/Stock/StockAdjustmentTest.php`
    - Gunakan `RefreshDatabase, CreatesTestData`
    - Setup: buat admin user dengan permission `manage-stock`, buat `Material` (stock=10) dan `Product` (stock=20)
    - Implementasi 3 test method:
      - `POST /stock-adjustments` actual_stock berbeda → `StockAdjustment` dibuat, `current_stock` diperbarui
      - `POST /stock-adjustments` actual_stock sama → error "Tidak ada perubahan stok."
      - `POST /stock-adjustments` tanpa autentikasi → 302 redirect ke login
    - _Requirements: 20.1, 20.2, 20.3_

- [~] 18. Verifikasi konfigurasi `phpunit.xml` dan setup environment test
  - Periksa `phpunit.xml` — pastikan `testSuites` sudah mencakup direktori `tests/Unit` dan `tests/Feature` beserta subdirektori baru
  - Pastikan environment `APP_ENV=testing` dan `DB_CONNECTION=sqlite` (in-memory) terkonfigurasi untuk test
  - Verifikasi `tests/TestCase.php` tidak perlu diubah
  - _Requirements: 1.1, 1.3, 1.5_

- [~] 19. Final Checkpoint — jalankan full test suite
  - Jalankan `php artisan test --stop-on-failure` untuk menjalankan semua test
  - Pastikan seluruh test hijau (Unit + Feature)
  - Tanyakan ke user jika ada test yang perlu di-skip atau ada penyesuaian scope

---

## Notes

- Task dengan tanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Setiap task mereferensi requirement spesifik untuk traceability
- Checkpoint memastikan validasi inkremental — jangan lanjut ke fase berikutnya jika ada test merah
- PBT diimplementasi sebagai loop statistik (50 iterasi) menggunakan `fake()` bawaan Laravel — tidak butuh library PBT eksternal
- Factory polimorfik (`PaymentFactory`, `StockAdjustmentFactory`) **HARUS** selalu di-override `_type` dan `_id` saat digunakan di test
- Untuk `CheckoutOrderTest`, data `Region` dibuat langsung via `Region::create()` karena tabel `regions` diisi seeder, bukan factory

---

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.3", "1.4", "1.5"] },
    { "id": 1, "tasks": ["2.1", "2.2", "2.3", "2.4", "2.5"] },
    { "id": 2, "tasks": ["3.1"] },
    { "id": 3, "tasks": ["4.1", "5.1", "6.1", "7.1", "8.1", "8.2", "11.1", "11.3"] },
    { "id": 4, "tasks": ["4.2", "5.2", "6.2", "11.2"] },
    { "id": 5, "tasks": ["4.3", "10.1", "10.2", "10.3"] },
    { "id": 6, "tasks": ["12.1", "12.2"] },
    { "id": 7, "tasks": ["13.1", "13.2"] },
    { "id": 8, "tasks": ["15.1", "15.2", "16.1", "16.2", "17.1"] },
    { "id": 9, "tasks": ["18.1"] }
  ]
}
```
