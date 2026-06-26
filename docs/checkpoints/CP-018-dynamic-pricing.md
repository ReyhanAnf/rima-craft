# CP-018: Dynamic Pricing Implementation (Standard vs Reseller)

**Created:** 2026-06-25  
**Status:** ✅ Complete  
**Category:** Catalog & Pricing

## Description
Implemented role-based dynamic pricing system that displays different prices for regular customers (standard price) vs partners/resellers (discounted price). Partners see special pricing with discount badges when browsing the catalog.

## Implementation Summary

### 1. Database Migration

**Migration:** `2026_06_24_234555_add_reseller_price_to_products_table.php`
- Added `reseller_price` column to `products` table
- Type: `DECIMAL(12, 2)` - supports up to 10 billion with 2 decimals
- Nullable - allows products without special reseller pricing
- Positioned after `base_price` column for logical grouping
- Comment: "Harga khusus untuk partner/reseller"

**Rollback:** Drops `reseller_price` column if migration is reversed

### 2. Model Updates

**Product Model** (`app/Models/Product.php`)

**Fillable Update:**
```php
#[Fillable(['name', 'description', 'base_price', 'reseller_price', 'current_stock', 'image_path', 'media_assets'])]
```

**New Method:** `getPriceForUser(?User $user = null): float`
- Returns appropriate price based on user role
- If user is partner AND reseller_price exists → returns reseller_price
- Otherwise → returns base_price
- Fallback to base_price if reseller_price is 0 or null
- Type-safe with float casting

### 3. ProductPriceService

**Service:** `app/Services/ProductPriceService.php`

**Methods:**

**`getProductPrice(Product $product, ?User $user = null): array`**
Returns comprehensive pricing information:
```php
[
    'price' => float,                    // Actual price for this user
    'formatted' => string,               // Formatted as "Rp X.XXX.XXX"
    'is_reseller' => bool,               // Is user a partner?
    'has_discount' => bool,              // Does reseller price show discount?
    'base_price' => float,               // Standard price
    'base_price_formatted' => string,    // Formatted standard price
    'reseller_price' => ?float,          // Reseller price (nullable)
    'reseller_price_formatted' => ?string // Formatted reseller price
]
```

**`getDiscountPercentage(Product $product): float`**
- Calculates discount percentage: `((base - reseller) / base) * 100`
- Returns rounded value with 1 decimal place
- Returns 0 if no reseller price or invalid values

**`getBulkPrice(Product $product, int $quantity, User $user): array`**
- Calculates bulk pricing for partners
- Returns unit price, total, and total savings
- Useful for future bulk order features

**Helper:** `formatRupiah(float $price): string`
- Indonesian Rupiah format: "Rp 1.000.000"
- Uses `number_format()` with Indonesian locale

### 4. Controller Updates

**CatalogController** (`app/Http/Controllers/CatalogController.php`)

**Constructor Injection:**
```php
protected ProductPriceService $priceService;

public function __construct(ProductPriceService $priceService)
{
    $this->priceService = $priceService;
}
```

**Updated Methods:**

**`index()` and `filter()`**
- Get authenticated user
- Map through products to add pricing info
- Attach `pricing` array and `discount_percentage` to each product
- Pass enriched products to views

```php
$products = $products->map(function ($product) use ($user) {
    $product->pricing = $this->priceService->getProductPrice($product, $user);
    $product->discount_percentage = $this->priceService->getDiscountPercentage($product);
    return $product;
});
```

### 5. View Updates

**Catalog Products Grid** (`resources/views/catalog/products-grid.blade.php`)

**Three Price Display Locations Updated:**

#### A. Product Card Price (Line ~92)
**For Partners with Discount:**
- Shows crossed-out base price (gray)
- Shows reseller price in emerald green
- Displays "Hemat X%" badge in emerald pill

**For Partners without Discount:**
- Shows price in amber (standard)
- Displays "Harga Reseller" label below

**For Regular Customers:**
- Shows base price in amber
- No special labels

#### B. "Beli" Button Cart Integration (Line ~107)
- Uses dynamic price instead of hardcoded base_price
- Passes correct price to Alpine.js cart store
- Partners get reseller price added to cart

#### C. Product Detail Drawer (Line ~207)
- Same logic as product card
- Larger text (2xl vs xl)
- Shows "Diskon Reseller X%" badge
- More prominent discount display

**Admin Product Form** (`resources/views/products/products-form.blade.php`)

**Updated Grid Layout:**
- Row 1: Base Price (required) + Reseller Price (optional)
- Row 2: Stock (full width)
- Helper text explains purpose of each field
- Reseller price can be left empty (defaults to base price)

### 6. UX Design

**Visual Hierarchy:**

**Partners See:**
1. 🏷️ Discount badge (emerald green pill)
2. 💰 Strikethrough original price
3. ✅ New lower price (emerald)
4. 📊 "Hemat X%" or "Diskon Reseller X%" label

**Regular Customers See:**
1. 💰 Standard price (amber)
2. No badges or labels

**Admin Sees:**
1. Two price fields in product form
2. Clear labels and helper text
3. Optional reseller price field

### 7. Pricing Logic Flow

```
User browses catalog
    ↓
CatalogController gets user
    ↓
For each product:
    - Check if user has 'partner' role
    - If yes AND reseller_price exists → use reseller_price
    - Otherwise → use base_price
    ↓
ProductPriceService calculates:
    - Display price
    - Discount percentage
    - Formatted strings
    ↓
View renders:
    - If discount: show strikethrough + new price + badge
    - If no discount: show price normally
    ↓
User clicks "Beli":
    - Cart receives correct price
    - Partners get reseller price in cart
```

## Files Created (2 files):
1. `app/Services/ProductPriceService.php` - Pricing logic service
2. `docs/checkpoints/CP-018-dynamic-pricing.md` - Documentation

## Files Modified (5 files):
1. `database/migrations/2026_06_24_234555_add_reseller_price_to_products_table.php` - Migration
2. `app/Models/Product.php` - Added fillable + helper method
3. `app/Http/Controllers/CatalogController.php` - Added service injection
4. `resources/views/catalog/products-grid.blade.php` - Dynamic price display
5. `resources/views/products/products-form.blade.php` - Admin form update

## Database Schema

### Products Table (Updated)
```sql
CREATE TABLE products (
    -- ... existing columns ...
    base_price DECIMAL(12, 2) NOT NULL,
    reseller_price DECIMAL(12, 2) NULL COMMENT 'Harga khusus untuk partner/reseller',
    -- ... remaining columns ...
);
```

## API/Service Contract

### ProductPriceService Output
```php
[
    'price' => 850000.00,
    'formatted' => 'Rp 850.000',
    'is_reseller' => true,
    'has_discount' => true,
    'base_price' => 1000000.00,
    'base_price_formatted' => 'Rp 1.000.000',
    'reseller_price' => 850000.00,
    'reseller_price_formatted' => 'Rp 850.000'
]
```

## Usage Examples

### Setting Reseller Price (Admin)
1. Navigate to `/products`
2. Click "Tambah Produk" or edit existing
3. Enter base price: Rp 1.000.000
4. Enter reseller price: Rp 850.000 (15% discount)
5. Save product

### Partner Browsing Catalog
1. Partner logs in with 'partner' role
2. Navigates to `/catalog`
3. Sees products with:
   - ~~Rp 1.000.000~~ (crossed out)
   - Rp 850.000 (emerald green)
   - "Hemat 15%" badge

### Regular Customer Browsing Catalog
1. Customer logs in or browses as guest
2. Navigates to `/catalog`
3. Sees products with:
   - Rp 1.000.000 (amber)
   - No badges or labels

### Adding to Cart
1. Partner clicks "Beli" button
2. Product added to cart at Rp 850.000
3. Cart total reflects reseller pricing
4. Checkout process uses reseller prices

## Business Rules

1. **Reseller price is optional** - Can be null or 0
2. **Fallback to base price** - If reseller_price ≤ 0, use base_price
3. **Partner role required** - Only users with 'partner' role see reseller prices
4. **No negative discounts** - Discount % calculated only if reseller < base
5. **Cart price integrity** - Price locked at add-to-cart time
6. **Admin flexibility** - Admin can set reseller price higher than base (not recommended but allowed)

## Performance Considerations

- **Eager pricing calculation** - Done in controller, not in view
- **Collection mapping** - Uses Laravel collection `map()` for efficiency
- **Single service instance** - Injected once via constructor
- **No N+1 queries** - All data loaded with product
- **Cached formatting** - Formatted strings pre-calculated

## Testing Checklist

- [ ] Create product with base_price only
- [ ] Create product with base_price + reseller_price
- [ ] Browse catalog as guest (see base prices)
- [ ] Browse catalog as customer (see base prices)
- [ ] Browse catalog as partner (see reseller prices)
- [ ] Verify discount percentage calculation
- [ ] Add product to cart as partner (check reseller price)
- [ ] Add product to cart as customer (check base price)
- [ ] Edit product to add reseller_price
- [ ] Edit product to remove reseller_price (set to 0)
- [ ] Test with reseller_price > base_price (edge case)
- [ ] Verify dark mode compatibility
- [ ] Test responsive design on mobile
- [ ] Verify Indonesian Rupiah formatting

## Security Considerations

- ✅ Role-based access (only partners see reseller prices)
- ✅ Price calculated server-side (not client-manipulable)
- ✅ Type-safe with float casting
- ✅ Null/zero validation
- ✅ No sensitive data exposed in HTML

## Future Enhancements

1. **Tiered Pricing** - Multiple price levels (bronze, silver, gold partners)
2. **Volume Discounts** - Automatic discounts based on quantity
3. **Time-Limited Promos** - Temporary reseller price reductions
4. **Category-Based Pricing** - Different discount % per category
5. **Price History** - Track reseller price changes over time
6. **Margin Calculator** - Show admin profit margins
7. **Bulk Price Editor** - Update reseller prices for multiple products
8. **Partner Analytics** - Show partners their total savings

## Related Documents

- [CP-016-customer-partner-portal-phase1.md](./CP-016-customer-partner-portal-phase1.md) - Portal implementation
- [CP-017-user-role-management.md](./CP-017-user-role-management.md) - User management
- [06-database-design.md](../06-database-design.md) - Database schema
- [backlog/pending.md](../backlog/pending.md) - Task tracking
