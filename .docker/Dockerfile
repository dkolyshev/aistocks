FROM php:5.5-apache

# Install system dependencies including wkhtmltopdf for PDF generation
RUN printf '%s\n' \
    'deb http://archive.debian.org/debian jessie main contrib non-free' \
    'deb http://archive.debian.org/debian-security jessie/updates main contrib non-free' \
    > /etc/apt/sources.list \
    && printf 'Acquire::Check-Valid-Until "false";\n' > /etc/apt/apt.conf.d/99no-check-valid \
    && apt-get -o Acquire::Check-Valid-Until=false -o Acquire::AllowInsecureRepositories=true update \
    && apt-get -o Acquire::AllowInsecureRepositories=true install -y --allow-unauthenticated \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    wkhtmltopdf \
    xvfb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
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

# Set working directory
WORKDIR /var/www/html

# Copy application files into the image
COPY . /var/www/html

# Copy and set up entrypoint script
COPY .docker/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create necessary directories with proper permissions
RUN mkdir -p /var/www/html/reports /var/www/html/images /var/www/html/data/cache /var/www/html/cache/fmp \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/reports /var/www/html/images /var/www/html/data /var/www/html/cache

# Expose port 80
EXPOSE 80

# Use entrypoint script to fix permissions on startup
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Start Apache
CMD ["apache2-foreground"]
