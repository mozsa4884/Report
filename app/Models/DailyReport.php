<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    protected $fillable = [
        'date',
        'site_name',
        'status',
        'fuelman_id',
        'gl_id',
        'spv_id',
        'gl_feedback',
        'spv_feedback',
        'soh_spm1',
        'soh_spm2',
        'soh_spm3',
        'soh_ft05',
        'rata_spm1',
        'rata_spm2',
        'rata_spm3',
        'rata_ft05',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function fuelman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fuelman_id');
    }

    public function gl(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gl_id');
    }

    public function spv(): BelongsTo
    {
        return $this->belongsTo(User::class, 'spv_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyReportItem::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(DailyReportTransfer::class);
    }

    public function flowmeters(): HasMany
    {
        return $this->hasMany(DailyReportFlowmeter::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DailyReportAttachment::class);
    }
}
