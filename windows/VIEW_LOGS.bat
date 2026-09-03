@echo off
setlocal
cd /d "%~dp0.."

echo Following MedTrack live server logs (Press Ctrl+C to exit)...
docker compose logs -f app
endlocal
