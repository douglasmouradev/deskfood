-- Hash SHA-256 do token de motoboy (não armazenar segredo em texto claro)
ALTER TABLE motoboys
    ADD COLUMN access_token_hash CHAR(64) NULL AFTER access_token;

UPDATE motoboys
SET access_token_hash = SHA2(access_token, 256)
WHERE access_token IS NOT NULL AND access_token != '' AND access_token_hash IS NULL;

CREATE INDEX idx_motoboy_token_hash ON motoboys (access_token_hash);
