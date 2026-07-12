-- =====================================================================
-- 2nd Harvest -- Sample / Test Data
-- Run AFTER schema.sql:
--   mysql -u root -p second_harvest < seed.sql
--
-- Every seeded user below has the password:  Password123!
-- (already bcrypt-hashed, works directly with PHP's password_verify())
-- =====================================================================

USE second_harvest;

-- ---------------------------------------------------------------
-- USERS (one per role, all pre-approved so you can log in immediately)
-- ---------------------------------------------------------------
INSERT INTO users (role, email, password_hash, full_name, phone, status) VALUES
('employee', 'staff@freshmart.lk',   '$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Nimal Perera',      '0771234567', 'approved'),
('charity',  'contact@hopekitchen.lk','$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Hope Kitchen Rep',  '0777654321', 'approved'),
('consumer', 'consumer1@example.com','$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Amaya Silva',       '0712223344', 'approved'),
('admin',    'admin@2ndharvest.lk',  '$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Platform Admin',    '0700000000', 'approved'),
-- Extra pending accounts, useful for testing UC-20 (Verify & approve registrations)
('employee', 'staff2@greengrocer.lk','$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Kasun Fernando',    '0719988776', 'pending'),
('charity',  'info@foodforall.lk',   '$2b$12$i35ML/yWCfJlZZD.9Zgx2.cxVjakJAQZCLh3YHubBxWhMrOw6LRKS', 'Food For All Rep',  '0765554433', 'pending');

-- ---------------------------------------------------------------
-- OUTLET profile linked to the approved employee (user_id = 1)
-- ---------------------------------------------------------------
INSERT INTO outlets (user_id, outlet_name, branch_location, region) VALUES
(1, 'FreshMart Supermarket - Colombo 05', '123 Havelock Road, Colombo 05', 'Colombo');

-- ---------------------------------------------------------------
-- CHARITY profile linked to the approved charity (user_id = 2)
-- ---------------------------------------------------------------
INSERT INTO charities (user_id, org_name, address, operational_focus) VALUES
(2, 'Hope Kitchen', '45 Galle Road, Colombo 06', 'Daily meal programme for low-income families');

-- ---------------------------------------------------------------
-- LISTINGS posted by the outlet (outlet_id = 1, posted_by = user 1)
-- ---------------------------------------------------------------
INSERT INTO listings
  (outlet_id, item_name, category, quantity_kg, quantity_remaining_kg,
   expiry_date, claim_deadline, status, posted_by)
VALUES
(1, 'Bananas',       'fruit',     12.5, 12.5, CURDATE(),                       CONCAT(CURDATE(), ' 19:00:00'), 'available', 1),
(1, 'Carrots',       'vegetable',  8.0,  8.0, DATE_ADD(CURDATE(), INTERVAL 1 DAY), CONCAT(CURDATE(), ' 19:00:00'), 'available', 1),
(1, 'Tomatoes',      'vegetable', 15.0,  0.0, CURDATE(),                       CONCAT(CURDATE(), ' 19:00:00'), 'collected', 1);

-- ---------------------------------------------------------------
-- PICKUP SLOTS for the first two listings
-- ---------------------------------------------------------------
INSERT INTO pickup_slots (listing_id, slot_start, slot_end, capacity) VALUES
(1, CONCAT(CURDATE(), ' 19:00:00'), CONCAT(CURDATE(), ' 19:30:00'), 3),
(1, CONCAT(CURDATE(), ' 19:30:00'), CONCAT(CURDATE(), ' 20:00:00'), 3),
(2, CONCAT(CURDATE(), ' 19:00:00'), CONCAT(CURDATE(), ' 19:30:00'), 2);

-- ---------------------------------------------------------------
-- A completed reservation + pickup for the third (collected) listing
-- so the Collection Log / History pages have something to display
-- ---------------------------------------------------------------
INSERT INTO reservations
  (listing_id, user_id, reserved_qty_kg, reservation_type, discount_pct, price_paid, status)
VALUES
(3, 2, 15.0, 'charity_priority', 100.00, 0.00, 'completed');

INSERT INTO pickups (reservation_id, collected_qty_kg, confirmed_by) VALUES
(1, 15.0, 2);

-- ---------------------------------------------------------------
-- A sample notification
-- ---------------------------------------------------------------
INSERT INTO notifications (user_id, message, type) VALUES
(2, 'New listing available: Bananas at FreshMart Supermarket - Colombo 05', 'new_listing');

