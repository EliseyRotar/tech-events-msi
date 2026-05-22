<div align="center">

# 🎮 Tech Events - Esports Event Manager

### Gestisci Eventi Esports come un Pro
*Una piattaforma completa per creare eventi, organizzare tornei e gestire team in modo professionale.*

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-777bb4.svg?style=flat-square&logo=php)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-8A2BE2?style=flat-square)](LICENSE)
[![Status](https://img.shields.io/badge/status-active-success?style=flat-square)](https://github.com/EliseyRotar/tech-events-msi)

[Installazione](#-installazione) • [Funzionalità](#-funzionalità-principali) • [Database](#-struttura-database) • [Contribuire](CONTRIBUTING.md)

</div>

---

> **EEM è il gestionale pensato per organizer, team e community competitive.** 
> Dalla creazione dell'evento alla gestione dei tornei, tutto in un unico pannello centralizzato.

---

### ✨ Funzionalità Principali

#### 🗓️ Gestione Eventi
- **Creazione Eventi:** Pannello admin per creare eventi LAN o Online con date, luoghi e numero di posti.
- **Visualizzazione:** Dashboard pubblica e privata per consultare gli eventi attivi e i dettagli logistici.

#### 🏆 Organizzazione Tornei
- **Associazione Tornei:** Collega più tornei a un singolo evento.
- **Premi & Date:** Gestione montepremi e calendari di svolgimento per ogni competizione.
- **Iscrizioni:** Sistema di registrazione per i team ai singoli tornei.

#### 👥 Team & Player Management
- **Gestione Squadre:** Creazione team con sponsor associati.
- **Roster:** Gestione dei membri della squadra e dei loro ruoli (Player, Coach, Manager, etc.).
- **Account:** Sistema di autenticazione sicuro con ruoli differenziati (User/Admin).

#### 🧪 Storico & Risultati
- **Visualizzazione Risultati:** Pagine dedicate allo storico dei tornei per giochi come CS2, Valorant e Dota 2.
- **Statistiche:** Tracciamento delle partecipazioni e dei vincitori.

---

### 🛠️ Tech Stack

- **Backend:** PHP 8.x (Architettura procedurale in evoluzione verso OOP)
- **Database:** MySQL / MariaDB (PDO per connessioni sicure)
- **Frontend:** HTML5, CSS3 (Design moderno con font Orbitron & Syne)
- **Security:** Password hashing Argon2ID, supporto `.env` per credenziali sensibili.

---

### 📂 Struttura Database

Il sistema si appoggia su un database relazionale ottimizzato:
- **`utenti`**: Gestione account e permessi admin.
- **`evento`**: Dettagli logistici degli eventi principali.
- **`tornei`**: Competizioni specifiche collegate agli eventi.
- **`squadre` & `membri`**: Gestione gerarchica dei team esports.
- **`sponsor`**: Gestione delle partnership aziendali.

---

### 🚀 Installazione

1. **Clona il repository:**
   ```bash
   git clone https://github.com/EliseyRotar/tech-events-msi.git
   cd tech-events-msi
   ```

2. **Configura il Database:**
   - Importa i file `tables.sql` ed `elements.sql` nel tuo server MySQL.
   - Crea un file `.env` partendo da `.env.example`:
     ```bash
     cp .env.example .env
     ```
   - Modifica il file `.env` con le tue credenziali locali.

3. **Configura il Web Server:**
   - Punta la root del tuo server (Apache/Nginx) alla cartella del progetto.
   - Assicurati che il modulo `pdo_mysql` sia abilitato nel tuo `php.ini`.

4. **Accedi alla piattaforma:**
   - Apri il browser su `http://localhost/tech-events-msi`
   - Registrati come utente o usa un account admin predefinito.

---

### 🤝 Contribuire

Le contribuzioni sono ciò che rendono la community open source un posto fantastico per imparare, ispirare e creare. Qualsiasi contributo tu faccia è **molto apprezzato**.

1. Fork del Progetto
2. Crea il tuo Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit dei tuoi cambiamenti (`git commit -m 'Add some AmazingFeature'`)
4. Push del Branch (`git push origin feature/AmazingFeature`)
5. Apri una Pull Request

---

### 📄 Licenza

Distribuito sotto Licenza MIT. Vedi `LICENSE` per maggiori informazioni.

<p align="center">
  Realizzato con ❤️ da <a href="https://github.com/EliseyRotar">Elisey Rotar</a>
</p>
