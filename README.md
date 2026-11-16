# FitPulse — Laboratory Exercise 7
**Advanced MVC Architecture with Laravel: Automated Inventory Replenishment and Supplier Management**
Web Systems and Technologies · RMMC · Laravel 8 / PHP 8.0 / MySQL (XAMPP)

This is a standalone project. It uses its own database (`lab7_2411600026`), so your Lab 6 project and data are not touched.

## Quick start (Windows + XAMPP)
1. Start **MySQL** (and Apache) in the XAMPP Control Panel.
2. Put this folder in `C:\xampp\htdocs\lab7_2411600026`.
3. Double-click **`setup_lab7.bat`**. It creates the database, installs the PDF package, creates the tables, loads the demo data and starts the server.
4. Open http://127.0.0.1:8000 and log in.

### Manual steps (if you prefer)
```powershell
cd C:\xampp\htdocs\lab7_2411600026
composer require barryvdh/laravel-dompdf:^2.0
php artisan config:clear
php artisan migrate
php artisan db:seed
php artisan serve
```
Create the database `lab7_2411600026` (utf8mb4_unicode_ci) in phpMyAdmin first. `.env` is already set up for it.

## Logins (password for all: `password123`)
| Username  | Role      | Can do |
|-----------|-----------|--------|
| admin     | admin     | everything, including approving orders of ₱50,000+ and restoring suppliers |
| manager   | manager   | manage suppliers, submit/approve orders under ₱50,000, send orders, cancel, reports |
| staff     | staff     | create orders, edit own drafts |
| warehouse | warehouse | receive goods |

## E-mail (Mailtrap)
By default `.env` has `MAIL_MAILER=log`, so e-mails are written to `storage/logs/laravel.log` and nothing fails. For screenshot 15, set your Mailtrap SMTP values in `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=...
MAIL_PASSWORD=...
```
then `php artisan config:clear`. `QUEUE_CONNECTION=sync` sends immediately; use `database` plus `php artisan queue:work` for real queuing.

## Try the workflow
1. **Suppliers** — add, search, filter, delete (blocked while orders are open), filter "Deleted (soft)" and Restore (admin).
2. **Dashboard** — "Procurement overview" widgets (refresh every 30 s from `/api/dashboard/*`).
3. **Reorder check** (screenshot 16):
   `php artisan inventory:check-reorder --dry-run` then `php artisan inventory:check-reorder`
   Schedule: `php artisan schedule:list` · run it with `php artisan schedule:work` (daily at 06:00).
4. **Purchase order flow** — `staff` creates and submits → `manager` approves (admin if ≥ ₱50,000) and sends → `warehouse` receives (creates stock-in transactions) → **Supplier Performance** report updates.
5. **Exports** — CSV/PDF buttons on Purchase Orders, Transaction History and the performance report.

## Where the code is (for the screenshots)
| Item | File |
|---|---|
| Supplier model | `app/Models/Supplier.php` |
| PurchaseOrder model | `app/Models/PurchaseOrder.php` |
| ReorderService | `app/Services/ReorderService.php` |
| PurchaseOrderPolicy | `app/Policies/PurchaseOrderPolicy.php` (Gates in `app/Providers/AuthServiceProvider.php`) |
| Mailable | `app/Mail/PurchaseOrderCreated.php` |
| Scheduled command | `app/Console/Kernel.php`, `app/Console/Commands/CheckReorderPoints.php` |
| View composers | `app/Providers/ViewServiceProvider.php` |

## Design notes
- Reorder point = (average daily usage × lead time) + safety stock. Usage = units sold in the last 30 days; lead time = the supplier's; safety stock = the product's reorder level.
- Suggested quantity tops up to (reorder point + 14 days of demand), respects the supplier minimum order quantity, and rounds up to the pack size.
- Products already on an open order are skipped, so the daily job never makes duplicates.
- Approval history and status e-mails are handled in `PurchaseOrderObserver`.
