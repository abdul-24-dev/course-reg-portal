FROM php:8.2-apache

WORKDIR /var/www/html

# 🔥 Fix MPM issue
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy files
COPY . .

# Enable rewrite
RUN a2enmod rewrite

# Apache config
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf && \
a2enconf app

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# 🔥 FIX PORT AT RUNTIME (THIS IS THE IMPORTANT PART)
CMD sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && apache2-foreground