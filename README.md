# Diffindo Cakes and Bakes ??

A beginner-friendly bakery e-commerce application built with PHP and MySQL. This guide explains how to set up the project step by step, even if you have very little technical knowledge.

## ?? What this project does
- Customers can browse cakes, add items to cart, register, and place orders.
- Admins can manage orders and products.
- Orders can be approved or rejected.
- Stripe payments are used during checkout.
- Rejected paid orders are refunded automatically.
- Email notifications are sent for order actions.

## ?? What you need before starting
- A local PHP development environment such as:
  - **Laragon**
  - **XAMPP**
  - **WAMP**
- **PHP 7.4+**
- **MySQL 5.7+**
- **Composer** installed on your machine
- A browser such as Chrome or Edge

## ?? Step-by-step setup guide

### 1. Place the project in your local server folder
Put the folder in the correct location for your environment:
- Laragon: `C:\laragon\www\diffindo-cakes-and-bakes`
- XAMPP: `C:\xampp\htdocs\diffindo-cakes-and-bakes`
- WAMP: `C:\wamp64\www\diffindo-cakes-and-bakes`

### 2. Install dependencies
Open PowerShell and run:
```powershell
cd C:\laragon\www\diffindo-cakes-and-bakes
composer install
```

### 3. Create the `.env` file
The project uses a local `.env` file for private settings.

Copy the example file:
```powershell
copy .env.example .env
```

Open `.env` with a text editor and fill in your values. Example:
```ini
STRIPE_PUBLISHABLE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET_KEY=sk_test_your_secret_key_here
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=465
EMAIL_ENCRYPTION=ssl
EMAIL_USERNAME=your-email@gmail.com
EMAIL_PASSWORD=your-app-password
EMAIL_FROM=your-email@gmail.com
EMAIL_FROM_NAME="Diffindo Cakes & Bakes"
```

> Important: `.env` is private and should never be shared or uploaded to Git.

### 4. Set up the database
Open phpMyAdmin or MySQL and create a new database. Example name:
- `diffindo`

### 5. Import the database schema
Use `database.sql` to create tables and initial data.

Option A: Use phpMyAdmin
- Log in to phpMyAdmin
- Select the `diffindo` database
- Use the Import tab and upload `database.sql`

Option B: Use command line
```powershell
mysql -u root -p diffindo < database.sql
```

### 6. Apply migrations if needed
If you already have the database from an older version, run the migration files in order.

Each file in `migrations/` contains a schema update:
- `migrations/001_add_users_phone.sql`
- `migrations/002_add_password_reset_fields.sql`
- `migrations/003_add_role_to_users.sql`
- `migrations/004_add_stripe_fields.sql`

Run them with phpMyAdmin or command line, for example:
```powershell
mysql -u root -p diffindo < migrations/004_add_stripe_fields.sql
```

### 7. Configure the database connection
Open `includes/db.php` and update these values if your local database is different:
- `host`
- `db`
- `user`
- `pass`
- `port`

For most local systems, these are the defaults:
- host: `localhost`
- database: `diffindo`
- user: `root`
- password: `` (empty)
- port: `3306`

### 8. Configure email sending
The app uses SMTP email through `includes/email-config.php`.

If you use Gmail:
1. Turn on 2-Step Verification.
2. Create an App Password.
3. Put that app password in `EMAIL_PASSWORD`.

You can also keep email sending disabled and use the log file instead.

### 9. Open the website
In your browser, go to:
```text
http://localhost/diffindo-cakes-and-bakes/
```
If you use Laragon and the site is configured as a local domain, use the Laragon domain instead.

## ?? What to do first in the app
1. Register a new customer account.
2. Browse products from the homepage.
3. Add products to the cart.
4. Open the cart and click Checkout.
5. Fill in delivery details and click Proceed to Payment.
6. Enter Stripe test card details and complete payment.

## ?? Stripe test payments
Use these cards in the checkout form:
- Success: `4242 4242 4242 4242`
- Requires authentication: `4000 0025 0000 3155`
- Declined: `4000 0000 0000 0002`

Use any future expiration date and any 3-digit CVC.

## ?? How refunds work
- When an order is rejected in `admin/orders.php` or `support/orders.php`, the system attempts to refund the Stripe payment.
- The customer receives an email with refund details and refund ID.
- If the refund fails, the admin/user sees an error message.

## ?? Database migrations explained
Additional database changes are stored in `migrations/`.

If you started with an older copy of the database, use each migration in order:
- `migrations/001_add_users_phone.sql`
- `migrations/002_add_password_reset_fields.sql`
- `migrations/003_add_role_to_users.sql`
- `migrations/004_add_stripe_fields.sql`

If you created the database from scratch with `database.sql`, you may already have the required tables.

## ?? Important files to know
- `includes/db.php` — database connection settings
- `includes/stripe-config.php` — Stripe payment settings
- `includes/email-config.php` — email SMTP settings
- `includes/mailer.php` — code that sends email notifications
- `order/checkout.php` — checkout page and Stripe payment integration
- `admin/orders.php` — admin order approval and refund logic
- `support/orders.php` — support staff order handling
- `database.sql` — main database schema file
- `migrations/` — incremental schema updates
- `.env.example` — example environment file

## ??? Troubleshooting for beginners
### If the site does not load
- Make sure the project folder is inside your local server folder.
- Check the URL carefully.
- Restart your local server.

### If you see a database error
- Open `includes/db.php` and verify the database name, user, and password.
- Confirm the database exists in phpMyAdmin.
- Make sure `database.sql` has been imported.

### If payment setup fails
- Confirm your Stripe keys are in `.env`.
- Use the Stripe test cards above.
- Ensure `STRIPE_SECRET_KEY` is correct.

### If email does not send
- Check SMTP settings in `.env` or `includes/email-config.php`.
- Use an app password for Gmail.
- If you do not want real email sending, you can disable email sending in the project.

### If GitHub blocks your push
- Do not commit real keys or secrets.
- Keep sensitive values in `.env`.
- `.env` is already ignored by Git.

## ?? Beginner-friendly summary
To set this up, follow these exact steps:
1. Put the folder in Laragon/XAMPP/WAMP web directory.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and fill in your values.
4. Create MySQL database `diffindo`.
5. Import `database.sql`.
6. Open `http://localhost/diffindo-cakes-and-bakes/`.

If anything is confusing, go back to the step and do it slowly. The most important things are:
- `.env` must exist and contain your keys
- the database must be imported successfully
- `includes/db.php` must match your local MySQL login

## ? Summary
This project is a PHP/MySQL bakery shop with:
- user accounts
- shopping cart
- Stripe checkout
- order approval and refund workflow
- email notifications
- admin/support order tools

If you want, I can also create a short one-page “cheat sheet” with only the commands and settings needed for a first install.
