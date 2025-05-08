FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libpq-dev libonig-dev libxml2-dev libzip-dev \
    libjpeg-dev libpng-dev libfreetype6-dev libjpeg62-turbo-dev \
    libmcrypt-dev libxslt-dev \
    && docker-php-ext-install pdo pdo_mysql intl opcache zip gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy the app
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chmod -R 755 /var/www/html/var && chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 8000

# Start PHP built-in server
CMD php -S 0.0.0.0:8000 -t public
