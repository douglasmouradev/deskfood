-- Cartão online + tipos de pagamento
SET NAMES utf8mb4;

ALTER TABLE orders
  MODIFY COLUMN payment_method ENUM('pix','card','on_delivery') NOT NULL;

ALTER TABLE payments
  MODIFY COLUMN type ENUM('pix','card','on_delivery') NOT NULL;

CREATE TABLE IF NOT EXISTS card_transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  payment_id BIGINT UNSIGNED NOT NULL,
  external_id VARCHAR(120) NULL COMMENT 'preference_id ou payment_id MP',
  checkout_url TEXT NULL,
  status ENUM('criado','pendente','pago','expirado','cancelado','recusado') NOT NULL DEFAULT 'criado',
  webhook_payload JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_card_payment (payment_id),
  KEY idx_card_status (status),
  KEY idx_card_external (external_id),
  CONSTRAINT fk_card_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
