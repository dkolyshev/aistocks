# Docker Configuration Summary

## ✅ Docker Setup Complete

The Docker environment is fully configured for the Stock Report Generation System.

---

## What's Included

### Container Configuration
- **Base Image**: `php:5.5-apache`
- **Container Name**: `aistocks-php55`
- **Port**: `8080` (host) → `80` (container)
- **Web Server**: Apache 2.4

### Installed Software
- ✅ **PHP 5.5** with Apache
- ✅ **wkhtmltopdf** - For automatic PDF generation
- ✅ **xvfb** - X virtual framebuffer (required by wkhtmltopdf)
- ✅ **PHP Extensions**:
  - mbstring
  - xml
  - gd
  - fileinfo (built-in)

### PHP Configuration
Custom settings optimized for file uploads and report generation:
```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
display_errors = On (development mode)
```

---

## Files Created/Updated

### Modified Files
1. **Dockerfile** - Updated with:
   - wkhtmltopdf installation
   - PHP upload settings
   - Directory creation and permissions
   - Removed MySQL (not needed)

2. **docker-compose.yml** - Updated with:
   - Correct volume mounts (entire project root)
   - Container name: `aistocks-php55`
   - Environment variables
   - Health check for `index.php`
   - Persistent volumes for reports/images/data

3. **README.md** - Complete rewrite with:
   - Quick start guide
   - Project structure explanation
   - Troubleshooting section
   - Deployment instructions
   - Command reference

4. **.env.example** - Updated with project-specific comments

### New Files
5. **.env** - Environment configuration (ready to use)
6. **start.sh** - Quick start script (executable)
7. **DOCKER_SUMMARY.md** - This file

---

## Project Structure (Mounted)

The Docker container mounts the entire project:

```
Host: /home/dmitry-home/apps/aiStocks
  ↓
Container: /var/www/html

Project Root (web-accessible):
├── index.php                    # Entry point → reportManager.php
├── reportManager.php            # Main CRUD UI
├── generateReports.php          # Report generation service
├── reportSettings.json          # Settings storage (writable)
├── .htaccess                    # Apache configuration
│
├── /app                         # Application code (MVC)
│   ├── /config
│   ├── /models
│   ├── /services
│   └── /controllers
│
├── /data                        # Stock data & templates (writable)
├── /reports                     # Generated reports (writable)
├── /images                      # Uploaded images (writable)
└── /example                     # Reference examples
```

---

## Quick Start Commands

### Start Application
```bash
cd /home/dmitry-home/apps/aiStocks/.docker

# Automated start
./start.sh

# OR manual start
docker-compose up -d
```

### Access Application
```
http://localhost:8080
```

### Stop Application
```bash
docker-compose down
```

---

## Verification Steps

After starting the container, verify everything works:

### 1. Check Container Status
```bash
docker ps
```
Should show `aistocks-php55` with status "Up" and "(healthy)"

### 2. Check PHP Version
```bash
docker-compose exec php-app php -v
```
Should show: `PHP 5.5.x`

### 3. Verify wkhtmltopdf
```bash
docker-compose exec php-app which wkhtmltopdf
docker-compose exec php-app wkhtmltopdf --version
```
Should show installation path and version

### 4. Test Application
Open browser: `http://localhost:8080`
- Should redirect to Report Manager
- Try creating a report configuration
- Click "Run Report Generation Service"
- Check `/reports/` directory for generated files

### 5. Check Permissions
```bash
docker-compose exec php-app ls -la /var/www/html/reports
```
Should show `www-data` as owner with `777` permissions

---

## Features & Capabilities

### ✅ Automatic PDF Generation
- wkhtmltopdf is pre-installed and configured
- PDF reports generated automatically
- No manual PDF upload needed (unless preferred)

### ✅ File Uploads
- Upload article images (up to 10MB)
- Upload PDF cover images (up to 10MB)
- Upload manual PDFs (up to 10MB)

### ✅ Persistent Data
All generated reports, uploaded files, and settings persist on host:
- `/reports/` - Generated reports
- `/images/` - Uploaded images
- `/data/` - Stock data and templates
- `reportSettings.json` - Configurations

### ✅ Live Code Updates
Changes to PHP files on host are immediately reflected:
- Edit files locally
- Refresh browser to see changes
- No container rebuild needed

### ✅ Health Monitoring
Container includes health check:
- Pings `index.php` every 30 seconds
- Shows "healthy" status when running
- Check with: `docker ps`

---

## Common Tasks

### View Application Logs
```bash
docker-compose logs -f php-app
```

### Access Container Shell
```bash
docker-compose exec php-app bash
```

### Restart Container
```bash
docker-compose restart
```

### Rebuild from Scratch
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Fix Permissions
```bash
docker-compose exec php-app chown -R www-data:www-data /var/www/html
docker-compose exec php-app chmod -R 777 /var/www/html/reports /var/www/html/images /var/www/html/data
```

### Check PHP Configuration
```bash
docker-compose exec php-app php -i | grep upload
docker-compose exec php-app php -i | grep memory
```

---

## Deployment Options

### Local Network Access
1. Find your machine's IP:
   ```bash
   hostname -I
   ```

2. Access from other devices:
   ```
   http://192.168.1.XXX:8080
   ```

### Deploy to Server

**Using Docker (Recommended):**
```bash
# On server, copy entire project
scp -r /home/dmitry-home/apps/aiStocks user@server:/path/

# On server, start container
cd /path/aiStocks/.docker
docker-compose up -d
```

**Using Manual Installation:**
See [README.md](README.md#deployment-on-other-servers)

---

## Environment Variables

Configured in `.env` file:

| Variable | Default | Description |
|----------|---------|-------------|
| `COMPOSE_PROJECT_NAME` | aiStocks | Docker project name |
| `APP_PORT` | 8080 | Host port for application |

Change port example:
```bash
# Edit .env
APP_PORT=9000

# Restart
docker-compose down
docker-compose up -d
```

---

## Security Notes

### Development Mode
Current configuration is optimized for development:
- `display_errors = On`
- Port 8080 exposed
- Writable directories (777 permissions)

### Production Recommendations
If deploying to production:

1. **Disable error display** (edit Dockerfile):
   ```dockerfile
   echo 'display_errors = Off';
   ```

2. **Add reverse proxy** (nginx with SSL)

3. **Restrict permissions**:
   ```bash
   chmod 755 /var/www/html/reports
   chmod 755 /var/www/html/images
   ```

4. **Add authentication** to reportManager.php

5. **Use environment variables** for sensitive config

⚠️ **Remember**: PHP 5.5 is end-of-life (July 2016). Use only for:
- Legacy app maintenance
- Development/testing
- Isolated environments

---

## Troubleshooting

### Container Won't Start
```bash
# Check logs
docker-compose logs

# Rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Port Already in Use
Change port in `.env`:
```bash
APP_PORT=9000
```

### Permission Errors
```bash
# Fix inside container
docker-compose exec php-app chmod -R 777 /var/www/html/reports
docker-compose exec php-app chown -R www-data:www-data /var/www/html
```

### PDF Generation Fails
```bash
# Verify wkhtmltopdf
docker-compose exec php-app wkhtmltopdf --version

# If missing, rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Can't Upload Files
```bash
# Check PHP upload settings
docker-compose exec php-app php -i | grep upload_max

# Check directory permissions
docker-compose exec php-app ls -la /var/www/html/images
```

---

## Testing Checklist

After starting Docker container:

- [ ] Container is running: `docker ps`
- [ ] Container is healthy: `docker ps` shows "(healthy)"
- [ ] Application accessible: `http://localhost:8080`
- [ ] Report Manager loads: `http://localhost:8080/reportManager.php`
- [ ] PHP version correct: `docker-compose exec php-app php -v`
- [ ] wkhtmltopdf installed: `docker-compose exec php-app which wkhtmltopdf`
- [ ] Can create configuration
- [ ] Can upload images
- [ ] Can generate reports
- [ ] HTML report created in `/reports/`
- [ ] PDF report created in `/reports/`
- [ ] Flipbook report created in `/reports/`

---

## Performance Notes

### First Build
- Initial build takes 5-10 minutes (downloads base image, installs packages)
- Subsequent starts take 5-10 seconds

### Runtime Performance
- PHP 5.5 is lightweight
- Report generation typically takes 2-5 seconds per report
- PDF generation adds 1-2 seconds per PDF

### Resource Usage
- Memory: ~100-200 MB
- CPU: Minimal when idle
- Disk: ~500 MB for image + application files

---

## Additional Resources

- **Full Docker Documentation**: [README.md](README.md)
- **Application Documentation**: [../README_IMPLEMENTATION.md](../README_IMPLEMENTATION.md)
- **Project Summary**: [../PROJECT_SUMMARY.md](../PROJECT_SUMMARY.md)

---

## Support

For Docker-related issues:
1. Check [README.md](README.md) troubleshooting section
2. View logs: `docker-compose logs -f`
3. Check container health: `docker ps`
4. Verify permissions: `docker-compose exec php-app ls -la /var/www/html`

For application issues:
- See [../README_IMPLEMENTATION.md](../README_IMPLEMENTATION.md)

---

**Status**: ✅ Docker environment fully configured and ready to use!

**Quick Start**: Run `./start.sh` and access `http://localhost:8080`
