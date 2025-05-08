# 🐘 Base PHP image with FPM
FROM php:8.2-fpm

# 🧰 Install PHP and Python system dependencies
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

# 📦 Copy only composer files first (better Docker layer caching)
COPY composer.json composer.lock ./

# ⚙️ Install PHP dependencies (scripts and autoload skipped at first)
RUN composer install --no-scripts --no-autoloader --no-interaction --no-progress

# 📁 Copy all project files
COPY . .

# 🐍 Setup Python virtualenv and install YOLO dependencies
RUN python3 -m venv /var/www/venv && \
    /var/www/venv/bin/pip install --upgrade pip && \
    /var/www/venv/bin/pip install --no-cache-dir ultralytics pillow huggingface_hub

# 📥 Download YOLOv8 model from HuggingFace
RUN /var/www/venv/bin/python -c "from huggingface_hub import hf_hub_download; \
    hf_hub_download(repo_id='arnabdhar/YOLOv8-Face-Detection', filename='model.pt', cache_dir='/var/www/.cache')"

# 🔗 Optional alias to make Python easier to call
RUN ln -sf /var/www/venv/bin/python3 /usr/local/bin/python-app

# 🌍 Set environment for Symfony
ENV APP_ENV=prod

# ⚙️ Dump autoload and warm Symfony cache
RUN composer dump-autoload --optimize \
 && php bin/console cache:clear --env=prod --no-interaction || true \
 && php bin/console cache:warmup --env=prod --no-interaction || true

# 🔐 Secure permissions – only if paths exist
RUN mkdir -p /var/www/var /var/www/scripts \
 && touch /var/www/scripts/detect_face.py \
 && chown -R www-data:www-data /var/www/var \
 && chmod -R 755 /var/www/var \
 && chmod +x /var/www/scripts/detect_face.py

# 🌐 Expose the port used by Symfony
EXPOSE 8081

# 🚀 Start the Symfony server
CMD php -S 0.0.0.0:8081 -t public
