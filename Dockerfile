# ---------- Stage 1: Frontend (Vite) ----------
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build  # output: public/build

# ---------- Stage 2: App (PHP + Apache) ----------
FROM php:8.2-apache

# Apache: aktifkan rewrite & set DocumentRoot ke public
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!<Directory /var/www/>!<Directory /var/www/html/public/>!g' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

WORKDIR /var/www/html

# PHP deps untuk Laravel
RUN apt-get update && apt-get install -y git unzip libzip-dev \
 && docker-php-ext-install pdo_mysql bcmath zip \
 && rm -rf /var/lib/apt/lists/*

# Composer (copy biner)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files dulu (biar cache efisien), lalu install vendor
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Copy source code
COPY . .

# Copy hasil build Vite
COPY --from=frontend /app/public/build ./public/build

# Optimize autoload
RUN composer dump-autoload -o

# Permission untuk storage/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Entrypoint: siapkan kredensial Firebase & jalankan Apache
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
