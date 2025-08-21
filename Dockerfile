# ---------- Stage 1: Frontend (Vite) ----------
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---------- Stage 2: App (PHP + Apache) ----------
FROM php:8.2-apache

# Konfigurasi Apache: Aktifkan rewrite, set DocumentRoot, dan IZINKAN .htaccess
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!<Directory /var/www/>!<Directory /var/www/html/public/>!g' /etc/apache2/apache2.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

WORKDIR /var/www/html

# Install dependensi PHP untuk Laravel
RUN apt-get update && apt-get install -y git unzip libzip-dev \
 && docker-php-ext-install pdo_mysql bcmath zip \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# === PERUBAHAN URUTAN DIMULAI DI SINI ===

# Salin semua kode sumber aplikasi TERLEBIH DAHULU
COPY . .

# Baru jalankan composer install setelah semua file ada
RUN composer install --no-dev --prefer-dist --no-interaction

# Salin hasil build Vite dari stage sebelumnya
COPY --from=frontend /app/public/build ./public/build

# VERIFIKASI: Pastikan manifest.json ada
RUN if [ ! -f public/build/manifest.json ]; then echo "Vite manifest.json not found after copy!" && exit 1; fi

# Optimasi autoloader
RUN composer dump-autoload --no-dev -o

# PERBAIKAN FINAL: Ubah kepemilikan SEMUA file ke www-data
RUN chown -R www-data:www-data /var/www/html

# Setel Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]