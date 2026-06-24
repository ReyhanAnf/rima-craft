# In Progress Backlog

## Architecture Refactoring (Phase 2) - Started 2026-06-16

### 🔄 RBAC Middleware & Gates Implementation
- **Status**: Not Started
- **Priority**: High
- **Description**: Implement route-level authorization using Laravel gates and middleware
- **Tasks**:
  - [ ] Create custom middleware for permission checking
  - [ ] Define gates in AuthServiceProvider for all resources
  - [ ] Update routes/web.php to use middleware groups
  - [ ] Test role-based access for admin, manager, staff roles

### 🔄 Type Safety Improvements
- **Status**: Not Started
- **Priority**: Medium
- **Description**: Add strict_types declarations and comprehensive type hints
- **Tasks**:
  - [ ] Add `declare(strict_types=1);` to all PHP files
  - [ ] Add return type hints to all methods
  - [ ] Add parameter type hints to all methods
  - [ ] Verify type safety with static analysis (PHPStan optional)

## Pending Verification
- **Status**: Pending
- **Priority**: High
- **Description**: Final verification after refactoring completion
- **Tasks**:
  - [ ] Run `php artisan route:list` and verify all routes
  - [ ] Clear all caches (config, route, view, application)
  - [ ] Test CRUD operations for all modules
  - [ ] Verify no regression in existing functionality