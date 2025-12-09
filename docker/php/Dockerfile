FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    libicu-dev \
    zlib1g-dev \
    libxml2-dev \
    unzip \
    git \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    intl \
    mysqli \
    pdo_mysql \
    zip \
    opcache \
    sockets \
    bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom php.ini
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Create log directory
RUN mkdir -p /var/log/php && chown www-data:www-data /var/log/php

WORKDIR /var/www/html

CMD ["php-fpm"]
