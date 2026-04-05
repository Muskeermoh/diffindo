-- Add role column to users table
ALTER TABLE users ADD COLUMN role ENUM('admin', 'customer', 'support_staff') DEFAULT 'customer' AFTER email;

-- Add support staff specific fields
ALTER TABLE users ADD COLUMN nic VARCHAR(50) DEFAULT NULL AFTER role;
ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL AFTER nic;

-- Update admin user role
UPDATE users SET role = 'admin' WHERE email = 'admin@diffindo.com';
