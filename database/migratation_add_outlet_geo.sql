-- =====================================================================
-- Adds geocoded coordinates to outlets, needed for the 30km radius
-- filter on the consumer marketplace. Run once via phpMyAdmin's SQL
-- tab, or:
--   mysql -u root -p second_harvest < database/migration_add_outlet_geo.sql
-- =====================================================================
USE second_harvest;

ALTER TABLE outlets
  ADD COLUMN latitude  DECIMAL(10,7) NULL AFTER region,
  ADD COLUMN longitude DECIMAL(10,7) NULL AFTER latitude;

-- Speeds up the bounding-box pre-filter used before the precise
-- Haversine distance calculation (see Outlet::findNearby()).
CREATE INDEX idx_outlets_geo ON outlets (latitude, longitude);
