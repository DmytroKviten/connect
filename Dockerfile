# Базуємося на офіційному PHP + Apache
FROM php:8.2-apache

# Встановимо розширення MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Увімкнемо модуль rewrite для Laravel (через .htaccess)
RUN a2enmod rewrite

# Копіюємо наш файл VirtualHost із поточної теки в контейнер
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Копіюємо увесь Laravel-код у /var/www/html
COPY . /var/www/html

# Зробимо власником www-data і роздамо права
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
