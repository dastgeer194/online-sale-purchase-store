# Barqora Store

Barqora Store is a PHP/MySQL ecommerce application with a customer-facing storefront and an admin panel for managing products, categories, orders, pages, sliders, and site settings.

This project runs as a classic PHP application. There is no Composer, npm, or build step required.

## Main Features

- Product, category, slider, FAQ, page, and customer management from the admin panel
- Cart and checkout flow for the storefront
- Guest checkout support
- Cash on Delivery, Bank Deposit, and PayPal payment options
- Order and shipping management in admin
- Product reviews and ratings

## Requirements

- PHP 7.2 or later
- MySQL or MariaDB
- Apache or another PHP-capable web server
- A local stack such as XAMPP, WAMP, or Laragon

## Project Structure

- Frontend entry: `index.php`
- Admin panel: `admin/`
- Database config: `admin/inc/config.php`
- Payment handlers: `payment/`
- Uploads and theme assets: `assets/`

## Local Setup

1. Place the project inside your web root.
   Example for XAMPP on Windows:
   `C:\xampp\htdocs\barqora`

2. Start Apache and MySQL from your local stack.

3. Create a database in MySQL or MariaDB.
   Example:
   `u890682789_barqora`

4. Import your database.
   If you already have a local SQL export such as `u890682789_barqora.sql`, import that into the database you created.

5. Update the database and base URL settings in `admin/inc/config.php`.
   These are the values you should review:

   - `$dbhost`
   - `$dbname`
   - `$dbuser`
   - `$dbpass`
   - `BASE_URL`

6. For local XAMPP usage, a typical config looks like this:

```php
$dbhost = 'localhost';
$dbname = 'u890682789_barqora';
$dbuser = 'root';
$dbpass = '';
define("BASE_URL", "http://localhost/barqora//");
```

7. Open the site in your browser:

   - Storefront: `http://localhost/barqora/`
   - Admin panel: `http://localhost/barqora/admin/`

## Admin Login

Admin authentication uses the `tbl_user` table in your database.

- If you imported an existing SQL dump, use the admin account stored in that dump.
- If login fails, first confirm that `tbl_user` contains at least one active admin record.

## Database Notes

The full database dumps used locally are intentionally not committed to this repository. That is why you will only see the migration file here.

Included migration:

- `guest_checkout_migration.sql`

This migration adds the guest checkout fields to `tbl_payment`:

- `customer_phone`
- `customer_address`
- `customer_city`

Run it if your existing database was created before guest checkout support was added.

## Payment Options

This project currently supports:

- PayPal
- Bank Deposit
- Cash on Delivery

Cash on Delivery and guest checkout are already integrated in the current codebase.

## Guest Checkout

Customers can place orders without creating an account.

Required guest checkout fields:

- Name
- Phone
- Address
- City

Optional guest checkout field:

- Email

## Troubleshooting

### Database connection error

Check the values in `admin/inc/config.php` and make sure:

- MySQL is running
- The database exists
- The username and password are correct

### Wrong links, missing CSS, or broken images

Make sure `BASE_URL` in `admin/inc/config.php` matches the folder name and URL you are using.

Example:

- Correct for local XAMPP folder `barqora`:
  `http://localhost/barqora//`

### Admin login does not work

Verify that:

- `tbl_user` exists
- The admin record is present
- The account status is `Active`

### SQL import error: `#1118 Row size too large`

If you are importing an older database dump and hit this error on `tbl_settings`, use the updated dump structure or convert the wide `VARCHAR(255)` columns in `tbl_settings` to `TEXT` and use `ROW_FORMAT=DYNAMIC`.

### Uploads not displaying

Make sure these directories and files are present and readable by the web server:

- `assets/uploads/`
- `assets/uploads/product_photos/`

## Deployment Notes

Before moving to production:

- Update `BASE_URL`
- Replace local database credentials
- Review payment settings in admin
- Verify uploaded images and banners
- Test checkout, guest checkout, and Cash on Delivery flow

## License

No license file is currently included in this repository.
