# Medical Inventory - LAN Deployment Guide

This guide provides step-by-step instructions for deploying the Medical Inventory system on a Local Area Network (LAN), allowing any computer, tablet, or mobile device on the same network to access it via the host machine's IP address.

## Prerequisites

Before you begin, ensure you have the following installed on the host machine:

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows/macOS) or Docker Engine (Linux)
- [Docker Compose](https://docs.docker.com/compose/install/) (included with Docker Desktop)
- [Git](https://git-scm.com/downloads) (to clone the repository)

The host machine must remain powered on and connected to the network for client devices to access the system.

---

## 1. Clone or Copy the Repository

On the host machine, navigate to your desired location and clone the repository:

```bash
git clone <repository-url>
cd medical-inventory
```

If you have the files already, ensure you're in the project root directory.

---

## 2. Run the Automated Setup Script

The easiest way to deploy is using the provided setup script, which will automatically detect your LAN IP, configure the environment, and start the containers.

### For Windows (Command Prompt/PowerShell)

```cmd
scripts\setup-lan.bat
```

### For macOS/Linux (Terminal)

```bash
chmod +x scripts/setup-lan.sh
./scripts/setup-lan.sh
```

The script will:
1. Detect your local IP address (or prompt you to enter it)
2. Create a `.env` file from `.env.lan.example`
3. Update `APP_URL` with your host machine's IP
4. Create the SQLite database and set proper permissions
5. Install PHP dependencies and build frontend assets
6. Build and start Docker containers

Once complete, it will display the access URL (e.g., `http://192.168.1.100`).

---

## 3. Find Your Host Machine's LAN IP Address (Manual Method)

If you need to find your IP manually, use the following commands:

### Windows
1. Open Command Prompt (`cmd`) or PowerShell
2. Run:
```cmd
ipconfig
```
3. Look for "IPv4 Address" under your active network adapter (Ethernet or Wi-Fi). It will look like `192.168.1.100` or `10.0.0.50`.

### macOS
1. Open Terminal
2. Run:
```bash
ifconfig | grep "inet " | grep -v 127.0.0.1
```
Or for Wi-Fi specifically:
```bash
ipconfig getifaddr en0
```
For Ethernet: `ipconfig getifaddr en1`

### Linux
1. Open Terminal
2. Run:
```bash
ip addr | grep "inet " | grep -v 127.0.0.1
```
Or:
```bash
hostname -I | awk '{print $1}'
```

---

## 4. Configure Firewall Rules

To allow other devices on the LAN to access the application, you may need to allow inbound traffic on port 80 (or the port you're using).

### Windows Defender Firewall

1. Open "Windows Defender Firewall" from Control Panel
2. Click "Advanced Settings" → "Inbound Rules"
3. Click "New Rule" → "Port" → Next
4. Select "TCP" and specify port `80` → Next
5. Select "Allow the connection" → Next
6. Apply to Domain, Private, Public as appropriate (Private is recommended for hospital LAN) → Next
7. Name it "Medical Inventory" → Finish

### macOS

macOS firewall is typically enabled but may block incoming connections. Check:
1. System Settings → Network → Firewall
2. If enabled, ensure Docker has permission or temporarily allow incoming connections for the app

### Linux (UFW - Uncomplicated Firewall)

```bash
sudo ufw allow 80/tcp
sudo ufw status
```

### Linux (firewalld)

```bash
sudo firewall-cmd --permanent --add-port=80/tcp
sudo firewall-cmd --reload
```

---

## 5. Connect Client Devices

On any device connected to the same LAN (same Wi-Fi network or wired Ethernet):

1. Open a web browser (Chrome, Safari, Firefox, Edge)
2. Navigate to: `http://<HOST_IP>` (replace `<HOST_IP>` with your host machine's IP, e.g., `http://192.168.1.100`)
3. The Medical Inventory login page should load

**Supported Devices:** Windows PCs, macOS, Linux, iOS/iPadOS tablets/phones, Android tablets/phones.

---

## 6. Troubleshooting

### Cannot Access from Other Devices

- **Check network:** Ensure both host and client devices are on the same network/subnet
- **Verify IP:** Double-check the host IP hasn't changed (DHCP can change IPs). Consider setting a static IP reservation on your router for the host machine
- **Firewall:** Confirm firewall rules allow port 80
- **Docker:** Verify containers are running: `docker-compose -f docker-compose.lan.yml ps`
- **Bind to all interfaces:** The compose file binds to `0.0.0.0:80:80` which allows external connections

### Session/Cookie Issues on HTTP

Since this is a local HTTP-only deployment (no SSL/TLS), some browsers may have strict cookie policies. The configuration above sets:
- `SESSION_ENCRYPT=false` - Acceptable for trusted LAN environments
- `SESSION_DOMAIN=` (empty) - Allows cookies for the IP/domain used
- `SESSION_SECURE_COOKIE` is not set (defaults to false on HTTP)

If you experience login/session issues:
1. Ensure you're using `http://` not `https://`
2. Clear browser cookies for the IP address
3. Check `APP_URL` matches exactly what you're typing (including no trailing slash)
4. Try an incognito/private window to rule out cached cookies

### Port Conflicts

If port 80 is already in use on the host machine, you can modify `docker-compose.lan.yml` to use a different port, e.g.:

```yaml
ports:
  - "0.0.0.0:8080:80"
```

Then access via `http://<HOST_IP>:8080`.

### Containers Won't Start

Check the logs for errors:
```bash
docker-compose -f docker-compose.lan.yml logs -f
```

Common fixes:
- Ensure Docker Desktop is running
- Check disk space on host machine
- Rebuild containers: `docker-compose -f docker-compose.lan.yml up -d --build --force-recreate`

### Database/Storage Permissions (Linux)

On some Linux systems, you may need to adjust ownership:
```bash
sudo chown -R $USER:$USER database storage bootstrap/cache
chmod -R 775 storage bootstrap/cache database
```

---

## 7. Maintenance Commands

| Action | Command |
|---|---|
| View logs | `docker-compose -f docker-compose.lan.yml logs -f` |
| Stop containers | `docker-compose -f docker-compose.lan.yml down` |
| Start containers | `docker-compose -f docker-compose.lan.yml up -d` |
| Restart containers | `docker-compose -f docker-compose.lan.yml restart` |
| Rebuild after code changes | `docker-compose -f docker-compose.lan.yml up -d --build` |
| Run migrations | `docker-compose -f docker-compose.lan.yml exec app php artisan migrate --force` |

---

## 8. Security Notes

This configuration is intended for **trusted local network environments** (e.g., hospital internal LAN behind a router/firewall). Key considerations:

- **No SSL/TLS:** Communication is over plain HTTP. Do not expose this to the internet
- **Local network only:** Ensure your router doesn't forward port 80 to the internet
- **Session encryption disabled:** `SESSION_ENCRYPT=false` is acceptable for LAN-only use where the network is trusted
- **Keep host secure:** The host machine should have standard security updates applied

For production internet-facing deployments, use HTTPS with a valid SSL certificate.

---

## Support

If you encounter issues, check the logs first using the commands above, or consult your IT department for network/firewall configurations.
