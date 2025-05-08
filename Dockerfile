# 🐘 Base PHP image with FPM
FROM php:8.2-fpm

# 🧰 Install PHP system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libicu-dev \
    libzip-dev \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    nodejs \
    npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql zip opcache gd xml mbstring \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# ➕ Installe Node.js (version LTS) + Yarn
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
 && apt-get install -y nodejs \
 && npm install --global yarn

# 🎼 Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 📁 Set working directory
WORKDIR /var/www

# ✅ Set Symfony environment variables
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV COMPOSER_ALLOW_SUPERUSER=1

# 📁 Copy all project files
COPY . .

# 📦 Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 🧱 Build Webpack Encore assets
RUN yarn install && yarn encore production

# ⚙️ Symfony cache warmup
RUN php bin/console cache:clear --env=prod --no-interaction || true \
 && php bin/console cache:warmup --env=prod --no-interaction || true

# 🔐 Permissions
RUN mkdir -p /var/www/var \
 && chown -R www-data:www-data /var/www/var \
 && chmod -R 755 /var/www/var

# 🌐 Expose port
EXPOSE 8081

# 🚀 Start server
CMD ["php", "-S", "0.0.0.0:8081", "-t", "public"]
