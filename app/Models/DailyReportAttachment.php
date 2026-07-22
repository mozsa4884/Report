<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DailyReportAttachment extends Model
{
    protected $fillable = ['daily_report_id', 'section', 'attachment_key', 'context', 'path'];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    /**
     * Get the public URL for this attachment.
     * Ensures browser-accessible URL is generated (not internal hostname).
     */
    public function getPublicUrl(): string
    {
        $disk = config('filesystems.report_attachment_disk', 'public');
        
        // Generate URL using configured disk
        $url = Storage::disk($disk)->url($this->path);
        
        // If S3_PUBLIC_URL is set and URL contains internal hostname, replace it
        $publicUrl = config('filesystems.disks.s3.url');
        if ($disk === 's3' && $publicUrl && str_contains($url, 'railway.internal')) {
            // Extract the path after bucket name
            $bucket = config('filesystems.disks.s3.bucket');
            if (preg_match('#/' . preg_quote($bucket, '#') . '/(.+)$#', $url, $matches)) {
                $filePath = $matches[1];
                $url = rtrim($publicUrl, '/') . '/' . ltrim($filePath, '/');
            }
        }
        
        return $url;
    }
}
