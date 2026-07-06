# Design Document — Comprehensive Test Suite

## Architecture

Test suite dibangun di atas PHPUnit 11 (bawaan Laravel 13) tanpa dependency eksternal tambahan. Arsitektur mengikuti dua lapisan:

1. **Unit Layer** — test terisolasi untuk satu class (Action, Model, FormRequest). Tidak ada HTTP request. Semua menggunakan database via `DatabaseTransactions` — setiap test dibungkus dalam transaksi yang di-rollback otomatis, tanpa drop/recreate tabel.
2. **Feature Layer** — test end-to-end yang menembak HTTP endpoint, memeriksa response dan efek samping database.

Setiap test class menggunakan trait `CreatesTestData` sebagai helper terpusat. Database factories di `database/factories/` menyediakan fixture data untuk setiap model.

---

## Components and Interfaces

| Komponen | Lokasi | Tanggung Jawab |
|----------|--------|----------------|
| Factories | `database/factories/` | Generate data model tiruan yang valid untuk test |
| Trait `CreatesTestData` | `tests/Traits/CreatesTestData.php` | Helper methods: `createAdminUser()`, `createUserWithRole()`, `createUserWithPermission()`, `createAccount()` |
| Unit Tests — Actions | `tests/Unit/Actions/` | Verifikasi logika bisnis tiap Action class |
| Unit Tests — Models | `tests/Unit/Models/` | Verifikasi method model: pricing, lifecycle, RBAC |
| Unit Tests — Requests | `tests/Unit/Requests/` | Verifikasi aturan validasi FormRequest |
| Feature Tests | `tests/Feature/` | Verifikasi HTTP endpoint, redirect, dan efek samping DB |

---

## Data Models

Test suite tidak mendefinisikan model baru. Semua model yang diuji adalah model aplikasi yang sudah ada:

- `Account` — saldo kas, diakses oleh semua Action keuangan
- `Product` — stok produk, harga berjenjang
- `Material` — stok bahan baku
- `Sale`, `SaleItem` — transaksi penjualan
- `Purchase`, `PurchaseItem` — transaksi pembelian
- `Production`, `ProductionMaterial`, `ProductionResult` — proses produksi
- `Payment` (polimorfik) — catatan pembayaran untuk Sale/Purchase/Production/Order
- `CashLedger` — buku kas dengan category constants
- `Order` — pesanan publik dari katalog
- `User`, `Role`, `Permission` — RBAC
- `Contact` — data kontak customer/supplier
- `StockAdjustment` (polimorfik) — log penyesuaian stok
- `ProductUserPrice`, `ProductRegionPrice` — harga berjenjang produk

Relasi polimorfik yang perlu perhatian saat factory:
- `Payment::payable` → Sale | Purchase | Production | Order
- `StockAdjustment::adjustable` → Material | Product
- `CashLedger::reference` → Payment | Production

---

## Overview

Dokumen ini merancang arsitektur, struktur direktori, pola implementasi, dan panduan penulisan kode untuk test suite komprehensif proyek Rima Craft (Laravel 13, PHPUnit 11). Test suite mencakup Unit Tests, Feature Tests, dan Property-Based Testing (PBT) untuk memvalidasi seluruh logika bisnis kritis.

---

## 1. Struktur Direktori

```
tests/
├── TestCase.php                        # Base TestCase (sudah ada)
├── Unit/
│   ├── Actions/
│   │   ├── RecordSaleActionTest.php
│   │   ├── RecordPurchaseActionTest.php
│   │   ├── RecordProductionActionTest.php
│   │   ├── RecordPaymentActionTest.php
│   │   ├── RecordTransactionActionTest.php
│   │   └── AdjustStockActionTest.php
│   ├── Models/
│   │   ├── ProductGetPriceForUserTest.php
│   │   ├── OrderLifecycleTest.php
│   │   └── UserRbacTest.php
│   └── Requests/
│       ├── StoreSaleRequestTest.php
│       ├── StorePurchaseRequestTest.php
│       └── StoreProductRequestTest.php
└── Feature/
    ├── Auth/
    │   └── AuthenticationTest.php
    ├── Catalog/
    │   └── CatalogPublicTest.php
    ├── Orders/
    │   ├── CheckoutOrderTest.php
    │   └── AdminOrderManagementTest.php
    ├── Sales/
    │   └── SaleModuleTest.php
    ├── Purchases/
    │   └── PurchaseModuleTest.php
    ├── Finance/
    │   └── FinanceModuleTest.php
    ├── Products/
    │   └── ProductCrudTest.php
    └── Stock/
        └── StockAdjustmentTest.php

database/factories/
├── UserFactory.php                     # sudah ada
├── AccountFactory.php                  # baru
├── ContactFactory.php                  # baru
├── ProductFactory.php                  # baru
├── MaterialFactory.php                 # baru
├── SaleFactory.php                     # baru
├── SaleItemFactory.php                 # baru
├── PurchaseFactory.php                 # baru
├── PurchaseItemFactory.php             # baru
├── ProductionFactory.php               # baru
├── OrderFactory.php                    # baru
├── RoleFactory.php                     # baru
├── PermissionFactory.php               # baru
├── PaymentFactory.php                  # baru
├── CashLedgerFactory.php               # baru
└── StockAdjustmentFactory.php          # baru
```

---

## 2. Standar Penulisan Test (Wajib)

Setiap file test PHP **harus** mengikuti template berikut:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use Illuminate\Foundation\Testing\DatabaseTransactions; // semua test — unit dan feature
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecordSaleActionTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    #[Test]
    public function it_does_something(): void
    {
        // Arrange
        // Act
        // Assert
    }
}
```

Aturan:
- `declare(strict_types=1)` di baris pertama setiap file test
- Atribut `#[Test]` (bukan `@test` docblock)
- **Semua test** (Unit maupun Feature) menggunakan `DatabaseTransactions` — bukan `RefreshDatabase`
- `DatabaseTransactions` membungkus setiap test dalam transaksi DB yang di-rollback setelah test selesai; tidak ada drop/recreate tabel, sehingga lebih cepat dan aman untuk database yang sudah terisi data
- Pola AAA (Arrange / Act / Assert) pada setiap metode test
- Nama metode deskriptif: `it_creates_a_sale_when_stock_is_sufficient()`

---

## 3. Desain Database Factories

Setiap factory harus menghasilkan data valid yang konsisten dengan constraint database. Berikut definisi state penting setiap factory:

### AccountFactory
```php
// Default state: akun kas utama dengan saldo 1.000.000
[
    'name'    => 'Kas Utama',
    'type'    => 'cash',
    'balance' => 1_000_000.00,
]
```

### ProductFactory
```php
// Default state: produk aktif dengan stok cukup
[
    'name'          => fake()->words(3, true),
    'base_price'    => fake()->randomFloat(2, 10_000, 500_000),
    'reseller_price'=> fake()->randomFloat(2, 8_000, 400_000),
    'current_stock' => fake()->numberBetween(10, 100),
]
// State: out_of_stock → current_stock = 0
// State: with_reseller_price($price) → reseller_price = $price
```

### MaterialFactory
```php
// Default state: bahan baku dengan stok memadai
[
    'name'           => fake()->word() . ' bahan',
    'unit'           => fake()->randomElement(['kg', 'meter', 'lembar', 'pcs']),
    'current_stock'  => fake()->numberBetween(20, 200),
    'last_buy_price' => fake()->randomFloat(2, 1_000, 50_000),
    'min_stock'      => 5,
]
```

### RoleFactory
```php
// Requires name to be passed explicitly — no random names
[
    'name' => 'customer', // pass as argument: Role::factory()->create(['name' => 'admin'])
]
```

### ContactFactory
```php
[
    'type'  => 'customer',
    'name'  => fake()->name(),
    'email' => fake()->unique()->safeEmail(),
    'phone' => fake()->numerify('08##########'),
]
```

### SaleFactory
```php
[
    'date'           => now()->format('Y-m-d'),
    'total_amount'   => fake()->randomFloat(2, 50_000, 1_000_000),
    'shipping_fee'   => 0,
    'discount'       => 0,
    'grand_total'    => fn(array $a) => $a['total_amount'],
    'payment_status' => 'unpaid',
    'shipping_status'=> 'pending',
]
```

### PurchaseFactory
```php
[
    'date'           => now()->format('Y-m-d'),
    'total_amount'   => fake()->randomFloat(2, 50_000, 500_000),
    'payment_status' => 'unpaid',
]
```

### ProductionFactory
```php
[
    'date'                => now()->format('Y-m-d'),
    'labor_cost'          => 0,
    'overhead_cost'       => 0,
    'additional_cost'     => 0,
    'total_material_cost' => 0,
    'grand_total_cost'    => 0,
    'status'              => 'completed',
]
```

### OrderFactory
```php
[
    'customer_name'  => fake()->name(),
    'customer_phone' => fake()->numerify('08##########'),
    'customer_email' => fake()->safeEmail(),
    'customer_address'=> fake()->address(),
    'items'          => [['id' => 1, 'name' => 'Produk', 'qty' => 1, 'price' => 100000, 'subtotal' => 100000]],
    'subtotal'       => 100_000,
    'shipping_cost'  => 0,
    'total'          => 100_000,
    'status'         => 'pending',
    'payment_status' => 'unpaid',
    'payment_method' => 'transfer',
    'order_method'   => 'form',
]
// province_id dan city_id harus di-setup via seeder atau migration fixture karena
// tabel regions diisi seeder, bukan factory
```

### PaymentFactory
```php
// Polymorphic: harus diberikan payable_type dan payable_id saat create
[
    'date'         => now()->format('Y-m-d'),
    'amount'       => fake()->randomFloat(2, 10_000, 500_000),
    'payable_type' => Sale::class,  // default, override saat digunakan
    'payable_id'   => null,         // SELALU override saat digunakan
]
```

### CashLedgerFactory
```php
[
    'date'        => now()->format('Y-m-d'),
    'type'        => 'in',
    'category'    => 'manual',
    'amount'      => fake()->randomFloat(2, 10_000, 100_000),
    'balance_after'=> fake()->randomFloat(2, 100_000, 1_000_000),
    'description' => fake()->sentence(),
    'payment_label'=> 'Cash',
]
```

### StockAdjustmentFactory
```php
// Polymorphic: harus diberikan adjustable_type dan adjustable_id saat create
[
    'previous_stock'      => 10,
    'actual_stock'        => 15,
    'quantity_difference' => 5,
    'reason'              => 'Stok opname bulanan',
    'adjustable_type'     => Material::class, // default
    'adjustable_id'       => null,            // SELALU override
]
```

---

## 4. Pola Test Helper — Trait `CreatesTestData`

Untuk menghindari duplikasi setup berulang di setiap test class, buat trait reusable di `tests/`:

```php
// tests/Traits/CreatesTestData.php
trait CreatesTestData
{
    protected function createAdminUser(): User
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'dev-admin']);
        $user->roles()->attach($role->id);
        return $user;
    }

    protected function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role->id);
        return $user;
    }

    protected function createUserWithPermission(string $permissionName): User
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'operator-test-' . $permissionName]);
        $permission = Permission::firstOrCreate(['name' => $permissionName]);
        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id);
        return $user;
    }

    protected function createAccount(float $balance = 1_000_000): Account
    {
        return Account::factory()->create(['balance' => $balance, 'name' => 'Kas Utama']);
    }
}
```

Gunakan trait ini di setiap test class yang memerlukannya:
```php
class RecordSaleActionTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;
}
```

---

## 5. Desain Unit Tests — Action Classes

### 5.1 RecordSaleActionTest

**File:** `tests/Unit/Actions/RecordSaleActionTest.php`

**Setup (setUp method):**
- Buat `Account` dengan `balance = 1_000_000`
- Buat 2 `Product` dengan `current_stock = 10`
- Instantiate `RecordSaleAction`

**Data helper `validSaleData()`:**
```php
private function validSaleData(array $overrides = []): array
{
    return array_merge([
        'date'            => now()->format('Y-m-d'),
        'payment_status'  => 'unpaid',
        'shipping_status' => 'pending',
        'shipping_fee'    => 0,
        'discount'        => 0,
        'items'           => [
            ['product_id' => $this->productA->id, 'qty' => 2, 'price' => 50_000],
        ],
    ], $overrides);
}
```

**Test methods (9):**
| # | Method | Assertion Utama |
|---|--------|-----------------|
| 1 | `it_creates_sale_with_correct_grand_total` | `$sale->grand_total == total + shipping - discount` |
| 2 | `it_creates_sale_items_for_each_item_in_array` | `SaleItem::count() == count(items)` |
| 3 | `it_reduces_product_stock_after_sale` | `$product->current_stock == 10 - 2` |
| 4 | `it_creates_payment_and_ledger_when_paid` | `Payment::count() == 1`, `CashLedger::where('type','in')->count() == 1` |
| 5 | `it_increases_account_balance_when_paid` | `$account->balance == 1_000_000 + grand_total` |
| 6 | `it_does_not_create_payment_or_ledger_when_unpaid` | `Payment::count() == 0`, `CashLedger::count() == 0` |
| 7 | `it_throws_exception_when_stock_is_insufficient` | `$this->expectException(\Exception::class)` + message contains product name |
| 8 | `it_creates_customer_contact_when_save_customer_is_true` | `Contact::where('type','customer')->count() == 1` |
| 9 | `it_grand_total_formula_holds_for_various_shipping_and_discount` | Loop 20 random combos, assert formula |

### 5.2 RecordPurchaseActionTest

**File:** `tests/Unit/Actions/RecordPurchaseActionTest.php`

**Setup:** Buat `Account`, 2 `Material` (current_stock=10)

**Test methods (8):**
| # | Method | Assertion Utama |
|---|--------|-----------------|
| 1 | `it_creates_purchase_and_purchase_items` | `Purchase::count() == 1`, `PurchaseItem::count() == n` |
| 2 | `it_increases_material_stock` | `$material->current_stock == 10 + qty` |
| 3 | `it_updates_last_buy_price_when_price_is_positive` | `$material->last_buy_price == item.price` |
| 4 | `it_creates_payment_and_ledger_type_out_when_paid` | `CashLedger::where('type','out')->count() == 1` |
| 5 | `it_decreases_account_balance_when_paid` | `$account->balance == 1_000_000 - total_amount` |
| 6 | `it_does_not_create_payment_or_ledger_when_unpaid` | `Payment::count() == 0` |
| 7 | `it_creates_supplier_contact_when_save_supplier_is_true` | `Contact::where('type','supplier')->count() == 1` |
| 8 | `it_total_amount_equals_sum_of_qty_times_price` | Loop 20 random multi-item combos |

### 5.3 RecordProductionActionTest

**File:** `tests/Unit/Actions/RecordProductionActionTest.php`

**Setup:** Buat `Account`, 2 `Material` (current_stock=50, last_buy_price=5000), 1 `Product`

**Test methods (9):**
| # | Method | Assertion Utama |
|---|--------|-----------------|
| 1 | `it_creates_production_material_and_result_records` | Count tiap model == 1 |
| 2 | `it_reduces_material_stock` | `$material->current_stock` berkurang |
| 3 | `it_increases_product_stock` | `$product->current_stock` bertambah |
| 4 | `it_calculates_grand_total_cost_correctly` | `$prod->grand_total_cost == mat + labor + overhead + additional` |
| 5 | `it_creates_labor_cost_ledger_entry` | `CashLedger::where('category','production_labor')->count() == 1` |
| 6 | `it_creates_overhead_cost_ledger_entry` | `CashLedger::where('category','production_overhead')->count() == 1` |
| 7 | `it_creates_material_cost_ledger_entry` | `CashLedger::where('category','production_material')->count() == 1` |
| 8 | `it_throws_exception_when_material_stock_insufficient` | Exception + material name in message |
| 9 | `it_total_ledger_amount_equals_grand_total_cost` | `CashLedger::sum('amount') == grand_total_cost` |

### 5.4 RecordPaymentActionTest

**File:** `tests/Unit/Actions/RecordPaymentActionTest.php`

**Setup:** Buat `Account` (balance=2_000_000), `Sale` (grand_total=500_000, payment_status='unpaid'), `Purchase` (total_amount=300_000, payment_status='unpaid')

**Test methods (7):**
| # | Method | Assertion Utama |
|---|--------|-----------------|
| 1 | `it_creates_payment_and_in_ledger_for_sale` | `CashLedger::where('type','in')->count() == 1` |
| 2 | `it_creates_payment_and_out_ledger_for_purchase` | `CashLedger::where('type','out')->count() == 1` |
| 3 | `it_updates_payment_status_to_paid_when_fully_paid` | `$sale->fresh()->payment_status == 'paid'` |
| 4 | `it_updates_payment_status_to_partial_when_partially_paid` | `$sale->fresh()->payment_status == 'partial'` |
| 5 | `it_decreases_account_balance_for_purchase_payment` | `$account->balance == 2_000_000 - amount` |
| 6 | `it_increases_account_balance_for_sale_payment` | `$account->balance == 2_000_000 + amount` |
| 7 | `it_throws_exception_when_balance_insufficient_for_purchase_payment` | Exception "Saldo kas ... tidak mencukupi!" |

### 5.5 RecordTransactionActionTest

**File:** `tests/Unit/Actions/RecordTransactionActionTest.php`

**Setup:** Buat `Account` (balance=500_000, name='Kas Utama')

**Test methods (6):**
| # | Method | Assertion Utama |
|---|--------|-----------------|
| 1 | `it_creates_in_ledger_and_increases_balance` | `$ledger->type == 'in'`, balance bertambah |
| 2 | `it_creates_out_ledger_and_decreases_balance_when_sufficient` | `$ledger->type == 'out'`, balance berkurang |
| 3 | `it_sets_correct_balance_after_for_in_transaction` | `$ledger->balance_after == 500_000 + amount` |
| 4 | `it_sets_correct_balance_after_for_out_transaction` | `$ledger->balance_after == 500_000 - amount` |
| 5 | `it_throws_exception_when_out_amount_exceeds_balance` | Exception "Saldo kas 'Kas Utama' tidak mencukupi!" |
| 6 | `it_does_not_modify_balance_when_exception_is_thrown` | `$account->fresh()->balance == 500_000` |

### 5.6 AdjustStockActionTest

**File:** `tests/Unit/Actions/AdjustStockActionTest.php`

**Setup:** Buat `Material` (current_stock=10), `Product` (current_stock=20)

**Test methods (5):**
| # | Method | Assertion Utama |
|---|--------|-----------------|
| 1 | `it_creates_adjustment_and_updates_material_stock` | `StockAdjustment::count() == 1`, `$material->current_stock == actual_stock` |
| 2 | `it_creates_adjustment_and_updates_product_stock` | `$product->current_stock == actual_stock` |
| 3 | `it_calculates_quantity_difference_correctly` | `$adj->quantity_difference == actual - previous` |
| 4 | `it_throws_exception_when_no_stock_change` | Exception "Tidak ada perubahan stok." |
| 5 | `it_does_not_create_adjustment_when_stock_unchanged` | `StockAdjustment::count() == 0` |

---

## 6. Desain Unit Tests — Model Methods

### 6.1 ProductGetPriceForUserTest

**File:** `tests/Unit/Models/ProductGetPriceForUserTest.php`

**Setup per test:** Buat `Product` (base_price=100_000, reseller_price=80_000), buat `Region` province dan city

**Test methods (6):**
| # | Skenario | Expected Return |
|---|----------|----------------|
| 1 | Reseller + `product_user_prices` ada | `$userPrice->price` |
| 2 | Reseller + region kota punya `reseller_price > 0` | `$regionPrice->reseller_price` |
| 3 | Reseller + kota tidak ada, provinsi ada | `$provincePrice->base_price` atau `reseller_price` |
| 4 | Reseller tanpa harga khusus, `reseller_price > 0` | `$product->reseller_price` |
| 5 | Customer biasa (non-reseller) | `$product->base_price` |
| 6 | `null` user | `$product->base_price` |

**Catatan:** Test ini membutuhkan `RefreshDatabase` karena menyentuh tabel `product_user_prices` dan `product_region_prices`.

### 6.2 OrderLifecycleTest

**File:** `tests/Unit/Models/OrderLifecycleTest.php`

**Setup:** Order dibuat dengan factory, `RefreshDatabase`

**Test methods (11):**
- `it_auto_generates_order_number_on_create` — assertMatchesRegularExpression `/^ORD-\d{8}-\d{3}$/`
- `it_generates_unique_order_numbers_for_same_day_orders`
- `it_confirm_sets_status_confirmed_and_confirmed_at`
- `it_mark_processing_sets_status_processing`
- `it_mark_shipped_sets_status_shipped_and_shipped_at`
- `it_complete_sets_status_completed_and_completed_at`
- `it_cancel_sets_status_cancelled_with_reason_and_timestamp`
- `it_is_pending_returns_true_when_status_is_pending`
- `it_is_confirmed_returns_true_when_status_is_confirmed`
- `it_is_paid_returns_true_when_payment_status_is_paid`
- `it_is_partially_paid_returns_true_when_payment_status_is_partial`

### 6.3 UserRbacTest

**File:** `tests/Unit/Models/UserRbacTest.php`

**Setup:** `RefreshDatabase`, buat roles dan permissions via factory

**Test methods (9):**
- `it_has_role_returns_true_when_user_has_that_role`
- `it_has_role_returns_false_when_user_lacks_that_role`
- `it_dev_admin_bypasses_all_permission_checks`
- `it_has_permission_returns_true_when_user_has_it_via_role`
- `it_has_permission_returns_false_when_user_lacks_it`
- `it_has_any_role_returns_true_when_at_least_one_role_matches`
- `it_has_any_role_returns_false_when_no_roles_match`
- `it_has_all_permissions_returns_true_when_all_owned`
- `it_has_all_permissions_returns_false_when_one_missing`

---

## 7. Desain Unit Tests — FormRequest Validation

**Pola umum:** Instantiate `FormRequest`, panggil `rules()`, gunakan `Illuminate\Support\Facades\Validator` secara langsung — **tidak perlu** HTTP request atau database.

```php
$request = new StoreSaleRequest();
$validator = Validator::make($data, $request->rules());
$this->assertTrue($validator->passes());
```

**Kecuali** untuk rule `exists:materials,id` — test tersebut membutuhkan `RefreshDatabase` karena query ke tabel nyata.

### 7.1 StoreSaleRequestTest

**File:** `tests/Unit/Requests/StoreSaleRequestTest.php`

Test methods (4):
- `it_passes_with_valid_data`
- `it_fails_without_date_or_items`
- `it_fails_when_qty_is_zero_or_negative`
- `it_fails_with_invalid_payment_status`

### 7.2 StorePurchaseRequestTest

**File:** `tests/Unit/Requests/StorePurchaseRequestTest.php`

Test methods (3):
- `it_passes_with_valid_data`
- `it_fails_when_material_id_does_not_exist` — membutuhkan `RefreshDatabase`
- `it_fails_without_required_fields`

### 7.3 StoreProductRequestTest

**File:** `tests/Unit/Requests/StoreProductRequestTest.php`

Test methods (3):
- `it_passes_with_valid_name_base_price_and_stock`
- `it_fails_without_name_or_with_negative_base_price`
- `it_fails_when_current_stock_is_non_integer`

---

## 8. Desain Feature Tests

Semua Feature Tests menggunakan:
- `use RefreshDatabase`
- `use CreatesTestData` (trait di atas)
- Seeded region data minimal (province + city) via `$this->artisan('db:seed', ['--class' => 'RegionSeeder'])` jika dibutuhkan, atau `Region::create()` langsung

### 8.1 AuthenticationTest

**File:** `tests/Feature/Auth/AuthenticationTest.php`

**Pendekatan:** Gunakan `$this->post()` langsung, cek redirect dan session.

```
POST /admin/login  →  kredensial valid admin  →  redirect /dashboard
POST /admin/login  →  password salah          →  redirect back + errors.email
POST /logout       →  user autentikasi        →  redirect /login
POST /register/customer → data valid          →  redirect customer.dashboard + User+Contact dibuat
POST /register/customer → email duplikat      →  errors.email
POST /admin/login  →  user customer           →  redirect customer.dashboard
```

### 8.2 CatalogPublicTest

**File:** `tests/Feature/Catalog/CatalogPublicTest.php`

**Pendekatan:** Buat beberapa Product via factory, gunakan `GET /catalog` (Inertia response). Karena menggunakan Inertia, gunakan `assertInertia()` dari package `inertiajs/inertia-laravel` atau cek `assertStatus(200)`.

Untuk endpoint filter (`GET /api/catalog/filter?search=...`), cek JSON response karena filter endpoint mengembalikan `JsonResponse`.

```
GET /catalog              →  200, ada data products
GET /api/catalog/filter?search=kerajinan → JSON dengan produk yang match
GET /api/catalog/filter?stock=tersedia   → hanya produk current_stock > 0
GET /api/catalog/filter?stock=habis      → hanya produk current_stock = 0
GET /api/catalog/filter?search=<script>  → 422
```

### 8.3 CheckoutOrderTest

**File:** `tests/Feature/Orders/CheckoutOrderTest.php`

**Setup:** Buat `PaymentMethod` (code='transfer'), buat `Region` (province + city), buat `Product`.

**Pendekatan:** `POST /order` dengan data lengkap. Karena Inertia middleware ada di stack, tambahkan header `X-Inertia: true` atau gunakan `withHeaders()`.

```
POST /order (guest, form)         →  redirect /order/success/{number}, Order dibuat status=pending
POST /order (user login)          →  Order terhubung user_id
POST /order (create_account=true) →  User+Contact baru dibuat, login otomatis
POST /order (reseller, dp >= 30%) →  Order payment_status=partial, remaining_balance benar
POST /order (reseller, dp < 30%)  →  error down_payment_amount
POST /order (items kosong)        →  error "Keranjang belanja kosong."
POST /order (sukses)              →  order_number format ORD-YYYYMMDD-NNN
```

### 8.4 AdminOrderManagementTest

**File:** `tests/Feature/Orders/AdminOrderManagementTest.php`

```
GET  /admin/orders          (view-orders permission)  →  200
GET  /admin/orders          (tanpa permission)         →  403
PATCH /admin/orders/{id}/status (status valid, admin)  →  redirect
DELETE /admin/orders/{id}   (manage-orders permission) →  redirect, soft deleted
DELETE /admin/orders/{id}   (tanpa permission)         →  403
```

### 8.5 SaleModuleTest

**File:** `tests/Feature/Sales/SaleModuleTest.php`

**Setup:** Buat admin user dengan permission `view-sales` dan `manage-sales`, buat `Account`, buat `Product`.

```
GET  /sales (view-sales)    →  200
GET  /sales (unauthenticated) →  302 redirect ke login
POST /sales (valid, stok cukup) →  redirect /sales, Sale dibuat, stok berkurang
POST /sales (stok kurang)   →  error berisi nama produk
POST /sales (tanpa date/items) →  error validasi
```

### 8.6 PurchaseModuleTest

**File:** `tests/Feature/Purchases/PurchaseModuleTest.php`

```
GET  /purchases (view-purchases) →  200
POST /purchases (valid)          →  redirect /purchases, Purchase dibuat, stok Material bertambah
POST /purchases (tanpa date/items) →  error validasi
```

### 8.7 FinanceModuleTest

**File:** `tests/Feature/Finance/FinanceModuleTest.php`

**Setup:** Buat `Account` (id=1, balance=500_000), admin dengan permission `view-finance`.

```
GET  /finance (view-finance)                  →  200
GET  /finance (tanpa permission)              →  403
POST /finance/transactions (type=in, valid)   →  CashLedger dibuat, balance naik
POST /finance/transactions (type=out, saldo kurang) → error saldo tidak mencukupi
```

### 8.8 ProductCrudTest

**File:** `tests/Feature/Products/ProductCrudTest.php`

**Setup:** Buat admin dengan permission `manage-products`.

```
GET  /products (admin)                  →  200
POST /products (valid)                  →  redirect /products, Product dibuat
POST /products (name null / price negatif) →  error validasi
PATCH /products/{id} (valid)            →  Product diperbarui
DELETE /products/{id} (admin)           →  soft deleted, redirect /products
POST /products (tanpa manage-products)  →  403
```

### 8.9 StockAdjustmentTest

**File:** `tests/Feature/Stock/StockAdjustmentTest.php`

```
POST /stock-adjustments (actual_stock berbeda)  →  StockAdjustment dibuat, current_stock diperbarui
POST /stock-adjustments (actual_stock sama)      →  error "Tidak ada perubahan stok."
POST /stock-adjustments (unauthenticated)        →  302 redirect ke login
```

---

## 9. Desain Property-Based Testing (PBT)

Karena PHPUnit tidak memiliki library PBT native, implementasi menggunakan **loop dengan data acak** — 50–100 iterasi per invariant. Ini disebut "statistical PBT" dan cukup untuk kebutuhan ini tanpa dependency tambahan.

**File:** Masuk sebagai metode tambahan di test class Action yang relevan.

### Pola Implementasi PBT

```php
#[Test]
public function it_grand_total_formula_always_holds(): void
{
    $account = $this->createAccount(999_999_999); // saldo besar agar tidak throws

    for ($i = 0; $i < 50; $i++) {
        $totalAmount  = fake()->randomFloat(2, 1_000, 1_000_000);
        $shippingFee  = fake()->randomFloat(2, 0, 100_000);
        $discount     = fake()->randomFloat(2, 0, $totalAmount);
        $expectedGrandTotal = round($totalAmount + $shippingFee - $discount, 2);

        $product = Product::factory()->create(['current_stock' => 100, 'base_price' => $totalAmount]);

        $data = [
            'date'            => now()->format('Y-m-d'),
            'payment_status'  => 'unpaid',
            'shipping_status' => 'pending',
            'shipping_fee'    => $shippingFee,
            'discount'        => $discount,
            'items'           => [['product_id' => $product->id, 'qty' => 1, 'price' => $totalAmount]],
        ];

        $sale = (new RecordSaleAction())->handle($data);

        $this->assertEquals(
            $expectedGrandTotal,
            round((float) $sale->grand_total, 2),
            "Iteration {$i}: grand_total formula failed"
        );
    }
}
```

### Invariants yang Diuji via PBT

| File | Method | Invariant |
|------|--------|-----------|
| `RecordSaleActionTest` | `it_grand_total_formula_always_holds` | `grand_total = total_amount + shipping_fee - discount` |
| `RecordPurchaseActionTest` | `it_total_amount_equals_sum_of_items` | `total_amount = SUM(qty_i * price_i)` |
| `RecordProductionActionTest` | `it_total_ledger_amount_equals_grand_total_cost` | `SUM(ledger.amount) == grand_total_cost` |

---

## 10. Dependency & Konfigurasi PHPUnit

**phpunit.xml** sudah ada. Pastikan `testSuites` mencakup folder baru:

```xml
<testSuites>
    <testSuite name="Unit">
        <directory>./tests/Unit</directory>
    </testSuite>
    <testSuite name="Feature">
        <directory>./tests/Feature</directory>
    </testSuite>
</testSuites>
```

**Environment:** Gunakan database SQLite in-memory di `.env.testing`:
```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

Tidak ada package PHP tambahan yang dibutuhkan — semua menggunakan PHPUnit 11 bawaan Laravel 13.

---

## 11. Urutan Implementasi (Dependency Order)

1. **Database Factories** — semua factory harus ada sebelum test bisa jalan
2. **Trait `CreatesTestData`** — helper reusable
3. **Unit Tests: Actions** — tidak bergantung pada Feature
4. **Unit Tests: Models** — `ProductGetPriceForUserTest`, `OrderLifecycleTest`, `UserRbacTest`
5. **Unit Tests: Requests** — `StoreSaleRequestTest`, `StorePurchaseRequestTest`, `StoreProductRequestTest`
6. **Feature Tests** — bergantung pada semua factory dan routes yang ada

---

## 12. Mapping Requirements → Implementation

| Requirement | File Test | Tipe |
|-------------|-----------|------|
| Req 1: Infrastruktur | Semua factories + `CreatesTestData` | Setup |
| Req 2: RecordSaleAction | `Unit/Actions/RecordSaleActionTest.php` | Unit + PBT |
| Req 3: RecordPurchaseAction | `Unit/Actions/RecordPurchaseActionTest.php` | Unit + PBT |
| Req 4: RecordProductionAction | `Unit/Actions/RecordProductionActionTest.php` | Unit + PBT |
| Req 5: RecordPaymentAction | `Unit/Actions/RecordPaymentActionTest.php` | Unit |
| Req 6: RecordTransactionAction | `Unit/Actions/RecordTransactionActionTest.php` | Unit |
| Req 7: AdjustStockAction | `Unit/Actions/AdjustStockActionTest.php` | Unit |
| Req 8: Product::getPriceForUser | `Unit/Models/ProductGetPriceForUserTest.php` | Unit |
| Req 9: Order Lifecycle | `Unit/Models/OrderLifecycleTest.php` | Unit |
| Req 10: RBAC | `Unit/Models/UserRbacTest.php` | Unit |
| Req 11: FormRequest | `Unit/Requests/*RequestTest.php` | Unit |
| Req 12: Autentikasi | `Feature/Auth/AuthenticationTest.php` | Feature |
| Req 13: Katalog Publik | `Feature/Catalog/CatalogPublicTest.php` | Feature |
| Req 14: Checkout & Order | `Feature/Orders/CheckoutOrderTest.php` | Feature |
| Req 15: Admin Order | `Feature/Orders/AdminOrderManagementTest.php` | Feature |
| Req 16: Modul Sales | `Feature/Sales/SaleModuleTest.php` | Feature |
| Req 17: Modul Purchases | `Feature/Purchases/PurchaseModuleTest.php` | Feature |
| Req 18: Modul Finance | `Feature/Finance/FinanceModuleTest.php` | Feature |
| Req 19: CRUD Products | `Feature/Products/ProductCrudTest.php` | Feature |
| Req 20: Stock Adjustment | `Feature/Stock/StockAdjustmentTest.php` | Feature |
| Req 21: PBT Invariant | Inline di Action tests | PBT |

---

## Correctness Properties

Invariant yang harus selalu berlaku, diverifikasi via PBT dan regular test:

### Property 1: Grand Total Sale
`grand_total = total_amount + shipping_fee - discount` — berlaku untuk setiap kombinasi nilai ≥ 0.

**Validates: Requirements 2.1, 21.1**

### Property 2: Total Amount Purchase
`total_amount = SUM(qty_i × price_i)` — berlaku untuk setiap jumlah item dan harga.

**Validates: Requirements 3.1, 21.2**

### Property 3: Grand Total Cost Production
`grand_total_cost = total_material_cost + labor_cost + overhead_cost + additional_cost` — berlaku untuk setiap kombinasi biaya ≥ 0.

**Validates: Requirements 4.4, 21.3**

### Property 4: CashLedger Amount Production
`SUM(CashLedger.amount untuk satu produksi) = grand_total_cost` — tidak ada biaya yang hilang atau dobel.

**Validates: Requirements 4.9, 21.3**

### Property 5: Payment Status Monoton
`payment_status` hanya boleh bergerak maju: `unpaid → partial → paid`, tidak pernah mundur.

**Validates: Requirements 5.3, 5.4, 21.4**

### Property 6: Stock Difference Calculation
`StockAdjustment.quantity_difference = actual_stock - previous_stock` — berlaku untuk semua nilai actual_stock.

**Validates: Requirements 7.3, 21.5**

---

## Error Handling

| Kondisi | Action | Perilaku yang Diharapkan |
|---------|--------|--------------------------|
| Stok produk tidak cukup | `RecordSaleAction` | Lempar `\Exception`, seluruh transaksi di-rollback |
| Stok material tidak cukup | `RecordProductionAction` | Lempar `\Exception`, seluruh transaksi di-rollback |
| Saldo akun tidak cukup (out) | `RecordPaymentAction`, `RecordTransactionAction` | Lempar `\Exception` dengan nama akun, tidak ada perubahan balance |
| Stok tidak berubah | `AdjustStockAction` | Lempar `\Exception("Tidak ada perubahan stok.")` |
| Semua exception di atas | Semua Action | Wrapped dalam `DB::transaction()` — semua perubahan DB di-rollback atomik |

---

## Testing Strategy

1. **Unit Tests terlebih dahulu** — test Action, Model, dan FormRequest tidak bergantung HTTP stack.
2. **Feature Tests setelah** — butuh routes terdaftar dan seeder minimal.
3. **PBT inline** — tidak terpisah, menjadi metode `#[Test]` tambahan di kelas Action.
4. **Tidak ada mocking** — test menggunakan database nyata (SQLite in-memory via `RefreshDatabase`). Pendekatan ini lebih realistis untuk Action yang melakukan banyak query DB.
5. **Inertia responses** — Feature tests yang mengakses halaman Inertia cukup `assertStatus(200)`. Untuk filter endpoint yang mengembalikan JSON, gunakan `assertJsonStructure()`.
6. **Permission setup** — gunakan `createUserWithPermission()` dari trait helper, bukan seed seluruh PermissionSeeder, agar test lebih cepat dan terisolasi.
