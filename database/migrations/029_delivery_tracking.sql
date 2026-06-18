-- Rastreamento GPS do entregador em tempo real
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS delivery_locations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  delivery_id BIGINT UNSIGNED NOT NULL,
  motoboy_id BIGINT UNSIGNED NOT NULL,
  latitude DECIMAL(10, 8) NOT NULL,
  longitude DECIMAL(11, 8) NOT NULL,
  accuracy_m SMALLINT UNSIGNED NULL,
  recorded_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_delivery_locations_delivery_recorded (delivery_id, recorded_at),
  KEY idx_delivery_locations_recorded (recorded_at),
  CONSTRAINT fk_delivery_locations_delivery FOREIGN KEY (delivery_id) REFERENCES deliveries(id) ON DELETE CASCADE,
  CONSTRAINT fk_delivery_locations_motoboy FOREIGN KEY (motoboy_id) REFERENCES motoboys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE deliveries
  ADD COLUMN last_latitude DECIMAL(10, 8) NULL AFTER delivered_at,
  ADD COLUMN last_longitude DECIMAL(11, 8) NULL AFTER last_latitude,
  ADD COLUMN last_location_at DATETIME NULL AFTER last_longitude;

ALTER TABLE orders
  ADD COLUMN delivery_latitude DECIMAL(10, 8) NULL AFTER delivery_zip,
  ADD COLUMN delivery_longitude DECIMAL(11, 8) NULL AFTER delivery_latitude;
