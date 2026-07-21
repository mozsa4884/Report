# 🚀 Panduan Deployment Aplikasi Daily Report

## 📋 Arsitektur Storage

Aplikasi ini menggunakan **S3-compatible storage** untuk menyimpan foto/attachment laporan:

### Development (Local)
- Storage: `storage/app/public/`
- Akses: via symlink `public/storage`

### Production (Docker)
- Storage: **MinIO** (S3-compatible object storage)
- Container tidak menyimpan file (stateless)
- Data persisten di volume Docker

---

## 🐳 Deployment dengan Docker Compose

### 1. Persiapan Server

```bash
# Clone repository
git clone <repository-url>
cd Report

# Copy .env.example ke .env
cp .env.example .env
```

### 2. Konfigurasi Environment Variables

Edit file `.env` dan sesuaikan nilai berikut:

```env
# Application
APP_NAME="Daily Report"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=daily_report
DB_USERNAME=postgres
DB_PASSWORD=your-secure-database-password-here

# File Storage (WAJIB menggunakan S3/MinIO di Docker)
FILESYSTEM_DISK=s3

# MinIO Configuration
MINIO_ROOT_USER=admin-minio
MINIO_ROOT_PASSWORD=secure-minio-password-min-8-chars
MINIO_BUCKET=daily-report

# AWS S3 Settings (untuk MinIO lokal)
AWS_ACCESS_KEY_ID=admin-minio
AWS_SECRET_ACCESS_KEY=secure-minio-password-min-8-chars
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=daily-report
AWS_ENDPOINT=http://minio:9000
AWS_URL=http://yourdomain.com:9000/daily-report
AWS_USE_PATH_STYLE_ENDPOINT=true

# Public URL untuk akses foto dari browser
# ⚠️ PENTING: Ganti localhost dengan domain/IP server Anda
S3_PUBLIC_URL=http://yourdomain.com:9000/daily-report

# Port Forwarding (opsional)
APP_PORT=8000
DB_PORT_FORWARD=5432
MINIO_PORT=9000
MINIO_CONSOLE_PORT=9001
```

### 3. Generate Application Key

```bash
docker compose run --rm app php artisan key:generate
```

### 4. Build dan Jalankan Container

```bash
# Build images
docker compose build --no-cache

# Start services
docker compose up -d

# Cek status
docker compose ps
```

### 5. Jalankan Database Migration

```bash
docker compose exec app php artisan migrate --force
```

### 6. Verifikasi Deployment

```bash
# Cek logs aplikasi
docker compose logs -f app

# Cek logs database
docker compose logs -f db

# Cek logs MinIO
docker compose logs -f minio

# Test storage connection
docker compose exec app php artisan tinker
>>> Storage::disk('s3')->put('test.txt', 'Hello World');
>>> Storage::disk('s3')->exists('test.txt');
>>> Storage::disk('s3')->delete('test.txt');
```

---

## 🗂️ Struktur Storage di MinIO

Foto-foto disimpan dengan struktur:

```
daily-report/                           (bucket)
└── report-attachments/
    └── {report_id}/                    (ID laporan)
        ├── section-A/                  (Laporan Harian - Main Tank)
        │   ├── photo1.jpg
        │   └── photo2.jpg
        └── section-B/                  (Transfer Solar)
            ├── photo1.jpg
            └── photo2.jpg
```

---

## 🔧 Konfigurasi Tambahan

### Akses MinIO Console (Web UI)

1. Buka browser: `http://yourdomain.com:9001`
2. Login dengan:
   - Username: `MINIO_ROOT_USER` (dari .env)
   - Password: `MINIO_ROOT_PASSWORD` (dari .env)

### Menggunakan AWS S3 Production (Real AWS)

Jika ingin menggunakan AWS S3 asli instead of MinIO, ubah di `.env`:

```env
FILESYSTEM_DISK=s3

# AWS S3 Real Configuration
AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=your-bucket-name
AWS_ENDPOINT=                           # Kosongkan untuk AWS asli
AWS_URL=https://your-bucket.s3.ap-southeast-1.amazonaws.com
AWS_USE_PATH_STYLE_ENDPOINT=false

# Hapus atau comment out MinIO settings
# MINIO_ROOT_USER=
# MINIO_ROOT_PASSWORD=
```

Kemudian hapus service MinIO dari docker-compose.yml atau comment out.

---

## 🔐 Security Best Practices

1. **Ganti semua password default**:
   - `DB_PASSWORD`
   - `MINIO_ROOT_PASSWORD`

2. **Set APP_DEBUG=false di production**

3. **Gunakan HTTPS** untuk domain production:
   - Setup reverse proxy (Nginx/Traefik)
   - Install SSL certificate (Let's Encrypt)

4. **Firewall Rules**:
   - Port 80/443: Akses publik (aplikasi)
   - Port 5432: Internal only (database)
   - Port 9000: Internal only atau IP whitelist (MinIO)
   - Port 9001: Internal only atau IP whitelist (MinIO Console)

---

## 📊 Monitoring & Maintenance

### Backup Database

```bash
# Manual backup
docker compose exec db pg_dump -U postgres daily_report > backup-$(date +%Y%m%d).sql

# Restore
docker compose exec -T db psql -U postgres daily_report < backup-20240101.sql
```

### Backup MinIO Data

```bash
# MinIO data ada di volume Docker
docker volume inspect Report_minio_data

# Backup volume
docker run --rm -v Report_minio_data:/data -v $(pwd):/backup alpine tar czf /backup/minio-backup-$(date +%Y%m%d).tar.gz -C /data .

# Restore
docker run --rm -v Report_minio_data:/data -v $(pwd):/backup alpine tar xzf /backup/minio-backup-20240101.tar.gz -C /data
```

### Logs

```bash
# View all logs
docker compose logs

# Follow app logs
docker compose logs -f app

# Last 100 lines
docker compose logs --tail=100 app
```

### Update Aplikasi

```bash
# Pull latest code
git pull origin main

# Rebuild and restart
docker compose build --no-cache
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --force

# Clear cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
```

---

## 🆘 Troubleshooting

### Foto tidak muncul di browser

**Problem:** URL foto return 404 atau Access Denied

**Solution:**
1. Cek `S3_PUBLIC_URL` di `.env` sudah benar
2. Pastikan bucket policy public untuk read:
   ```bash
   docker compose exec minio-init /bin/sh
   mc anonymous set download local/daily-report
   ```

### Container restart terus

**Problem:** App container restart loop

**Solution:**
```bash
# Cek logs
docker compose logs app

# Common issues:
# 1. Database belum ready → tunggu beberapa detik
# 2. Migration error → periksa struktur database
# 3. Permission error → cek ownership folder storage
```

### Upload foto gagal

**Problem:** Error saat upload foto

**Solution:**
1. Cek koneksi ke MinIO:
   ```bash
   docker compose exec app php artisan tinker
   >>> Storage::disk('s3')->put('test.txt', 'test');
   ```
2. Verify environment variables
3. Cek MinIO logs: `docker compose logs minio`

---

## 📞 Support

Jika ada masalah deployment, silakan hubungi tim development atau buat issue di repository.

**Tech Stack:**
- Laravel 13
- PHP 8.4
- PostgreSQL 15
- MinIO (S3-compatible storage)
- Nginx
- Docker & Docker Compose
