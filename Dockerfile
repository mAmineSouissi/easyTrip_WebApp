FROM php:8.2-fpm

# Install system dependencies for PHP and Python
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
    && docker-php-ext-install -j$(nproc) \
    intl \
    pdo \
    pdo_mysql \
    zip \
    opcache \
    gd \
    xml \
    mbstring

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader --no-dev

# Copy project files
COPY . .

# Install Python dependencies in a virtual environment
RUN python3 -m venv /var/www/venv && \
    /var/www/venv/bin/pip install --upgrade pip && \
    /var/www/venv/bin/pip install --no-cache-dir ultralytics pillow huggingface_hub

# Pre-download the YOLO model to avoid downloading at runtime
RUN /var/www/venv/bin/python -c "from huggingface_hub import hf_hub_download; hf_hub_download(repo_id='arnabdhar/YOLOv8-Face-Detection', filename='model.pt', cache_dir='/var/www/.cache')"

# Create a symlink to make the Python path easier to use in scripts
RUN ln -sf /var/www/venv/bin/python3 /usr/local/bin/python-app

# Generate optimized autoloader and run scripts
RUN composer dump-autoload --optimize --no-dev \
    && composer run-script post-install-cmd

# Set permissions
RUN chown -R www-data:www-data /var/www/var \
    && chmod -R 777 /var/www/var \
    && chmod +x /var/www/detect_face.py

# Expose port
EXPOSE 8000

# Start Symfony app using the built-in server
CMD php -S 0.0.0.0:8000 -t public
