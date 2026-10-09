@echo off
setlocal enabledelayedexpansion

REM Medical Inventory - LAN Deployment Setup Script for Windows
REM This script automates the setup for LAN hosting

echo ======================================
echo Medical Inventory LAN Setup
echo ======================================
echo.

REM Get local IP address
for /f "tokens=2 delims=:" %%i in ('ipconfig ^| findstr /C:"IPv4 Address"') do (
    set "ip=%%i"
    set "ip=!ip:~1!"
    goto found_ip
)

:found_ip
echo Detected local IP address: %ip%
set /p "user_ip=Enter the host machine's LAN IP address (or press Enter to use detected): "

if not "%user_ip%"=="" (
    set "ip=%user_ip%"
)

if "%ip%"=="" (
    echo ERROR: Could not detect IP address. Please enter it manually.
    pause
    exit /b 1
)

REM Copy .env if it doesn't exist
if not exist .env (
    echo Creating .env from .env.lan.example...
    copy .env.lan.example .env >nul
    echo.
)

REM Update APP_URL in .env (simple approach)
powershell -Command "(Get-Content .env) -replace 'APP_URL=.*', 'APP_URL=http://%ip%' | Set-Content .env"
echo Updated APP_URL to http://%ip% in .env
echo.

REM Prepare directories and database
echo Setting up directories and database...
if not exist database mkdir database
if not exist storage\app\public mkdir storage\app\public
if not exist storage\logs mkdir storage\logs
if not exist storage\framework\cache mkdir storage\framework\cache
if not exist storage\framework\sessions mkdir storage\framework\sessions
if not exist storage\framework\views mkdir storage\framework\views
if not exist bootstrap\cache mkdir bootstrap\cache

REM Create SQLite database if it doesn't exist
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo Created database\database.sqlite
)

echo.

REM Install PHP dependencies
echo Installing PHP dependencies...
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo.

REM Generate app key
echo Generating application key...
php artisan key:generate --force

echo.

REM Run migrations
echo Running database migrations...
php artisan migrate --force

echo.

REM Link storage
echo Creating storage link...
php artisan storage:link --force 2>nul || echo Storage link already exists

echo.

REM Install and build assets
if exist package.json (
    echo Installing Node dependencies...
    call npm ci --no-audit --no-fund 2>nul || call npm install --no-audit --no-fund
    echo Building frontend assets...
    call npm run build
)

echo.

REM Build and start containers
echo Building and starting Docker containers...
docker-compose -f docker-compose.lan.yml up -d --build

echo.
echo ======================================
echo Setup Complete!
echo ======================================
echo.
echo Your Medical Inventory system is now accessible at:
echo   http://%ip%
echo.
echo All devices on your LAN (same Wi-Fi/Ethernet network)
echo can access the system using this URL.
echo.
echo To view logs: docker-compose -f docker-compose.lan.yml logs -f
echo To stop: docker-compose -f docker-compose.lan.yml down
echo.
pause
