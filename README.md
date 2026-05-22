<div align="center">

# 🎮 Tech Events - Enterprise Esports Management

### The Infrastructure for Professional Competition
*A high-performance, secure, and architecturally sound platform for managing global esports events, tournaments, and professional organizations.*

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg?style=flat-square&logo=php)](https://www.php.net/)
[![MariaDB](https://img.shields.io/badge/database-MariaDB-003545.svg?style=flat-square&logo=mariadb)](https://mariadb.org/)
[![Docker](https://img.shields.io/badge/docker-ready-2496ed.svg?style=flat-square&logo=docker)](https://www.docker.com/)
[![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)](LICENSE)

[Architecture](#-architecture) • [Features](#-core-capabilities) • [Deployment](#-deployment-guide) • [Security](#-security-standards) • [Support](#-support)

</div>

---

## 🏗️ Architecture

Tech Events is built on a **Modern PHP Architecture** that prioritizes security through isolation.

```text
tech-events-msi/
├── 📂 public/           # Entry Points (Only folder exposed to web)
│   ├── 📂 assets/       # Compiled CSS & Static Data
│   └── 📄 index.php     # Main entry
├── 📂 src/              # Core Business Logic (PSR-4)
│   ├── 📄 Auth.php      # RBAC & Session Security
│   └── 📄 EnvLoader.php # Secure Credential Management
├── 📂 templates/        # Reusable UI Components
├── 📂 database/         # Versioned SQL Schema
└── 📄 config.php        # Application Bootstrap
```

---

## ✨ Core Capabilities

### 🗓️ Event Life-Cycle Management
- **Centralized Command**: Admin portal for creating LAN/Online events with capacity tracking.
- **Dynamic Scheduling**: Manage start/end timestamps with localized time-zone support.
- **Location Analytics**: Track events by city and country for global circuit planning.

### 🏆 Tournament Orchestration
- **Relational Integrity**: Associate multiple tournaments (CS2, Valorant, Dota 2) with a single master event.
- **Financial Tracking**: Manage prize pools and sponsorship distributions.
- **Registration Flow**: Self-service registration for verified professional organizations.

### 👥 Professional Organizations (Teams)
- **Organization Profiles**: Manage team names, rosters, and official sponsors.
- **Athlete Verification**: Add players to specific organization rosters with unique nicknames.
- **Role-Based Control**: Separate permissions for Players, Admins, and Organizers.

---

## 🚀 Deployment Guide

### 🐳 The One-Click Solution (Recommended)
The project is fully containerized. To deploy the entire stack including the database and web server:

**Arch Linux / Linux:**
```bash
./start_arch.sh
```

**Windows 10/11:**
```powershell
./start_windows.bat
```

### 🛠️ Manual Configuration
1. **Prerequisites**: PHP 8.2+, MariaDB/MySQL.
2. **Environment**: Copy `.env.example` to `.env` and configure your credentials.
3. **Database**: Import `database/01_tables.sql` followed by `database/02_elements.sql`.
4. **Server**: Point your web root to the `/public` directory.

---

## 🛡️ Security Standards

Tech Events is engineered with a **Security-First** mindset:

- **RBAC (Role-Based Access Control)**: Centralized `Auth` class gates every administrative and user-level action.
- **Password Integrity**: Uses `Argon2ID` (industry-standard hashing) for all user credentials.
- **Data Protection**: 100% PDO Prepared Statements to eliminate SQL Injection risks.
- **Isolation**: The core application logic and `.env` files are stored outside the web root.
- **Environment Safety**: Custom `EnvLoader` ensures sensitive keys are never exposed in server logs.

---

## 🤖 DevOps & CI/CD

Integrated with **GitHub Actions** for automated quality assurance:
- **Linting**: Automatic validation of `composer.json` and dependency trees.
- **Quality Checks**: On every push to `main`, the CI suite ensures the application is deployable.

---

## 🎨 Visual Identity

The platform features a custom-designed **"Liquid Tech"** UI:
- **Dark Mode**: Optimized for low-eye-strain during high-intensity competition.
- **Glassmorphism**: Backdrop-blur effects for high-end aesthetic depth.
- **Bento Grid**: Modular layout for dense data visualization.
- **Animated Background**: High-performance, GPU-accelerated mesh orbs.

---

<div align="center">

### Developed with Excellence by [Elisey Rotar](https://github.com/EliseyRotar)
*Turning Esports Ideas into Professional Reality*

</div>
