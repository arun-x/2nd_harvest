-- =====================================================================
-- Stores messages sent from the public Contact Us page (/contact) so
-- admins can read them under Admin > Messages. Run once via phpMyAdmin's
-- SQL tab, or:
--   mysql -u root -p second_harvest < database/migration_add_contact_messages.sql
-- =====================================================================
USE second_harvest;

CREATE TABLE contact_messages (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  first_name  VARCHAR(80)  NOT NULL,
  last_name   VARCHAR(80)  NOT NULL,
  email       VARCHAR(150) NOT NULL,
  message     TEXT         NOT NULL,
  website     VARCHAR(255) NULL,
  phone       VARCHAR(30)  NOT NULL,
  status      ENUM('new','read') NOT NULL DEFAULT 'new',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_contact_messages_status ON contact_messages(status);
