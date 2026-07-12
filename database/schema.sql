-- =====================================================================
-- 2nd Harvest -- Full Database Schema
-- Run this once via phpMyAdmin's SQL tab, or:
--   mysql -u root -p < schema.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS second_harvest
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE second_harvest;

-- ---------------------------------------------------------------
-- USERS: one row per login-capable account, any role
-- ---------------------------------------------------------------
CREATE TABLE users (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  role            ENUM('employee','charity','consumer','admin') NOT NULL,
  email           VARCHAR(150) NOT NULL UNIQUE,
  password_hash   VARCHAR(255) NOT NULL,
  full_name       VARCHAR(150) NOT NULL,
  phone           VARCHAR(30)  NULL,
  status          ENUM('pending','approved','rejected','locked') NOT NULL DEFAULT 'pending',
  failed_logins   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- OUTLETS: supermarket branch profile, one-to-one with an Employee user
-- ---------------------------------------------------------------
CREATE TABLE outlets (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL,
  outlet_name     VARCHAR(150) NOT NULL,
  branch_location VARCHAR(255) NOT NULL,
  region          VARCHAR(100) NOT NULL,
  license_doc_path VARCHAR(255) NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- CHARITIES: organisation profile, one-to-one with a Charity user
-- ---------------------------------------------------------------
CREATE TABLE charities (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL,
  org_name        VARCHAR(150) NOT NULL,
  address         VARCHAR(255) NOT NULL,
  operational_focus VARCHAR(150) NULL,
  verification_doc_path VARCHAR(255) NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- LISTINGS: surplus food items posted by an outlet
-- ---------------------------------------------------------------
CREATE TABLE listings (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  outlet_id       INT NOT NULL,
  item_name       VARCHAR(150) NOT NULL,
  category        ENUM('fruit','vegetable') NOT NULL,
  quantity_kg     DECIMAL(6,2) NOT NULL,
  quantity_remaining_kg DECIMAL(6,2) NOT NULL,
  expiry_date     DATE NOT NULL,
  claim_deadline  DATETIME NOT NULL,          -- clamped to 7:00 PM same day
  status          ENUM('available','reserved','collected','expired','removed')
                    NOT NULL DEFAULT 'available',
  posted_by       INT NOT NULL,                -- users.id of the staff member
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (outlet_id)  REFERENCES outlets(id) ON DELETE CASCADE,
  FOREIGN KEY (posted_by)  REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- PICKUP_SLOTS: time windows an outlet makes available for a listing
-- ---------------------------------------------------------------
CREATE TABLE pickup_slots (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  listing_id      INT NOT NULL,
  slot_start      DATETIME NOT NULL,
  slot_end        DATETIME NOT NULL,
  capacity        TINYINT UNSIGNED NOT NULL DEFAULT 1,
  booked_count    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- RESERVATIONS: a charity or consumer claiming quantity from a listing
-- ---------------------------------------------------------------
CREATE TABLE reservations (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  listing_id      INT NOT NULL,
  user_id         INT NOT NULL,               -- charity or consumer
  reserved_qty_kg DECIMAL(6,2) NOT NULL,
  reservation_type ENUM('charity_priority','consumer_paid') NOT NULL,
  discount_pct    DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  price_paid      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  pickup_slot_id  INT NULL,
  status          ENUM('active','cancelled','no_show','completed') NOT NULL DEFAULT 'active',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (pickup_slot_id) REFERENCES pickup_slots(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- PICKUPS: chain-of-custody confirmation (UC-14)
-- ---------------------------------------------------------------
CREATE TABLE pickups (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  reservation_id      INT NOT NULL,
  collected_qty_kg    DECIMAL(6,2) NOT NULL,
  confirmed_by        INT NOT NULL,          -- users.id of charity/consumer
  confirmed_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
  FOREIGN KEY (confirmed_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- NOTIFICATIONS: in-app alerts (listing posted, pickup reminder, etc.)
-- ---------------------------------------------------------------
CREATE TABLE notifications (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  message     VARCHAR(255) NOT NULL,
  type        VARCHAR(50) NOT NULL,
  is_read     BOOLEAN NOT NULL DEFAULT FALSE,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- DISPUTES and PENALTIES (UC-22, UC-23)
-- ---------------------------------------------------------------
CREATE TABLE disputes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  raised_by     INT NOT NULL,
  against_user  INT NULL,
  subject       VARCHAR(150) NOT NULL,
  details       TEXT NOT NULL,
  status        ENUM('open','pending_info','resolved') NOT NULL DEFAULT 'open',
  resolution    TEXT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_at   DATETIME NULL,
  FOREIGN KEY (raised_by) REFERENCES users(id),
  FOREIGN KEY (against_user) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE penalties (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       INT NOT NULL,
  reservation_id INT NULL,
  reason        VARCHAR(255) NOT NULL,
  status        ENUM('active','waived') NOT NULL DEFAULT 'active',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (reservation_id) REFERENCES reservations(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- AUDIT LOG: every create/update, per Quality Attribute 8.3.2
-- ---------------------------------------------------------------
CREATE TABLE audit_log (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  action      VARCHAR(100) NOT NULL,
  entity      VARCHAR(50) NOT NULL,
  entity_id   INT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Helpful indexes for the queries used throughout the app
-- ---------------------------------------------------------------
CREATE INDEX idx_listings_status        ON listings(status);
CREATE INDEX idx_listings_outlet        ON listings(outlet_id);
CREATE INDEX idx_listings_deadline      ON listings(claim_deadline);
CREATE INDEX idx_reservations_listing   ON reservations(listing_id);
CREATE INDEX idx_reservations_user      ON reservations(user_id);
CREATE INDEX idx_notifications_user     ON notifications(user_id, is_read);

