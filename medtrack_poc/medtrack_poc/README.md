# MedTrack PoC Package

This package contains the combined product specification, Docker deployment foundation, and Windows operator scripts for the MedTrack hospital equipment-management PoC.

## What runs where

The central Windows Admin/server PC runs Docker Desktop. Docker Compose starts one FrankenPHP/Laravel container, one MariaDB container, and an optional Adminer container. Department computers do not install Laravel, Docker, MariaDB, or Adminer. They use a browser and visit the central PC’s private LAN address.

```text
Department browsers ── hospital Wi‑Fi ── Windows Admin/server PC
                                              └── Docker Desktop
                                                  ├── FrankenPHP/Laravel
                                                  ├── MariaDB database
                                                  └── Adminer (optional)
```

## First-time setup

Copy `.env.example` to `.env` and replace the placeholder passwords and Laravel key. Build the Laravel project in this folder so `composer.json`, `composer.lock`, the `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, and `routes/` directories exist. Install Docker Desktop on the central Windows computer and ensure it can run Linux containers. Confirm that the hospital permits Docker Desktop and that Windows Firewall can allow the application port on the private network.

The first deployment operator runs `windows\START_MEDTRACK.bat`. The container entrypoint runs Laravel migrations with `--force`, creates the storage link, and warms production caches. For a real production installation, review migrations before the first run and set `RUN_MIGRATIONS=false` after the schema is established if migrations should be applied manually.

## Daily use

The operator double-clicks `windows\START_MEDTRACK.bat`. It starts Docker Desktop, waits for Docker Engine, starts or rebuilds the app and database, and opens the local browser. Department users browse to the server’s LAN address, for example `http://192.168.1.50:8080`.

To stop the services without removing data, double-click `windows\STOP_MEDTRACK.bat`. This uses `docker compose stop`; it does not remove named volumes. Never use `docker compose down -v` during routine operations.

## Adminer

Run `windows\OPEN_ADMINER.bat` only when direct database administration is needed. Adminer is bound to `127.0.0.1:8081`, so it is accessible only from the central Admin/server PC by default. Log in with the database administrator credentials, use `db` as the server name, and close/stop Adminer when finished.

Adminer is a technical tool. Hospital staff should use Laravel’s validated admin screens for normal work. Before destructive SQL, create a backup and verify the target carefully.

## Backups

Run `windows\BACKUP_MEDTRACK.bat` while the stack is running. It writes a MariaDB SQL dump under `data\backups`. The PoC also uses persistent Docker storage for the database and application files. Configure a scheduled copy of backups and uploads to an approved external drive or network share. A backup is not considered reliable until a restore test has succeeded.

## LAN setup

Give the central PC a stable private IP through a DHCP reservation or static configuration. Allow the application port only on the private hospital network in Windows Firewall. Do not configure public router port forwarding. If hospital Wi-Fi uses client isolation or separate VLANs, hospital IT must allow department devices to reach the central server.

## Safety rules

Do not commit `.env` to source control. Do not expose MariaDB’s port to the LAN. Do not expose Adminer to the LAN unless there is a documented need and additional authentication. Do not run destructive migration commands in startup scripts. Keep the central PC powered on while the hospital is using the app.
