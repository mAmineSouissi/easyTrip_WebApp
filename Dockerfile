# 🐘 Use PHP 8.2 with FPM
FROM php:8.2-fpm

# 🧰 Install system dependencies for PHP + Python
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    python3 \
    python3-pip \
    python3-dev \
    python3-venv \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql zip opcache gd xml mbstring \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# 🎼 Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 📁 Set working directory
WORKDIR /var/www

# 📦 Copy composer files first (better cache usage)
COPY composer.json composer.lock ./

# ⚙️ Install PHP dependencies (no autoloader or scripts yet)
RUN composer install --no-scripts --no-autoloader --no-interaction --no-progress

# 📁 Copy full Symfony project
COPY . .

# 🐍 Setup Python virtual environment and install YOLO + dependencies
RUN python3 -m venv /var/www/venv && \
    /var/www/venv/bin/pip install --upgrade pip && \
    /var/www/venv/bin/pip install --no-cache-dir ultralytics pillow huggingface_hub

# 📥 Download YOLOv8 face detection model from HuggingFace
RUN /var/www/venv/bin/python -c "from huggingface_hub import hf_hub_download; \
    hf_hub_download(repo_id='arnabdhar/YOLOv8-Face-Detection', filename='model.pt', cache_dir='/var/www/.cache')"

# 🔗 Create shortcut to python3 for Symfony use
RUN ln -sf /var/www/venv/bin/python3 /usr/local/bin/python-app

# ⚙️ Set Symfony production environment
ENV APP_ENV=prod

# ⚙️ Generate autoloader and warmup Symfony cache
RUN composer dump-autoload --optimize \
 && php bin/console cache:clear --env=prod --no-interaction || true \
 && php bin/console cache:warmup --env=prod --no-interaction || true

# 🔐 Set secure permissions
RUN chown -R www-data:www-data /var/www/var \
 && chmod -R 755 /var/www/var \
 && chmod +x /var/www/scripts/detect_face.py

# 🌐 Expose port for Railway or Docker
EXPOSE 8000

# 🚀 Launch Symfony app using PHP's built-in web server
CMD php -S 0.0.0.0:8000 -t public
