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
    email VARCHAR(100) NOT NULL,
    pswd VARCHAR(100) NOT NULL                 
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
    isAdmin tinyint not null,
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