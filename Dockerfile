FROM php:8.2-apache

WORKDIR /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite

# Fix MPM conflict: disable all mpm modules then enable only prefork
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true
RUN a2enmod mpm_prefork

# Force Apache to work with Railway
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY . /var/www/html

# Copy and set up startup script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]