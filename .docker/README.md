# Stock Report Generation System - Docker Setup

This Docker configuration provides a **PHP 5.5** environment with Apache web server and **wkhtmltopdf** for PDF generation.

---

## Prerequisites

- Docker installed on your machine
- Docker Compose installed

---

## Quick Start

### 1. Navigate to the Docker directory

```bash
cd /home/dmitry-home/apps/aiStocks/.docker
```

### 2. Copy environment file (optional)

```bash
cp .env.example .env
```

Edit `.env` to change the port if needed (default: 8080).

### 3. Start the Docker container

```bash
docker-compose up -d
```

This will:
- Build the PHP 5.5 + Apache image
- Install wkhtmltopdf for PDF generation
- Mount the project directory
- Start the web server on port 8080

### 4. Access the application

Open your browser and navigate to:

**http://localhost:8080**

You'll be automatically redirected to the Report Manager.

### 5. Stop the container

```bash
docker-compose down
```

---

## Project Structure (Mounted in Container)

The entire project root is mounted to `/var/www/html` in the container:

```
/var/www/html (container) → /home/dmitry-home/apps/aiStocks (host)
├── index.php                    # Entry point
├── reportManager.php            # CRUD interface (main UI)
├── generateReports.php          # Report generation service
├── reportSettings.json          # Settings storage
│
├── /app                         # Application code (classes)
│   ├── /config
│   ├── /models
│   ├── /services
│   └── /controllers
│
├── /data                        # Data files
│   ├── data.csv                # Stock data
│   ├── disclaimer.html         # Templates
│   ├── reportIntro.html
│   └── stockBlock.html
│
├── /reports                     # Generated reports (output)
│   ├── {filename}.html
│   ├── {filename}.pdf
│   └── {filename} flipbook.html
│
└── /images                      # Uploaded images
    └── articleImage.jpg
```

---

## Usage

### Creating Reports

1. **Access Report Manager**: http://localhost:8080/reportManager.php
2. **Create Configuration**:
   - Enter report details (file name, title, number of stocks)
   - Upload images (optional)
   - Customize HTML templates (optional)
   - Save configuration
3. **Generate Reports**: Click "🚀 Run Report Generation Service"
4. **View Reports**: Reports are saved in `/reports/` directory

### Generated Files

After generation, you'll find:
- `reports/{filename}.html` - Standard HTML report
- `reports/{filename}.pdf` - PDF report (wkhtmltopdf)
- `reports/{filename} flipbook.html` - Interactive flipbook

---

## Docker Configuration Details

### Installed Software

- **PHP 5.5** with Apache
- **wkhtmltopdf** - For PDF generation
- **PHP Extensions**: mbstring, xml, gd, fileinfo

### PHP Configuration

Custom PHP settings (configured in Dockerfile):
- `upload_max_filesize = 10M`
- `post_max_size = 10M`
- `memory_limit = 256M`
- `max_execution_time = 300`
- `display_errors = On` (for development)

### Persistent Volumes

The following directories are mounted as volumes to persist data:
- `/reports` - Generated reports
- `/images` - Uploaded images
- `/data` - Stock data and templates

Files created in these directories are accessible on your host machine.

### Ports

- **8080** (host) → **80** (container)
- Change port in `.env` file: `APP_PORT=9000`

---

## Troubleshooting

### Permission Issues

If you encounter permission errors when generating reports or uploading files:

```bash
# Fix permissions inside the container
docker-compose exec php-app chown -R www-data:www-data /var/www/html/reports
docker-compose exec php-app chown -R www-data:www-data /var/www/html/images
docker-compose exec php-app chown -R www-data:www-data /var/www/html/data
docker-compose exec php-app chmod -R 777 /var/www/html/reports
docker-compose exec php-app chmod -R 777 /var/www/html/images
docker-compose exec php-app chmod -R 777 /var/www/html/data
```

### View Container Logs

```bash
docker-compose logs -f php-app
```

### Rebuild Container

If you make changes to the Dockerfile:

```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Access Container Shell

```bash
docker-compose exec php-app bash
```

Once inside, you can:
```bash
# Check PHP version
php -v

# Check wkhtmltopdf installation
which wkhtmltopdf
wkhtmltopdf --version

# Check file permissions
ls -la /var/www/html/reports
```

### PDF Generation Not Working

Check if wkhtmltopdf is installed:

```bash
docker-compose exec php-app wkhtmltopdf --version
```

If not installed, rebuild the container:

```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### File Upload Fails

Check PHP upload settings:

```bash
docker-compose exec php-app php -i | grep upload
```

Should show:
- `upload_max_filesize => 10M`
- `post_max_size => 10M`

---

## Deployment on Other Servers

To deploy this application on another server:

### Option 1: Using Docker (Recommended)

1. **Install Docker & Docker Compose** on the target server
2. **Copy the entire project** to the server:
   ```bash
   scp -r /home/dmitry-home/apps/aiStocks user@server:/path/to/destination
   ```
3. **Navigate to `.docker` directory**:
   ```bash
   cd /path/to/destination/aiStocks/.docker
   ```
4. **Start the container**:
   ```bash
   docker-compose up -d
   ```
5. **Access via server IP**:
   ```
   http://server-ip:8080
   ```

### Option 2: Manual PHP Installation

If Docker is not available:

1. Install **PHP 5.5+** and **Apache**
2. Install **wkhtmltopdf**: `apt-get install wkhtmltopdf`
3. Copy project files to web root: `/var/www/html`
4. Set permissions:
   ```bash
   chmod -R 755 /var/www/html/app
   chmod -R 777 /var/www/html/reports
   chmod -R 777 /var/www/html/images
   chmod -R 777 /var/www/html/data
   chmod 666 /var/www/html/reportSettings.json
   ```
5. Configure Apache virtual host to point to project root
6. Restart Apache: `service apache2 restart`

---

## Environment Variables

Available environment variables (set in `.env`):

| Variable | Default | Description |
|----------|---------|-------------|
| `COMPOSE_PROJECT_NAME` | aiStocks | Docker Compose project name |
| `APP_PORT` | 8080 | Port to access the application |

---

## Production Considerations

### Security

⚠️ **PHP 5.5 reached end-of-life in July 2016** and no longer receives security updates.

This setup should only be used for:
- Legacy application maintenance
- Development/testing purposes
- Isolated/internal environments

**For production**, consider:
- Upgrading to PHP 7.4+ or PHP 8.x
- Using HTTPS (add nginx reverse proxy with SSL)
- Implementing user authentication
- Moving sensitive files outside document root
- Disabling PHP error display

### Hardening for Production

1. **Disable error display** (edit Dockerfile):
   ```dockerfile
   echo 'display_errors = Off'; \
   ```

2. **Add SSL/HTTPS** using nginx reverse proxy

3. **Restrict file access** via `.htaccess` (already included)

4. **Regular backups**:
   ```bash
   # Backup settings and data
   tar -czf backup.tar.gz reportSettings.json data/ reports/ images/
   ```

5. **Implement authentication** in `reportManager.php`

---

## Accessing from Other Machines

### On Local Network

1. Find your machine's IP address:
   ```bash
   ip addr show
   # or
   hostname -I
   ```

2. Access from other devices on the same network:
   ```
   http://192.168.1.XXX:8080
   ```

### Using Reverse Proxy (Production)

For clean URLs and HTTPS, use nginx as reverse proxy:

```nginx
server {
    listen 80;
    server_name yourdomain.com;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

---

## Monitoring & Logs

### Application Logs

PHP errors are logged to Apache error log:

```bash
docker-compose exec php-app tail -f /var/log/apache2/error.log
```

### Container Health Check

The container includes a health check that pings `index.php` every 30 seconds.

Check health status:

```bash
docker ps
```

Look for "healthy" status in the STATUS column.

---

## Updating the Application

To update the application code:

1. Make changes to PHP files on your host machine
2. Changes are immediately reflected (files are mounted)
3. No need to rebuild the container for code changes
4. Only rebuild if Dockerfile changes

---

## Complete Command Reference

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Rebuild from scratch
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Access container shell
docker-compose exec php-app bash

# Check PHP version
docker-compose exec php-app php -v

# Check running containers
docker ps

# Remove all containers and volumes
docker-compose down -v

# Restart containers
docker-compose restart
```

---

## Support

For issues related to:
- **Application**: See main [README_IMPLEMENTATION.md](../README_IMPLEMENTATION.md)
- **Docker**: Check logs with `docker-compose logs`
- **Permissions**: Run permission fix commands above

---

## License

This Docker configuration is part of the Stock Report Generation System.

**Security Warning**: PHP 5.5 is outdated. Use at your own risk in production environments.
