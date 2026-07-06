# Authentication & Registration Redesign PRD

## Objective

Redesign the authentication flow into a **single authentication gateway** for all users (Customer, Reseller, Admin) to simplify the user experience, reduce maintenance complexity, and provide a scalable foundation for Google OAuth integration.

The system should determine the user's role **after successful authentication**, instead of asking users to choose which portal they want to enter.

---

# Background

Currently the system has three separate login portals:

* Customer Login
* Reseller Login
* Admin Login

This approach introduces unnecessary complexity:

* Users must know which login page to use.
* Google OAuth would require multiple authentication entry points.
* Authentication logic becomes duplicated.
* Future maintenance becomes more difficult.

The new design introduces **one authentication gateway** while keeping role-based authorization unchanged.

---

# Goals

## Business Goals

* Simplify login and registration.
* Reduce user confusion.
* Increase registration conversion.
* Improve onboarding for new customers.
* Prepare the system for Google OAuth.

## Technical Goals

* Single authentication endpoint.
* Single login page.
* Single registration page.
* Automatic role detection after authentication.
* Maintain existing role-based authorization.
* Support future authentication providers without redesigning the authentication flow.

---

# Scope

## In Scope

* Single Login page.
* Single Registration page.
* Google OAuth integration (Google only).
* Automatic role detection.
* Role-based redirection.
* Customer registration.
* Reseller registration request.
* Admin authentication.

## Out of Scope

* Facebook OAuth.
* GitHub OAuth.
* Apple OAuth.
* Multi-factor Authentication.
* Enterprise SSO.

---

# Authentication Flow

There is only **one login page** for every user.

No role selection is shown during login.

Users authenticate using either:

* Email & Password
* Google Sign-In

After authentication succeeds, the system determines the user's role and redirects them to the correct dashboard.

Example:

| Role     | Redirect              |
| -------- | --------------------- |
| Customer | `/customer/dashboard` |
| Reseller | `/reseller/dashboard` |
| Admin    | `/admin/dashboard`    |

The authentication mechanism must never require users to choose their role before logging in.

---

# Login Page

The login page contains:

* Email
* Password
* Login button
* Google Sign-In button
* Link to Registration page
* Forgot Password

There must be only one login page across the entire application.

---

# Google OAuth Flow

Google OAuth uses the same authentication gateway.

Flow:

1. User clicks **Continue with Google**.
2. User authenticates with Google.
3. System receives Google callback.
4. System searches for an existing user using the Google email.
5. If the account exists:

   * Link Google account automatically (if not already linked).
   * Login user.
6. If the account does not exist:

   * Create a new Customer account.
   * Link Google account.
   * Login user.
7. Redirect user based on their role.

No role selection should ever appear during the OAuth process.

---

# Registration Flow

The application only provides **one registration page**.

The default registration type is **Customer**.

At the bottom of the registration form, users are presented with an option:

> **Want to become a Reseller? Register as a Reseller instead.**

Selecting this option expands the registration form to display additional reseller information.

This approach keeps the onboarding process simple for most users while allowing reseller applicants to complete additional requirements without navigating to another page.

---

# Customer Registration

Required fields:

* Full Name
* Email
* Phone Number
* Password
* Password Confirmation

After successful registration:

* Create Customer account.
* Login automatically.
* Redirect to Customer Dashboard.

---

# Reseller Registration

The registration form extends the Customer form by displaying additional reseller-specific fields.

Suggested additional fields:

* Business Name
* Business Type
* Business Address
* City
* Province
* Instagram (optional)
* Website (optional)
* Additional Notes (optional)

The exact fields should remain configurable and may evolve based on business requirements.

---

# Reseller Approval Flow

Registering as a reseller **immediately assigns the Reseller role** to the user account.

However, the reseller account starts with a **verification status of `pending`**, which restricts access to reseller-specific features until an admin approves the account.

Verification Status:

* `pending` — Default status after registration. User has Reseller role but access is limited to customer-level features.
* `verified` — Admin has approved. Full reseller dashboard and pricing access is unlocked.
* `rejected` — Admin has rejected. User is notified and access remains at customer level.

Flow:

1. User submits reseller registration.
2. User account is created with role `reseller` and status `pending`.
3. User is logged in and redirected — during pending state, the portal behaves like a customer portal (no reseller pricing, no reseller dashboard).
4. A banner or notice informs the user that their reseller account is under review.
5. Admin reviews the application in the admin panel.
6. Admin approves or rejects the application.
7. When `verified`, the user gains full reseller dashboard access and reseller pricing.
8. When `rejected`, the user remains in customer-mode and is notified.

This approach assigns the role immediately (no role switching required) while keeping access controlled by verification status.

---

# Role Detection

Authentication is independent from authorization.

After successful authentication, the application automatically determines which role(s) the authenticated user possesses.

The application then redirects the user to the appropriate dashboard.

Users should never manually choose their role during login.

---

# Authorization

Authentication only identifies the user.

Authorization determines which modules can be accessed.

Every module must continue to use Role-Based Access Control (RBAC).

---

# Database Recommendation

Instead of tightly coupling a single role inside the users table, the authorization system should remain extensible.

Recommended structure:

Users

* id
* name
* email
* password

Roles

* id
* name

User Roles

* user_id
* role_id

This architecture allows future roles such as:

* Owner
* Admin
* Customer
* Reseller
* Warehouse Staff
* Cashier
* Production Staff
* Finance

without redesigning the authentication system.

---

# Business Rules

## Login

* Only one login page exists.
* Users never select their role before logging in.
* Google OAuth follows the same authentication flow.
* Authentication automatically detects user roles.

---

## Customer Registration

* Default registration type.
* Account becomes active immediately.
* User is logged in automatically.

---

## Reseller Registration

* Uses the same registration page.
* Displays additional reseller fields.
* **Immediately assigns the Reseller role** upon registration.
* Reseller account starts with verification status `pending`.
* During `pending`, access and portal behavior mirrors a customer account.
* User sees a notice indicating their account is under review.
* Requires admin approval to change status to `verified`.
* Full reseller access (pricing, dashboard) is only active when status is `verified`.

---

## Google OAuth

* Google is the only OAuth provider.
* New Google users become Customers by default.
* Existing accounts with the same email are linked automatically.
* Google login must respect existing roles.

---

# Non-Functional Requirements

* Laravel 13
* PHP 8.3+
* Vue 3 SPA
* Mobile-First UI
* PWA compatible
* Shared Hosting compatible
* No queue worker dependency
* OAuth configuration via `.env`
* Secure session regeneration after login
* HTTPS only

---

# Acceptance Criteria

### Authentication

* There is only one login page.
* Users can login using Email & Password.
* Users can login using Google.
* The system automatically detects the user's role.
* Users are redirected to the correct dashboard.
* No role selection is displayed during login.

### Registration

* There is only one registration page.
* Customer registration is the default option.
* Selecting "Register as Reseller" reveals additional reseller fields.
* Customer accounts become active immediately with full customer access.
* Reseller accounts are created immediately with the Reseller role, but start with `pending` verification status.
* During `pending`, reseller portal behavior is limited to customer-level access.
* Reseller full access is granted only after admin sets status to `verified`.

### Google OAuth

* Google OAuth supports both new and existing users.
* Existing accounts are automatically linked by email.
* New OAuth users become Customers by default.
* OAuth uses the same authentication gateway as Email & Password.

### Security

* Session is regenerated after login.
* OAuth state validation is implemented.
* HTTPS is required.
* Authentication endpoints are rate limited.
* Login activity is recorded in the audit log.


# Authentication UI/UX Design Requirements

## Objective

Design a modern, clean, mobile-first authentication interface that reflects a professional business application while remaining approachable for general users.

The authentication pages (Login & Register) must share a consistent visual language with the rest of the Rima Craft application.

---

# Overall Design Style

The UI should feel:

* Modern
* Professional
* Clean
* Elegant
* Minimalist
* Fast to use
* Mobile-first
* Accessible

Avoid generic admin template designs.

The interface should feel closer to a modern SaaS product than a traditional ERP.

Use soft Earth Tone colors that match the Rima Craft branding.

Example characteristics:

* Large whitespace
* Rounded corners (rounded-xl / rounded-2xl)
* Soft shadows
* Smooth transitions
* Comfortable spacing
* Consistent typography
* Clear visual hierarchy

---

# Desktop Layout

Use a **split-screen horizontal layout**.

```
+----------------------+----------------------+
|                      |                      |
|                      |                      |
|     Branding         |     Login Card       |
|                      |                      |
| Illustration         | Email               |
| Product Preview      | Password            |
| Company Tagline      | Google Login        |
|                      | Login Button        |
|                      | Register Link       |
|                      |                      |
+----------------------+----------------------+
```

## Left Section (Branding)

Approximately 50% width.

Contains:

* Brand logo
* Business name
* Short tagline
* Hero illustration or handcrafted product imagery
* Small feature highlights
* Soft decorative background elements

This section should strengthen branding and make the application feel premium.

Suggested content:

* "Kelola Bisnis Kerajinan Anda Lebih Mudah."
* "ERP Mini & E-Katalog untuk UMKM."

Avoid clutter.

---

## Right Section (Authentication)

Approximately 50% width.

Centered vertically.

Contains:

* Welcome title
* Short description
* Authentication form
* Google Sign-In
* Divider
* Register/Login links

The authentication card should have:

* rounded-2xl
* subtle shadow
* generous padding
* comfortable spacing

---

# Registration Page

Maintain the same split layout.

When the user selects:

> Register as Reseller

Do **not** navigate to another page.

Instead:

* smoothly expand the form
* reveal reseller fields with a subtle animation
* preserve the user's previously entered values

The transition should feel seamless.

---

# Mobile Layout

The interface must be fully responsive.

On mobile:

* Remove the horizontal split.
* Stack sections vertically.
* Prioritize the authentication form.
* Branding section moves above the form.
* Reduce illustration height.
* Ensure all controls are easily tappable.

Example:

```
Logo

Brand Name

Tagline

----------------

Login Card

Email

Password

Google

Login

Register
```

No horizontal scrolling should occur.

---

# Form Design

Use modern input components.

Each field should have:

* Floating label or clean top label
* Validation states
* Focus ring
* Error message
* Success state

Password field should include:

* Show/Hide Password button

Google button should include:

* Official Google icon
* White background
* Proper hover effect

---

# Buttons

Primary button:

* Full width
* Large height
* Rounded
* Loading state
* Disabled state

Google button:

* Equal prominence
* Full width
* Proper spacing
* Accessible contrast

---

# Animations

Use subtle animations only.

Examples:

* Fade
* Slide
* Scale
* Smooth expand
* Button hover
* Input focus
* Card entrance

Avoid excessive motion.

---

# Accessibility

The authentication UI must:

* Support keyboard navigation.
* Have visible focus indicators.
* Meet WCAG AA contrast guidelines.
* Use semantic HTML.
* Properly associate labels with inputs.
* Display validation messages accessibly.

---

# Performance

* Optimize images.
* Lazy-load non-critical assets.
* Prevent layout shifts.
* Keep first paint fast.
* Avoid unnecessary JavaScript.

---

# Technical Requirements

Frontend stack:

* Vue 3
* Vue Router
* Tailwind CSS
* Mobile-first responsive design

Do not use external UI templates.

Create reusable Vue components for:

* Auth Layout
* Auth Card
* Input Components
* Google Button
* Divider
* Form Footer
* Branding Panel

Avoid duplicated markup between Login and Register pages.

---

# Expected Result

The final authentication experience should resemble a modern SaaS application rather than a conventional ERP login page.

The design should be visually polished, highly responsive, intuitive for first-time users, and consistent with the Rima Craft brand identity while remaining clean, lightweight, and maintainable.
