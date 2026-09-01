@echo off
setlocal
cd /d "%~dp0.."

if not exist "data\backups" mkdir "data\backups"
for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd_HHmmss"') do set STAMP=%%i
set FILE=data\backups\medtrack_%STAMP%.sql

echo Creating database backup...
docker compose exec -T db sh -c "mariadb-dump -u root -p\"$MARIADB_ROOT_PASSWORD\" $MARIADB_DATABASE" > "%FILE%"
if errorlevel 1 (
  echo Backup failed. Check that the stack is running and .env is configured.
  if exist "%FILE%" del /q "%FILE%"
  pause
  exit /b 1
)

echo Database backup created: %FILE%
echo Configure a scheduled copy of data\backups and persistent uploads to an approved external or network drive.
endlocal
pause
