-- Pedido mínimo por unidade
SET NAMES utf8mb4;

ALTER TABLE units
  ADD COLUMN minimum_order DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER delivery_fee;
