# ---------- Stage 1: Frontend (Vite) ----------
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
# Hasil default Laravel Vite -> public/build
RUN npm run build

# ---------- Stage 2: App (PHP + Apache) ----------
FROM php:8.2-apache

# Aktifkan mod_rewrite dan ganti DocumentRoot ke public
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Dependensi PHP yang umum untuk Laravel
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
 && docker-php-ext-install pdo_mysql bcmath zip \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy source code
COPY . .

# Copy hasil build Vite
COPY --from=frontend /app/public/build ./public/build

# Install vendor (production)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# Permission untuk cache/logs (opsional)
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
