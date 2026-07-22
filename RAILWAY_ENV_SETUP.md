# Railway Environment Variables Setup

**Copy paste ini ke Railway Variables:**

## Required Variables:

```
DB_PASSWORD=ChangeThisSecurePassword123
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin123
MINIO_BUCKET=daily-report
S3_PUBLIC_URL=https://daily-report.up.railway.app/storage
```

## Cara Set di Railway:

1. Buka Railway dashboard: https://railway.app
2. Klik project "daily-report"  
3. Klik tab **"Variables"**
4. Klik **"RAW Editor"** (atau "+ New Variable")
5. Paste semua variables di atas
6. Klik **"Save"**
7. Railway akan auto-redeploy (tunggu 2-3 menit)

## Setelah Deploy:

- PostgreSQL dari docker-compose akan jalan
- MinIO akan jalan
- App akan pakai S3 storage (MinIO)
- Upload foto akan berhasil!

## Notes:

- `DB_PASSWORD`: Password untuk PostgreSQL (ganti dengan password kuat)
- `MINIO_ROOT_USER`: Username MinIO (default: minioadmin)
- `MINIO_ROOT_PASSWORD`: Password MinIO (ganti dengan password kuat)
- `MINIO_BUCKET`: Nama bucket untuk simpan foto
- `S3_PUBLIC_URL`: URL public untuk akses foto (ganti dengan domain Railway kamu)
