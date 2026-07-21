<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Deploy with Docker, PostgreSQL, and S3 storage

One `docker compose up` deploys the application together with PostgreSQL and
MinIO (an S3-compatible object store). The `minio-init` service automatically
creates the `MINIO_BUCKET` bucket and configures public read access so report
attachment photos can be displayed by a browser.

1. Create the deployment environment file and replace every placeholder secret:

   ```sh
   cp .env.example .env
   php -r "echo 'APP_KEY=base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
   ```

   Put the generated `APP_KEY` in `.env`, then set strong, different values for
   `DB_PASSWORD`, `MINIO_ROOT_USER`, and `MINIO_ROOT_PASSWORD`.

2. Set `S3_PUBLIC_URL` to the URL browsers can reach. On a server, replace
   `localhost` with its public hostname or IP, for example
   `https://storage.example.com/daily-report` when a reverse proxy is used.

3. Start or update the complete stack:

   ```sh
   docker compose up -d --build
   ```

The application is served on `APP_PORT` (default `8000`), the S3 API on
`MINIO_PORT` (default `9000`), and the MinIO console on `MINIO_CONSOLE_PORT`
(default `9001`). PostgreSQL data and S3 objects are kept in named Docker
volumes, so rebuilding containers does not delete them. Protect ports 5432 and
9001 with a firewall or do not expose them publicly.

## Daily Report - Aplikasi Laporan Harian Fuelman

Sistem manajemen laporan harian untuk warehouse & inventory site.

## 🎯 Fitur Utama

- ✅ Laporan harian sounding tangki BBM (Main Tank)
- ✅ Pencatatan transfer solar antar tangki
- ✅ Monitoring pemakaian flowmeter
- ✅ Upload foto dokumentasi (max 2 foto per item)
- ✅ Workflow approval (Fuelman → Group Leader → Supervisor)
- ✅ Export/Print laporan
- ✅ Monitoring tangki real-time
- ✅ Dark/Light mode

## 🏗️ Tech Stack

- **Backend:** Laravel 13 (PHP 8.4)
- **Database:** PostgreSQL 15
- **Storage:** MinIO (S3-compatible) / AWS S3
- **Frontend:** Blade Templates + Vanilla JS
- **Styling:** Custom CSS
- **Container:** Docker + Docker Compose

## 📸 File Storage Architecture

### Development (Local)
```
storage/app/public/report-attachments/
```

### Production (Docker)
```
MinIO (S3-compatible storage)
- Stateless container
- Data persisten di volume
- Public read access untuk foto
```

## 🚀 Quick Start

### Development

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate

# Create storage symlink
php artisan storage:link

# Run development server
php artisan serve
npm run dev
```

### Production (Docker)

Lihat panduan lengkap di [DEPLOYMENT.md](DEPLOYMENT.md)

```bash
# Setup
cp .env.example .env
# Edit .env sesuai kebutuhan

# Deploy
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --force
```

## 👥 User Roles

1. **Fuelman** - Membuat dan submit laporan harian
2. **Group Leader (GL)** - Verifikasi laporan dari Fuelman
3. **Supervisor (SPV)** - Approve final laporan

## 📱 Default Credentials

```
Fuelman:
  Email: fuelman@example.com
  Password: password

Group Leader:
  Email: gl@example.com
  Password: password

Supervisor:
  Email: supervisor@example.com
  Password: password
```

**⚠️ PENTING:** Ganti password default setelah deployment!

## 🗂️ Struktur Tank

- **FT05** - TENGAH (Fuel Truck)
- **SPM1** - TENGAH (Storage Tank 1)
- **SPM2** - TENGAH (Storage Tank 2)
- **SPM3** - (DEPAN + BELAKANG) / 2 (Storage Tank 3 - Average)

## 📄 License

Private Project - All Rights Reserved

## 🔗 Links

- [Deployment Guide](DEPLOYMENT.md) - Panduan lengkap deployment
- [Docker Compose](docker-compose.yml) - Container orchestration

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
