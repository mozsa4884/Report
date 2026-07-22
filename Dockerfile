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
    postgresql-client \
    oniguruma-dev \
    nginx \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl intl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Configure OPcache and PHP settings for production uploads
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'memory_limit=512M'; \
    echo 'upload_max_filesize=20M'; \
    echo 'post_max_size=25M'; \
    echo 'max_file_uploads=50'; \
    echo 'max_execution_time=300'; \
    echo 'max_input_time=300'; \
    echo 'output_buffering=4096'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Configure PHP-FPM for better stability
RUN { \
    echo '[www]'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 20'; \
    echo 'pm.start_servers = 4'; \
    echo 'pm.min_spare_servers = 2'; \
    echo 'pm.max_spare_servers = 6'; \
    echo 'pm.max_requests = 500'; \
    echo 'request_terminate_timeout = 300s'; \
} > /usr/local/etc/php-fpm.d/zz-custom.conf

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

# Copy only the clean application source. Runtime files such as .env, compiled
# Blade views, local runtime files, vendor, and node_modules are excluded
# by .dockerignore so a deployment cannot inherit state from a local machine.
COPY . /var/www

# Run composer scripts
RUN composer dump-autoload --optimize

# Configure Nginx to run as www-data user
RUN sed -i 's/user nginx;/user www-data;/g' /etc/nginx/nginx.conf

# Create Nginx temporary directories with proper permissions
RUN mkdir -p \
    /var/lib/nginx/tmp/client_body \
    /var/lib/nginx/tmp/proxy \
    /var/lib/nginx/tmp/fastcgi \
    /var/lib/nginx/tmp/uwsgi \
    /var/lib/nginx/tmp/scgi && \
    chown -R www-data:www-data /var/lib/nginx

# Recreate Laravel runtime directories excluded from the build context. Laravel
# requires these paths even when they contain no cached files yet.
RUN mkdir -p \
    /var/www/storage/logs \
    /var/www/storage/framework/cache/data \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/testing \
    /var/www/storage/framework/views \
    /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy and set entrypoint script
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 80
EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
