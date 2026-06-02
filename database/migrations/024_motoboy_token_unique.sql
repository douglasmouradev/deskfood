-- Permite tokens só por hash (sem UNIQUE no texto legado)
ALTER TABLE motoboys DROP INDEX uq_motoboy_token;
ALTER TABLE motoboys MODIFY access_token VARCHAR(64) NULL DEFAULT NULL;
