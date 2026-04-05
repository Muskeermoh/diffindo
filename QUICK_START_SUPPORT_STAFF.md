# Quick Start - Support Staff System

## 1. Apply Database Migration

Run this SQL in your database:

```sql
ALTER TABLE users ADD COLUMN role ENUM('admin', 'customer', 'support_staff') DEFAULT 'customer' AFTER email;
ALTER TABLE users ADD COLUMN nic VARCHAR(50) DEFAULT NULL AFTER role;
ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL AFTER nic;
UPDATE users SET role = 'admin' WHERE email = 'admin@diffindo.com';
```

Or use the migration file: `migrations/003_add_role_to_users.sql`

## 2. Create Support Staff User

### Via Admin Panel (Recommended)
1. **Login as Admin**: admin@diffindo.com / admin123
2. **Navigate to**: Dashboard → Support Staff
3. **Click**: "Add New Staff"
4. **Fill in**:
   - Full Name: (Support staff name)
   - Email Address: (unique email for login)
   - NIC Number: (national ID)
   - Phone Number: (contact number)
   - Address: (full address)
   - Password: (create password)
5. **Click**: "Create Staff"

### Via SQL (Direct)
```sql
INSERT INTO users (name, email, role, nic, phone, address, password) 
VALUES 
(
    'John Support',
    'john@support.com',
    'support_staff',
    '123456789',
    '+94712345678',
    '123 Support Street, City',
    SHA2('password123', 256)
);
```

## 3. Support Staff Login & Usage

1. **Go to**: http://localhost/diffindo-cakes-and-bakes/login.php
2. **Enter**:
   - Email: (support staff email)
   - Password: (password set during creation)
3. **Access**: Support Dashboard
4. **Features**:
   - View order statistics
   - Filter orders by status
   - Accept/reject pending orders
   - View customer details
   - Track order items and amounts

## 4. File Structure

```
diffindo-cakes-and-bakes/
├── includes/
│   └── auth.php                    (Updated - Role functions)
├── admin/
│   ├── dashboard.php               (Updated - Updated navigation)
│   ├── orders.php                  (Updated - Updated navigation)
│   ├── products.php                (Updated - Updated navigation)
│   ├── image-manager.php           (Updated - Updated navigation)
│   └── support-staff.php           (NEW - Create/manage support staff)
├── support/                        (NEW - Support staff folder)
│   ├── dashboard.php               (NEW - Support staff dashboard)
│   └── orders.php                  (NEW - Order management)
├── login.php                       (Updated - Role-based redirect)
├── database.sql                    (Updated - New schema)
├── migrations/
│   └── 003_add_role_to_users.sql   (NEW - Migration file)
└── SETUP_SUPPORT_STAFF.md          (NEW - Setup guide)
```

## 5. Login Redirects by Role

| User Type | Login Email | Redirect To |
|-----------|-------------|-------------|
| Admin | admin@diffindo.com | /admin/dashboard.php |
| Support Staff | (created via admin) | /support/dashboard.php |
| Customer | (registered customer) | /user/dashboard.php |

## 6. Key Functions (auth.php)

```php
is_admin()             // Returns true if user is admin
is_support_staff()     // Returns true if user is support staff
is_customer()          // Returns true if user is customer
require_admin()        // Redirect if not admin
require_support_staff()// Redirect if not support staff
get_user_role()        // Get current user's role
```

## 7. New Columns in Users Table

| Column | Type | Purpose |
|--------|------|---------|
| role | ENUM | User type: admin, customer, support_staff |
| nic | VARCHAR(50) | NIC number for support staff |
| address | TEXT | Address for support staff |

## 8. Order Status Management

Support staff can:
- **View** - All orders with filters
- **Filter** - By status (pending, accepted, rejected)
- **Accept** - Set status to "accepted"
- **Reject** - Set status to "rejected"

## 9. Troubleshooting

### "Support Staff Dashboard not found"
- Make sure migration was applied
- Check that support/dashboard.php file exists
- Verify directory permissions

### "Cannot create support staff"
- Verify you're logged in as admin
- Check that /admin/support-staff.php exists
- Make sure email is unique

### Login redirects to wrong page
- Verify database migration was applied
- Check that user role is set correctly in database
- Clear browser cache and session

### Orders not showing for support staff
- Verify support staff folder exists (`/support/`)
- Check database connection
- Verify order data exists in database

## 10. Security Notes

✅ **Implemented Security**:
- Passwords hashed with SHA2(256)
- Role-based access control on all pages
- Support staff cannot access admin panel
- Admin cannot access support staff pages
- Automatic redirects for unauthorized access

## 11. Next Steps

1. ✅ Apply migration
2. ✅ Create test support staff account
3. ✅ Test login with support staff credentials
4. ✅ Test order accept/reject functionality
5. ✅ Verify email notifications (optional - requires email config)
6. ✅ Train support staff on system usage

## Support

For issues or questions, check:
1. SETUP_SUPPORT_STAFF.md (detailed documentation)
2. Database migration status
3. File permissions in /support/ directory
4. Session/cookie settings in browser
