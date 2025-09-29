# Use official PHP with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install necessary PHP extensions
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    && docker-php-ext-install sockets \
    && a2enmod rewrite

# Copy app files
COPY . /var/www/html/

# Set permissions for data folder
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
