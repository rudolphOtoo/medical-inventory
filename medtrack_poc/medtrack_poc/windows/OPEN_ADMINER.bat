@echo off
setlocal
cd /d "%~dp0.."

echo Starting Adminer for technical administration...
docker compose --profile adminer up -d adminer
if errorlevel 1 (
  echo Adminer failed to start.
  pause
  exit /b 1
)
start "" "http://127.0.0.1:8081"
echo Adminer is available only on this Admin/server PC at http://127.0.0.1:8081
endlocal
pause
