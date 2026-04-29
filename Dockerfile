FROM php:8.2-apache

WORKDIR /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite

# FORCE correct Apache behavior
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY . /var/www/html

# Copy startup script and make it executable
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Default port (will be overridden by Railway's PORT env var)
EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]