FROM dunglas/frankenphp:php8.3-bookworm

# Install PHP extensions
RUN install-php-extensions pdo_mysql intl gd zip opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader

# Build cache
RUN APP_SECRET=build DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" \
    php bin/console cache:clear --env=prod

RUN mkdir -p public/uploads/products var/cache var/log \
    && chmod -R 777 public/uploads var

EXPOSE 8080

CMD php bin/console doctrine:migrations:migrate --no-interaction --env=prod 2>&1; \
    php bin/console doctrine:fixtures:load --no-interaction --env=prod 2>/dev/null; \
    php -S 0.0.0.0:${PORT:-8080} -t public
