@echo off
setlocal
cd /d "%~dp0.."

echo Stopping MedTrack containers...
docker compose stop
if errorlevel 1 (
  echo Stop failed. Run windows\VIEW_LOGS.bat for details.
  pause
  exit /b 1
)

echo MedTrack stopped. Database volumes were preserved.
endlocal
pause
