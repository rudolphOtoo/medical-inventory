@echo off
setlocal
cd /d "%~dp0.."

echo Starting Docker Desktop if needed...
start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe" 2>nul

echo Waiting for Docker Engine...
:wait_docker
docker info >nul 2>&1
if errorlevel 1 (
  timeout /t 3 /nobreak >nul
  goto wait_docker
)

echo Starting MedTrack and MariaDB...
docker compose up -d --build
if errorlevel 1 (
  echo Startup failed. Run windows\VIEW_LOGS.bat for details.
  pause
  exit /b 1
)

echo MedTrack is starting at http://localhost:8080
start "" "http://localhost:8080"
endlocal
