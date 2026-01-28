FROM php:5.6-apache

# Install system dependencies (Debian Stretch archive) + wkhtmltopdf + headers for PHP extensions
RUN set -eux; \
  printf '%s\n' \
    'deb http://archive.debian.org/debian stretch main contrib non-free' \
    'deb http://archive.debian.org/debian stretch-updates main contrib non-free' \
    'deb http://archive.debian.org/debian-security stretch/updates main contrib non-free' \
    > /etc/apt/sources.list; \
  printf '%s\n' \
    'Acquire::Check-Valid-Until "false";' \
    'Acquire::AllowInsecureRepositories "true";' \
    'Acquire::AllowDowngradeToInsecureRepositories "true";' \
    > /etc/apt/apt.conf.d/99archive; \
  apt-get -o Acquire::Check-Valid-Until=false update || true; \
  apt-get -y --allow-unauthenticated install \
    ca-certificates \
    apt-transport-https \
    git \
    curl \
    zip \
    unzip \
    wkhtmltopdf \
    xvfb \
    zlib1g-dev \
    libpng-dev \
    libxml2-dev; \
  rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN set -eux; \
  docker-php-ext-install \
    mbstring \
    xml \
    gd

# Enable Apache modules
RUN a2enmod rewrite

# Configure Apache to allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Configure PHP settings for file uploads
RUN { \
    echo 'upload_max_filesize = 10M'; \
    echo 'post_max_size = 10M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
    echo 'display_errors = On'; \
    echo 'error_reporting = E_ALL'; \
} > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

COPY . /var/www/html

RUN set -eux; \
  mkdir -p /var/www/html/reports /var/www/html/images; \
  chown -R www-data:www-data /var/www/html; \
  chmod -R 755 /var/www/html; \
  chmod -R 777 /var/www/html/reports /var/www/html/images

EXPOSE 80

CMD ["apache2-foreground"]