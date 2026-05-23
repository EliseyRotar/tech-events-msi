-- ============================================================
-- Migration 04 — username field, password reset, news table
-- Run once on TiDB Cloud console (or any MySQL-compatible shell)
-- ============================================================

-- 1. Rename codice_fiscale → username, make it unique
ALTER TABLE utenti
  CHANGE COLUMN codice_fiscale username VARCHAR(50) DEFAULT NULL;

ALTER TABLE utenti
  ADD UNIQUE INDEX idx_username (username);

-- 2. Password-reset columns
ALTER TABLE utenti
  ADD COLUMN reset_token      VARCHAR(64)  DEFAULT NULL,
  ADD COLUMN reset_expires_at DATETIME     DEFAULT NULL;

-- 3. News / announcements table
CREATE TABLE IF NOT EXISTS notizie (
    idNotizia    INT AUTO_INCREMENT PRIMARY KEY,
    titolo       VARCHAR(200) NOT NULL,
    contenuto    TEXT         NOT NULL,
    immagine_url VARCHAR(500) DEFAULT NULL,
    tag          VARCHAR(50)  DEFAULT 'announcement',
    autore       INT          NOT NULL,
    pubblicata_il DATETIME    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (autore) REFERENCES utenti(idUtente) ON DELETE CASCADE
);
