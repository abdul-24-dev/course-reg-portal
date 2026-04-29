FROM php:8.2-apache

WORKDIR /var/www/html

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable rewrite
RUN a2enmod rewrite

# 🔥 FORCE CLEAN MPM STATE (IMPORTANT)
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2dismod mpm_prefork || true
RUN a2enmod mpm_prefork

# Copy project
COPY . /var/www/html

# Ensure Apache listens correctly
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]