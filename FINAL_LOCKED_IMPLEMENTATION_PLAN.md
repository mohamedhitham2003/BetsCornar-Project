# FINAL LOCKED IMPLEMENTATION PLAN

## 1. Architecture Summary

- **Tech stack**
    - Laravel 12, PHP 8.3+
    - Blade templating
    - Bootstrap 5 with RTL
    - Vanilla JavaScript only (no Alpine.js or frameworks)
    - MySQL
    - Clean MVC (Controllers → Form Requests → Services → Eloquent Models)

- **Modules**
    - **Dashboard**
        - Today’s visits (invoice count for today)
        - Today’s revenue (sum of today’s invoice totals)
        - Total products count
        - Total vaccinations count
        - Upcoming vaccinations
        - Low-stock items
        - Vaccine expiry / expiring-soon alerts
    - **Customers**
        - Customer master data (name, normalized phone, address, animal_type, notes)
        - Customer list with search and last vaccination date
        - Customer visit form: creates/updates customer, invoice, invoice_items, vaccination (if any), and stock deductions in one transaction
    - **Products**
        - Unified catalog of:
            - `product` (physical goods)
            - `service` (non-stock services)
            - `vaccination` (vaccines)
        - Stock tracking controlled by `track_stock` boolean
        - Consultation product (“كشف”) as a real `service` item
    - **Invoices**
        - Financial center; every monetary operation must be an invoice
        - Sources:
            - `customer` (from customer visit flow)
            - `quick_sale` (from invoices page)
        - Multi-item invoices with fractional quantities
        - Drives today’s visits & revenue
    - **Vaccinations**
        - Medical vaccination records created only via customer visit flow
        - Linked to customer, vaccine product, and invoice
        - Vaccination list with WhatsApp contact

- **Service layer**
    - **CustomerVisitService**
        - Normalizes phone
        - Finds or creates customer by normalized phone
        - Creates invoice + invoice_items for consultation, vaccines, and additional products/services
        - Creates vaccination record when `has_vaccination = yes`
        - Delegates stock handling (including FEFO vaccine batches) to StockService
        - Runs everything in a DB transaction
    - **InvoiceService**
        - Handles quick-sale invoices
        - Generates unique, sequential `invoice_number` (e.g. `INV-000001`)
        - Creates invoices and invoice_items
        - Delegates stock handling to StockService
    - **StockService**
        - Handles stock deduction and status updates
        - For normal products:
            - Simple decrement of `products.quantity` when `track_stock = true`
        - For vaccines:
            - Uses `vaccine_batches` with FEFO (First Expired, First Out)
            - Deducts `quantity_remaining` per batch
            - Updates `invoice_item_vaccine_batches`
            - Recalculates usable-only vaccine `products.quantity` and `stock_status`

- **Language & layout**
    - All UI (labels, navigation, validation messages, buttons, tables, modals, placeholders) is **Arabic** and **RTL**
    - All internal code (DB tables, models, controllers, services, routes) remains in **English**

- **Integrity & safety**
    - Multi-table operations (customer visit, invoice save) are wrapped in database transactions
    - Invoices are never hard-deleted; future cancellation/edit is status-based
    - Products used in invoices, vaccinations, or vaccine_batches are never hard-deleted; they are deactivated (`is_active = false`)

---

## 2. Database Schema Summary

### 2.1 `customers`

- **Purpose**
    - Store persistent customer information (not per-visit financials)
- **Columns (core)**
    - `id` bigint PK
    - `name` string
    - `phone` string (normalized, unique)
    - `address` string nullable
    - `animal_type` string (Arabic content allowed, e.g. “قط”, “كلب”, “طائر”, custom)
    - `notes` text nullable
    - Timestamps
- **Indexes / constraints**
    - Unique index on normalized `phone`

### 2.2 `products`

- **Purpose**
    - Unified catalog of all sellable/usable entities: products, services, vaccines
- **Columns**
    - `id` bigint PK
    - `name` string
    - `type` string/enum: `product`, `service`, `vaccination`
    - `price` decimal(10, 2)
    - `quantity` decimal(10, 2) (for:
        - non-vaccines: direct stock
        - vaccines: usable-only aggregate of non-expired batches)
    - `track_stock` boolean (default true)
        - `product` and `vaccination`: `track_stock = true`
        - `service`: `track_stock = false`
    - `stock_status` string/enum: `available`, `low`, `out_of_stock`
    - `low_stock_threshold` decimal(10, 2)
    - `is_active` boolean (default true) for product deactivation (no soft deletes)
    - `notes` text nullable
    - Timestamps
- **Special**
    - Consultation service product (e.g. name “كشف”, `type = 'service'`, `track_stock = false`) must exist

### 2.3 `invoices`

- **Purpose**
    - Invoice headers (financial transactions)
- **Columns**
    - `id` bigint PK
    - `invoice_number` string, unique (e.g. `INV-000001`)
    - `customer_id` bigint FK nullable (`customers.id`)
    - `customer_name` string (snapshot)
    - `source` string/enum: `customer`, `quick_sale`
    - `total` decimal(10, 2)
    - Optional: `status` string/enum (e.g. `confirmed`, `cancelled`) for future controlled cancellation
    - Timestamps
- **Indexes**
    - Index on `created_at` (for dashboard metrics)

### 2.4 `invoice_items`

- **Purpose**
    - Per-invoice line items
- **Columns**
    - `id` bigint PK
    - `invoice_id` bigint FK (`invoices.id`)
    - `product_id` bigint FK (`products.id`)
    - `quantity` decimal(10, 2) (allows fractional quantities like 1.5)
    - `unit_price` decimal(10, 2) (actual charged price)
    - `line_total` decimal(10, 2) (`quantity * unit_price`)
    - Timestamps

### 2.5 `vaccinations`

- **Purpose**
    - Medical vaccination events (NOT every vaccine sale)
- **Columns**
    - `id` bigint PK
    - `customer_id` bigint FK (`customers.id`)
    - `product_id` bigint FK (`products.id`, `type = 'vaccination'`)
    - `invoice_id` bigint FK (`invoices.id`) (must always link to a real invoice)
    - `vaccination_date` date
    - `next_dose_date` date nullable
    - Timestamps
- **Indexes**
    - Index on `next_dose_date` for upcoming vaccination queries

### 2.6 `vaccine_batches`

- **Purpose**
    - Track physical vaccine batches for FEFO and expiry
- **Columns**
    - `id` bigint PK
    - `product_id` bigint FK (`products.id`, `type = 'vaccination'`, `track_stock = true`)
    - `batch_code` string nullable/required (internal identifier)
    - `received_date` date (when batch entered clinic)
    - `expiry_date` date
    - `quantity_received` decimal(10, 2)
    - `quantity_remaining` decimal(10, 2)
    - Timestamps
- **Indexes**
    - Index on `(product_id, expiry_date)`
    - Index on `expiry_date` (for global expiry monitoring)

### 2.7 `invoice_item_vaccine_batches`

- **Purpose**
    - Link table between vaccine invoice items and batches
- **Columns**
    - `id` bigint PK
    - `invoice_item_id` bigint FK (`invoice_items.id`)
    - `vaccine_batch_id` bigint FK (`vaccine_batches.id`)
    - `quantity` decimal(10, 2) (deducted from this batch for this item)
    - Timestamps
- **Notes**
    - Only used for vaccine invoice items
    - Sum of `quantity` across these rows for an invoice_item equals that item’s `quantity`

### 2.8 Relationships Summary

- `Customer hasMany Invoice`
- `Customer hasMany Vaccination`
- `Product hasMany InvoiceItem`
- `Product hasMany VaccineBatch`
- `Product hasMany Vaccination`
- `Invoice belongsTo Customer`
- `Invoice hasMany InvoiceItem`
- `Invoice hasMany Vaccination`
- `InvoiceItem belongsTo Invoice`
- `InvoiceItem belongsTo Product`
- `InvoiceItem hasMany InvoiceItemVaccineBatch` (for vaccines)
- `Vaccination belongsTo Customer`
- `Vaccination belongsTo Product`
- `Vaccination belongsTo Invoice`
- `VaccineBatch belongsTo Product`
- `VaccineBatch hasMany InvoiceItemVaccineBatch`
- `InvoiceItemVaccineBatch belongsTo InvoiceItem`
- `InvoiceItemVaccineBatch belongsTo VaccineBatch`

---

## 3. Business Rules (Non-Vaccine General)

### 3.1 Global Financial Rules

- Anything that involves money must be represented as an invoice (`invoices` + `invoice_items`)
- Today’s metrics:
    - Today’s visits: count invoices where `DATE(created_at) = today`
    - Today’s revenue: sum `total` where `DATE(created_at) = today`
- Invoices must not be hard-deleted:
    - Deletion is restricted; use status/cancellation in future versions, but no physical delete

### 3.2 Customers & Phone Matching

- **Phone normalization**
    - Every phone number must be normalized before:
        - Lookup for existing customer
        - Saving to DB
        - Using in WhatsApp links
    - `customers.phone` stores the normalized value only
- **Uniqueness and reuse (locked)**
    - `customers.phone` is unique after normalization
    - If a normalized phone already exists:
        - The system must **always reuse** the existing customer automatically
        - Must **not block** creation of a visit
        - Must **not create duplicate customers** with the same normalized phone
- **Customer list**
    - Shows: name, phone (normalized), animal_type, last vaccination date, actions
    - Searchable by name and phone

### 3.3 Consultation & Visit Flow

- Consultation is a real product:
    - Service product (e.g. “كشف”) in `products` with `type = 'service'`, `track_stock = false`
    - `products.price` = default consultation fee
- On visit:
    - Visit form pre-fills consultation price from `products.price` but allows override
    - `invoice_items.unit_price` for the consultation line = actual charged price at visit time
    - `invoice_items.product_id` = consultation product ID
- Customer visit save (transactional):
    - Normalize phone → find or create customer (reusing normalized phone)
    - Create `invoice` with `source = 'customer'`
    - Add `invoice_items` for:
        - Consultation
        - Vaccine (if `has_vaccination = yes`)
        - Additional products/services
    - If `has_vaccination = yes`, create `vaccinations` record linked to customer, vaccine product, and invoice
    - Invoke `StockService` to handle stock and vaccine batches
    - Roll back everything on any error

### 3.4 Products & Stock

- Types:
    - `product` (physical goods)
    - `service` (non-stock services)
    - `vaccination` (vaccine products)
- `track_stock`:
    - `true` for `product`, `vaccination`
    - `false` for `service`
- Products in use (referenced in invoice_items, vaccinations, or vaccine_batches) must never be hard-deleted:
    - They are marked inactive (`is_active = false`) and hidden from new selection lists while remaining in historical records

### 3.5 Invoices & Quick Sale

- Sources:
    - `customer` (created from visit form)
    - `quick_sale` (created directly on invoices page)
- Invoice number:
    - Sequential, unique, human-readable, e.g. `INV-000001`
- Quick sale:
    - Can optionally link to an existing customer (phone normalized if used)
    - Supports multiple items (products, services, vaccines)
    - UI uses JS to calculate totals; server recomputes and validates
- Quick sale vaccines:
    - Selling a vaccine product via quick sale:
        - Creates `invoice` + `invoice_items`
        - Triggers FEFO vaccine batch stock deduction
        - **Does not create any record in `vaccinations`**

---

## 4. Vaccine Batch & Stock Rules

### 4.1 Authoritative Vaccine Stock

- For vaccines (`products.type = 'vaccination'`, `track_stock = true`):
    - **Authoritative stock** is `vaccine_batches.quantity_remaining` plus `expiry_date`
    - All availability, FEFO, and validation decisions use batch data

### 4.2 Usable vs Historical Stock

- Usable stock:
    - Batches where:
        - `expiry_date >= today`
        - `quantity_remaining > 0`
- Expired batches:
    - `expiry_date < today`
    - Never used for deduction
    - Excluded from availability checks and aggregates
    - Kept in DB for audit/history

### 4.3 Aggregate Quantity for Vaccines

- For vaccine products:
    - `products.quantity` (display/cache) =  
      SUM of `vaccine_batches.quantity_remaining` where `expiry_date >= today`
- Expired batches are always excluded from `products.quantity`:
    - They cannot inflate displayed stock
    - They cannot affect low-stock logic or dashboard quantities

### 4.4 Stock Status & Alerts

- For vaccines (`type = 'vaccination'`):
    - Stock status based on **usable** `products.quantity` vs `low_stock_threshold`:
        - `available` if `quantity > low_stock_threshold`
        - `low` if `0 < quantity <= low_stock_threshold`
        - `out_of_stock` if `quantity <= 0`
- Expiry-based alerts:
    - `expired`:
        - `expiry_date < today`
    - `expiring_soon`:
        - `expiry_date` between today and today + 5 days
    - Used for dashboard and stock monitoring

### 4.5 FEFO Deduction

- No batch UI:
    - Staff choose only vaccine product and quantity
    - System chooses batches automatically
- Deduction steps (per vaccine invoice item):
    - Collect valid batches:
        - `expiry_date >= today`, `quantity_remaining > 0`
    - Sort by `expiry_date` ascending (FEFO)
    - Deduct from earliest batch first, then next, etc
    - Support multi-batch coverage:
        - May split requested quantity across multiple valid batches
- Validation before commit:
    - Sum usable `quantity_remaining` across valid batches
    - If total < requested quantity:
        - Block entire save
        - Show clear Arabic error explaining insufficient valid vaccine stock
        - No invoice, no vaccination, no stock changes are committed
- Internal tracking:
    - For each deduction:
        - Create `invoice_item_vaccine_batches` rows (invoice_item_id, vaccine_batch_id, quantity)
    - Batch and per-batch usage is **never shown on customer invoice**

---

## 5. Execution Rules (Key Locked Behaviors)

### 5.1 Consultation Pricing

- Consultation exists as a real `service` product in `products`
- `products.price` = default consultation fee
- Visit form:
    - Pre-fills consultation fee from `products.price`
    - Allows override per visit
- For the consultation `invoice_item`:
    - `product_id` = consultation product ID
    - `unit_price` = actual charged price from form
    - `line_total` = `unit_price * quantity` (usually `1`)

### 5.2 Phone Normalization & Customer Uniqueness

- All phone inputs must be normalized before:
    - Lookup
    - Storage
    - WhatsApp URL generation
- Store normalized phone in `customers.phone`
- `customers.phone` is unique after normalization
- On new visit or any workflow that may create a customer:
    - Normalize phone
    - If an existing customer with same normalized phone exists:
        - Reuse that customer automatically
        - Never create another row for the same normalized phone
        - Never block creation of the visit because of phone uniqueness

### 5.3 Quick Sale Vaccine Behavior

- Vaccines in quick sale:
    - Act only as invoice items + stock items
    - Trigger FEFO deduction via `StockService`
- No `vaccinations` record:
    - Quick sale of vaccines **never** creates a row in `vaccinations`
    - Only the customer visit flow (`has_vaccination = yes`) creates vaccination records

### 5.4 Product Deletion

- No soft deletes for products in version 1:
    - Do not use `deleted_at` for products
- `is_active` strategy:
    - Products table includes `is_active` boolean (default true)
    - When “deleting” a product:
        - If the product is referenced by `invoice_items`, `vaccinations`, or `vaccine_batches`:
            - Do not hard-delete
            - Set `is_active = false`
    - Inactive products:
        - Hidden from new selection lists (products, services, vaccines)
        - Still visible in historical invoices and vaccination records
- Products never in use may be hard-deleted if desired, but in general, use `is_active` for safety

---

## 6. Phase-by-Phase Implementation Plan

### Phase 1: Final Requirements & Architecture (ALREADY DONE)

- Lock business rules and vaccine batch logic (this document).

### Phase 2: Database Schema & Migrations

- Implement migrations for:
    - `customers`
    - `products`
    - `invoices`
    - `invoice_items`
    - `vaccinations`
    - `vaccine_batches`
    - `invoice_item_vaccine_batches`
- Laravel default migrations (e.g. `users`, `password_reset_tokens`, `sessions`, `cache`, `jobs`) may exist alongside the business schema.
- Add all indexes and constraints:
    - Unique normalized `customers.phone`
    - Indexes on `invoices.created_at`, `vaccinations.next_dose_date`, `vaccine_batches.product_id, expiry_date`
- Ensure:
    - `is_active` boolean exists on `products`
    - All FK constraints align with locked rules

### Phase 3: Eloquent Models & Core Helpers

- Create models:
    - `Customer`, `Product`, `Invoice`, `InvoiceItem`, `Vaccination`, `VaccineBatch`, `InvoiceItemVaccineBatch`
- Define relationships as per schema summary
- Implement:
    - Phone normalization helper (e.g. service or trait)
    - `Customer` helper to find or create by normalized phone
    - `Product` helpers:
        - Scopes for active products only
        - Scopes for vaccines vs other products

### Phase 4: Base Layout, RTL UI, and Localization

- Create master Blade layout:
    - Bootstrap 5 RTL
    - Arabic navigation and headings
    - `<html dir="rtl" lang="ar">`
- Layout structure:
    - Collapsible sidebar (`x-sidebar`) with navigation links and overlay for mobile
    - Top navbar (`x-navbar`) with page title and quick action buttons (e.g. New visit, Quick sale)
    - Alerts component (`x-alerts`) for success, warning, error, and validation errors
- Assets:
    - Google Fonts (e.g. Tajawal), Bootstrap Icons, custom layout CSS (e.g. via Vite)
- Common components:
    - Arabic flash messages
    - Pagination
- Localization:
    - Set up Arabic language files for:
        - Validation errors
        - Common labels/messages
        - Module-specific strings: `messages`, `invoices`, `products`, `customers`, `vaccinations`, `vaccine_batches`, `validation`, `pagination`

### Phase 5: Products Module

- Build:
    - Products index with search/filter:
        - Search by name (`q`)
        - Filter by type (product, service, vaccination)
        - Filter by status (all, active, inactive)
    - Create/edit product forms in Arabic
    - Toggle for `is_active`: dedicated route (e.g. `PATCH /products/{product}/toggle-active`) for one-click enable/disable
    - Delete action: hard-delete when product is not referenced; when referenced, set `is_active = false` and show appropriate message
- Enforce:
    - No hard-deletion for products that are referenced
    - Inactive products are excluded from selection lists for new entries
- For vaccines:
    - Show `products.quantity` based on usable-only batches (Phase 6 logic)

### Phase 6: Vaccine Batches & StockService

- Implement UI (admin-only) for:
    - Vaccine batches list (index) with search (e.g. by batch code or product name) and filter by vaccine product
    - Creating/editing `vaccine_batches` for vaccine products
    - Deleting batches (with validation; e.g. referenced batches may be blocked or handled via `StockService`)
- Implement `StockService`:
    - Vaccine batch CRUD helpers: `createVaccineBatch`, `updateVaccineBatch`, `deleteVaccineBatch` (where applicable)
    - For vaccines:
        - FEFO selection
        - Batch deduction and `invoice_item_vaccine_batches` creation
        - Recalculate `products.quantity` from usable-only batches
        - Update `products.stock_status` for vaccines
    - For non-vaccine, stock-tracked products:
        - Simple quantity decrement and status update

### Phase 7: Customers Module & Visit Flow

- Implement:
    - Customers list page with search and “Add customer visit” button
    - Customer visit form:
        - Customer info, consultation fee, `has_vaccination`, vaccination fields, additional products/services, final price
- Execution details:
    - Normalize phone and reuse existing customer if phone matches
    - Fetch consultation product price as default; allow override
    - On submit:
        - Run `CustomerVisitService` with full transaction:
            - Find/create customer by normalized phone
            - Create invoice (`source = 'customer'`)
            - Create invoice_items (consultation, vaccine, additional items)
            - Create vaccination entry if `has_vaccination = yes`
            - Invoke `StockService` for stock and vaccine batches
- Error handling:
    - If vaccine stock insufficient (usable-only), block save and show Arabic error

### Phase 8: Invoices Module & Quick Sale

- Implement:
    - Invoice list view in Arabic:
        - Search by invoice number and/or customer name
        - Filter by source (customer, quick_sale)
        - Link to single invoice view (show)
    - Single invoice view (show): invoice header, items table, and linked vaccinations section (if any)
    - Quick sale form:
        - Customer name (and optional phone → normalized for linking)
        - Multiple item rows with dynamic totals (vanilla JS)
- Routes:
    - Resource routes for invoices including `show` (e.g. `invoices.index`, `invoices.create`, `invoices.store`, `invoices.show`)
- `InvoiceService`:
    - Generate `invoice_number`
    - Create invoice and items
    - Call `StockService` for stock handling
- Ensure:
    - Vaccine sales via quick sale do not create `vaccinations` records

### Phase 9: Vaccinations Module & WhatsApp

- Implement:
    - Vaccinations list page:
        - Customer name, normalized phone, animal_type, vaccine name, vaccination_date, next_dose_date, WhatsApp button
    - WhatsApp button:
        - Use normalized phone with `https://wa.me/<phone>`
- Upcoming doses:
    - Filter by `next_dose_date >= today`

### Phase 10: Dashboard

- Metrics:
    - Today’s visits (invoice count)
    - Today’s revenue (invoice sum)
    - Total products
    - Total vaccinations
    - Upcoming vaccinations (next 7 days)
    - Low-stock items
    - Vaccine expiry monitor:
        - Count/list expired and expiring-soon batches
- UI:
    - Quick Actions card with links to: New visit, Quick sale, Add product, Add vaccine batch
- Implementation:
    - Dashboard may be implemented as a route closure (e.g. `Route::get('/', ...)`) or a dedicated controller; use efficient aggregate queries and Eloquent where practical

### Phase 11: Validation, Policies, and Edge Cases

- Use Form Requests for:
    - Products
    - Customers/visits
    - Quick sale invoices
    - Vaccine batches
- Key validations:
    - Normalized phone uniqueness
    - Numeric and non-negative prices/quantities
    - No vaccine operations if usable stock is insufficient
- Ensure:
    - All multi-table operations wrapped in DB transactions
    - Clear Arabic error messages for all failures

### Phase 12: Seeders, Factories, and Installation Guide

- Seeders:
    - Default consultation service product (“كشف”) with default price
    - Example products, services, vaccines
    - Example vaccine_batches with realistic dates and quantities
    - Example customers, invoices, vaccinations
- Factories:
    - For core models to enable future testing
- Installation guide:
    - Environment setup
    - Database creation
    - `composer install`, `php artisan migrate --seed`
    - Overview of phone normalization, invoice number format, and batch-based vaccine stock

---

This `FINAL_LOCKED_IMPLEMENTATION_PLAN.md` is the **single source of truth** for all future implementation phases of the Veterinary Clinic Management System.
