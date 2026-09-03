@echo off
setlocal
cd /d "%~dp0.."

echo Stopping MedTrack hospital server containers...
docker compose down

echo MedTrack containers stopped safely.
timeout /t 3 /nobreak >nul
endlocal
