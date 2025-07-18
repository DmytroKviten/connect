FROM php:8.2-apache

# PHP-розширення та git/unzip для composer
RUN apt-get update \
 && apt-get install -y git unzip \
 && docker-php-ext-install pdo pdo_mysql

# mod_rewrite потрібен Laravel-у для .htaccess
RUN a2enmod rewrite

# Наш vhost із DocumentRoot=/var/www/html/public
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Робоча папка — збігається з томом
WORKDIR /var/www/html

# Права: достатньо на корінь; під-том весь проєкт
RUN chown www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]