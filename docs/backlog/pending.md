# Pending Backlog

## Future Enhancements

### 📋 Portal Pelanggan (Customer & Partner) - PHASE 1 COMPLETE ✅
- **Priority**: High (Phase 1 - Core Structure)
- **Status**: ✅ Implemented (2026-06-25)
- **Completed Tasks**:
  - ✅ Added Customer & Partner permissions to PermissionSeeder (9 new permissions)
  - ✅ Created CustomerPortalController with dashboard, orders, profile
  - ✅ Created PartnerPortalController with dashboard, orders, billing, profile
  - ✅ Added routes for /customer/* and /partner/* with role middleware
  - ✅ Created all portal views (dashboard, orders, profile, billing)
  - ✅ Updated RoleSeeder with customer and partner roles

### 📋 Portal Pelanggan (Customer & Partner) - PHASE 2 REMAINING
- **Priority**: High (Phase 2 - Enhanced Features)
- **Description**: Fitur lanjutan untuk portal pelanggan dan partner
- **Remaining Tasks**:
  - [x] **Poin 2**: Create User Management UI (Admin Panel) ✅ COMPLETE
    - ✅ Create UserController for CRUD users
    - ✅ Create RoleController for managing roles & permissions
    - ✅ Create views: users/index, users/create, users/edit
    - ✅ Create views: roles/index, roles/edit (permission matrix)
    - ✅ Add FormRequest validation (UserRequest, RoleRequest)
    - ✅ Add routes with 'manage-users' and 'manage-roles' permissions
  - [x] **Poin 3**: Implement Dynamic Pricing (Standard vs Reseller) ✅ COMPLETE
    - ✅ Add reseller_price column to products table
    - ✅ Create ProductPriceService for role-based pricing
    - ✅ Update CatalogController to show role-based prices
    - ✅ Update catalog views to display different prices
    - ✅ Add price badge showing "Harga Reseller" for partners
    - ✅ Update admin product form to set reseller prices
    - ✅ Add discount percentage calculation and display
  - [x] **Poin 4**: Customer/Partner Registration & Auto-Linking ✅ COMPLETE
    - ✅ Create registration pages for customer and partner
    - ✅ Implement auto-create Contact when User registers
    - ✅ Auto-login after successful registration
    - ✅ Redirect to appropriate portal (customer/partner)
    - ✅ Add registration links to login page
    - ✅ Partner-specific fields (company_name, business_type)
    - ✅ Phone number normalization
    - ✅ Terms & conditions agreement
    - ⏳ Welcome email (skipped - requires mail server setup)
    - ⏳ Partner approval workflow (future enhancement)

### 📋 PWA (Progressive Web App) Features
- **Priority**: Medium
- **Description**: Full PWA implementation for offline access and mobile experience
- **Tasks**:
  - [ ] Service worker caching strategies
  - [ ] Offline fallback pages
  - [ ] Push notifications for low stock alerts
  - [ ] Install prompt optimization

### 📋 Advanced Reporting
- **Priority**: Low
- **Description**: Export and advanced analytics features
- **Tasks**:
  - [ ] PDF export for sales/purchase reports
  - [ ] Excel/CSV export functionality
  - [ ] Advanced filtering and date ranges
  - [ ] Scheduled report generation

### 📋 Notification System
- **Priority**: Medium
- **Description**: In-app and email notifications
- **Tasks**:
  - [ ] Low stock alert notifications
  - [ ] Payment due reminders
  - [ ] Production completion alerts
  - [ ] Email notification preferences

### 📋 Audit Trail
- **Priority**: High (for compliance)
- **Description**: Track all CRUD operations for accountability
- **Tasks**:
  - [ ] Create audit_logs table
  - [ ] Implement model observers for automatic logging
  - [ ] Admin UI for viewing audit trails
  - [ ] Filter and search audit logs

### 📋 Multi-warehouse Support
- **Priority**: Low (future scaling)
- **Description**: Support for multiple inventory locations
- **Tasks**:
  - [ ] Add warehouse/location field to stock tables
  - [ ] Update stock tracking logic
  - [ ] Warehouse transfer functionality
  - [ ] Location-based stock reports

### 📋 Barcode/QR Code Integration
- **Priority**: Medium
- **Description**: Barcode scanning for inventory management
- **Tasks**:
  - [ ] Generate barcodes for products
  - [ ] Barcode scanning for stock adjustments
  - [ ] Quick product lookup via barcode
  - [ ] Mobile-friendly scanner UI

## Technical Debt

### 📋 Testing Coverage
- **Priority**: High
- **Description**: Comprehensive test suite
- **Tasks**:
  - [ ] Unit tests for Action classes
  - [ ] Unit tests for Repository classes
  - [ ] Feature tests for all CRUD operations
  - [ ] Integration tests for payment flows
  - [ ] Browser tests for critical user journeys

### 📋 Performance Optimization
- **Priority**: Medium
- **Description**: Optimize database queries and caching
- **Tasks**:
  - [ ] Add database indexes where needed
  - [ ] Implement query caching for dashboard metrics
  - [ ] Optimize N+1 queries
  - [ ] Add pagination to all list views

### 📋 Code Quality
- **Priority**: Medium
- **Description**: Maintain high code standards
- **Tasks**:
  - [ ] Setup PHPStan for static analysis
  - [ ] Setup Laravel Pint for code formatting
  - [ ] Add PHPDoc comments to all public methods
  - [ ] Review and update .gitignore rules