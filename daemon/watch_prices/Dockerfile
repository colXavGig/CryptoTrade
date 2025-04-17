FROM php:8.3-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /daemon

COPY daemon/watch_prices/watch_prices.php /daemon/watch_prices.php
COPY app /app

CMD ["php", "/daemon/watch_prices.php"]
