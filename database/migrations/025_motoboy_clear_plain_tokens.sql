-- Remove tokens de motoboy em texto puro (uso apenas access_token_hash).
UPDATE motoboys SET access_token = NULL WHERE access_token IS NOT NULL AND access_token != '';
