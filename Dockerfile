FROM php:8.2-apache

WORKDIR /var/www/html

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files
COPY . .

# Enable mod_rewrite for routing
RUN a2enmod rewrite

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Set environment variables
ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data
ENV APACHE_LOG_DIR=/var/log/apache2

EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
