-- E-mail opcional do cliente; expiração do link do motoboy
SET NAMES utf8mb4;

ALTER TABLE users ADD COLUMN email VARCHAR(190) NULL AFTER phone_e164;
ALTER TABLE users ADD UNIQUE KEY uq_users_email (email);

ALTER TABLE motoboys ADD COLUMN token_expires_at DATETIME NULL AFTER access_token;
