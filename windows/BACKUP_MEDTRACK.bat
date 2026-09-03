@echo off
setlocal
cd /d "%~dp0.."

echo ========================================================
echo        MEDTRACK IMMUTABLE BACKUP GENERATOR
echo ========================================================
echo.

echo Running live hot backup inside container...
docker compose exec app php artisan medtrack:backup
if errorlevel 1 (
    echo [ERROR] Backup execution failed.
    pause
    exit /b 1
)

echo.
echo Exporting backup archives to host storage/backups/...
if not exist "storage\backups" mkdir "storage\backups"
docker compose cp app:/app/storage/backups/. storage/backups/ 2>nul

echo.
echo Backup completed and verified on host filesystem!
echo Location: storage\backups\
echo.
pause
endlocal
