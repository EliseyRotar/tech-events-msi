-- Combined migration for production deployment
-- Run once to set up a fresh database

-- Tables
-- ============================================
-- 1. TABELLE SENZA DIPENDENZE (prima di tutto)
-- ============================================

CREATE TABLE evento (
    idEvento INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,                -- ✅ Aggiunto NOT NULL
    nPosti INT NOT NULL,                       -- ✅ Aggiunto NOT NULL
    citta VARCHAR(100),                        -- ✅ Aggiunta lunghezza (mancava)
    paese VARCHAR(100),                        -- ✅ Aggiunta lunghezza (mancava)
    dataInizio DATE NOT NULL,                  -- ✅ Aggiunto NOT NULL
    dataFine DATE NOT NULL                     -- ✅ Rinominato per coerenza (era data_fine)
);

CREATE TABLE utenti (
    idUtente INT AUTO_INCREMENT PRIMARY KEY,
    codice_fiscale VARCHAR(16),    -- ✅ Aggiunta PRIMARY KEY (mancava!)
    nome VARCHAR(50) NOT NULL,                 -- ✅ Aggiunto NOT NULL
    cognome VARCHAR(50) NOT NULL,              -- ✅ Aggiunto NOT NULL
    dataNascita DATE NOT NULL,
    isAdmin tinyint not null,
    email VARCHAR(100) NOT NULL UNIQUE,
    pswd VARCHAR(255) NOT NULL                 
);

CREATE TABLE giochi (
    idGioco INT AUTO_INCREMENT PRIMARY KEY,
    nomeGioco VARCHAR(100) NOT NULL,           -- ✅ Aggiunta lunghezza (mancava)
    copyright VARCHAR(200)                     -- ✅ Aggiunta lunghezza (mancava)
);

CREATE TABLE ruoli (
    idRuolo INT AUTO_INCREMENT PRIMARY KEY,
    nomeRuolo VARCHAR(50) NOT NULL,            -- ✅ Aggiunto NOT NULL
    descrizione VARCHAR(250)                   
);

CREATE TABLE sponsor (
    idSponsor INT AUTO_INCREMENT PRIMARY KEY,
    nomeAzienda VARCHAR(50) NOT NULL,          -- ✅ Aggiunto NOT NULL
    nomeResponsabile VARCHAR(50) NOT NULL,
    cognomeResponsabile VARCHAR(50) NOT NULL,
    emailResponsabile VARCHAR(100) NOT NULL           
);

-- ============================================
-- 2. TABELLE CON DIPENDENZE DI PRIMO LIVELLO
-- ============================================

CREATE TABLE tornei (
    idTorneo INT AUTO_INCREMENT PRIMARY KEY,
    nomeTorneo varchar(100),
    montePremi DECIMAL(10,2),                  -- ✅ Cambiato INT → DECIMAL (è denaro!)
    giornoSvolgimento DATE NOT NULL,
    idEvento INT NOT NULL,                     -- ✅ AGGIUNTO: collegamento a evento
    idGioco INT NOT NULL,                      -- ✅ Aggiunto NOT NULL
    FOREIGN KEY (idGioco) REFERENCES giochi(idGioco),    -- ✅ Corretta sintassi
    FOREIGN KEY (idEvento) REFERENCES evento(idEvento)   -- ✅ AGGIUNTO
);

CREATE TABLE squadre (
    idSquadra INT AUTO_INCREMENT PRIMARY KEY,
    nomeSquadra VARCHAR(50) NOT NULL,          -- ✅ Aggiunto NOT NULL
    nComponenti INT NOT NULL,
    idSponsor INT,                             -- ✅ Può essere NULL (non tutte hanno sponsor)
    FOREIGN KEY (idSponsor) REFERENCES sponsor(idSponsor)  -- ✅ Corretta sintassi
);

CREATE TABLE membri (
    idMembro INT AUTO_INCREMENT PRIMARY KEY,
    nickname VARCHAR(50) NOT NULL UNIQUE,      -- ✅ Aggiunto UNIQUE (nickname deve essere unico)
    idSquadra INT NOT NULL,
    idUtente INT NOT NULL,
    FOREIGN KEY (idSquadra) REFERENCES squadre(idSquadra),  -- ✅ Corretta sintassi
    FOREIGN KEY (idUtente) REFERENCES utenti(idUtente)
);


-- ============================================
-- 3. TABELLE PONTE (relazioni N:N)
-- ============================================

CREATE TABLE membri_ruoli (
    idRuolo INT NOT NULL,
    idMembro INT NOT NULL,
    PRIMARY KEY (idRuolo, idMembro),           -- ✅ AGGIUNTA chiave primaria composta
    FOREIGN KEY (idRuolo) REFERENCES ruoli(idRuolo),      -- ✅ Corretta sintassi
    FOREIGN KEY (idMembro) REFERENCES membri(idMembro)     -- ✅ Corretta sintassi
);

CREATE TABLE evento_sponsor (
    idEvento INT NOT NULL,
    idSponsor INT NOT NULL,
    importo_sponsor DECIMAL(10,2) NOT NULL,    -- ✅ Cambiato INT → DECIMAL (è denaro!)
    PRIMARY KEY (idEvento, idSponsor),         -- ✅ AGGIUNTA chiave primaria composta
    FOREIGN KEY (idSponsor) REFERENCES sponsor(idSponsor), -- ✅ Corretta sintassi
    FOREIGN KEY (idEvento) REFERENCES evento(idEvento)     -- ✅ Corretta sintassi
);

CREATE TABLE evento_utenti (
    idEvento INT NOT NULL,
    idUtente int NOT NULL,
    PRIMARY KEY (idEvento, idUtente),    -- ✅ AGGIUNTA chiave primaria composta
    FOREIGN KEY (idUtente) REFERENCES utenti(idUtente), -- ✅ Corretta sintassi
    FOREIGN KEY (idEvento) REFERENCES evento(idEvento)
);

CREATE TABLE tornei_squadre (
    idTorneo INT NOT NULL,
    idSquadra INT NOT NULL,
    PRIMARY KEY (idTorneo, idSquadra),         -- ✅ AGGIUNTA chiave primaria composta
    FOREIGN KEY (idTorneo) REFERENCES tornei(idTorneo),
    FOREIGN KEY (idSquadra) REFERENCES squadre(idSquadra)
);

CREATE TABLE giochi_membri (
    idGioco INT NOT NULL,
    idMembro INT NOT NULL,
    PRIMARY KEY (idGioco, idMembro),           -- ✅ AGGIUNTA chiave primaria composta
    FOREIGN KEY (idGioco) REFERENCES giochi(idGioco),
    FOREIGN KEY (idMembro) REFERENCES membri(idMembro)
);
-- ============================================
-- POPOLAMENTO DATABASE AGGIORNATO
-- ============================================

-- 1. EVENTI
INSERT INTO evento (nome, nPosti, citta, paese, dataInizio, dataFine) VALUES
('Gaming Fest', 500, 'Milano', 'Italia', '2026-06-10', '2026-06-12'),
('Esports Arena', 300, 'Roma', 'Italia', '2026-07-05', '2026-07-07');

-- 2. UTENTI (password già hashate di esempio)
INSERT INTO utenti (codice_fiscale, nome, cognome, dataNascita, isAdmin, email, pswd) VALUES
('RSSMRA90A01H501Z', 'Mario', 'Rossi', '1990-01-01', 1, 'mario@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('VRDLGI95B12F205X', 'Luigi', 'Verdi', '1995-02-12', 0, 'luigi@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('BNCLRA88C41D612Y', 'Laura', 'Bianchi', '1988-03-21', 0, 'laura@example.com', '$2y$10$abcdefghijklmnopqrstuv');

-- 3. GIOCHI
INSERT INTO giochi (nomeGioco, copyright) VALUES
('League of Legends', 'Riot Games'),
('FIFA 24', 'EA Sports'),
('Call of Duty', 'Activision');

-- 4. RUOLI
INSERT INTO ruoli (nomeRuolo, descrizione) VALUES
('Player', 'Giocatore attivo'),
('Coach', 'Allenatore'),
('Manager', 'Gestore del team');

-- 5. SPONSOR
INSERT INTO sponsor (nomeAzienda, nomeResponsabile, cognomeResponsabile, emailResponsabile) VALUES
('Red Bull', 'Marco', 'Neri', 'neri@redbull.com'),
('Intel', 'Giulia', 'Ferrari', 'ferrari@intel.com');

-- 6. TORNEI (aggiunto nomeTorneo)
INSERT INTO tornei (nomeTorneo, montePremi, giornoSvolgimento, idEvento, idGioco) VALUES
('LoL Championship', 10000.00, '2026-06-10', 1, 1),
('FIFA Cup', 5000.00, '2026-07-05', 2, 2);

-- 7. SQUADRE
INSERT INTO squadre (nomeSquadra, nComponenti, idSponsor) VALUES
('Team Alpha', 5, 1),
('Team Beta', 5, 2),
('Team Gamma', 3, NULL);

-- 8. MEMBRI
INSERT INTO membri (nickname, idSquadra, idUtente) VALUES
('ProGamer1', 1, 1),
('SniperX', 1, 2),
('Shadow', 2, 3);

-- 9. MEMBRI_RUOLI
INSERT INTO membri_ruoli (idRuolo, idMembro) VALUES
(1, 1),
(1, 2),
(2, 3);

-- 10. EVENTO_SPONSOR
INSERT INTO evento_sponsor (idEvento, idSponsor, importo_sponsor) VALUES
(1, 1, 2000.00),
(2, 2, 1500.00);

-- 11. EVENTO_UTENTI
INSERT INTO evento_utenti (idEvento, idUtente) VALUES
(1, 1),
(1, 2),
(2, 3);

-- 12. TORNEI_SQUADRE
INSERT INTO tornei_squadre (idTorneo, idSquadra) VALUES
(1, 1),
(1, 2),
(2, 3);

-- 13. GIOCHI_MEMBRI
INSERT INTO giochi_membri (idGioco, idMembro) VALUES
(1, 1),
(1, 2),
(2, 3);
-- Migration: add email verification fields to utenti
-- Run once: mariadb -u user -p tech_dragons_events < database/03_email_verification.sql

ALTER TABLE utenti
    ADD COLUMN email_verified     TINYINT(1)   NOT NULL DEFAULT 0         AFTER pswd,
    ADD COLUMN verification_token VARCHAR(64)  DEFAULT NULL               AFTER email_verified,
    ADD COLUMN token_expires_at   DATETIME     DEFAULT NULL               AFTER verification_token;

-- Existing accounts are pre-verified (admins / seeded data)
UPDATE utenti SET email_verified = 1 WHERE email_verified = 0;
