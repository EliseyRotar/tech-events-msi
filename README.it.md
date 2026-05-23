<div align="center">

🌐 **Language / Lingua:** [🇬🇧 English](README.md) · [🇮🇹 Italiano](README.it.md)

<img src="public/assets/img/logo.jpg" width="80" alt="Tech Dragons Events logo">

# Tech Dragons Events

**L'infrastruttura per la competizione esports professionale**

[![Live](https://img.shields.io/badge/Live-tech--events--msi.onrender.com-00d4ff?style=flat-square&logo=render&logoColor=white)](https://tech-events-msi.onrender.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777bb4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![TiDB](https://img.shields.io/badge/TiDB-Cloud-e85c4a?style=flat-square&logo=mysql&logoColor=white)](https://tidbcloud.com)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ed?style=flat-square&logo=docker&logoColor=white)](https://docker.com)
[![WebGL](https://img.shields.io/badge/WebGL-shader-00d4ff?style=flat-square&logo=opengl&logoColor=white)](#frontend)
[![License](https://img.shields.io/badge/License-MIT-22c55e?style=flat-square)](LICENSE)

**[🌐 tech-events-msi.onrender.com](https://tech-events-msi.onrender.com)**

[Panoramica](#panoramica) · [Architettura](#architettura) · [Funzionalità](#funzionalità) · [Frontend](#frontend) · [Avvio rapido](#avvio-rapido) · [Sicurezza](#sicurezza) · [Contribuire](#contribuire)

</div>

---

## Panoramica

Tech Dragons Events è un'applicazione web full-stack per la gestione professionale di eventi esports — dalla creazione di eventi e torneamenti alla registrazione di team e gestione dei roster. Unisce un backend PHP 8.2 / TiDB Cloud a un frontend cinematografico: shader fluido WebGL personalizzato, animazioni GSAP, sezione di storytelling 3D scroll-driven con Three.js, glassmorphism e un design system CSS completo — distribuito su Render.com via Docker.

---

## Demo Live

**[https://tech-events-msi.onrender.com](https://tech-events-msi.onrender.com)**

> Ospitato su Render.com (piano gratuito) — il primo caricamento dopo inattività può richiedere ~30 secondi.  
> Database: TiDB Cloud Serverless (compatibile MySQL, piano gratuito).

---

## Architettura

Solo la directory `public/` è esposta al web server. Tutta la logica applicativa, le credenziali e i template si trovano fuori dalla web root.

```
tech-events-msi/
├── public/                       # Web root (document root di Apache)
│   ├── assets/
│   │   ├── css/
│   │   │   ├── main.css          # Design system completo — token, componenti, layout
│   │   │   └── php-pages.css     # Override per pagine form/admin
│   │   ├── js/
│   │   │   ├── hero-bg.js        # Shader WebGL (FBM con domain warping)
│   │   │   ├── main.js           # Animazioni GSAP, cursore personalizzato
│   │   │   └── scrollytelling.js # Sezione 3D con 4000 particelle Three.js
│   │   ├── img/
│   │   │   ├── logo.png          # Logo con sfondo trasparente (web)
│   │   │   ├── logo.jpg          # Logo opaco (README / OG)
│   │   │   └── logo.svg          # Logo vettoriale
│   │   └── storici/              # Archivi HTML statici delle partite
│   ├── favicon.ico               # Favicon multi-dimensione (16/32/48)
│   ├── favicon-32.png            # Favicon PNG per browser moderni
│   ├── apple-touch-icon.png      # Icona touch 180×180
│   ├── index.php                 # Landing: Hero · Storia · Stats · Eventi · About · Contatti
│   ├── dashboard.php             # Portale di gestione eventi autenticato
│   ├── login.php
│   ├── register.php
│   ├── privacy.php               # Privacy Policy (IT/EN via i18n)
│   ├── terms.php                 # Termini di Servizio (IT/EN via i18n)
│   ├── createEvent.php           # Admin: crea eventi
│   ├── addTournament.php         # Admin: associa tornei agli eventi
│   ├── addGame.php               # Admin: registra titoli di gioco
│   ├── addTeam.php               # Registra una nuova organizzazione
│   ├── addMember.php             # Aggiungi giocatori al roster
│   ├── signTeam.php              # Iscrivi un team a un torneo
│   ├── viewTeam.php              # Visualizza i roster registrati
│   ├── assignGame.php            # Associa una disciplina a un membro
│   └── assignRole.php            # Assegna un ruolo competitivo a un membro
├── templates/
│   └── layout/
│       ├── header.php            # <head>, favicon, font, CDN GSAP/Three.js, nav, overlay
│       └── footer.php            # Colonne footer + inclusione main.js
├── src/
│   ├── Auth.php                  # RBAC — sessione, login guard, admin guard
│   ├── EnvLoader.php             # Legge .env senza esporre valori in $_ENV
│   └── helpers.php               # runInTransaction(), helper i18n t()
├── lang/
│   ├── en.php                    # Stringhe di traduzione inglese
│   └── it.php                    # Stringhe di traduzione italiana
├── database/
│   ├── 01_tables.sql             # Schema completo
│   ├── 02_elements.sql           # Dati seed
│   └── combined_migration.sql    # Migrazione in un unico file per la produzione
├── config.php                    # Bootstrap PDO + caricamento env + SSL TiDB
├── Dockerfile
└── docker-compose.yml
```

---

## Funzionalità

### Gestione Eventi
- Crea eventi LAN e online con intervallo di date, location e capienza
- Creazione e assegnazione torneo riservata agli admin
- Dashboard con lista eventi, dettaglio torneo e indicatori badge

### Sistema Tornei
- Più tornei per evento, ognuno legato a un titolo di gioco
- Monitoraggio del prize pool in EUR
- Registrazione team e visualizzazione roster per torneo

### Gestione Team e Roster
- Registra organizzazioni con integrazione sponsor opzionale
- Aggiungi giocatori al roster con nickname in-game unici
- Assegna discipline di gioco e ruoli competitivi ai singoli membri

### Internazionalizzazione (i18n)
- Cambio lingua (Italiano / Inglese) nella nav globale
- Persistenza basata su cookie (TTL 1 anno)
- Aggiungi nuove lingue inserendo un file in `lang/`

---

## Frontend

L'intero frontend è stato ridisegnato come un design system dark futuristico — nessun framework CSS, nessuna libreria di componenti, solo CSS custom properties e vanilla JS.

### Design System

| Token | Valore |
|---|---|
| `--bg-primary` | `#0a0a0a` |
| `--bg-secondary` | `#111111` |
| `--accent-blue` | `#00d4ff` |
| `--text-primary` | `#ffffff` |
| `--text-secondary` | `#888888` |
| `--border` | `rgba(255,255,255,0.08)` |
| Font titoli | Space Grotesk (700) |
| Font corpo | Inter (400/500/600) |

### Sfondo hero WebGL (`hero-bg.js`)

Shader fluido GPU in tempo reale — WebGL 1.0 puro:

- **FBM con domain warping** (tecnica di Inigo Quilez): due livelli di moto browniano frattale che si deformano a vicenda, generando pattern fluidi organici mai ripetuti
- **Interazione mouse**: il campo fluido si distorce verso il cursore in tempo reale con smoothing esponenziale
- **Shockwave al click**: ogni click genera due anelli espansi concentrici più un burst all'origine, con decadimento fisico
- Ottimizzato per girare a **~60 fps** al 35% della risoluzione schermo

### Sezione Scrollytelling 3D (`scrollytelling.js`)

Esperienza immersiva scroll-driven con **Three.js r128** + **GSAP ScrollTrigger**:

- **4 000 particelle sprite additive** che morfano attraverso 5 forme procedurali durante lo scroll: nuvola → globo di Fibonacci → arena a livelli → tabellone torneo → emblema TD
- Shimmer wobble per particella, drift ambientale della camera, parallax mouse/touch
- Pannelli di testo frosted-glass per ogni atto che scorrono in/out indipendentemente
- Mesh halo luminoso dietro il campo di particelle

### Animazioni (`main.js`)

| Effetto | Implementazione |
|---|---|
| Overlay di caricamento pagina | Fade-out logo su `window.load` |
| Cursore personalizzato | Dot + anello ritardato via `requestAnimationFrame` lerp |
| Headline hero | Stagger parola per parola con `translateY` al caricamento |
| Navbar | Trasparente → vetro smerigliato allo scroll |
| Contatori stats | Tween GSAP da 0 → target all'entrata nella viewport |
| Parole About | Illuminazione parola per parola legata al progresso di scroll |

---

## Deploy

### Produzione (Render.com)

L'app viene distribuita automaticamente su [Render.com](https://render.com) ad ogni push su `main`.

**Variabili d'ambiente richieste:**

| Variabile | Descrizione |
|---|---|
| `DB_HOST` | Host TiDB Cloud |
| `DB_PORT` | `4000` |
| `DB_USER` | Utente TiDB |
| `DB_PASS` | Password TiDB |
| `DB_NAME` | Nome database |
| `RESEND_API_KEY` | Chiave API [Resend](https://resend.com) per email transazionali |
| `MAIL_FROM` | Indirizzo email mittente |
| `CONTACT_EMAIL` | Destinatario del modulo di contatto |
| `MAIL_FROM_NAME` | Nome visualizzato mittente |

### Locale (Docker)

**Linux / Arch Linux:**
```bash
./start_arch.sh
```

**Windows 10/11:**
```bat
start_windows.bat
```

Entrambi gli script costruiscono l'immagine, avviano Apache + PHP 8.2 + MariaDB 10.6 e importano il database seed. Apri **http://localhost:8080**.

### Setup manuale

**Requisiti:** PHP 8.2+, MariaDB 10.6+ (o TiDB Cloud)

```bash
# 1. Clona
git clone https://github.com/EliseyRotar/tech-events-msi.git
cd tech-events-msi

# 2. Configura l'ambiente
cp .env.example .env
# modifica .env — imposta DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS

# 3. Importa schema + dati seed
mariadb -u root -p               < database/01_tables.sql
mariadb -u root -p tech_dragons_events < database/02_elements.sql
# oppure per la produzione: usa database/combined_migration.sql

# 4. Punta il document root del web server su ./public
```

### Account seed predefiniti

| Email | Ruolo | Nota |
|---|---|---|
| mario@example.com | Admin | Usa register.php per creare un nuovo account, poi imposta `isAdmin = 1` nel DB |
| luigi@example.com | Utente | Idem |

---

## Sicurezza

| Problema | Implementazione |
|---|---|
| SQL injection | 100% prepared statement PDO — zero interpolazione di stringhe nelle query |
| XSS | `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` su ogni output `<?=` |
| Bypass auth | `Auth::requireLogin()` / `Auth::requireAdmin()` in cima a ogni pagina protetta |
| Archiviazione password | `password_hash(..., PASSWORD_ARGON2ID)` |
| Sicurezza transazioni | `runInTransaction()` avvolge ogni scrittura; cattura `\Throwable` e fa rollback |
| Redirect aperto | Il handler `?lang=` valida che l'URL inizi con `/` e non `//` |
| Esposizione credenziali | `.env` è in gitignore; `EnvLoader` lo legge senza esporre in `$_ENV` |
| Isolamento web root | `src/`, `templates/`, `lang/`, `database/` sono tutti fuori da `public/` |
| SSL TiDB | PDO si connette con certificato CA; `SSL_VERIFY_SERVER_CERT=false` per serverless |

---

## Stack Tecnologico

| Livello | Tecnologia |
|---|---|
| Linguaggio | PHP 8.2 |
| Database | TiDB Cloud Serverless (compatibile MySQL) |
| Web server | Apache 2.4 (Docker) |
| Hosting | Render.com (piano gratuito, auto-deploy) |
| Containerizzazione | Docker |
| 3D / WebGL | Three.js r128 (morph particelle) + WebGL 1.0 puro (shader fluido) |
| Animazioni | GSAP 3.12 + ScrollTrigger |
| CSS | Design system personalizzato — CSS custom properties, nessun framework |
| Tipografia | Space Grotesk + Inter (Google Fonts) |
| Auth | RBAC personalizzato (`src/Auth.php`) |
| i18n | Basato su cookie, file per lingua (`lang/*.php`) |
| Email | Resend HTTP API |

---

## Contribuire

1. Fai fork e crea un branch di funzionalità da `main`
2. Backend: prepared statement PDO, `htmlspecialchars()` su tutti gli output, avvolgi le scritture in `runInTransaction()`
3. Frontend: nessun framework — estendi i token di `main.css`, anima tramite GSAP
4. Apri una pull request su `main` — Render effettua il deploy automaticamente al merge

---

## Contributori

<div align="center">

| | Contributore | GitHub | Commit |
|---|---|---|---|
| <img src="https://github.com/EliseyRotar.png" width="36" style="border-radius:50%"> | Elisey Rotar | [@EliseyRotar](https://github.com/EliseyRotar) | project lead |
| <img src="https://github.com/DaminelliF.png" width="36" style="border-radius:50%"> | DaminelliF | [@DaminelliF](https://github.com/DaminelliF) | 14 commit |
| <img src="https://github.com/manuel-greco-s.png" width="36" style="border-radius:50%"> | Manuel Greco | [@manuel-greco-s](https://github.com/manuel-greco-s) | 6 commit |
| <img src="https://github.com/Andrea-Valente08.png" width="36" style="border-radius:50%"> | Andrea Valente | [@Andrea-Valente08](https://github.com/Andrea-Valente08) | 1 commit |

</div>
