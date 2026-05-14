-- Motoboys e entregas
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS motoboys (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  unit_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  cpf_encrypted TEXT NOT NULL,
  photo_path VARCHAR(255) NULL,
  access_token VARCHAR(64) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_motoboy_token (access_token),
  KEY idx_motoboys_unit (unit_id),
  CONSTRAINT fk_motoboys_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS deliveries (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  motoboy_id BIGINT UNSIGNED NOT NULL,
  status ENUM('assigned','out_for_delivery','delivered') NOT NULL DEFAULT 'assigned',
  started_at DATETIME NULL,
  delivered_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_deliveries_order (order_id),
  KEY idx_deliveries_motoboy (motoboy_id),
  CONSTRAINT fk_deliveries_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_deliveries_motoboy FOREIGN KEY (motoboy_id) REFERENCES motoboys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
