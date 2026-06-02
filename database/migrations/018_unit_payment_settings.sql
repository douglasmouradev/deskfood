-- Configuração de pagamento por unidade (PIX / cartão)
SET NAMES utf8mb4;

ALTER TABLE units
  ADD COLUMN payment_provider VARCHAR(32) NULL DEFAULT NULL COMMENT 'mock|efipay|mercadopago ou NULL=herda .env' AFTER minimum_order,
  ADD COLUMN payment_pix_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER payment_provider,
  ADD COLUMN payment_card_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER payment_pix_enabled,
  ADD COLUMN pix_key VARCHAR(120) NULL AFTER payment_card_enabled,
  ADD COLUMN mp_access_token VARCHAR(255) NULL AFTER pix_key,
  ADD COLUMN mp_public_key VARCHAR(255) NULL AFTER mp_access_token,
  ADD COLUMN efi_client_id VARCHAR(120) NULL AFTER mp_public_key,
  ADD COLUMN efi_client_secret VARCHAR(255) NULL AFTER efi_client_id,
  ADD COLUMN efi_sandbox TINYINT(1) NULL DEFAULT NULL AFTER efi_client_secret;
