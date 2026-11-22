-- =====================================================================
-- Stores the registration numbers collected on the supermarket and
-- charity sign-up forms, so admins can verify them before approving
-- the account (Admin > Registrations). Run once via phpMyAdmin's SQL
-- tab, or:
--   mysql -u root -p second_harvest < database/migration_add_registration_numbers.sql
-- =====================================================================
USE second_harvest;

ALTER TABLE outlets
  ADD COLUMN business_reg_number VARCHAR(50) NULL AFTER region;

ALTER TABLE charities
  ADD COLUMN charity_reg_number VARCHAR(50) NULL AFTER org_name;
