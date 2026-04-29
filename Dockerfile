FROM php:8.2-apache

WORKDIR /var/www/html

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable rewrite
RUN a2enmod rewrite

# FORCE correct MPM (IMPORTANT FIX)
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2enmod mpm_prefork

# Copy project
COPY . /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]