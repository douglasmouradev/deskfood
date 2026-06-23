-- Segurança de contas admin/operador: troca de senha obrigatória e 2FA TOTP.
ALTER TABLE admins
    ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active,
    ADD COLUMN totp_secret_encrypted TEXT NULL AFTER must_change_password,
    ADD COLUMN totp_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER totp_secret_encrypted;

-- Contas demo: forçar troca de senha no primeiro acesso.
UPDATE admins SET must_change_password = 1 WHERE email LIKE '%@deskfood.local' AND deleted_at IS NULL;
