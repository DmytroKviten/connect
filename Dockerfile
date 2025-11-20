FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1

# Системні пакети + PHP розширення
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      git unzip curl ca-certificates nano \
      libzip-dev libpng-dev libicu-dev \
      iputils-ping net-tools \
 && docker-php-ext-install pdo pdo_mysql zip \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN { \
      echo 'opcache.enable=1'; \
      echo 'opcache.enable_cli=1'; \
      echo 'opcache.memory_consumption=128'; \
      echo 'opcache.interned_strings_buffer=16'; \
      echo 'opcache.max_accelerated_files=20000'; \
      echo 'opcache.validate_timestamps=1'; \
      echo 'opcache.revalidate_freq=2'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini \
 && { \
      echo 'memory_limit=256M'; \
      echo 'upload_max_filesize=50M'; \
      echo 'post_max_size=50M'; \
      echo 'max_execution_time=60'; \
    } > /usr/local/etc/php/conf.d/laravel.ini

# віртуальний хост 
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# робоча директорія
WORKDIR /var/www/html

# права
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
