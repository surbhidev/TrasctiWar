# Use an official PHP image with Apache
FROM php:8.1-apache

# Install PostgreSQL PDO extension
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable SSL and headers modules
RUN a2enmod ssl headers

# Create a directory for SSL certificates
RUN mkdir -p /etc/apache2/ssl

# Copy SSL certificates
COPY ssl/selfsigned.crt /etc/apache2/ssl/selfsigned.crt
COPY ssl/selfsigned.key /etc/apache2/ssl/selfsigned.key

# Copy Apache SSL configuration
COPY ssl/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf

# Enable the SSL site
RUN a2ensite default-ssl.conf

# Disable the default HTTP site
RUN a2dissite 000-default.conf

# Copy application files to the container
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose Apache ports
EXPOSE 80
EXPOSE 443

# Start Apache in foreground
CMD ["apache2-foreground"]