FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies, PHP extensions, Nginx, Node.js & npm
RUN apk update && apk add --no-cache \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    git \
    curl \
    postgresql-dev \
    oniguruma-dev \
    nginx \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy nginx configuration
COPY .docker/nginx.conf /etc/nginx/http.d/default.conf

# Copy composer files first (for better Docker layer caching)
COPY composer.json composer.lock /var/www/

# Install composer dependencies during BUILD
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy package.json & package-lock.json first
COPY package.json package-lock.json* /var/www/

# Install npm dependencies and build Vite assets
RUN npm ci || npm install
COPY vite.config.js* tailwind.config.js* postcss.config.js* /var/www/
COPY resources /var/www/resources
RUN npm run build

# Copy the rest of application files
COPY . /var/www

# Run composer scripts
RUN composer dump-autoload --optimize

# Configure Nginx to run as www-data user
RUN sed -i 's/user nginx;/user www-data;/g' /etc/nginx/nginx.conf

# Set permissions for Laravel and public static files
RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy and set entrypoint script
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 80
EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
