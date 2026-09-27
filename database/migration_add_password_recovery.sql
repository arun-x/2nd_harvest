-- =====================================================================
-- Forgot-password support without email:
--   * users.recovery_code_hash: one-time recovery code handed to the
--     user at registration (only its hash is stored, like passwords).
--   * password_reset_requests: "I lost my code" requests that an admin
--     verifies and approves (Admin > Password Resets).
-- Run once via phpMyAdmin's SQL tab, or:
--   mysql -u root -p second_harvest < database/migration_add_password_recovery.sql
-- =====================================================================
USE second_harvest;

ALTER TABLE users
  ADD COLUMN recovery_code_hash VARCHAR(255) NULL AFTER password_hash;

CREATE TABLE password_reset_requests (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL,
  ticket_hash     CHAR(64) NOT NULL,          -- sha256 of the request number shown to the user
  ticket_hint     CHAR(4)  NOT NULL,          -- last 4 characters, so the admin can confirm it with the caller
  status          ENUM('pending','approved','rejected','completed','expired','cancelled')
                    NOT NULL DEFAULT 'pending',
  reject_reason   VARCHAR(255) NULL,
  reviewed_by     INT NULL,
  reviewed_at     DATETIME NULL,
  approved_until  DATETIME NULL,              -- approval window; the user must reset before this
  completed_at    DATETIME NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)     REFERENCES users(id),
  FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE INDEX idx_reset_requests_user   ON password_reset_requests(user_id, status);
CREATE INDEX idx_reset_requests_status ON password_reset_requests(status);
