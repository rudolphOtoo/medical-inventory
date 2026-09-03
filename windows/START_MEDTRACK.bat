@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0.."

echo ========================================================
echo         MEDTRACK HOSPITAL OPERATIONS SYSTEM
echo                Local LAN Server Boot
echo ========================================================
echo.

echo [1/3] Checking Docker daemon status...
docker info >nul 2>&1
if errorlevel 1 (
    echo Starting Docker Desktop...
    start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe" 2>nul
    
    echo Waiting for Docker Engine to initialize...
    :wait_docker
    timeout /t 3 /nobreak >nul
    docker info >nul 2>&1
    if errorlevel 1 goto wait_docker
)

echo [2/3] Building and starting MedTrack FrankenPHP container...
docker compose up -d --build
if errorlevel 1 (
    echo [ERROR] Container startup failed. Run windows\VIEW_LOGS.bat to investigate.
    pause
    exit /b 1
)

echo [3/3] MedTrack is active and serving hospital LAN traffic!
echo.
echo ========================================================
echo  Local URL:   http://localhost:8000
echo  Credentials: admin@medtrack.test (pwd: password)
echo ========================================================
echo.

start "" "http://localhost:8000"
endlocal
