FROM php:8.2-apache

WORKDIR /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite

# FORCE correct Apache behavior
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY . /var/www/html

# Make sure Apache binds correctly
EXPOSE 80

CMD ["apache2-foreground"]