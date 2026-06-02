-- Consultas do quadro operador (filtro por unidade + status)
CREATE INDEX idx_orders_unit_active_created ON orders (unit_id, deleted_at, created_at);
