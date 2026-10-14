FROM php:8.2-apache

# Install sistem dependensi & ekstensi PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Aktifkan modul rewrite Apache untuk routing Laravel (.htaccess)
RUN a2enmod rewrite

# Arahkan DocumentRoot Apache ke folder public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Salin Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Tentukan direktori kerja
WORKDIR /var/www/html

# Salin kode aplikasi
COPY . .

# Install dependensi PHP (production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permission folder storage & bootstrap cache agar Laravel bisa menulis log/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Port default Render
EXPOSE 80

CMD ["apache2-foreground"]
