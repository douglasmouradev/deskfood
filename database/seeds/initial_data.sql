SET NAMES utf8mb4;

-- Unidade demo
INSERT INTO units (id, name, slug, cnpj, address_street, address_number, address_complement, neighborhood, city, state, zip, phone, delivery_radius_km, delivery_fee, business_hours, logo_path, is_active)
VALUES
(1, 'Desk Food Centro', 'centro', '12.345.678/0001-90', 'Rua das Flores', '100', NULL, 'Centro', 'São Paulo', 'SP', '01001000', '11999990001', 8.00, 6.90, '{"seg":{"open":"11:00","close":"23:00"},"dom":{"open":"12:00","close":"22:00"}}', 'assets/img/logo.png', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Dono (senha: Admin123!)
INSERT INTO admins (id, name, email, password_hash, role, unit_id, phone, is_active)
VALUES
(1, 'Dono Demo', 'dono@deskfood.local', '$2y$12$PZhhC2GlXgM8XCyRFUMnK.CuJpCxEfVL.YqtZy6jBaLw7ugSXq1NS', 'super_admin', NULL, '11988887777', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Operador da unidade 1 (mesma senha: Admin123!)
INSERT INTO admins (id, name, email, password_hash, role, unit_id, phone, is_active)
VALUES
(2, 'Operador Centro', 'operador@deskfood.local', '$2y$12$PZhhC2GlXgM8XCyRFUMnK.CuJpCxEfVL.YqtZy6jBaLw7ugSXq1NS', 'unit_operator', 1, '11977776666', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Categorias
INSERT INTO categories (id, unit_id, name, sort_order, is_active) VALUES
(1, 1, 'Lanches', 1, 1),
(2, 1, 'Bebidas', 2, 1),
(3, 1, 'Sobremesas', 3, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Produtos
INSERT INTO products (id, unit_id, category_id, name, description, price, image_path, status, sort_order) VALUES
(1, 1, 1, 'Smash Clássico', 'Blend 120g, queijo prato, molho da casa e pão brioche.', 32.90, NULL, 'active', 1),
(2, 1, 1, 'Frango Crocante', 'Peito empanado, maionese verde e pickles.', 28.90, NULL, 'active', 2),
(3, 1, 2, 'Refrigerante Lata', '350ml — sabores variados.', 6.50, NULL, 'active', 1),
(4, 1, 3, 'Brownie com sorvete', 'Brownie quente com creme gelado de baunilha.', 18.00, NULL, 'active', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Adicionais
INSERT INTO product_addons (id, product_id, name, price, is_required, sort_order, is_active) VALUES
(1, 1, 'Ponto da carne: mal passado', 0.00, 0, 1, 1),
(2, 1, 'Ponto da carne: ao ponto', 0.00, 0, 2, 1),
(3, 1, 'Bacon extra', 5.00, 0, 3, 1),
(4, 1, 'Molho barbecue', 2.00, 0, 4, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Motoboy demo (CPF cifrado fictício — substitua em produção)
INSERT INTO motoboys (id, unit_id, name, phone, cpf_encrypted, photo_path, access_token, is_active) VALUES
(1, 1, 'Carlos Entregas', '11966665555', '9f1pv010i5PR4YSRdqlFUcAN1USZT4pAMlzoenXxj7aOeITds6SW', NULL, 'demo_motoboy_token_seguro_1234567890123456789012', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);
