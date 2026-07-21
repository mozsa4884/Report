# Setup S3 Storage untuk Railway

Railway menggunakan **ephemeral filesystem** - semua file yang diupload akan **hilang setiap redeploy**. 

Solusi: Gunakan **object storage** seperti S3, Cloudflare R2, atau Backblaze B2.

---

## Option 1: Cloudflare R2 (RECOMMENDED - FREE 10GB)

### Kelebihan:
- ✅ **10GB storage gratis** per bulan
- ✅ **Unlimited bandwidth** (no egress fees)
- ✅ S3-compatible API
- ✅ Mudah setup
- ✅ Global CDN otomatis

### Langkah Setup:

1. **Daftar Cloudflare**
   - Buka https://dash.cloudflare.com/sign-up
   - Daftar akun gratis

2. **Buat R2 Bucket**
   - Login ke dashboard
   - Klik **"R2"** di sidebar kiri
   - Klik **"Create bucket"**
   - Nama bucket: `daily-report-files`
   - Location: `Automatic` (atau pilih Asia Pacific untuk lebih cepat)
   - Klik **"Create bucket"**

3. **Buat API Token**
   - Di halaman R2, klik **"Manage R2 API Tokens"**
   - Klik **"Create API token"**
   - Permission: **"Admin Read & Write"**
   - Klik **"Create API token"**
   - **SIMPAN credentials ini:**
     - Access Key ID
     - Secret Access Key
     - Endpoint URL (format: `https://xxxxxx.r2.cloudflarestorage.com`)

4. **Update Environment Variables di Railway**
   
   Buka Railway dashboard → Project → Variables, tambahkan:
   
   ```bash
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=<your-access-key-id>
   AWS_SECRET_ACCESS_KEY=<your-secret-access-key>
   AWS_DEFAULT_REGION=auto
   AWS_BUCKET=daily-report-files
   AWS_ENDPOINT=<your-r2-endpoint>
   AWS_USE_PATH_STYLE_ENDPOINT=false
   AWS_URL=<your-r2-endpoint>/daily-report-files
   ```

5. **Setup Public Access (agar foto bisa diakses)**
   
   Di Cloudflare R2 Dashboard:
   - Klik bucket `daily-report-files`
   - Tab **"Settings"**
   - Scroll ke **"Public Access"**
   - Klik **"Allow Access"**
   - Copy **"Public R2.dev Subdomain"** → pakai ini sebagai `AWS_URL`
   
   Atau setup custom domain:
   - Tab **"Settings"** → **"Custom Domains"**
   - Klik **"Connect Domain"**
   - Masukkan domain (misal: `files.daily-report.com`)
   - Ikuti instruksi DNS setup
   - Update `AWS_URL` dengan custom domain

---

## Option 2: AWS S3 (Lebih Mahal)

### Biaya:
- Storage: ~$0.023/GB/bulan (~Rp 350/GB)
- Transfer keluar: ~$0.09/GB (~Rp 1,400/GB)
- Request: $0.0004 per 1000 GET requests

### Langkah Setup:

1. **Buat AWS Account**
   - Daftar di https://aws.amazon.com/
   - Butuh kartu kredit (free tier 12 bulan, 5GB S3)

2. **Buat S3 Bucket**
   - Login ke AWS Console
   - Search "S3" → Create bucket
   - Bucket name: `daily-report-files` (harus unique globally)
   - Region: `ap-southeast-1` (Singapore) untuk latency lebih kecil
   - **Uncheck "Block all public access"** (agar foto bisa diakses)
   - Enable versioning (optional)
   - Create bucket

3. **Buat IAM User & Access Key**
   - Search "IAM" → Users → Create user
   - Username: `daily-report-app`
   - Attach policy: `AmazonS3FullAccess`
   - Create user
   - Klik user → Security credentials → Create access key
   - Choose: "Application running outside AWS"
   - **SIMPAN:**
     - Access key ID
     - Secret access key

4. **Update Bucket Policy (agar foto public)**
   
   Di S3 bucket → Permissions → Bucket policy:
   
   ```json
   {
     "Version": "2012-10-17",
     "Statement": [
       {
         "Sid": "PublicReadGetObject",
         "Effect": "Allow",
         "Principal": "*",
         "Action": "s3:GetObject",
         "Resource": "arn:aws:s3:::daily-report-files/*"
       }
     ]
   }
   ```

5. **Update Environment Variables di Railway**
   
   ```bash
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=<your-access-key-id>
   AWS_SECRET_ACCESS_KEY=<your-secret-access-key>
   AWS_DEFAULT_REGION=ap-southeast-1
   AWS_BUCKET=daily-report-files
   AWS_URL=https://daily-report-files.s3.ap-southeast-1.amazonaws.com
   ```

---

## Option 3: Backblaze B2 (PALING MURAH)

### Kelebihan:
- ✅ **10GB storage gratis**
- ✅ **1GB/day download gratis**
- ✅ Storage cuma $0.005/GB (~Rp 75/GB)
- ✅ S3-compatible

### Setup mirip Cloudflare R2, ikuti dokumentasi:
https://www.backblaze.com/docs/cloud-storage-use-the-s3-compatible-api-for-backblaze-b2

---

## Cara Update Railway Environment Variables

1. Buka https://railway.app
2. Login → pilih project `daily-report`
3. Klik tab **"Variables"**
4. Klik **"+ New Variable"**
5. Tambahkan satu per satu variable di atas
6. Setelah selesai, Railway akan **auto-redeploy**
7. Tunggu deployment selesai (~2-5 menit)

---

## Testing

Setelah setup selesai:

1. Buka https://daily-report.up.railway.app/reports
2. Buat laporan baru dengan foto
3. Submit form
4. Cek apakah foto berhasil diupload (tidak error lagi)
5. Lihat detail laporan - foto harusnya tampil
6. Cek bucket S3/R2 - harusnya ada file di folder `report-attachments/`

---

## Troubleshooting

### Error: "Class 'League\Flysystem\AwsS3V3\AwsS3V3Adapter' not found"
```bash
# Install S3 driver
composer require league/flysystem-aws-s3-v3 "^3.0"
git add composer.* && git commit -m "Add S3 driver" && git push
```

### Foto tidak tampil (403 Forbidden)
- Pastikan bucket public access enabled
- Pastikan bucket policy benar
- Pastikan `AWS_URL` benar

### Error "The bucket you are attempting to access must be addressed using the specified endpoint"
- Ganti `AWS_USE_PATH_STYLE_ENDPOINT=true` di Railway variables

### Error 500 masih muncul
- Cek Railway logs: Project → Deployments → View Logs
- Cari error message spesifik
- Pastikan semua environment variables sudah di-set

---

## Rekomendasi

Untuk production, saya sarankan:

1. **Cloudflare R2** - gratis 10GB, unlimited bandwidth, easy setup
2. **AWS S3** - kalau sudah punya AWS account, lebih mature
3. **Backblaze B2** - kalau butuh storage lebih besar dengan budget minim

Pilih salah satu, ikuti langkah di atas, dan foto upload harusnya work! 🚀
