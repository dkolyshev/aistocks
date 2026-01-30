#!/bin/bash
set -e

echo "Fixing permissions for writable files and directories..."

# Fix permissions for directories that need to be writable
chmod -R 777 /var/www/html/reports 2>/dev/null || true
chmod -R 777 /var/www/html/images 2>/dev/null || true
chmod -R 777 /var/www/html/data 2>/dev/null || true
chmod -R 777 /var/www/html/cache 2>/dev/null || true

# Fix permissions for configuration files that need to be writable
chmod 666 /var/www/html/reportSettings.json 2>/dev/null || true

# Ensure www-data owns the files (this works better with some file systems)
chown -R www-data:www-data /var/www/html/reports 2>/dev/null || true
chown -R www-data:www-data /var/www/html/images 2>/dev/null || true
chown -R www-data:www-data /var/www/html/data 2>/dev/null || true
chown -R www-data:www-data /var/www/html/cache 2>/dev/null || true
chown www-data:www-data /var/www/html/reportSettings.json 2>/dev/null || true

echo "Permissions fixed. Starting Apache..."

# Execute the original CMD (apache2-foreground)
exec "$@"
