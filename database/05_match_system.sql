-- Migration 05: Match system, bracket engine, check-ins, Discord webhooks
-- Run once against production DB

-- ── Tournament metadata ──────────────────────────────────────────────────────

ALTER TABLE tornei
    ADD COLUMN formato          ENUM('single_elimination','double_elimination','round_robin')
                                NOT NULL DEFAULT 'single_elimination' AFTER nomeTorneo,
    ADD COLUMN status           ENUM('registration','checkin','live','completed')
                                NOT NULL DEFAULT 'registration'        AFTER formato,
    ADD COLUMN max_teams        INT          NOT NULL DEFAULT 16        AFTER status,
    ADD COLUMN checkin_opens_at DATETIME     DEFAULT NULL               AFTER max_teams;

-- ── Seeding / placement on registration ─────────────────────────────────────

ALTER TABLE tornei_squadre
    ADD COLUMN seed         INT         DEFAULT NULL,
    ADD COLUMN placement    INT         DEFAULT NULL,
    ADD COLUMN eliminated   TINYINT(1)  NOT NULL DEFAULT 0;

-- ── Captain flag ────────────────────────────────────────────────────────────

ALTER TABLE membri
    ADD COLUMN is_captain TINYINT(1) NOT NULL DEFAULT 0 AFTER idUtente;

-- ── Discord webhook per event ────────────────────────────────────────────────

ALTER TABLE evento
    ADD COLUMN discord_webhook VARCHAR(500) DEFAULT NULL;

-- ── Matches ──────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS matches (
    idMatch         INT AUTO_INCREMENT PRIMARY KEY,
    idTorneo        INT NOT NULL,
    round_number    INT NOT NULL DEFAULT 1,
    match_number    INT NOT NULL DEFAULT 1,
    idSquadra1      INT DEFAULT NULL,
    idSquadra2      INT DEFAULT NULL,
    punteggio1      INT NOT NULL DEFAULT 0,
    punteggio2      INT NOT NULL DEFAULT 0,
    idVincitore     INT DEFAULT NULL,
    status          ENUM('scheduled','live','completed','bye','forfeit') NOT NULL DEFAULT 'scheduled',
    scheduled_at    DATETIME DEFAULT NULL,
    completed_at    DATETIME DEFAULT NULL,
    stream_url      VARCHAR(500) DEFAULT NULL,
    next_match_id   INT DEFAULT NULL,
    next_match_slot TINYINT NOT NULL DEFAULT 1,
    FOREIGN KEY (idTorneo)      REFERENCES tornei(idTorneo)    ON DELETE CASCADE,
    FOREIGN KEY (idSquadra1)    REFERENCES squadre(idSquadra),
    FOREIGN KEY (idSquadra2)    REFERENCES squadre(idSquadra),
    FOREIGN KEY (idVincitore)   REFERENCES squadre(idSquadra),
    FOREIGN KEY (next_match_id) REFERENCES matches(idMatch)
);

-- ── Check-ins ────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS checkins (
    idCheckin     INT AUTO_INCREMENT PRIMARY KEY,
    idTorneo      INT NOT NULL,
    idSquadra     INT NOT NULL,
    checked_in_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_checkin (idTorneo, idSquadra),
    FOREIGN KEY (idTorneo)  REFERENCES tornei(idTorneo)   ON DELETE CASCADE,
    FOREIGN KEY (idSquadra) REFERENCES squadre(idSquadra) ON DELETE CASCADE
);
