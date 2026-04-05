# Support Staff & Role-Based System - Setup Guide

## Overview
This implementation adds a complete role-based user system with support for Admin, Customer, and Support Staff roles. Support staff can login and manage customer orders independently.

## Database Changes

### Migration File
Run the migration: `migrations/003_add_role_to_users.sql`

This adds the following to the users table:
- `role` ENUM('admin', 'customer', 'support_staff') - Default: 'customer'
- `nic` VARCHAR(50) - Support staff NIC number
- `address` TEXT - Support staff address

**To apply the migration:**

Option 1: Using phpMyAdmin
1. Open phpMyAdmin
2. Select your `diffindo` database
3. Click on "SQL" tab
4. Copy and paste the contents of `migrations/003_add_role_to_users.sql`
5. Click "Go"

Option 2: Using MySQL command line
```bash
mysql -u root diffindo < migrations/003_add_role_to_users.sql
```

Option 3: Using the raw SQL commands
```sql
ALTER TABLE users ADD COLUMN role ENUM('admin', 'customer', 'support_staff') DEFAULT 'customer' AFTER email;
ALTER TABLE users ADD COLUMN nic VARCHAR(50) DEFAULT NULL AFTER role;
ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL AFTER nic;
UPDATE users SET role = 'admin' WHERE email = 'admin@diffindo.com';
```

## New Files & Features

### 1. Admin Panel - Support Staff Management
**File:** `admin/support-staff.php`

Features:
- Create new support staff members with: name, email, NIC, phone, address, password
- Edit existing support staff details
- Delete support staff members
- View list of all support staff

**Access:** Admin dashboard → Support Staff link

### 2. Support Staff Dashboard
**File:** `support/dashboard.php`

Features:
- Dashboard overview with statistics (total orders, pending, accepted, rejected)
- Recent orders list
- Quick links to manage orders

**Access:** Login → Redirects to support staff dashboard for support_staff role

### 3. Support Staff Order Management
**File:** `support/orders.php`

Features:
- View all orders with filtering (all, pending, accepted, rejected)
- View detailed order information including:
  - Customer details and contact info
  - Order items with images and prices
  - Delivery information
  - Total amount
- Accept or reject pending orders
- Status management

**Access:** Support dashboard → Manage Orders link

## Updated Files

### 1. Database Schema
**File:** `database.sql`
- Updated users table creation script with new columns

### 2. Authentication Functions
**File:** `includes/auth.php`
- `is_admin()` - Check if user is admin
- `is_support_staff()` - Check if user is support staff
- `is_customer()` - Check if user is customer
- `require_admin()` - Require admin access (with redirect)
- `require_support_staff()` - Require support staff access (with redirect)
- `get_user_role()` - Get current user's role

### 3. Login System
**File:** `login.php`
- Role-based redirect (admin → /admin/dashboard.php, support_staff → /support/dashboard.php, customer → /user/dashboard.php)

### 4. Admin Pages (Updated Navigation)
- `admin/dashboard.php` - Added Support Staff link
- `admin/products.php` - Added Support Staff link, updated to use require_admin()
- `admin/orders.php` - Added Support Staff link, updated to use require_admin()
- `admin/image-manager.php` - Added Support Staff link, updated to use require_admin()

## User Roles & Permissions

### Admin
- Login at `/login.php`
- Full access to admin panel
- Can create, edit, delete support staff members
- Can manage all products, orders, images, feedbacks
- Dashboard: `/admin/dashboard.php`

### Support Staff
- Login at `/login.php`
- Can view and manage orders (accept/reject)
- Cannot manage products or create support staff
- Dashboard: `/support/dashboard.php`
- Orders Management: `/support/orders.php`

### Customer
- Login at `/login.php`
- Can place orders
- Can view own orders
- Dashboard: `/user/dashboard.php`

## Creating Support Staff

1. Login as Admin
2. Go to Admin Panel → Support Staff
3. Click "Add New Staff"
4. Fill in the form:
   - Full Name: Support staff member's name
   - Email Address: Unique email for login
   - NIC Number: National ID number
   - Phone Number: Contact number
   - Address: Full address
   - Password: Login password (at least 6 characters recommended)
5. Click "Create Staff Button"

The support staff can now login with their email and password.

## How It Works

### Login Flow
1. User enters email and password
2. System checks credentials and fetches user role
3. Redirects based on role:
   - Admin → Admin Dashboard
   - Support Staff → Support Dashboard
   - Customer → Customer Dashboard

### Order Management (Support Staff)
1. Support staff logs in
2. Views dashboard with order statistics
3. Clicks "Manage Orders"
4. Can filter orders by status
5. Clicks on order to view details
6. Can accept or reject pending orders
7. Status changes are instant

## Session Management
- All role checks are done through `$_SESSION['user']['role']`
- Session data is set during login from database
- Logout clears the session

## Security Notes
- Passwords are hashed using SHA2(256)
- Admin check uses role-based permission system
- Each protected page checks user role before displaying content
- Support staff cannot access admin pages
- Customers cannot access support staff or admin pages

## Testing Checklist

- [ ] Apply migration to database
- [ ] Create a test support staff account via admin panel
- [ ] Login with support staff credentials
- [ ] Verify support staff dashboard loads correctly
- [ ] Test order filtering on support staff orders page
- [ ] Test accepting an order
- [ ] Test rejecting an order
- [ ] Verify order status updates correctly
- [ ] Test logout functionality
- [ ] Verify customer can still login and access their dashboard
- [ ] Verify admin still has full access
