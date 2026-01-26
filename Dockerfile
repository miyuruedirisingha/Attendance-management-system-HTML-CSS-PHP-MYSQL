# Use official PHP image with Apache
FROM php:8.1-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Disable Apache caching and set headers for no-cache
RUN a2enmod headers expires

# Set working directory
WORKDIR /var/www/html

# Copy application files to container
COPY . /var/www/html/

# Add build timestamp to verify deployment
RUN echo "<?php /* Build: $(date -u +%Y%m%d-%H%M%S) */ ?>" > /var/www/html/.build-info.php

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache to disable caching for development
RUN echo '<IfModule mod_headers.c>\n\
    Header set Cache-Control "no-cache, no-store, must-revalidate"\n\
    Header set Pragma "no-cache"\n\
    Header set Expires 0\n\
</IfModule>' > /etc/apache2/conf-available/no-cache.conf && \
    a2enconf no-cache

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
