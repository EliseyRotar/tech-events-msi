<div align="center">

# Tech Dragons Events

**Enterprise-grade esports event management platform**

[![PHP](https://img.shields.io/badge/PHP-8.2-777bb4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.6-003545?style=flat-square&logo=mariadb&logoColor=white)](https://mariadb.org)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ed?style=flat-square&logo=docker&logoColor=white)](https://docker.com)
[![License](https://img.shields.io/badge/License-MIT-22c55e?style=flat-square)](LICENSE)

[Overview](#overview) · [Architecture](#architecture) · [Features](#features) · [Quickstart](#quickstart) · [Security](#security) · [Contributing](#contributing)

</div>

---

## Overview

Tech Dragons Events is a full-stack web application for running professional esports events end-to-end — from event creation and tournament scheduling to team registration and roster management. Built on PHP 8.2 with MariaDB and shipped as a single `docker compose up` command.

---

## Architecture

The project follows a **public-root isolation** pattern: only the `public/` directory is exposed to the web server. Application logic, credentials, and templates live outside the web root.

```
tech-events-msi/
├── public/                 # Web root (only this is exposed)
│   ├── assets/             # CSS, images, static HTML archives
│   ├── index.php           # Homepage
│   ├── dashboard.php       # Event management portal
│   ├── login.php / register.php
│   ├── createEvent.php     # Admin: create events
│   ├── addTournament.php   # Admin: attach tournaments to events
│   ├── addGame.php         # Admin: register game titles
│   ├── addTeam.php         # Register a new organization
│   ├── addMember.php       # Add players to a roster
│   ├── signTeam.php        # Enter a team into a tournament
│   └── viewTeam.php        # View registered rosters
├── src/
│   ├── Auth.php            # RBAC — session, login, admin guards
│   ├── EnvLoader.php       # Reads .env without exposing values to logs
│   ├── helpers.php         # runInTransaction(), t() i18n helper
│   └── Controller/         # (future expansion)
├── templates/
│   └── layout/
│       ├── header.php      # Global nav, lang switcher, <head>
│       └── footer.php      # Global footer
├── lang/
│   ├── it.php              # Italian translation strings
│   └── en.php              # English translation strings
├── database/
│   ├── 01_tables.sql       # Schema
│   └── 02_elements.sql     # Seed data
└── config.php              # PDO bootstrap, env loading
```

---

## Features

### Event Management
- Create LAN and online events with date range, location, and capacity
- Admin-only creation and tournament assignment
- Dashboard with real-time event listing and tournament drill-down

### Tournament System
- Multiple tournaments per event, each tied to a specific game title
- Prize pool tracking
- Team registration and roster viewing per tournament

### Team & Roster Management
- Register organizations with sponsor associations
- Add players to team rosters using unique in-game nicknames
- Per-team roster management with user-to-member linking

### Internationalisation (i18n)
- Language switcher (Italian / English) in the global nav
- Cookie-based language persistence (1-year expiry)
- Translation files under `lang/` — add new languages by dropping in a file

### Security
- All writes wrapped in `runInTransaction()` with `\Throwable` catch — no unclosed transactions
- 100% PDO prepared statements — zero string interpolation in SQL
- Argon2ID password hashing
- `htmlspecialchars()` on every user-facing output
- Auth guards on every authenticated route (`requireLogin()` / `requireAdmin()`)
- Open-redirect protection on the lang-switch handler
- `.env` and `src/` outside the web root

---

## Quickstart

### Docker (recommended)

**Linux / Arch Linux:**
```bash
./start_arch.sh
```

**Windows 10/11:**
```bat
start_windows.bat
```

Both scripts start the full stack (Apache + PHP 8.2 + MariaDB 10.6) and seed the database automatically.

Open **http://localhost:8080** — the app is ready.

### Manual setup

**Requirements:** PHP 8.2+, MariaDB 10.6+, Composer (optional, no runtime dependencies).

```bash
# 1. Clone
git clone https://github.com/EliseyRotar/tech-events-msi.git
cd tech-events-msi

# 2. Configure environment
cp .env.example .env
# edit .env with your DB credentials

# 3. Import the schema and seed data
mariadb -u root -p < database/01_tables.sql
mariadb -u root -p tech_dragons_events < database/02_elements.sql

# 4. Point your web server document root to ./public
# Apache example:
#   DocumentRoot /path/to/tech-events-msi/public
```

### Default credentials (seeded)

| Email | Password | Role |
|-------|----------|------|
| mario@example.com | *(hashed — reset via register.php)* | Admin |
| luigi@example.com | *(hashed — reset via register.php)* | User |

To create a working admin account, use `register.php` and then set `isAdmin = 1` in the DB.

---

## Security

| Concern | Implementation |
|---------|----------------|
| SQL Injection | PDO prepared statements everywhere — no string interpolation in queries |
| XSS | `htmlspecialchars()` on every `<?=` output; `ENT_QUOTES` on attributes |
| Auth bypass | `Auth::requireLogin()` / `Auth::requireAdmin()` at the top of every protected page |
| Password storage | `password_hash(..., PASSWORD_ARGON2ID)` |
| Open redirect | `?lang=` handler validates URL starts with `/` and not `//` |
| Transactions | `runInTransaction()` catches `\Throwable` — any exception rolls back |
| Credential exposure | `.env` is outside the web root; `EnvLoader` reads it without `$_ENV` leakage |

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.2 |
| Database | MariaDB 10.6 |
| Web server | Apache 2.4 (inside Docker) |
| Containerisation | Docker Compose |
| Frontend | Vanilla CSS (custom design system, no framework) |
| Auth | Custom RBAC (`src/Auth.php`) |

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full guide. In short:

1. Fork the repo and create a feature branch
2. Follow the existing code style (no frameworks, PDO prepared statements, `htmlspecialchars()` on output)
3. Open a pull request against `main`

---

<div align="center">

Built by [Elisey Rotar](https://github.com/EliseyRotar)

</div>
