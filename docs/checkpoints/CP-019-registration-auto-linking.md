# CP-019: Customer & Partner Registration with Auto-Linking

**Created:** 2026-06-25  
**Status:** ✅ Complete  
**Category:** Authentication & User Management

## Description
Implemented public registration system for customers (B2C) and partners (B2B/reseller) with automatic Contact creation, role assignment, and seamless login after registration.

## Implementation Summary

### 1. FormRequest Validation

**RegistrationRequest** (`app/Http/Requests/Auth/RegistrationRequest.php`)

**Validation Rules:**
```php
'name' => required|string|max:255
'email' => required|email|unique:users,email
'password' => required|string|min:8|confirmed
'phone' => nullable|string|max:20
'address' => nullable|string|max:1000
'company_name' => required_if:type,partner|nullable|string|max:255
'business_type' => nullable|string|max:100
'agree_terms' => required|accepted
```

**Features:**
- Indonesian error messages
- Dynamic validation (company_name required for partners only)
- Phone number normalization (removes non-numeric characters except +)
- Email uniqueness check
- Password confirmation requirement
- Terms & conditions agreement required

### 2. AuthController Updates

**New Methods:**

**`showRegistration(string $type)`**
- Validates type ('customer' or 'partner')
- Returns registration view with type context
- Aborts with 404 for invalid types

**`register(RegistrationRequest $request, string $type)`**

**Registration Flow:**
1. Validate type ('customer' or 'partner')
2. Create User with name, email, password
3. Assign role (customer/partner)
4. Auto-create Contact record with:
   - user_id (link to user)
   - type (customer/partner)
   - name, email, phone, address
   - company_name, business_type (for partners)
5. Auto-login user
6. Regenerate session
7. Redirect to appropriate portal:
   - Customer → `/customer/dashboard`
   - Partner → `/partner/dashboard`

**HTMX Support:**
- Returns HX-Redirect header for AJAX requests
- Smooth redirect without full page reload

### 3. Database Migrations

**Migration 1:** `2026_06_24_235734_add_fields_to_contacts_table.php`

**Added Columns:**
```php
$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
$table->string('email')->nullable();
$table->string('company_name')->nullable();
$table->string('business_type')->nullable();
```

**Features:**
- Links Contact to User account
- Stores email separately from User (for contacts without accounts)
- Company name for B2B contacts (partners, suppliers)
- Business type classification

**Migration 2:** `2026_06_24_235823_update_contact_type_enum.php`

**Updated Enum:**
```sql
-- Before: ENUM('supplier', 'customer', 'crafter')
-- After:  ENUM('supplier', 'customer', 'crafter', 'partner')
```

**Raw SQL:**
- Uses `DB::statement()` for enum modification
- Laravel Schema Builder doesn't support enum modification directly
- Includes rollback to previous enum values

### 4. Model Updates

**Contact Model** (`app/Models/Contact.php`)

**Updated Fillable:**
```php
#[Fillable([
    'type', 'user_id', 'name', 'email', 'phone', 
    'address', 'company_name', 'business_type'
])]
```

**New Fields:**
- `user_id` - Foreign key to users table
- `email` - Contact email (may differ from user email)
- `company_name` - Business name for B2B contacts
- `business_type` - Classification (retailer, distributor, etc.)

### 5. Registration View

**register.blade.php** (`resources/views/auth/register.blade.php`)

**Features:**
- Unified template for both customer and partner
- Conditional fields based on registration type
- Beautiful form design with Earth Tones theme
- Dark mode support
- Responsive layout

**Customer Registration:**
- Name, Email, Phone, Address
- Password + Confirmation
- Terms agreement
- Simple, streamlined form

**Partner Registration:**
- All customer fields PLUS:
  - Company Name (required)
  - Business Type (optional)
- Benefits box showing partner advantages:
  - Reseller pricing (10-20% discount)
  - Dashboard analytics
  - Transaction history & invoices
  - Priority access to new products

**UX Features:**
- Error summary box at top
- Loading spinner on submit
- Password hint (minimum 8 characters)
- Clear required field indicators (*)
- Link to login page
- Terms & Privacy Policy links

### 6. Login Page Updates

**login.blade.php** - Added registration links:

**Two-column button layout:**
- "Daftar Customer" - Standard button (gray)
- "Daftar Partner" - Highlighted button (emerald green)
- Icons for visual clarity
- Hover effects

### 7. Routes Added

```php
Route::middleware('guest')->group(function () {
    Route::get('register/{type}', [AuthController::class, 'showRegistration'])
         ->name('register.show');
    Route::post('register/{type}', [AuthController::class, 'register'])
         ->name('register.submit');
});
```

**Access:**
- Only for guest users (not logged in)
- Type parameter: 'customer' or 'partner'
- Auto-redirects to portal after successful registration

## Files Created (3 files):
1. `app/Http/Requests/Auth/RegistrationRequest.php` - Form validation
2. `resources/views/auth/register.blade.php` - Registration form
3. `docs/checkpoints/CP-019-registration-auto-linking.md` - Documentation

## Files Modified (6 files):
1. `app/Http/Controllers/Auth/AuthController.php` - Added registration methods
2. `app/Models/Contact.php` - Updated fillable fields
3. `database/migrations/2026_06_24_235734_add_fields_to_contacts_table.php` - New columns
4. `database/migrations/2026_06_24_235823_update_contact_type_enum.php` - Enum update
5. `routes/web.php` - Added registration routes
6. `resources/views/auth/login.blade.php` - Added registration links

## Database Schema Updates

### Contacts Table (Enhanced)
```sql
CREATE TABLE contacts (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NULL,                    -- NEW: Links to user account
    type ENUM('supplier','customer','crafter','partner'),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,                -- NEW: Contact email
    company_name VARCHAR(255) NULL,         -- NEW: Business name
    business_type VARCHAR(100) NULL,        -- NEW: Classification
    phone VARCHAR(255) NULL,
    address TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

## Registration Flow Diagram

```
User visits /register/customer or /register/partner
    ↓
Fills registration form
    ↓
Submits form (POST)
    ↓
RegistrationRequest validates:
    - Required fields
    - Email uniqueness
    - Password strength & confirmation
    - Company name (if partner)
    - Terms agreement
    ↓
AuthController::register():
    1. Create User (name, email, password)
    2. Find Role (customer/partner)
    3. Attach Role to User
    4. Create Contact:
       - user_id = new user's ID
       - type = customer/partner
       - name, email, phone, address
       - company_name, business_type (partners)
    5. Auth::login(user)
    6. Session::regenerate()
    ↓
Redirect:
    - Customer → /customer/dashboard
    - Partner → /partner/dashboard
    ↓
User sees welcome message & portal dashboard
```

## Usage Examples

### Customer Registration
1. Visit `/register/customer`
2. Fill in: Name, Email, Phone, Address, Password
3. Check "I agree to Terms & Conditions"
4. Click "Daftar Sekarang"
5. Auto-login → Redirected to `/customer/dashboard`
6. Can now browse catalog with personalized features

### Partner Registration
1. Visit `/register/partner`
2. Fill in: Name, Email, Phone, Address
3. Fill in: Company Name (required), Business Type (optional)
4. Fill in: Password
5. Check "I agree to Terms & Conditions"
6. Click "Daftar Sekarang"
7. Auto-login → Redirected to `/partner/dashboard`
8. Sees reseller prices in catalog (10-20% discount)

### Login Page Registration Links
1. Visit `/login`
2. See "Belum punya akun?" section
3. Choose:
   - "Daftar Customer" (for personal shopping)
   - "Daftar Partner" (for reseller/business)
4. Redirected to appropriate registration form

## Business Rules

1. **One Contact per User** - Each user gets one auto-created contact
2. **Auto-Login After Registration** - Seamless onboarding experience
3. **Role Assignment** - Automatic based on registration type
4. **Partner Benefits** - Immediate access to reseller pricing
5. **Email Uniqueness** - One account per email address
6. **Password Security** - Minimum 8 characters with confirmation
7. **Terms Agreement Required** - Legal compliance
8. **Phone Normalization** - Removes formatting characters
9. **Guest-Only Access** - Logged-in users cannot access registration
10. **Type Validation** - Only 'customer' or 'partner' allowed

## Security Features

- ✅ CSRF protection
- ✅ Password hashing (bcrypt)
- ✅ Email uniqueness validation
- ✅ Password confirmation
- ✅ Session regeneration after login
- ✅ Guest-only middleware
- ✅ XSS protection (Blade escaping)
- ✅ Type validation (prevent invalid roles)
- ✅ Terms agreement requirement

## UX Improvements

**Registration Form:**
- Beautiful Earth Tones design
- Dark mode support
- Responsive layout (mobile-first)
- Clear error messages (Indonesian)
- Loading spinner during submission
- Password strength hint
- Required field indicators
- Logical field grouping
- Partner benefits showcase

**Login Page:**
- Prominent registration links
- Two-button layout (Customer vs Partner)
- Visual distinction (emerald for partner)
- Icon support for clarity

## Testing Checklist

- [ ] Register as customer with all fields
- [ ] Register as customer with minimal fields
- [ ] Register as partner with all fields
- [ ] Register as partner without company name (should fail)
- [ ] Try duplicate email (should fail)
- [ ] Try weak password (should fail)
- [ ] Try mismatched passwords (should fail)
- [ ] Try without terms agreement (should fail)
- [ ] Verify auto-login after registration
- [ ] Verify Contact creation with user_id link
- [ ] Verify role assignment (customer/partner)
- [ ] Verify redirect to correct portal
- [ ] Test phone number normalization
- [ ] Test HTMX registration (AJAX)
- [ ] Test responsive design on mobile
- [ ] Test dark mode
- [ ] Verify partner sees reseller prices
- [ ] Verify customer sees standard prices
- [ ] Try accessing registration while logged in (should redirect)

## Future Enhancements

1. **Welcome Email** - Send email with account details (requires mail setup)
2. **Email Verification** - Verify email before activation
3. **Partner Approval Workflow** - Admin approval for partner accounts
4. **Social Login** - Google, Facebook registration
5. **OTP Verification** - Phone number verification
6. **Referral System** - Track who referred whom
7. **Registration Analytics** - Track conversion rates
8. **Progressive Profiling** - Collect more info over time
9. **Captcha/ReCaptcha** - Prevent bot registrations
10. **Multi-step Registration** - Wizard-style form for better UX

## Related Documents

- [CP-016-customer-partner-portal-phase1.md](./CP-016-customer-partner-portal-phase1.md) - Portal implementation
- [CP-017-user-role-management.md](./CP-017-user-role-management.md) - User management
- [CP-018-dynamic-pricing.md](./CP-018-dynamic-pricing.md) - Dynamic pricing
- [03-requirements.md](../03-requirements.md) - Product requirements
- [backlog/pending.md](../backlog/pending.md) - Task tracking

## Notes

**Welcome Email (Skipped):**
- Email functionality requires mail server configuration
- Can be implemented later when SMTP is available
- Registration works perfectly without email notification
- User can still login with credentials they created

**Partner Approval (Future):**
- Currently partners are auto-approved
- Future implementation may require admin approval
- Can add `is_approved` flag to users table
- Partner dashboard access can be gated by approval status
