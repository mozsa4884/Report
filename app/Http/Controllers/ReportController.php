<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\Tank;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DailyReport::with(['fuelman', 'gl', 'spv', 'site'])->orderBy('date', 'desc');

        // Fuelman only sees their own reports
        if ($user->isFuelman()) {
            $query->where('fuelman_id', $user->id);
        }

        $reports = $query->paginate(15);

        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        if (!Auth::user()->isFuelman()) {
            abort(403, 'Hanya Fuelman yang dapat membuat laporan baru.');
        }

        // Check if report already exists for today or a specific date
        $tanks = Tank::where('is_active', true)
            ->orderBy('code')
            ->orderBy('main_hole')
            ->get();
        $defaultDate = now()->format('Y-m-d');
        $sites = \App\Models\Site::where('is_active', true)->orderBy('code')->get();

        return view('reports.create', compact('tanks', 'defaultDate', 'sites'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isFuelman()) {
            abort(403, 'Hanya Fuelman yang dapat membuat laporan baru.');
        }

        \Log::info('Store report attempt', [
            'user_id' => Auth::id(),
            'site_id' => $request->site_id,
            'date' => $request->date,
            'items_count' => count($request->items ?? []),
            'has_files' => $request->hasFile('items.*.photos.*'),
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
        ]);

        $request->validate([
            'date' => [
                'required',
                'date',
                Rule::unique('daily_reports')->where(function ($query) use ($request) {
                    return $query->where('site_id', $request->site_id);
                }),
            ],
            'site_id' => 'required|exists:sites,id',
            'items' => 'nullable|array',
            'items.*.tank_id' => 'nullable|exists:tanks,id',
            'items.*.sounding_pagi' => 'nullable|numeric',
            'items.*.liter_pagi' => 'nullable|string',
            'items.*.jam_pagi' => 'nullable',
            'items.*.petugas_pagi' => 'nullable|string',
            'items.*.sounding_sore' => 'nullable|numeric',
            'items.*.liter_sore' => 'nullable|string',
            'items.*.jam_sore' => 'nullable',
            'items.*.petugas_sore' => 'nullable|string',
            'items.*.fm_pagi' => 'nullable|numeric',
            'items.*.fm_sore' => 'nullable|numeric',
            'items.*.keterangan' => 'nullable|string',
            'items.*.photos' => 'nullable|array|max:2',
            'items.*.photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'kapasitas' => 'nullable|array',
            'kapasitas.*.soh' => 'nullable|numeric|min:0',
            'kapasitas.*.rata' => 'nullable|numeric|min:0',
            
            // Transfers B validation
            'transfers' => 'nullable|array',
            'transfers.*.dari_tangki' => 'nullable|string',
            'transfers.*.ke_tangki' => 'nullable|string',
            'transfers.*.spm_awal' => 'nullable|numeric',
            'transfers.*.spm_akhir' => 'nullable|numeric',
            'transfers.*.spm_hasil' => 'nullable|numeric',
            'transfers.*.spm_liter' => 'nullable|string',
            'transfers.*.ft_awal' => 'nullable|numeric',
            'transfers.*.ft_akhir' => 'nullable|numeric',
            'transfers.*.ft_hasil' => 'nullable|numeric',
            'transfers.*.ft_liter' => 'nullable|string',
            'transfers.*.fm_awal' => 'nullable|numeric',
            'transfers.*.fm_akhir' => 'nullable|numeric',
            'transfers.*.fm_jumlah' => 'nullable|numeric',
            'transfers.*.jam_mulai' => 'nullable',
            'transfers.*.jam_selesai' => 'nullable',
            'transfers.*.lama_transfer' => 'nullable|string',
            'transfers.*.photos' => 'nullable|array|max:2',
            'transfers.*.photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',

            // Flowmeters C validation
            'flowmeters' => 'nullable|array',
            'flowmeters.*.unit' => 'nullable|string',
            'flowmeters.*.jenis_flowmeter' => 'nullable|string',
            'flowmeters.*.nomor_seri' => 'nullable|string',
            'flowmeters.*.awal_pagi' => 'nullable|numeric',
            'flowmeters.*.akhir_sore' => 'nullable|numeric',
            'flowmeters.*.jumlah_pakai' => 'nullable|numeric',
        ], [
            'date.unique' => 'Laporan untuk site ini pada tanggal tersebut sudah ada.',
            'site_id.required' => 'Site harus dipilih.',
            'site_id.exists' => 'Site yang dipilih tidak valid.',
            'items.*.photos.*.image' => 'File harus berupa gambar.',
            'items.*.photos.*.mimes' => 'Format gambar harus: JPG, JPEG, PNG, atau WEBP.',
            'items.*.photos.*.max' => 'Ukuran gambar maksimal 5MB per file.',
            'transfers.*.photos.*.image' => 'File harus berupa gambar.',
            'transfers.*.photos.*.mimes' => 'Format gambar harus: JPG, JPEG, PNG, atau WEBP.',
            'transfers.*.photos.*.max' => 'Ukuran gambar maksimal 5MB per file.',
        ]);

        DB::beginTransaction();
        try {
            $kapasitas = $request->kapasitas ?? [];
            $report = DailyReport::create([
                'date'       => $request->date,
                'site_id'    => $request->site_id,
                'status'     => 'draft',
                'fuelman_id' => Auth::id(),
                'soh_spm1'   => $kapasitas['SPM1']['soh'] ?? null,
                'soh_spm2'   => $kapasitas['SPM2']['soh'] ?? null,
                'soh_spm3'   => $kapasitas['SPM3']['soh'] ?? null,
                'soh_ft05'   => $kapasitas['FT05']['soh'] ?? null,
                'rata_spm1'  => $kapasitas['SPM1']['rata'] ?? null,
                'rata_spm2'  => $kapasitas['SPM2']['rata'] ?? null,
                'rata_spm3'  => $kapasitas['SPM3']['rata'] ?? null,
                'rata_ft05'  => $kapasitas['FT05']['rata'] ?? null,
            ]);

            \Log::info('Report created', ['report_id' => $report->id]);

            $this->saveItems($report, $request->items);
            \Log::info('Items saved', ['report_id' => $report->id]);
            
            $this->saveTransfers($report, $request->transfers ?? []);
            \Log::info('Transfers saved', ['report_id' => $report->id]);
            
            $this->saveFlowmeters($report, $request->flowmeters ?? []);
            \Log::info('Flowmeters saved', ['report_id' => $report->id]);

            DB::commit();
            \Log::info('Report stored successfully', ['report_id' => $report->id]);

            return redirect()->route('reports.show', $report->id)
                ->with('success', 'Laporan harian berhasil dibuat sebagai Draft.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation error in store', ['errors' => $e->errors()]);
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to store report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memory' => memory_get_usage(true) / 1024 / 1024 . ' MB'
            ]);
            return back()->withInput()->with('error', 'Gagal membuat laporan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $report = DailyReport::with(['items.tank', 'transfers', 'flowmeters', 'attachments', 'fuelman', 'gl', 'spv', 'site'])->findOrFail($id);

        return view('reports.show', compact('report'));
    }

    public function edit($id)
    {
        $report = DailyReport::with(['items.tank', 'transfers', 'flowmeters', 'attachments'])->findOrFail($id);
        $user = Auth::user();

        if (!$user->isFuelman() || $report->fuelman_id !== $user->id) {
            abort(403, 'Hanya pembuat laporan yang dapat mengubah draft.');
        }

        if (!in_array($report->status, ['draft', 'rejected'])) {
            return redirect()->route('reports.show', $report->id)
                ->with('error', 'Hanya laporan dengan status Draft atau Direvisi yang dapat diubah.');
        }

        // Load items indexed by tank_id for easy lookup in form
        $items = $report->items->keyBy('tank_id');
        $transfers = $report->transfers;
        $flowmeters = $report->flowmeters;
        $tanks = Tank::where('is_active', true)
            ->orderBy('code')
            ->orderBy('main_hole')
            ->get();
        $sites = \App\Models\Site::where('is_active', true)->orderBy('code')->get();

        return view('reports.edit', compact('report', 'tanks', 'items', 'transfers', 'flowmeters', 'sites'));
    }

    public function update(Request $request, $id)
    {
        $report = DailyReport::findOrFail($id);
        $user = Auth::user();

        if (!$user->isFuelman() || $report->fuelman_id !== $user->id) {
            abort(403, 'Hanya pembuat laporan yang dapat mengubah draft.');
        }

        if (!in_array($report->status, ['draft', 'rejected'])) {
            return redirect()->route('reports.show', $report->id)
                ->with('error', 'Hanya laporan dengan status Draft atau Direvisi yang dapat diubah.');
        }

        $request->validate([
            'date' => [
                'required',
                'date',
                Rule::unique('daily_reports')->where(function ($query) use ($request) {
                    return $query->where('site_id', $request->site_id);
                })->ignore($id),
            ],
            'site_id' => 'required|exists:sites,id',
            'items' => 'nullable|array',
            'items.*.tank_id' => 'nullable|exists:tanks,id',
            'items.*.sounding_pagi' => 'nullable|numeric',
            'items.*.liter_pagi' => 'nullable|string',
            'items.*.jam_pagi' => 'nullable',
            'items.*.petugas_pagi' => 'nullable|string',
            'items.*.sounding_sore' => 'nullable|numeric',
            'items.*.liter_sore' => 'nullable|string',
            'items.*.jam_sore' => 'nullable',
            'items.*.petugas_sore' => 'nullable|string',
            'items.*.fm_pagi' => 'nullable|numeric',
            'items.*.fm_sore' => 'nullable|numeric',
            'items.*.keterangan' => 'nullable|string',
            'items.*.photos' => 'nullable|array|max:2',
            'items.*.photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'delete_attachment_ids' => 'nullable|array',
            'delete_attachment_ids.*' => 'integer',
            'kapasitas' => 'nullable|array',
            'kapasitas.*.soh' => 'nullable|numeric|min:0',
            'kapasitas.*.rata' => 'nullable|numeric|min:0',

            // Transfers B validation
            'transfers' => 'nullable|array',
            'transfers.*.dari_tangki' => 'nullable|string',
            'transfers.*.ke_tangki' => 'nullable|string',
            'transfers.*.spm_awal' => 'nullable|numeric',
            'transfers.*.spm_akhir' => 'nullable|numeric',
            'transfers.*.spm_hasil' => 'nullable|numeric',
            'transfers.*.spm_liter' => 'nullable|string',
            'transfers.*.ft_awal' => 'nullable|numeric',
            'transfers.*.ft_akhir' => 'nullable|numeric',
            'transfers.*.ft_hasil' => 'nullable|numeric',
            'transfers.*.ft_liter' => 'nullable|string',
            'transfers.*.fm_awal' => 'nullable|numeric',
            'transfers.*.fm_akhir' => 'nullable|numeric',
            'transfers.*.fm_jumlah' => 'nullable|numeric',
            'transfers.*.jam_mulai' => 'nullable',
            'transfers.*.jam_selesai' => 'nullable',
            'transfers.*.lama_transfer' => 'nullable|string',
            'transfers.*.photos' => 'nullable|array|max:2',
            'transfers.*.photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',

            // Flowmeters C validation
            'flowmeters' => 'nullable|array',
            'flowmeters.*.unit' => 'nullable|string',
            'flowmeters.*.jenis_flowmeter' => 'nullable|string',
            'flowmeters.*.nomor_seri' => 'nullable|string',
            'flowmeters.*.awal_pagi' => 'nullable|numeric',
            'flowmeters.*.akhir_sore' => 'nullable|numeric',
            'flowmeters.*.jumlah_pakai' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $kapasitas = $request->kapasitas ?? [];
            $report->update([
                'date'      => $request->date,
                'site_id'   => $request->site_id,
                'status'    => 'draft',
                'soh_spm1'  => $kapasitas['SPM1']['soh'] ?? $report->soh_spm1,
                'soh_spm2'  => $kapasitas['SPM2']['soh'] ?? $report->soh_spm2,
                'soh_spm3'  => $kapasitas['SPM3']['soh'] ?? $report->soh_spm3,
                'soh_ft05'  => $kapasitas['FT05']['soh'] ?? $report->soh_ft05,
                'rata_spm1' => $kapasitas['SPM1']['rata'] ?? $report->rata_spm1,
                'rata_spm2' => $kapasitas['SPM2']['rata'] ?? $report->rata_spm2,
                'rata_spm3' => $kapasitas['SPM3']['rata'] ?? $report->rata_spm3,
                'rata_ft05' => $kapasitas['FT05']['rata'] ?? $report->rata_ft05,
            ]);

            $this->deleteAttachments($report, $request->input('delete_attachment_ids', []));

            // Clear existing items and save new ones
            $report->items()->delete();
            $report->transfers()->delete();
            $report->flowmeters()->delete();

            $this->saveItems($report, $request->items);
            $this->saveTransfers($report, $request->transfers ?? []);
            $this->saveFlowmeters($report, $request->flowmeters ?? []);

            DB::commit();

            return redirect()->route('reports.show', $report->id)
                ->with('success', 'Laporan harian berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }
    }

    public function submit($id)
    {
        $report = DailyReport::findOrFail($id);
        $user = Auth::user();

        if (!$user->isFuelman() || $report->fuelman_id !== $user->id) {
            abort(403, 'Hanya pembuat laporan yang dapat mengirim laporan.');
        }

        if (!in_array($report->status, ['draft', 'rejected'])) {
            return redirect()->route('reports.show', $report->id)
                ->with('error', 'Laporan sudah dikirim atau disetujui.');
        }

        // Reset approval saat submit ulang
        $report->update([
            'status' => 'submitted',
            'gl_id' => null,
            'spv_id' => null,
            'gl_feedback' => null,
            'spv_feedback' => null,
        ]);

        return redirect()->route('reports.show', $report->id)
            ->with('success', 'Laporan berhasil dikirim ke Group Leader.');
    }

    public function verify(Request $request, $id)
    {
        $report = DailyReport::findOrFail($id);
        $user = Auth::user();

        if (!$user->isGl()) {
            abort(403, 'Hanya Group Leader yang dapat memverifikasi laporan.');
        }

        if ($report->status !== 'submitted') {
            return redirect()->route('reports.show', $report->id)
                ->with('error', 'Laporan ini tidak dalam antrean verifikasi.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'feedback' => 'required_if:action,reject|nullable|string',
        ]);

        if ($request->action === 'approve') {
            $report->update([
                'status' => 'verified',
                'gl_id' => $user->id,
                'gl_feedback' => null,
            ]);
            return redirect()->route('reports.show', $report->id)
                ->with('success', 'Laporan berhasil diverifikasi dan diteruskan ke Supervisor.');
        } else {
            // GL Reject: tidak ada approval sama sekali
            $report->update([
                'status' => 'rejected',
                'gl_id' => null,
                'spv_id' => null,
                'gl_feedback' => $request->feedback,
                'spv_feedback' => null,
            ]);
            return redirect()->route('reports.show', $report->id)
                ->with('warning', 'Laporan telah ditolak dan dikembalikan ke Fuelman.');
        }
    }

    public function approve(Request $request, $id)
    {
        $report = DailyReport::findOrFail($id);
        $user = Auth::user();

        if (!$user->isSpv()) {
            abort(403, 'Hanya Supervisor yang dapat menyetujui laporan.');
        }

        if ($report->status !== 'verified') {
            return redirect()->route('reports.show', $report->id)
                ->with('error', 'Laporan ini belum diverifikasi oleh Group Leader.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'feedback' => 'required_if:action,reject|nullable|string',
        ]);

        if ($request->action === 'approve') {
            $report->update([
                'status' => 'approved',
                'spv_id' => $user->id,
                'spv_feedback' => null,
            ]);
            return redirect()->route('reports.show', $report->id)
                ->with('success', 'Laporan berhasil disetujui (Approved).');
        } else {
            // SPV Reject: GL tetap tercatat, SPV tidak
            $report->update([
                'status' => 'rejected',
                // 'gl_id' tetap ada (sudah diverifikasi sebelumnya)
                'spv_id' => null,
                // 'gl_feedback' tetap (tidak diubah)
                'spv_feedback' => $request->feedback,
            ]);
            return redirect()->route('reports.show', $report->id)
                ->with('warning', 'Laporan telah ditolak dan dikembalikan ke Fuelman.');
        }
    }

    public function destroy($id)
    {
        $report = DailyReport::findOrFail($id);
        $user = Auth::user();

        // Authorization checks
        if ($user->isFuelman()) {
            if ($report->fuelman_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk menghapus laporan ini.');
            }
            if (!in_array($report->status, ['draft', 'rejected'])) {
                return redirect()->route('reports.show', $report->id)
                    ->with('error', 'Laporan yang sudah diajukan atau disetujui tidak dapat dihapus.');
            }
        } elseif (!$user->isSpv()) {
            // Only Fuelman (owner) and Supervisor can delete reports
            abort(403, 'Hanya Fuelman pembuat laporan atau Supervisor yang dapat menghapus laporan.');
        }

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Laporan harian berhasil dihapus.');
    }

    /**
     * Helper to save report items and calculate automated values.
     */
    private function saveItems(DailyReport $report, array $itemsData)
    {
        // Every tank row, including SPM3 (D+B)/2, is saved from manual input.
        foreach ($itemsData as $data) {
            if (empty($data['tank_id'])) {
                continue;
            }

            $tankId = $data['tank_id'];
            $tank = Tank::find($tankId);

            // Get sounding values
            $soundingPagi = isset($data['sounding_pagi']) && $data['sounding_pagi'] !== '' ? (double)$data['sounding_pagi'] : null;
            $soundingSore = isset($data['sounding_sore']) && $data['sounding_sore'] !== '' ? (double)$data['sounding_sore'] : null;

            // Calculate liter from sounding using calibration data
            $literPagi = null;
            $literSore = null;
            
            if ($tank) {
                if ($soundingPagi !== null) {
                    $literPagi = $tank->soundingToLiter($soundingPagi);
                }
                if ($soundingSore !== null) {
                    $literSore = $tank->soundingToLiter($soundingSore);
                }
            }

            // Calculate Flow Meter Usage: fm_sore - fm_pagi (hanya jika KEDUA terisi)
            $fmPagi = isset($data['fm_pagi']) && $data['fm_pagi'] !== '' ? (double)$data['fm_pagi'] : null;
            $fmSore = isset($data['fm_sore']) && $data['fm_sore'] !== '' ? (double)$data['fm_sore'] : null;
            $fmPakai = null;
            if ($fmPagi !== null && $fmSore !== null) {
                $fmPakai = $fmSore - $fmPagi;
            }

            $item = new DailyReportItem([
                'tank_id' => $tankId,
                'sounding_pagi' => $soundingPagi,
                'liter_pagi' => $literPagi,
                'jam_pagi' => $data['jam_pagi'] ?: null,
                'petugas_pagi' => $data['petugas_pagi'] ?: null,
                
                'sounding_sore' => $soundingSore,
                'liter_sore' => $literSore,
                'jam_sore' => $data['jam_sore'] ?: null,
                'petugas_sore' => $data['petugas_sore'] ?: null,
                
                'fm_pagi' => $fmPagi,
                'fm_sore' => $fmSore,
                'fm_pakai' => $fmPakai,
                'keterangan' => $data['keterangan'] ?: null,
            ]);

            $report->items()->save($item);

            $context = trim(implode(' — ', array_filter([
                'Tangki ' . ($tank?->code ?? '-'),
                $tank?->main_hole,
                $data['keterangan'] ?? null,
            ])));
            $this->saveAttachmentPhotos($report, 'A', $data['attachment_key'] ?? "item-{$tankId}", $context, $data['photos'] ?? []);
        }
    }

    private function saveTransfers(DailyReport $report, array $transfersData)
    {
        foreach ($transfersData as $data) {
            // Skip empty rows
            if (empty($data['dari_tangki']) && empty($data['ke_tangki'])) {
                continue;
            }

            $spmAwal = isset($data['spm_awal']) && $data['spm_awal'] !== '' ? (double)$data['spm_awal'] : null;
            $spmAkhir = isset($data['spm_akhir']) && $data['spm_akhir'] !== '' ? (double)$data['spm_akhir'] : null;
            $spmHasil = null;
            if ($spmAwal !== null && $spmAkhir !== null) {
                $spmHasil = $spmAwal - $spmAkhir;
            }

            $ftAwal = isset($data['ft_awal']) && $data['ft_awal'] !== '' ? (double)$data['ft_awal'] : null;
            $ftAkhir = isset($data['ft_akhir']) && $data['ft_akhir'] !== '' ? (double)$data['ft_akhir'] : null;
            $ftHasil = null;
            if ($ftAwal !== null && $ftAkhir !== null) {
                $ftHasil = $ftAkhir - $ftAwal;
            }

            $fmAwal = isset($data['fm_awal']) && $data['fm_awal'] !== '' ? (double)$data['fm_awal'] : null;
            $fmAkhir = isset($data['fm_akhir']) && $data['fm_akhir'] !== '' ? (double)$data['fm_akhir'] : null;
            $fmJumlah = null;
            if ($fmAwal !== null && $fmAkhir !== null) {
                $fmJumlah = $fmAkhir - $fmAwal;
            }

            // Calculate liter values based on sounding hasil and tank calibration
            $spmLiter = null;
            $ftLiter = null;

            // Find SPM tank (dari_tangki)
            if ($spmHasil !== null && !empty($data['dari_tangki'])) {
                $spmTank = Tank::where('code', $data['dari_tangki'])->first();
                if ($spmTank) {
                    $spmLiter = $spmTank->soundingToLiter(abs($spmHasil));
                }
            }

            // Find FT tank (ke_tangki)
            if ($ftHasil !== null && !empty($data['ke_tangki'])) {
                $ftTank = Tank::where('code', $data['ke_tangki'])->first();
                if ($ftTank) {
                    $ftLiter = $ftTank->soundingToLiter(abs($ftHasil));
                }
            }

            $transfer = $report->transfers()->create([
                'dari_tangki'   => $data['dari_tangki'] ?: null,
                'ke_tangki'     => $data['ke_tangki'] ?: null,
                'spm_awal'      => $spmAwal,
                'spm_akhir'     => $spmAkhir,
                'spm_hasil'     => $spmHasil,
                'spm_liter'     => $spmLiter,
                'ft_awal'       => $ftAwal,
                'ft_akhir'      => $ftAkhir,
                'ft_hasil'      => $ftHasil,
                'ft_liter'      => $ftLiter,
                'fm_awal'       => $fmAwal,
                'fm_akhir'      => $fmAkhir,
                'fm_jumlah'     => $fmJumlah,
                'jam_mulai'     => $data['jam_mulai'] ?: null,
                'jam_selesai'   => $data['jam_selesai'] ?: null,
                'lama_transfer' => $data['lama_transfer'] ?: null,
            ]);

            $context = trim(implode(' — ', array_filter([
                'Transfer ' . ($data['dari_tangki'] ?: '-') . ' ke ' . ($data['ke_tangki'] ?: '-'),
                $data['lama_transfer'] ?? null,
            ])));
            $this->saveAttachmentPhotos($report, 'B', $data['attachment_key'] ?? "transfer-{$transfer->id}", $context, $data['photos'] ?? []);
        }
    }

    /** Add photos to an attachment set, up to the two-photo limit. */
    private function saveAttachmentPhotos(DailyReport $report, string $section, string $attachmentKey, string $context, array $files): void
    {
        $photos = array_values(array_filter($files, fn ($file) => $file instanceof UploadedFile));
        if ($photos === []) {
            return;
        }

        $existing = $report->attachments()
            ->where('section', $section)
            ->where('attachment_key', $attachmentKey)
            ->get();

        $availableSlots = max(0, 2 - $existing->count());

        foreach (array_slice($photos, 0, $availableSlots) as $photo) {
            try {
                $disk = $this->attachmentDisk();
                \Log::info("Attempting to upload photo", [
                    'disk' => $disk,
                    'filename' => $photo->getClientOriginalName(),
                    'size' => $photo->getSize(),
                    'section' => $section,
                ]);
                
                $path = $photo->store("report-attachments/{$report->id}/section-{$section}", $disk);
                
                \Log::info("Photo uploaded successfully", [
                    'path' => $path,
                    'disk' => $disk,
                ]);
                
                $report->attachments()->create([
                    'section' => $section,
                    'attachment_key' => $attachmentKey,
                    'context' => $context,
                    'path' => $path,
                ]);
            } catch (\Exception $e) {
                \Log::error("Failed to upload photo", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'disk' => $disk ?? 'unknown',
                ]);
                throw $e;
            }
        }
    }

    /** Delete only attachments that belong to the report currently being edited. */
    private function deleteAttachments(DailyReport $report, array $attachmentIds): void
    {
        if ($attachmentIds === []) {
            return;
        }

        $attachments = $report->attachments()
            ->whereIn('id', array_unique($attachmentIds))
            ->get();

        foreach ($attachments as $attachment) {
            Storage::disk($this->attachmentDisk())->delete($attachment->path);
            $attachment->delete();
        }
    }

    /** Local storage is private; use Laravel's public disk for browser-visible attachments. */
    private function attachmentDisk(): string
    {
        $disk = config('filesystems.default');

        return $disk === 'local' ? 'public' : $disk;
    }

    private function saveFlowmeters(DailyReport $report, array $flowmetersData)
    {
        foreach ($flowmetersData as $data) {
            // Skip empty rows
            if (empty($data['unit'])) {
                continue;
            }

            $awal = isset($data['awal_pagi']) && $data['awal_pagi'] !== '' ? (double)$data['awal_pagi'] : null;
            $akhir = isset($data['akhir_sore']) && $data['akhir_sore'] !== '' ? (double)$data['akhir_sore'] : null;
            $jumlah = null;
            if ($awal !== null && $akhir !== null) {
                $jumlah = round($akhir - $awal);
            }

            $report->flowmeters()->create([
                'unit'            => $data['unit'] ?: null,
                'jenis_flowmeter' => $data['jenis_flowmeter'] ?: null,
                'nomor_seri'      => $data['nomor_seri'] ?: null,
                'awal_pagi'       => $awal,
                'akhir_sore'      => $akhir,
                'jumlah_pakai'    => $jumlah,
            ]);
        }
    }
}
