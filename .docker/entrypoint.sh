#!/bin/sh

# =============================================
# STEP 1: Parse DATABASE_URL / POSTGRES_URL / PG* variables
# =============================================
# Gunakan URL PostgreSQL jika tersedia
DB_URL_TO_PARSE=""
if [ -n "$DATABASE_URL" ]; then
    DB_URL_TO_PARSE="$DATABASE_URL"
elif [ -n "$POSTGRES_URL" ]; then
    DB_URL_TO_PARSE="$POSTGRES_URL"
fi

if [ -n "$DB_URL_TO_PARSE" ]; then
    echo "Parsing database URL..."
    PARSED=$(php -r "
        \$url = parse_url(getenv('DATABASE_URL') ?: getenv('POSTGRES_URL'));
        echo 'DB_HOST=' . (\$url['host'] ?? '127.0.0.1') . PHP_EOL;
        echo 'DB_PORT=' . (\$url['port'] ?? '5432') . PHP_EOL;
        echo 'DB_DATABASE=' . ltrim(\$url['path'] ?? '/daily_report', '/') . PHP_EOL;
        echo 'DB_USERNAME=' . (\$url['user'] ?? 'postgres') . PHP_EOL;
        echo 'DB_PASSWORD=' . (\$url['pass'] ?? '') . PHP_EOL;
    ")
    export DB_CONNECTION="pgsql"
    export DB_HOST=$(echo "$PARSED" | grep DB_HOST | cut -d= -f2)
    export DB_PORT=$(echo "$PARSED" | grep DB_PORT | cut -d= -f2)
    export DB_DATABASE=$(echo "$PARSED" | grep DB_DATABASE | cut -d= -f2)
    export DB_USERNAME=$(echo "$PARSED" | grep DB_USERNAME | cut -d= -f2)
    export DB_PASSWORD=$(echo "$PARSED" | grep DB_PASSWORD | cut -d= -f2-)
elif [ -n "$PGHOST" ]; then
    echo "Mapping Railway PG variables..."
    export DB_CONNECTION="pgsql"
    export DB_HOST="$PGHOST"
    export DB_PORT="$PGPORT"
    export DB_DATABASE="$PGDATABASE"
    export DB_USERNAME="$PGUSER"
    export DB_PASSWORD="$PGPASSWORD"
fi

# =============================================
# PostgreSQL is required in production.
#
# A local .env is intentionally not copied into a Docker image. The hosting
# service must therefore provide either DATABASE_URL/POSTGRES_URL, Railway's
# PG* variables, or the standard DB_CONNECTION/DB_HOST/DB_* variables.
# Never silently create a separate SQLite database in production: it makes a
# successful deployment appear to have lost its data.
# =============================================
# =============================================
# FALLBACK: Jika tidak ada DB eksternal yang di-configure (masih 127.0.0.1)
# Kita fallback ke SQLite agar container tetap bisa start dan jalan dengan sukses!
# =============================================
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "127.0.0.1" ]; then
    if [ -z "$DATABASE_URL" ] && [ -z "$PGHOST" ]; then
        echo "No external database configured. Falling back to SQLite..."
        export DB_CONNECTION="sqlite"
        export DB_DATABASE="/var/www/database/database.sqlite"
        # Buat file database sqlite jika belum ada
        mkdir -p /var/www/database
        touch /var/www/database/database.sqlite
        chown -R www-data:www-data /var/www/database
    fi
fi

# =============================================
# STEP 2: Generate file .env
# =============================================
if [ "$DB_CONNECTION" = "sqlite" ]; then
cat > /var/www/.env << ENVEOF
APP_NAME="${APP_NAME:-Daily Report}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-true}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/database/database.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK="${FILESYSTEM_DISK:-local}"
AWS_ACCESS_KEY_ID="${AWS_ACCESS_KEY_ID:-}"
AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY:-}"
AWS_DEFAULT_REGION="${AWS_DEFAULT_REGION:-us-east-1}"
AWS_BUCKET="${AWS_BUCKET:-}"
AWS_ENDPOINT="${AWS_ENDPOINT:-}"
AWS_URL="${AWS_URL:-}"
AWS_USE_PATH_STYLE_ENDPOINT="${AWS_USE_PATH_STYLE_ENDPOINT:-false}"
ENVEOF
else
cat > /var/www/.env << ENVEOF
APP_NAME="${APP_NAME:-Daily Report}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-true}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-daily_report}"
DB_USERNAME="${DB_USERNAME:-postgres}"
DB_PASSWORD="${DB_PASSWORD:-password}"

SESSION_DRIVER="${SESSION_DRIVER:-database}"
SESSION_LIFETIME=120
CACHE_STORE="${CACHE_STORE:-database}"
QUEUE_CONNECTION=sync
FILESYSTEM_DISK="${FILESYSTEM_DISK:-local}"
AWS_ACCESS_KEY_ID="${AWS_ACCESS_KEY_ID:-}"
AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY:-}"
AWS_DEFAULT_REGION="${AWS_DEFAULT_REGION:-us-east-1}"
AWS_BUCKET="${AWS_BUCKET:-}"
AWS_ENDPOINT="${AWS_ENDPOINT:-}"
AWS_URL="${AWS_URL:-}"
AWS_USE_PATH_STYLE_ENDPOINT="${AWS_USE_PATH_STYLE_ENDPOINT:-false}"
ENVEOF
fi

echo "=============================="
echo "DB Config Applied:"
echo "  CONNECTION : $DB_CONNECTION"
if [ "$DB_CONNECTION" != "sqlite" ]; then
echo "  HOST       : $DB_HOST"
echo "  PORT       : $DB_PORT"
echo "  DATABASE   : $DB_DATABASE"
else
echo "  DATABASE   : SQLite File"
fi
echo "=============================="
echo "Storage Config:"
echo "  FILESYSTEM_DISK: ${FILESYSTEM_DISK:-local}"
echo "  AWS_ENDPOINT   : ${AWS_ENDPOINT:-not set}"
echo "  AWS_BUCKET     : ${AWS_BUCKET:-not set}"
echo "  AWS_KEY        : ${AWS_ACCESS_KEY_ID:+***${AWS_ACCESS_KEY_ID: -4}}"
echo "=============================="

# =============================================
# STEP 3: Generate APP_KEY jika belum diset
# =============================================
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# =============================================
# STEP 4: Clear Laravel caches before connecting to the configured database
# =============================================
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear

# =============================================
# STEP 5: Wait for PostgreSQL to be ready
# =============================================
if [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "Waiting for PostgreSQL database to be ready..."
    MAX_TRIES=15
    COUNT=0
    until php artisan db:show > /dev/null 2>&1; do
        COUNT=$((COUNT + 1))
        if [ $COUNT -ge $MAX_TRIES ]; then
            echo "PostgreSQL is not available after $MAX_TRIES attempts."
            exit 1
        fi
        echo "Database not ready yet (attempt $COUNT/$MAX_TRIES), retrying in 2s..."
        sleep 2
    done
fi

# =============================================
# STEP 6: Jalankan migrasi dan seeder
# =============================================
echo "Running migrations..."
php artisan migrate --force

echo "Running seeders (if fails, continue anyway)..."
php artisan db:seed --force || echo "Seeder failed, but continuing..."

# Compiled Blade views can otherwise survive a container restart when storage
# is persistent, making a deployment appear to run an older report form.
php artisan optimize:clear
php artisan storage:link --force

# =============================================
# STEP 7: Setup Nginx port
# =============================================
LISTEN_PORT="${PORT:-80}"
echo "Setting Nginx port to $LISTEN_PORT"
sed -i "s/listen 80;/listen $LISTEN_PORT;/g" /etc/nginx/http.d/default.conf

# Pre-compile all Blade views in a single process before any worker starts.
# This prevents race conditions where two PHP-FPM workers simultaneously
# compile the same template and one worker reads a partially-written file.
echo "Pre-compiling Blade views..."
php artisan view:cache

# =============================================
# STEP 8: Start services
# =============================================
echo "Starting Nginx..."
nginx

echo "Starting PHP-FPM..."
exec php-fpm -d opcache.validate_timestamps=1 -d opcache.revalidate_freq=0
