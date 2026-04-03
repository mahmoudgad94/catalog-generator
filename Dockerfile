FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql intl gd zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Build cache
RUN php bin/console cache:clear --env=prod

# Create upload directory
RUN mkdir -p public/uploads/products && chmod -R 777 public/uploads var

EXPOSE 8080

CMD php bin/console doctrine:migrations:migrate --no-interaction --env=prod 2>&1; \
    php bin/console doctrine:fixtures:load --no-interaction --env=prod 2>/dev/null; \
    php -S 0.0.0.0:${PORT:-8080} -t public
