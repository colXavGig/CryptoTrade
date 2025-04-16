FROM php:8.3-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /daemon

COPY daemon/simulate_price/simulate_price.php /daemon/simulate_price.php
COPY app /app

CMD ["php", "/daemon/simulate_price.php"]
