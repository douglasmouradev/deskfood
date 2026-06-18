CREATE TABLE IF NOT EXISTS session_carts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_id VARCHAR(128) NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  unit_id INT UNSIGNED NOT NULL,
  payload JSON NOT NULL,
  updated_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_session_carts_session (session_id),
  KEY idx_session_carts_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
