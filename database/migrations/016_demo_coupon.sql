-- Cupom demo (após migration 015)
SET NAMES utf8mb4;

INSERT INTO coupons (unit_id, code, discount_type, discount_value, min_subtotal, is_active, created_at, updated_at)
VALUES (1, 'BEMVINDO10', 'percent', 10.00, 0.00, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE discount_value = VALUES(discount_value);
