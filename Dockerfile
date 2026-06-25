FROM dunglas/frankenphp:php8.4-bookworm

# Install system dependencies for GD and other extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Copy application code
COPY . .

# Expose the port Railway provides
EXPOSE ${PORT:-80}

# Start FrankenPHP
CMD ["frankenphp", "php-server", "--listen", ":${PORT:-80}"]
