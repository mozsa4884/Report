# Instruksi Update Logo Aplikasi

## Logo Baru
Gambar logo baru menampilkan:
- **Rantai merah** (kiri) dan **rantai biru** (kanan) yang saling terhubung
- **Panah/daun hijau** dengan aksen merah di tengah
- Background **transparan** (bukan putih)

## File yang Perlu Diganti

### 1. Favicon utama
- **Lokasi:** `/public/favicon.png`
- **Format:** PNG dengan background transparan
- **Ukuran:** 512x512px atau 256x256px
- **Keterangan:** Ini adalah logo utama yang digunakan di semua tempat

### 2. Favicon ICO (opsional)
- **Lokasi:** `/public/favicon.ico`
- **Format:** ICO
- **Ukuran:** 16x16, 32x32, 48x48px
- **Keterangan:** Untuk kompatibilitas browser lama

## Tempat Logo Digunakan

1. **Sidebar dashboard** (`resources/views/layouts/app.blade.php`)
   - Tampil di sebelah kiri atas sidebar
   - CSS sudah diupdate dengan `mix-blend-mode: multiply` untuk hilangkan background putih

2. **Halaman login** (`resources/views/auth/login.blade.php`)
   - Tampil di header card login bersama logo Pertamina dan AGM
   - CSS sudah diupdate untuk transparan

3. **Landing page** (`resources/views/welcome.blade.php`)
   - Tampil di header navigation
   - CSS sudah diupdate untuk transparan

4. **Browser tab** (favicon di semua halaman)
   - Tampil sebagai icon di tab browser

## CSS yang Sudah Diupdate

Saya telah menambahkan CSS berikut untuk menghilangkan background putih:

```css
mix-blend-mode: multiply;
background: transparent;
```

Property ini akan membuat background putih menjadi transparan secara otomatis.

## Cara Mengganti Logo

### Opsi 1: Manual
1. Simpan gambar logo baru dengan nama `favicon.png`
2. Ganti file di `/public/favicon.png`
3. Refresh browser (Cmd+Shift+R di Mac atau Ctrl+F5 di Windows)

### Opsi 2: Via Terminal
```bash
# Backup logo lama
cp /Users/cal/Pekerjaan/Report/public/favicon.png /Users/cal/Pekerjaan/Report/public/favicon.png.backup

# Copy logo baru (setelah download/simpan)
cp /path/to/new-logo.png /Users/cal/Pekerjaan/Report/public/favicon.png
```

## Catatan Penting

- **Pastikan logo baru memiliki background transparan** (format PNG dengan alpha channel)
- Jika logo masih menampilkan background putih setelah diganti, clear browser cache
- Logo Pertamina dan AGM di header laporan tidak berubah (tetap menggunakan file lama)
