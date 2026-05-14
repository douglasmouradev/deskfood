-- Caixa (abertura, sangrias, fechamento)
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS cash_registers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  unit_id BIGINT UNSIGNED NOT NULL,
  admin_id BIGINT UNSIGNED NOT NULL,
  opened_at DATETIME NOT NULL,
  closed_at DATETIME NULL,
  opening_balance DECIMAL(10,2) NOT NULL,
  closing_balance DECIMAL(10,2) NULL,
  expected_balance DECIMAL(10,2) NULL,
  difference_amount DECIMAL(10,2) NULL,
  difference_note VARCHAR(255) NULL,
  report_path VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cash_unit_open (unit_id, closed_at),
  CONSTRAINT fk_cash_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
  CONSTRAINT fk_cash_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cash_entries (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cash_register_id BIGINT UNSIGNED NOT NULL,
  entry_type ENUM('entrada','sangria') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('pix','cash','card','other') NULL,
  reason VARCHAR(255) NULL,
  order_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cash_entries_register (cash_register_id),
  KEY idx_cash_entries_order (order_id),
  CONSTRAINT fk_cash_entries_register FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id) ON DELETE CASCADE,
  CONSTRAINT fk_cash_entries_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
