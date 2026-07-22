# Railway Multi-Service Setup untuk MinIO + PostgreSQL

Railway tidak support docker-compose multi-container dalam 1 service.
Solusinya: Deploy sebagai **3 services terpisah** dalam 1 project.

---

## Step 1: Setup PostgreSQL

1. **Buka Railway Dashboard** → Project "daily-report"
2. **Klik "+ New"** → **"Database"** → **"Add PostgreSQL"**
3. Railway akan otomatis create PostgreSQL service
4. PostgreSQL akan otomatis ter-connect ke app dengan environment variables:
   - `DATABASE_URL` atau
   - `PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`
5. **Entrypoint.sh sudah handle ini** - tidak perlu ubah code

---

## Step 2: Deploy MinIO Service

1. **Buka Railway Dashboard** → Project "daily-report"
2. **Klik "+ New"** → **"Empty Service"**
3. **Rename service** → "MinIO"
4. **Klik service MinIO** → Tab "Settings"
5. **Source** → Pilih **"Docker Image"**
6. **Image**: `minio/minio:latest`
7. **Start Command**: `server /data --console-address ":9001"`
8. **Tab "Variables"** → Add:
   ```
   MINIO_ROOT_USER=minioadmin
   MINIO_ROOT_PASSWORD=minioadmin123
   ```
9. **Tab "Settings"** → **"Networking"**
   - Expose Port: `9000` (MinIO API)
   - Generate Domain (akan dapat URL seperti: `minio-production-xxxx.up.railway.app`)
10. **Deploy**

---

## Step 3: Create MinIO Bucket (One-time setup)

Setelah MinIO service running:

### Option A: Via MinIO Console
1. Akses MinIO console: `https://minio-production-xxxx.up.railway.app:9001`
2. Login: `minioadmin` / `minioadmin123`
3. **Buckets** → **Create Bucket** → Name: `daily-report`
4. **Bucket** → **Access Policy** → Set to **"public"** atau **"download"**

### Option B: Via mc command (MinIO Client)
1. Di Railway MinIO service → Tab "Settings" → **"Service Metrics"** → Klik **"Connect"**
2. Run command di local terminal:
   ```bash
   docker run -it --rm minio/mc alias set minio https://minio-production-xxxx.up.railway.app minioadmin minioadmin123
   docker run -it --rm minio/mc mb minio/daily-report
   docker run -it --rm minio/mc anonymous set download minio/daily-report
   ```

---

## Step 4: Update Laravel App Environment Variables

1. **Klik service Laravel** (yang sudah ada)
2. **Tab "Variables"** → Add/Update:
   ```
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=minioadmin
   AWS_SECRET_ACCESS_KEY=minioadmin123
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=daily-report
   AWS_ENDPOINT=https://minio-production-xxxx.up.railway.app
   AWS_URL=https://minio-production-xxxx.up.railway.app/daily-report
   AWS_USE_PATH_STYLE_ENDPOINT=true
   ```
   
   **PENTING:** Ganti `minio-production-xxxx.up.railway.app` dengan **actual MinIO domain** dari Step 2.

3. **Save** → Railway akan auto-redeploy

---

## Step 5: Test

Setelah semua deploy:

1. **Test PostgreSQL:**
   - App harusnya connect ke PostgreSQL (tidak pakai SQLite lagi)
   - Check di Railway logs: `DB Config Applied: CONNECTION : pgsql`

2. **Test MinIO:**
   - Akses: `https://daily-report.up.railway.app/test-storage`
   - Harusnya return: `"status": "success"`

3. **Test Upload Foto:**
   - Buat laporan dengan foto
   - Submit
   - **Harusnya BERHASIL!** 🎉

---

## Arsitektur Final

```
Railway Project: daily-report
│
├── Service: Laravel App (your Dockerfile)
│   ├── Connected to: PostgreSQL (via DATABASE_URL)
│   ├── Connected to: MinIO (via AWS_ENDPOINT)
│   └── Public URL: daily-report.up.railway.app
│
├── Service: PostgreSQL (Railway Plugin)
│   ├── Internal hostname: postgres.railway.internal
│   └── Auto-connected via environment variables
│
└── Service: MinIO (Docker Image: minio/minio)
    ├── Port 9000: MinIO API
    ├── Port 9001: MinIO Console
    └── Public URL: minio-production-xxxx.up.railway.app
```

---

## Biaya

- **PostgreSQL**: Gratis untuk hobby plan ($5/month Pro plan)
- **MinIO**: ~$5-10/month (tergantung usage)
- **Laravel App**: Gratis untuk hobby plan
- **Total**: ~$0-15/month tergantung plan

---

## Troubleshooting

### MinIO tidak bisa diakses dari Laravel
- Check `AWS_ENDPOINT` di Laravel variables
- Pastikan pakai HTTPS (Railway domains pakai HTTPS)
- Test connectivity: `https://daily-report.up.railway.app/test-storage`

### PostgreSQL connection failed
- Railway PostgreSQL plugin harusnya auto-connect
- Check entrypoint.sh logs di Railway deploy logs
- Pastikan `DATABASE_URL` atau `PGHOST` ter-set

### Upload foto masih gagal
- Check Railway logs untuk error message
- Test MinIO: `curl https://minio-production-xxxx.up.railway.app/minio/health/live`
- Check bucket permission: harus public/download

---

## Alternative: Deploy ke VPS

Kalau Railway terlalu ribet atau mahal, bisa deploy ke VPS dengan docker-compose langsung:

**VPS Providers:**
- **Hetzner**: €4.5/month (~Rp 75k) - 2 CPU, 4GB RAM
- **DigitalOcean**: $6/month (~Rp 95k) - 1 CPU, 1GB RAM
- **Contabo**: €5/month (~Rp 80k) - 4 CPU, 8GB RAM

Dengan VPS, tinggal:
```bash
git clone repo
docker-compose up -d
```

Semua jalan otomatis (PostgreSQL + MinIO + Laravel).
