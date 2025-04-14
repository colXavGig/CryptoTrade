# Base PHP CLI image
FROM php:8.3-cli

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /daemon

# Copy the daemon script only (you don't need the whole app here unless you're using classes)
COPY ./daemon /daemon
COPY ./app /app

# Start the daemon script
CMD ["php", "/daemon/simulate_price.php"]
