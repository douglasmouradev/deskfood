-- Desk Food — Unidades (filiais)
SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS units (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  cnpj VARCHAR(18) NOT NULL,
  address_street VARCHAR(200) NOT NULL,
  address_number VARCHAR(20) NOT NULL,
  address_complement VARCHAR(120) NULL,
  neighborhood VARCHAR(120) NOT NULL,
  city VARCHAR(120) NOT NULL,
  state CHAR(2) NOT NULL,
  zip VARCHAR(12) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  delivery_radius_km DECIMAL(6,2) NOT NULL DEFAULT 5.00,
  delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  business_hours TEXT NULL,
  logo_path VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_units_slug (slug),
  KEY idx_units_active (is_active),
  KEY idx_units_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
