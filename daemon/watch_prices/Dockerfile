# daemon/watch_prices/Dockerfile

FROM php:8.3-cli

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory inside container
WORKDIR /daemon

# Copy only this daemon script
COPY watch_prices.php /daemon/watch_prices.php

# Copy full app directory
COPY ../../app /app

# Start the watcher
CMD ["php", "/daemon/watch_prices.php"]
