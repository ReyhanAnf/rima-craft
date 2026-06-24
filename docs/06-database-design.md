# Database Design (Scalable ERP Approach)

Pendekatan database dirancang untuk skalabilitas jangka panjang. Mendukung pembayaran parsial (DP/Utang/Cicilan), otentikasi pihak ketiga, dan manajemen otorisasi granular. Semua tabel utama harus memiliki `created_at`, `updated_at`, dan `deleted_at` (Soft Deletes).

## 1\. Authentication & Authorization (Spatie-like Custom)

*   `users`: `id`, `name`, `email`, `email_verified_at`, `password` (nullable untuk OAuth), `google_id`, `avatar`, `remember_token`.
*   `roles`: `id`, `name` (misal: super-admin, owner, admin, partner, customer).
*   `permissions`: `id`, `name` (misal: create-sale, edit-product).
*   `role_user` (Pivot): `role_id`, `user_id`.
*   `permission_role` (Pivot): `permission_id`, `role_id`.

## 2\. Master Data

*   `contacts` (Buku Alamat): `id`, `user_id` (nullable, relasi ke akun login), `type` (supplier, customer, partner, crafter), `name`, `phone`, `address`.
*   `materials` (Bahan Baku): `id`, `name`, `unit` (kg, meter, pcs), `min_stock`, `current_stock`.
*   `products` (Produk Jadi): `id`, `name`, `description`, `base_price`, `current_stock`, `image_path`.

## 3\. Core Transactions (Inbound, Outbound, Production)

*   `purchases` (Beli Bahan): `id`, `supplier_id`, `date`, `total_amount`, `payment_status` (unpaid, partial, paid), `due_date`.
    *   _Relasi:_ `purchase_items` (`purchase_id`, `material_id`, `qty`, `price`).
*   `sales` (Jual Produk): `id`, `customer_id`, `date`, `total_amount`, `payment_status` (unpaid, partial, paid), `due_date`, `shipping_status`.
    *   _Relasi:_ `sale_items` (`sale_id`, `product_id`, `qty`, `final_price`).
*   `productions` (Makloon): `id`, `crafter_id`, `start_date`, `end_date`, `status` (in\_progress, completed, cancelled), `wage_fee`, `payment_status` (unpaid, partial, paid).
    *   _Relasi Keluar:_ `production_materials` (`production_id`, `material_id`, `qty_given`, `qty_returned`).
    *   _Relasi Masuk:_ `production_results` (`production_id`, `product_id`, `qty_received`).

## 4\. Finance & Payments (Scalable Tracking)

*   `payments` (Cicilan/Pembayaran): `id`, `payable_type` (Polymorphic: Purchase, Sale, Production), `payable_id`, `amount`, `payment_date`, `payment_method`.
*   `cash_ledgers` (Buku Kas Besar): `id`, `date`, `type` (income, expense), `amount`, `description`, `reference_type` (Polymorphic ke transactions atau manual), `reference_id`.