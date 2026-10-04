FROM php:8.4-cli
RUN apt-get update && apt-get install -y --no-install-recommends git unzip && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-interaction --prefer-dist
COPY . .
RUN sed -i "s#^    ->withMiddleware(function (Middleware \$middleware): void {#&\n        \$middleware->trustProxies(at: '*');#" bootstrap/app.php && composer dump-autoload --optimize && php artisan package:discover
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr DB_CONNECTION=sqlite DB_DATABASE=/app/database/database.sqlite SESSION_DRIVER=file CACHE_STORE=file QUEUE_CONNECTION=sync
CMD ["sh", "-c", "touch database/database.sqlite && export APP_KEY=${APP_KEY:-$(php artisan key:generate --show)} && php artisan migrate --force --seed && exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
