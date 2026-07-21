# Fix: ERR_HTTP2_PROTOCOL_ERROR saat Upload Gambar

## 🔍 Problem
Error `ERR_HTTP2_PROTOCOL_ERROR` muncul saat membuat laporan dengan gambar/foto, tapi berhasil tanpa foto.

## 🎯 Root Cause
1. **PHP Upload Limits** - Default terlalu kecil (2MB)
2. **Memory Limit** - Tidak cukup untuk proses gambar
3. **Timeout** - Request timeout saat upload file besar
4. **HTTP/2 Protocol** - Railway/production server sensitif terhadap large payload

## ✅ Solutions Implemented

### 1. PHP Configuration Files Created

**File: `public/.user.ini`**
```ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
```

**File: `php.ini` (root)**
```ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
max_input_vars = 5000
```

### 2. .htaccess Updated
Added PHP settings for Apache/mod_php:
```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 12M
    php_value memory_limit 256M
    php_value max_execution_time 300
</IfModule>
```

### 3. Enhanced Error Logging
Updated `ReportController::store()` with:
- Detailed logging for each step
- Memory usage tracking
- Better error messages
- Validation error handling

### 4. Nginx Already Configured
File: `.docker/nginx.conf` already has:
```nginx
client_max_body_size 100M;
fastcgi_read_timeout 300;
fastcgi_buffers 16 256k;
```

## 🚀 Deployment Steps

### For Railway.app:

1. **Add Environment Variables** (if needed):
```bash
# In Railway dashboard, add:
PHP_MEMORY_LIMIT=256M
PHP_UPLOAD_MAX_FILESIZE=10M
PHP_POST_MAX_SIZE=12M
```

2. **Commit & Push**:
```bash
git add .
git commit -m "Fix: Increase upload limits for image attachments"
git push
```

3. **Check Logs**:
```bash
# In Railway, check deployment logs for:
- "Report created"
- "Items saved"
- "Transfers saved"
- Memory usage info
```

### For VPS/Traditional Server:

1. **Update php.ini** (find with `php --ini`):
```bash
sudo nano /etc/php/8.x/fpm/php.ini
# OR
sudo nano /etc/php.ini
```

2. **Restart PHP-FPM**:
```bash
sudo systemctl restart php8.x-fpm
# OR
sudo systemctl restart php-fpm
```

3. **Restart Nginx**:
```bash
sudo systemctl restart nginx
```

## 🧪 Testing

1. **Test Small Image** (< 1MB):
   - Should work immediately

2. **Test Medium Image** (2-5MB):
   - Should work after deployment

3. **Test Large Image** (5-10MB):
   - Should work with new limits

4. **Check Logs**:
   ```bash
   # Laravel logs
   tail -f storage/logs/laravel.log
   
   # PHP errors
   tail -f /var/log/php_errors.log
   ```

## 📊 Validation Messages Added

New user-friendly messages:
- ✅ "File harus berupa gambar"
- ✅ "Format gambar harus: JPG, JPEG, PNG, atau WEBP"
- ✅ "Ukuran gambar maksimal 5MB per file"

## 🔧 Troubleshooting

### If still error after deployment:

1. **Check PHP Settings**:
```bash
php -i | grep upload_max_filesize
php -i | grep post_max_size
php -i | grep memory_limit
```

2. **Check Railway Logs**:
Look for:
- "Failed to store report"
- Memory usage logs
- Validation errors

3. **Try Smaller Image First**:
- Compress image to < 1MB
- Test if it works
- Gradually increase size

4. **Check Storage Permissions**:
```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

## 📝 Notes

- **Max 2 photos** per item/transfer
- **Max 5MB** per image
- **Supported formats**: JPG, JPEG, PNG, WEBP
- **Total request size**: Max 12MB (can upload 2x 5MB images + form data)

## ✅ Expected Result

After deployment:
- ✅ Upload laporan dengan foto berhasil
- ✅ No more `ERR_HTTP2_PROTOCOL_ERROR`
- ✅ Detailed logs untuk debugging
- ✅ User-friendly error messages

---

**Deploy sekarang dan test dengan image!** 🚀
