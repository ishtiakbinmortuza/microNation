-- =============================================================
-- Qamarshan Database Schema
-- Created: 2025-10-04
-- Target: MySQL 8.0+ (utf8mb4, InnoDB)
-- =============================================================

-- Safety: create database if not exists
CREATE DATABASE IF NOT EXISTS qamarshan_cms
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_unicode_520_ci;
USE qamarshan_cms;

-- -------------------------------------------------------------
-- Helper: enforce sane SQL modes (optional if set globally)
-- -------------------------------------------------------------
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -------------------------------------------------------------
-- Table: users (system authentication accounts)
-- Purpose: Portal logins (could be citizens or admins)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
		id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		username        VARCHAR(50) NOT NULL,
		email           VARCHAR(120) NOT NULL,
		password_hash   VARCHAR(255) NOT NULL,           -- store bcrypt/argon2 hash
		role            ENUM('citizen','admin','staff') DEFAULT 'citizen',
		is_active       TINYINT(1) NOT NULL DEFAULT 1,
		created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at      TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		UNIQUE KEY uq_users_username (username),
		UNIQUE KEY uq_users_email (email),
		KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Table: citizenship_applications
-- Raw applications submitted through the form (apply.php)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS citizenship_applications (
		id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		fullname         VARCHAR(120) NOT NULL,
		email            VARCHAR(120) NOT NULL,
		photo_path       VARCHAR(255) DEFAULT NULL,      -- stored filename if uploaded
		dob              DATE DEFAULT NULL,
		phone            VARCHAR(30) DEFAULT NULL,
	address          TEXT DEFAULT NULL,
	motivation       TEXT DEFAULT NULL,
		motivation       TEXT DEFAULT NULL,              -- 'reason' field renamed for clarity
		application_type ENUM('permanent','honorary','dual') DEFAULT NULL,
		status           ENUM('pending','under_review','approved','rejected','withdrawn') NOT NULL DEFAULT 'pending',
		reviewed_by      INT UNSIGNED DEFAULT NULL,      -- FK to users.id
		reviewed_at      DATETIME DEFAULT NULL,
		decision_notes   TEXT DEFAULT NULL,
		submitted_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at       TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		CONSTRAINT fk_app_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
		KEY idx_app_email (email),
		KEY idx_app_status (status),
		KEY idx_app_type (application_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Table: citizens
-- Approved citizens (subset of applications). Kept separate so we retain
-- immutable application history while allowing profile updates here.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS citizens (
		id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		application_id   INT UNSIGNED DEFAULT NULL,      -- link back to original application
		user_id          INT UNSIGNED DEFAULT NULL,      -- optional link to users table (login)
		fullname         VARCHAR(120) NOT NULL,
		email            VARCHAR(120) NOT NULL,
		photo_path       VARCHAR(255) DEFAULT NULL,
		dob              DATE DEFAULT NULL,
		phone            VARCHAR(30) DEFAULT NULL,
		address          TEXT DEFAULT NULL,
		citizenship_type ENUM('permanent','honorary','dual') DEFAULT NULL,
		active_status    ENUM('active','suspended','revoked','resigned') NOT NULL DEFAULT 'active',
		granted_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at       TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		CONSTRAINT fk_cit_app FOREIGN KEY (application_id) REFERENCES citizenship_applications(id) ON UPDATE CASCADE ON DELETE SET NULL,
		CONSTRAINT fk_cit_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
		UNIQUE KEY uq_citizens_email (email),
		KEY idx_cit_type (citizenship_type),
		KEY idx_cit_status (active_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Table: diplomatic_messages
-- Messages from diplomacy form (diplomacy.php)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS diplomatic_messages (
		id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		state_name       VARCHAR(160) NOT NULL,
		contact_person   VARCHAR(120) NOT NULL,
		email            VARCHAR(160) NOT NULL,
		category         ENUM('recognition','treaty','visit','press','other') NOT NULL,
		message_body     TEXT NOT NULL,
		attachment_path  VARCHAR(255) DEFAULT NULL,
		status           ENUM('received','in_review','responded','archived') NOT NULL DEFAULT 'received',
		handled_by       INT UNSIGNED DEFAULT NULL,      -- staff user
		handled_at       DATETIME DEFAULT NULL,
		internal_notes   TEXT DEFAULT NULL,
		submitted_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at       TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		CONSTRAINT fk_dip_user FOREIGN KEY (handled_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
		KEY idx_dip_email (email),
		KEY idx_dip_category (category),
		KEY idx_dip_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- (Optional) Table: sessions (simple PHP session / token tracking)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sessions (
		id            CHAR(64) PRIMARY KEY,         -- session / token id
		user_id       INT UNSIGNED NOT NULL,
		ip_address    VARCHAR(45) DEFAULT NULL,
		user_agent    VARCHAR(255) DEFAULT NULL,
		created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		expires_at    TIMESTAMP NOT NULL,
		CONSTRAINT fk_session_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Views
-- -------------------------------------------------------------
CREATE OR REPLACE VIEW v_active_citizens AS
SELECT id,
			 fullname,
			 email,
			 citizenship_type,
			 granted_at
FROM citizens
WHERE active_status = 'active';

CREATE OR REPLACE VIEW v_pending_applications AS
SELECT id,
			 fullname,
			 email,
			 application_type,
			 status,
			 submitted_at
FROM citizenship_applications
WHERE status = 'pending';

-- -------------------------------------------------------------
-- Triggers (normalize email to lowercase)
-- -------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_users_before_insert
BEFORE INSERT ON users FOR EACH ROW
BEGIN
	SET NEW.email = LOWER(NEW.email);
	SET NEW.username = LOWER(NEW.username);
END$$
CREATE TRIGGER trg_users_before_update
BEFORE UPDATE ON users FOR EACH ROW
BEGIN
	SET NEW.email = LOWER(NEW.email);
	SET NEW.username = LOWER(NEW.username);
END$$

CREATE TRIGGER trg_apps_before_insert
BEFORE INSERT ON citizenship_applications FOR EACH ROW
BEGIN
	SET NEW.email = LOWER(NEW.email);
END$$
CREATE TRIGGER trg_apps_before_update
BEFORE UPDATE ON citizenship_applications FOR EACH ROW
BEGIN
	SET NEW.email = LOWER(NEW.email);
END$$

CREATE TRIGGER trg_cit_before_insert
BEFORE INSERT ON citizens FOR EACH ROW
BEGIN
	SET NEW.email = LOWER(NEW.email);
END$$
CREATE TRIGGER trg_cit_before_update
BEFORE UPDATE ON citizens FOR EACH ROW
BEGIN
	SET NEW.email = LOWER(NEW.email);
END$$

CREATE TRIGGER trg_dip_before_insert
BEFORE INSERT ON diplomatic_messages FOR EACH ROW
BEGIN
	SET NEW.email = LOWER(NEW.email);
END$$
CREATE TRIGGER trg_dip_before_update
BEFORE UPDATE ON diplomatic_messages FOR EACH ROW
BEGIN
	SET NEW.email = LOWER(NEW.email);
END$$
DELIMITER ;

-- -------------------------------------------------------------
-- Seed (optional minimal admin account placeholder — set a real hash!)
-- (Remove or update in production)
-- -------------------------------------------------------------
INSERT INTO users (username, email, password_hash, role)
VALUES ('admin', 'admin@example.com', '$2y$10$REPLACE_WITH_REAL_BCRYPT_HASH___________abcdefghiJKLMNO123456', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- -------------------------------------------------------------
-- Helper Queries (reference)
-- -------------------------------------------------------------
-- Approve an application and create a citizen (transaction recommended):
-- START TRANSACTION;
-- UPDATE citizenship_applications SET status='approved', reviewed_by=1, reviewed_at=NOW(), decision_notes='Approved' WHERE id = ?;
-- INSERT INTO citizens (application_id, fullname, email, photo_path, dob, phone, address, citizenship_type)
--   SELECT id, fullname, email, photo_path, dob, phone, address, application_type FROM citizenship_applications WHERE id = ?;
-- COMMIT;

-- List active citizens: SELECT * FROM v_active_citizens ORDER BY granted_at DESC;
-- Pending apps: SELECT * FROM v_pending_applications ORDER BY submitted_at ASC;
-- New diplomacy messages: SELECT * FROM diplomatic_messages WHERE status='received';

-- =============================================================
-- End of schema
-- =============================================================
