#!/bin/bash

# Medical Inventory - LAN Deployment Setup Script
# This script automates the setup for LAN hosting

set -e

echo "======================================"
echo "Medical Inventory LAN Setup"
echo "======================================"
echo ""

# Detect OS and get local IP
OS=$(uname -s)

get_local_ip() {
    if [[ "$OS" == "Darwin" ]]; then
        # macOS
        local_ip=$(ifconfig | grep -E "inet (192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1]))" | head -1 | awk '{print $2}')
        if [ -z "$local_ip" ]; then
            local_ip=$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null)
        fi
    else
        # Linux
        local_ip=$(ip addr show | grep -E "inet (192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1]))" | head -1 | awk '{print $2}' | cut -d/ -f1)
        if [ -z "$local_ip" ]; then
            local_ip=$(hostname -I | awk '{print $1}')
        fi
    fi
    echo "$local_ip"
}

LOCAL_IP=$(get_local_ip)

# Copy .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env from .env.lan.example..."
    cp .env.lan.example .env
    echo ""
fi

# Prompt for IP address
echo "Detected local IP address: ${LOCAL_IP:-Not detected}"
read -p "Enter the host machine's LAN IP address (or press Enter to use detected): " user_ip
if [ -n "$user_ip" ]; then
    LOCAL_IP="$user_ip"
fi

if [ -z "$LOCAL_IP" ]; then
    echo "ERROR: Could not detect IP address. Please enter it manually."
    exit 1
fi

# Update APP_URL in .env
if command -v sed >/dev/null 2>&1; then
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "s|APP_URL=.*|APP_URL=http://${LOCAL_IP}|" .env
    else
        sed -i "s|APP_URL=.*|APP_URL=http://${LOCAL_IP}|" .env
    fi
    echo "Updated APP_URL to http://${LOCAL_IP} in .env"
fi

echo ""

# Prepare directories and database
echo "Setting up directories and database..."
mkdir -p database storage/app/public storage/logs storage/framework/{cache,sessions,views} bootstrap/cache

# Create SQLite database if it doesn't exist
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "Created database/database.sqlite"
fi

# Set proper permissions
chmod 775 database/database.sqlite 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""

# Install dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo ""

# Generate app key if needed
echo "Generating application key..."
php artisan key:generate --force

echo ""

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

echo ""

# Link storage
echo "Creating storage link..."
php artisan storage:link --force 2>/dev/null || true

echo ""

# Install and build assets
if [ -f package.json ]; then
    echo "Installing Node dependencies..."
    npm ci --no-audit --no-fund 2>/dev/null || npm install --no-audit --no-fund
    echo "Building frontend assets..."
    npm run build
fi

echo ""

# Build and start containers
echo "Building and starting Docker containers..."
docker-compose -f docker-compose.lan.yml up -d --build

echo ""
echo "======================================"
echo "Setup Complete!"
echo "======================================"
echo ""
echo "Your Medical Inventory system is now accessible at:"
echo "  http://${LOCAL_IP}"
echo ""
echo "All devices on your LAN (same Wi-Fi/Ethernet network)"
echo "can access the system using this URL."
echo ""
echo "To view logs: docker-compose -f docker-compose.lan.yml logs -f"
echo "To stop: docker-compose -f docker-compose.lan.yml down"
echo ""
