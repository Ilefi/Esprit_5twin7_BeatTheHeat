# NutriTrace production image (built and published to ghcr.io by .github/workflows/pipeline.yml).
# Stage 1 installs the PHP dependencies, stage 2 builds the Vite assets, stage 3 is the Apache + PHP runtime.

FROM php:8.4-cli AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y --no-install-recommends unzip && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

FROM node:24-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
# Tailwind scans the pagination views in vendor/ (see resources/css/app.css).
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

FROM php:8.4-apache AS app
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'upload_max_filesize=8M\npost_max_size=10M\n' > "$PHP_INI_DIR/conf.d/uploads.ini"
WORKDIR /var/www/html
COPY --from=vendor /app ./
COPY --from=assets /app/public/build ./public/build
RUN php artisan package:discover --ansi && chown -R www-data:www-data storage bootstrap/cache
COPY docker/entrypoint.sh /usr/local/bin/nutritrace-entrypoint
RUN chmod +x /usr/local/bin/nutritrace-entrypoint

ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
EXPOSE 80
HEALTHCHECK --interval=10s --timeout=3s --start-period=30s --retries=5 CMD curl -fsS http://localhost/up || exit 1
ENTRYPOINT ["nutritrace-entrypoint"]
CMD ["apache2-foreground"]
