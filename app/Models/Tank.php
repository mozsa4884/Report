<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tank extends Model
{
    protected $fillable = [
        'site_id',
        'code',
        'main_hole',
        'capacity',
        'is_active',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailyReportItem::class);
    }

    public function calibrations(): HasMany
    {
        return $this->hasMany(TankCalibration::class);
    }

    /**
     * Convert sounding (cm) to liter based on calibration data.
     * Uses linear interpolation between calibration points.
     * 
     * @param float|null $soundingCm
     * @return float|null
     */
    public function soundingToLiter($soundingCm)
    {
        if ($soundingCm === null || $soundingCm === '') {
            return null;
        }

        // Get calibration data sorted by sounding
        $calibrations = $this->calibrations()
            ->whereNotNull('sounding_cm')
            ->whereNotNull('volume_liters')
            ->orderBy('sounding_cm')
            ->get();

        if ($calibrations->isEmpty()) {
            return null; // No calibration data available
        }

        // Exact match
        $exact = $calibrations->firstWhere('sounding_cm', $soundingCm);
        if ($exact) {
            return (float) $exact->volume_liters;
        }

        // Find interpolation range
        $lower = null;
        $upper = null;

        foreach ($calibrations as $cal) {
            if ($cal->sounding_cm <= $soundingCm) {
                $lower = $cal;
            }
            if ($cal->sounding_cm >= $soundingCm && $upper === null) {
                $upper = $cal;
                break;
            }
        }

        // Extrapolate if needed
        if ($lower === null && $upper !== null) {
            // Below minimum calibration point - return lowest volume
            return (float) $upper->volume_liters;
        }

        if ($upper === null && $lower !== null) {
            // Above maximum calibration point - return highest volume
            return (float) $lower->volume_liters;
        }

        if ($lower && $upper && $lower->id !== $upper->id) {
            // Linear interpolation: y = y1 + (x - x1) * (y2 - y1) / (x2 - x1)
            $soundingDiff = $upper->sounding_cm - $lower->sounding_cm;
            $volumeDiff = $upper->volume_liters - $lower->volume_liters;
            
            if ($soundingDiff > 0) {
                $interpolated = $lower->volume_liters + (($soundingCm - $lower->sounding_cm) * $volumeDiff / $soundingDiff);
                return round($interpolated, 2);
            }
        }

        return $lower ? (float) $lower->volume_liters : null;
    }
}
