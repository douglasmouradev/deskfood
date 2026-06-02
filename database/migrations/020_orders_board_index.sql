-- Índice para detecção de mudanças no quadro do operador (max updated_at por unidade)
CREATE INDEX idx_orders_unit_updated ON orders (unit_id, updated_at);
