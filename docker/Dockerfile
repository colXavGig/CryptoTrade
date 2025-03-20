FROM php:8.3-apache

# Install required system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*  # Cleanup to reduce image size

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Ensure /usr/src/php/lib is in PHP include_path
RUN echo "include_path = \".:/usr/src/php/lib\"" >> /usr/local/etc/php/php.ini

# Set working directory
WORKDIR /var/www/html
