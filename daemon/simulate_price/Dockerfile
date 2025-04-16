# daemon/simulate_price/Dockerfile

FROM php:8.3-cli

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /daemon

# Copy only the simulation script
COPY simulate_price.php /daemon/simulate_price.php

# Copy the application logic
COPY ../../app /app

# Start the daemon script
CMD ["php", "/daemon/simulate_price.php"]
