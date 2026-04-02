-- Add columns to support password reset tokens and expiration times.
ALTER TABLE users
  ADD COLUMN password_reset_token VARCHAR(255) NULL AFTER password,
  ADD COLUMN password_reset_expires DATETIME NULL AFTER password_reset_token;
