<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Tank;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class TankController extends Controller
{
    /**
     * Display the daily tank monitoring dashboard.
     *
     * Monitoring only shows approved reports. Draft, submitted, verified,
     * or rejected reports are excluded to ensure data accuracy.
     */
    public function monitoring(Request $request)
    {
        $user = Auth::user();

        if (!$user->isGl() && !$user->isSpv()) {
            abort(403, 'Hanya Group Leader dan Supervisor yang dapat memantau kondisi tangki BBM.');
        }

        $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        // Default to the newest approved report date
        $selectedDate = $request->input('date')
            ?: DailyReport::query()->where('status', 'approved')->orderByDesc('date')->value('date');
        $selectedDate = $selectedDate ? \Carbon\Carbon::parse($selectedDate)->toDateString() : now()->toDateString();

        $selectedReport = DailyReport::query()
            ->where('status', 'approved')
            ->whereDate('date', $selectedDate)
            ->with('items')
            ->first();

        $tanks = Tank::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->orderBy('main_hole')
            ->get();

        $reportItems = $selectedReport
            ? $selectedReport->items->keyBy('tank_id')
            : collect();

        $monitoringRows = $tanks->map(function (Tank $tank) use ($reportItems) {
            $item = $reportItems->get($tank->id);
            $capacity = $tank->capacity !== null ? (float) $tank->capacity : null;
            $finalLiters = $item?->liter_sore !== null ? (float) $item->liter_sore : null;
            $availableCapacity = $capacity !== null && $finalLiters !== null
                ? max(0, $capacity - $finalLiters)
                : null;

            return (object) [
                'tank' => $tank,
                'item' => $item,
                'capacity' => $capacity,
                'final_liters' => $finalLiters,
                'available_capacity' => $availableCapacity,
                'is_over_capacity' => $capacity !== null && $finalLiters !== null && $finalLiters > $capacity,
                'fill_percent' => $capacity && $finalLiters !== null
                    ? min(100, max(0, ($finalLiters / $capacity) * 100))
                    : null,
            ];
        });

        // Only tanks with both a capacity and a final (sore) reading are
        // included in the daily average and available-volume totals.
        $calculatedRows = $monitoringRows->filter(fn ($row) => $row->available_capacity !== null);
        $totalCapacity = $monitoringRows->whereNotNull('capacity')->sum('capacity');
        $totalFinalLiters = $calculatedRows->sum('final_liters');
        $totalCanEnter = $calculatedRows->sum('available_capacity');
        $averageCanEnter = $calculatedRows->count() > 0
            ? $totalCanEnter / $calculatedRows->count()
            : null;

        $chartData = [
            'labels' => $monitoringRows->map(fn ($row) => trim($row->tank->code . ' ' . $row->tank->main_hole))->values(),
            'capacity' => $monitoringRows->map(fn ($row) => $row->capacity ?? 0)->values(),
            'finalLiters' => $monitoringRows->map(fn ($row) => $row->final_liters ?? 0)->values(),
            'availableCapacity' => $monitoringRows->map(fn ($row) => $row->available_capacity ?? 0)->values(),
        ];

        return view('tanks.monitoring', compact(
            'tanks',
            'selectedDate',
            'selectedReport',
            'monitoringRows',
            'totalCapacity',
            'totalFinalLiters',
            'totalCanEnter',
            'averageCanEnter',
            'calculatedRows',
            'chartData'
        ));
    }

    public function index()
    {
        if (Auth::user()->isFuelman()) {
            abort(403, 'Fuelman tidak memiliki akses ke data tangki BBM.');
        }

        $tanks = Tank::orderBy('code')->orderBy('main_hole')->get();
        return view('tanks.index', compact('tanks'));
    }

    public function create()
    {
        if (!Auth::user()->isSpv()) {
            abort(403, 'Hanya Supervisor yang dapat menambah tangki baru.');
        }
        return view('tanks.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSpv()) {
            abort(403, 'Hanya Supervisor yang dapat menambah tangki baru.');
        }

        $request->validate([
            'code'              => 'required|string|max:50',
            'main_hole'         => 'required|string|max:50',
            'capacity'          => 'nullable|numeric|min:0',
            'is_active'         => 'required|boolean',
            'calibration_file'  => 'nullable|file|mimes:xlsx,xls|max:10240', // max 10MB
        ]);

        $tank = Tank::create([
            'code'      => $request->code,
            'main_hole' => $request->main_hole,
            'capacity'  => $request->capacity,
            'is_active' => $request->is_active,
        ]);

        if ($request->hasFile('calibration_file')) {
            try {
                $this->importCalibrationData($tank, $request->file('calibration_file'));
            } catch (Throwable $e) {
                Log::error('Gagal mengimpor kalibrasi tangki.', [
                    'tank_id' => $tank->id,
                    'exception' => $e,
                ]);

                return redirect()->route('tanks.edit', $tank->id)
                    ->with('warning', 'Tangki berhasil dibuat, namun file kalibrasi gagal diproses. Pastikan file Excel valid dan memiliki kolom DIPP serta VOLUME (L).');
            }
        }

        return redirect()->route('tanks.index')
            ->with('success', 'Tangki baru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if (!Auth::user()->isSpv()) {
            abort(403, 'Hanya Supervisor yang dapat mengubah data tangki.');
        }

        $tank = Tank::findOrFail($id);
        return view('tanks.edit', compact('tank'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isSpv()) {
            abort(403, 'Hanya Supervisor yang dapat mengubah data tangki.');
        }

        $tank = Tank::findOrFail($id);

        $request->validate([
            'code'              => 'required|string|max:50',
            'main_hole'         => 'required|string|max:50',
            'capacity'          => 'nullable|numeric|min:0',
            'is_active'         => 'required|boolean',
            'calibration_file'  => 'nullable|file|mimes:xlsx,xls|max:10240',
        ]);

        $tank->update([
            'code'      => $request->code,
            'main_hole' => $request->main_hole,
            'capacity'  => $request->capacity,
            'is_active' => $request->is_active,
        ]);

        if ($request->hasFile('calibration_file')) {
            try {
                DB::transaction(function () use ($tank, $request) {
                    $tank->calibrations()->delete();
                    $this->importCalibrationData($tank, $request->file('calibration_file'));
                });
            } catch (Throwable $e) {
                Log::error('Gagal memperbarui kalibrasi tangki.', [
                    'tank_id' => $tank->id,
                    'exception' => $e,
                ]);

                return redirect()->route('tanks.edit', $tank->id)
                    ->with('error', 'Gagal memproses file kalibrasi. Data kalibrasi sebelumnya tidak diubah. Pastikan file Excel valid dan memiliki kolom DIPP serta VOLUME (L).');
            }
        }

        return redirect()->route('tanks.index')
            ->with('success', 'Data tangki berhasil diperbarui.');
    }

    private function importCalibrationData(Tank $tank, UploadedFile $file): void
    {
        if (!$file->isValid() || !$file->getRealPath()) {
            throw new RuntimeException('File unggahan tidak valid atau tidak dapat dibaca.');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        
        if (count($rows) < 2) {
            throw new \Exception('File Excel kosong atau tidak memiliki baris data.');
        }

        // Normalize header text while preserving the original Excel column letter.
        // Excel headers can contain uppercase text or multiple spaces, for example
        // "DIPP (CM)" and "VOLUME   (L)".
        $headerRow = [];
        foreach ($rows[1] as $columnLetter => $headerName) {
            $headerRow[$columnLetter] = strtolower(preg_replace('/\s+/', ' ', trim((string) $headerName)));
        }
        
        // Find indices
        $dippCmKey = null;
        $dippMmKey = null;
        $volumeKey = null;

        foreach ($headerRow as $colLetter => $headerName) {
            if ($headerName === 'dipp (cm)' || $headerName === 'dipp(cm)' || str_contains($headerName, 'dipp (cm)')) {
                $dippCmKey = $colLetter;
            }
            if ($headerName === 'dipp (mm)' || $headerName === 'dipp(mm)' || str_contains($headerName, 'dipp (mm)')) {
                $dippMmKey = $colLetter;
            }
            if ($headerName === 'volume (l)' || $headerName === 'volume(l)' || str_contains($headerName, 'volume (l)') || $headerName === 'volume(liter)') {
                $volumeKey = $colLetter;
            }
        }

        // If headers not found by exact string, try general matches
        if (!$dippCmKey && !$dippMmKey) {
            foreach ($headerRow as $colLetter => $headerName) {
                if (str_contains($headerName, 'dipp') && str_contains($headerName, 'cm')) {
                    $dippCmKey = $colLetter;
                    break;
                }
            }
        }
        if (!$dippMmKey) {
            foreach ($headerRow as $colLetter => $headerName) {
                if (str_contains($headerName, 'dipp') && str_contains($headerName, 'mm')) {
                    $dippMmKey = $colLetter;
                    break;
                }
            }
        }
        if (!$volumeKey) {
            foreach ($headerRow as $colLetter => $headerName) {
                if (str_contains($headerName, 'volume') && (str_contains($headerName, '(l)') || str_contains($headerName, ' l'))) {
                    $volumeKey = $colLetter;
                    break;
                }
            }
        }

        if ((!$dippCmKey && !$dippMmKey) || !$volumeKey) {
            throw new \Exception('Kolom header "DIPP (CM)" atau "DIPP (MM)" dan "VOLUME (L)" tidak ditemukan pada baris pertama Excel.');
        }

        // Start reading data from row 2
        $calibrations = [];
        $now = now();

        for ($i = 2; $i <= count($rows); $i++) {
            $row = $rows[$i];
            
            $rawVol = isset($row[$volumeKey]) ? trim($row[$volumeKey]) : null;
            if ($rawVol === null || $rawVol === '') continue; // Skip empty rows

            // Clean volume (replace comma with dot if string float representation)
            $vol = floatval(str_replace(',', '.', str_replace('.', '', $rawVol))); // Handles formats like 10.000 or 10,000 or 10.2

            // Use DIPP (CM) as the source of truth when both columns exist.
            // The form submits sounding in CM, while some Excel templates use
            // a different scale in their DIPP (MM) column.
            if ($dippCmKey && isset($row[$dippCmKey]) && trim((string) $row[$dippCmKey]) !== '') {
                $cmVal = floatval(str_replace(',', '.', trim($row[$dippCmKey])));
                // The calibration workbook represents 0.1 in DIPP (CM) as
                // 10 in DIPP (MM), so its lookup scale is 100 (not 10).
                $mmVal = intval(round($cmVal * 100));
            } elseif ($dippMmKey && isset($row[$dippMmKey]) && trim((string) $row[$dippMmKey]) !== '') {
                $mmVal = intval(trim($row[$dippMmKey]));
                $cmVal = $mmVal / 10.0;
            } else {
                continue; // Skip if no sounding value
            }

            $calibrations[] = [
                'tank_id'       => $tank->id,
                'sounding_cm'   => $cmVal,
                'sounding_mm'   => $mmVal,
                'volume_liters' => $vol,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            // Bulk insert every 500 records to save memory/prevent timeouts
            if (count($calibrations) >= 500) {
                \App\Models\TankCalibration::insert($calibrations);
                $calibrations = [];
            }
        }

        if (count($calibrations) > 0) {
            \App\Models\TankCalibration::insert($calibrations);
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->isSpv()) {
            abort(403, 'Hanya Supervisor yang dapat menghapus tangki.');
        }

        $tank = Tank::findOrFail($id);
        
        // Prevent deletion if items exist to preserve database integrity
        if ($tank->items()->exists()) {
            return redirect()->route('tanks.index')
                ->with('error', 'Tangki tidak dapat dihapus karena sudah memiliki catatan laporan kegiatan.');
        }

        $tank->delete();

        return redirect()->route('tanks.index')
            ->with('success', 'Tangki berhasil dihapus.');
    }

    public function getVolume(Request $request, $tank_id)
    {
        $sounding = $request->query('sounding');
        
        if ($sounding === null || $sounding === '') {
            return response()->json(['volume' => null]);
        }

        // Parse sounding from CM, convert to MM for precise database lookup
        $soundingCm = (float) str_replace(',', '.', trim((string) $sounding));
        // Match the DIPP (CM) → DIPP (MM) scale used by the calibration
        // workbook: 0.5 maps to 50 and therefore returns its 72 L value.
        $soundingMm = intval(round($soundingCm * 100));

        // 1. Try to find exact sounding match
        $calibration = \App\Models\TankCalibration::where('tank_id', $tank_id)
            ->where('sounding_mm', $soundingMm)
            ->first();

        if ($calibration) {
            return response()->json(['volume' => floatval($calibration->volume_liters)]);
        }

        // Some imported calibration files preserve the centimetre value but
        // have an inconsistent millimetre column. Try the CM source directly
        // before treating an existing value such as 0.5 cm as unavailable.
        $calibration = \App\Models\TankCalibration::where('tank_id', $tank_id)
            ->whereBetween('sounding_cm', [$soundingCm - 0.0001, $soundingCm + 0.0001])
            ->first();

        if ($calibration) {
            return response()->json(['volume' => floatval($calibration->volume_liters)]);
        }

        // Only values explicitly listed in the calibration table are valid.
        // Do not estimate from adjacent measurements: an unlisted sounding
        // must be shown as XXXX by the form.
        return response()->json(['volume' => null]);
    }
}
