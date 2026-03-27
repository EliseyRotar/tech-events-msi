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