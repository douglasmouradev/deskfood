-- Cache de geocodificação (reduz chamadas a Google/Nominatim).
CREATE TABLE IF NOT EXISTS geocode_cache (
    query_hash CHAR(64) NOT NULL PRIMARY KEY,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    provider VARCHAR(20) NOT NULL DEFAULT 'nominatim',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_geocode_cache_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
