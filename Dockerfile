# Використовуємо офіційний образ PHP з Apache
FROM php:8.1-apache

# Встановлюємо необхідні розширення для MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Включаємо модуль Apache mod_rewrite
RUN a2enmod rewrite

# Копіюємо код проекту в контейнер
COPY . /var/www/html/

# Відкриваємо порт для Apache
EXPOSE 80