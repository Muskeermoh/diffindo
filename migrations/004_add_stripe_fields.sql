-- Add Stripe payment fields to orders table
ALTER TABLE orders ADD COLUMN stripe_payment_intent_id VARCHAR(255) DEFAULT NULL AFTER status;
ALTER TABLE orders ADD COLUMN stripe_charge_id VARCHAR(255) DEFAULT NULL AFTER stripe_payment_intent_id;
ALTER TABLE orders ADD COLUMN payment_status ENUM('pending', 'completed', 'refunded') DEFAULT 'pending' AFTER stripe_charge_id;
