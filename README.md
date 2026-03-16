# Veterinary Clinic Management System (VCMS)

A robust, modern, clean, and fully right-to-left (RTL) Arabic web application built with Laravel 12 and Bootstrap 5 to manage veterinary clinic operations efficiently. 

This system handles customer visits, invoicing, product/service management, complex vaccine inventory (using FEFO batch deduction), and upcoming vaccination reminders via WhatsApp.

## Key Features

- **Modern Arabic UI**: Fully responsive, professional RTL design built with custom Bootstrap 5.
- **Unified Catalog**: Manages physical products, non-stock services (e.g., consultations), and vaccines in one place.
- **Complex Vaccine Stock**: Authoritative vaccine availability is dynamically calculated across non-expired batches using First-Expired-First-Out (FEFO) rules.
- **Customer Visits Workflow**: A streamlined single form handling customer entity, consultation fees, real-time total calculations, and medical vaccination records.
- **Quick Sales**: Fast point-of-sale interface for OTC products and services.
- **Financial Integrity**: All operations affecting cash flow are recorded strictly as un-deletable Invoices.
- **Intelligent Customer Re-use**: Automatic Egyptian phone number normalization ensures customer profiles are matched and reused without duplicate entries.
- **WhatsApp Integration**: Instant one-click messaging to clients for critical upcoming vaccinations.

## Requirements

- **PHP**: 8.3+
- **Database**: MySQL 8.0+ / MariaDB 10.6+
- **Extensions**: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML (standard Laravel requirements)
- **Composer**: 2.x

## Installation Guide

Follow these steps to set up the system on your development or production environment:

1. **Clone or Extract the Application**
   Navigate to your desired web root directory and place the application files.

2. **Install PHP Dependencies**
   Run Composer to install all necessary packages:
   ```bash
   composer install --no-interaction --prefer-dist --optimize-autoloader
   ```

3. **Environment Configuration**
   Copy the example environment file and generate your application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration**
   Open the `.env` file and configure your database connection settings:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=vet_clinic
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```
   *Make sure you create the `vet_clinic` empty database in MySQL before proceeding.*

5. **Run Migrations & Seeding**
   The application comes with structured seeders that populate the default required Consultation Service product ("كشف"), example clients, items, and realistic vaccine batches.
   Execute the migration and seeder:
   ```bash
   php artisan migrate --seed
   ```

6. **Serve the Application**
   For local development, you can start Laravel's built-in server:
   ```bash
   php artisan serve
   ```
   The application will be available at `http://127.0.0.1:8000`.

## Architecture & Technical Decisions

- **Phone Normalization**: The system automatically strips non-numeric characters, removes leading zeroes, and prepends '20' (Egypt country code) before processing. This guarantees `+20 10 1234-5678` and `01012345678` are perfectly matched to the same profile.
- **Invoice Sequences**: Uses database locks to ensure strict generation of invoice numbers (e.g. `INV-000001`). 
- **Product Safety**: Products utilized in any capacity (invoices or vaccinations) are never hard-deleted; they are toggled as structurally `inactive` to maintain history.
- **Transaction Safety**: Multi-table insertions like a `CustomerVisit` (spanning Customers, Invoices, Items, Vaccinations, and Stock Deductions) are grouped tightly inside `$db->transaction()`, ensuring rollbacks on partial failures (e.g., inadequate vaccine batch stock).

## Security & Maintenance

- Maintain scheduled backups of the MySQL Database.
- Keep the `APP_ENV` strictly as `production` and `APP_DEBUG` as `false` when deployed on a live server.
- The `is_active` boolean ensures referential data integrity avoiding orphan financial records compared to soft-deletes.
