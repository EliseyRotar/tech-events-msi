-- Migration: add email verification fields to utenti
-- Run once: mariadb -u user -p tech_dragons_events < database/03_email_verification.sql

ALTER TABLE utenti
    ADD COLUMN email_verified     TINYINT(1)   NOT NULL DEFAULT 0         AFTER pswd,
    ADD COLUMN verification_token VARCHAR(64)  DEFAULT NULL               AFTER email_verified,
    ADD COLUMN token_expires_at   DATETIME     DEFAULT NULL               AFTER verification_token;

-- Existing accounts are pre-verified (admins / seeded data)
UPDATE utenti SET email_verified = 1 WHERE email_verified = 0;
