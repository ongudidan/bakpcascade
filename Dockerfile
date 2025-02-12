# Use PHP 5.6 with an older Debian base
# FROM php:5.6-apache

FROM nibrev/php-5.3-apache


# Set working directory
WORKDIR /var/www/html

# Use older Debian repositories
RUN sed -i 's|http://deb.debian.org/debian|http://archive.debian.org/debian|g' /etc/apt/sources.list && \
    sed -i 's|http://security.debian.org|http://archive.debian.org/debian-security|g' /etc/apt/sources.list

# Install required dependencies
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set recommended PHP configurations
RUN echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini

# Copy Joomla files
COPY . /var/www/html

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/cache \
    && chmod -R 777 /var/www/html/logs \
    && chmod -R 777 /var/www/html/tmp \
    && chmod -R 777 /var/www/html/administrator/cache


RUN echo "magic_quotes_gpc = Off" >> /usr/local/etc/php/php.ini


# Expose Apache's default port
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
