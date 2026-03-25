-- ============================================
-- 1. TABELLE BASE
-- ============================================

INSERT INTO evento (nome, nPosti, citta, paese, dataInizio, dataFine) VALUES
('Gaming Expo', 500, 'Milano', 'Italia', '2026-06-10', '2026-06-12'),
('Esports World Cup', 1000, 'Roma', 'Italia', '2026-07-01', '2026-07-05');

INSERT INTO utenti (codice_fiscale, nome, cognome, dataNascita, email, pswd) VALUES
('RSSMRA90A01H501Z', 'Mario', 'Rossi', '1990-01-01', 'mario.rossi@email.com', 'pass1'),
('VRDLGI85B12F205X', 'Luigi', 'Verdi', '1985-02-12', 'luigi.verdi@email.com', 'pass2'),
('BNCLRA95C23H501Y', 'Lara', 'Bianchi', '1995-03-23', 'lara.bianchi@email.com', 'pass3');

INSERT INTO giochi (nomeGioco, copyright) VALUES
('League of Legends', 'Riot Games'),
('FIFA 24', 'EA Sports'),
('Call of Duty', 'Activision');

INSERT INTO ruoli (nomeRuolo, descrizione) VALUES
('Player', 'Giocatore della squadra'),
('Coach', 'Allenatore della squadra'),
('Manager', 'Gestore della squadra');

INSERT INTO sponsor (nomeAzienda, nomeResponsabile, cognomeResponsabile, emailResponsabile) VALUES
('RedBull', 'Marco', 'Neri', 'marco.neri@redbull.com'),
('Intel', 'Anna', 'Blu', 'anna.blu@intel.com');


-- ============================================
-- 2. TABELLE CON DIPENDENZE
-- ============================================

INSERT INTO tornei (montePremi, giornoSvolgimento, idEvento, idGioco) VALUES
(10000.00, '2026-06-10', 1, 1),
(5000.00, '2026-06-11', 1, 2),
(20000.00, '2026-07-02', 2, 3);

INSERT INTO squadre (nomeSquadra, nComponenti, idSponsor) VALUES
('Team Alpha', 5, 1),
('Team Beta', 4, 2),
('Team Gamma', 3, NULL);

INSERT INTO membri (nickname, isAdmin, idSquadra, idUtente) VALUES
('MarioPro', 1, 1, 1),
('LuigiMaster', 0, 1, 2),
('LaraSniper', 0, 2, 3);


-- ============================================
-- 3. TABELLE PONTE
-- ============================================

INSERT INTO membri_ruoli (idRuolo, idMembro) VALUES
(1, 1),
(2, 1),
(1, 2),
(1, 3);

INSERT INTO evento_sponsor (idEvento, idSponsor, importo_sponsor) VALUES
(1, 1, 5000.00),
(1, 2, 3000.00),
(2, 1, 7000.00);

INSERT INTO evento_utenti (idEvento, idUtente) VALUES
(1, 1),
(1, 2),
(2, 3);

INSERT INTO tornei_squadre (idTorneo, idSquadra) VALUES
(1, 1),
(1, 2),
(2, 2),
(3, 3);

INSERT INTO giochi_membri (idGioco, idMembro) VALUES
(1, 1),
(1, 2),
(2, 3),
(3, 1);