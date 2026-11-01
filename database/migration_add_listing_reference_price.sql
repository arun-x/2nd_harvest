-- =====================================================================
-- Adds the per-kg reference price that supermarket staff enter when
-- creating a listing (the Consumer discount engine reduces it). Databases
-- created from an older schema.sql are missing it, which makes creating a
-- listing fail. Run once via phpMyAdmin's SQL tab, or:
--   mysql -u root -p second_harvest < database/migration_add_listing_reference_price.sql
-- =====================================================================
USE second_harvest;

ALTER TABLE listings
  ADD COLUMN reference_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER quantity_remaining_kg;
