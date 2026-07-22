@extends('layouts.app')

@section('title', 'Detail Laporan - ' . $report->date->format('d-m-Y'))

@section('styles')
<style>
    /* Styling to match the Excel sheet color scheme */
    .sheet-table th {
        background-color: #e0f2fe !important;
        color: #1e293b !important;
        font-weight: 700;
        border: 1px solid #94a3b8 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .sheet-table th.section-pagi, .sheet-table th.section-sore {
        background-color: #ffe4e6 !important; /* Soft rose/pink */
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .sheet-table td {
        border: 1px solid #94a3b8 !important;
        font-weight: 500;
    }
    
    /* Blue font color for soundings */
    .val-sounding {
        color: #2563eb !important;
        font-weight: 600;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Red font color for liters and fm usage */
    .val-liter, .val-pakai {
        color: #dc2626 !important;
        font-weight: 600;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Layout styling for logo and headers */
    .sheet-header-container {
        display: grid;
        grid-template-columns: 120px 1fr 120px;
        align-items: center;
        width: 100%;
        margin-bottom: 1.5rem;
    }
    
    .sheet-logo-left {
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }
    
    .sheet-logo-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        color: #1e293b;
    }
    
    .sheet-logo-sub {
        font-size: 0.65rem;
        font-weight: 500;
        color: #64748b;
        letter-spacing: 0.5px;
    }

    .sheet-title-area {
        text-align: center;
    }

    .sheet-title-area h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: 0.5px;
    }

    .sheet-title-area h3 {
        font-size: 0.95rem;
        color: #475569;
        font-weight: 600;
        margin-top: 0.25rem;
    }

    /* Print alignment styling for meta details */
    .sheet-meta-row {
        display: flex;
        gap: 4rem;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-left: 0.5rem;
        text-transform: uppercase;
        color: #1e293b;
    }

    .sheet-meta-item {
        display: flex;
        gap: 0.5rem;
    }

    /* Average row style */
    .sheet-table tr.average-row td {
        background-color: #f8fafc;
        font-weight: 700;
    }

    .report-stats-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    }

    .report-summary {
        width: 100%;
        max-width: none !important;
    }

    @media (max-width: 768px) {
        .report-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    /* Section label styling */
    .section-label {
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        border-bottom: 2px solid #cbd5e1;
        padding-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Stat summary cards */
    .stat-summary-table td {
        padding: 3px 10px 3px 0;
        font-size: 8.5pt;
        border: none !important;
    }

    /* Percentage bar */
    .pct-bar-container {
        display: flex;
        align-items: center;
        height: 32px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 2px;
        overflow: hidden;
    }

    .pct-bar-fill {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        font-weight: bold;
        font-size: 8pt;
        color: white;
        padding: 0 8px;
        min-width: 50px;
    }

    .pct-bar-label {
        padding-left: 8px;
        font-weight: 600;
        font-size: 8pt;
        color: #1e293b;
    }

    .attachment-page {
        margin-top: 2rem;
        padding-top: 1.25rem;
        border-top: 2px solid #cbd5e1;
    }

    .attachment-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .attachment-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.75rem;
        break-inside: avoid;
        background: white;
    }

    .attachment-card img {
        display: block;
        width: 100%;
        height: 280px;
        object-fit: cover;
        margin-top: 0.5rem;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
        }

        /* Hide navigation elements */
        .sidebar, .btn, .review-panel, .alert, .no-print, .content-header, nav, footer {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            display: block !important;
            width: 100% !important;
        }

        body {
            background-color: white !important;
            font-size: 8pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .report-sheet-container {
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: visible !important;
            border: none !important;
            background: white !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        /* Main data table */
        .sheet-table {
            border: 1.5px solid #333 !important;
            width: 100% !important;
            min-width: unset !important;
            table-layout: auto !important;
            font-size: 6.5pt !important;
        }

        .sheet-table th, .sheet-table td {
            border: 1px solid #555 !important;
            padding: 2px 3px !important;
            word-break: break-word;
            white-space: normal !important;
        }

        .sheet-table th {
            font-size: 6pt !important;
        }

        /* ===== Preserve Colors ===== */
        .sheet-table th.section-pagi {
            background-color: #ffe4e6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .sheet-table th.section-sore {
            background-color: #ffe4e6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .sheet-table th {
            background-color: #e0f2fe !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .val-sounding { color: #2563eb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .val-liter, .val-pakai { color: #dc2626 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Show Kapasitas Tangki widget on print */
        .no-print-widget {
            display: block !important;
        }

        /* Signers */
        .sheet-signers {
            page-break-inside: avoid;
        }

        .attachment-page {
            page-break-before: always;
            border-top: 0;
        }

        /* Sections B, C, stats should stay together */
        .section-b-container, .section-c-container, .stats-container {
            page-break-inside: avoid;
        }

        /* Percentage bars */
        .pct-bar-fill {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Keep the four summary cards on one row in the printed report. */
        .report-summary {
            max-width: none !important;
        }

        .report-stats-grid {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 6px !important;
        }

        .report-stats-grid > div {
            padding: 6px 8px !important;
        }

        /* Header logos */
        .sheet-header-container {
            display: grid !important;
            grid-template-columns: 120px 1fr 120px !important;
            align-items: center !important;
            margin-bottom: 0.5rem !important;
            width: 100% !important;
            gap: 1rem !important;
        }

        .sheet-logo-left,
        .sheet-logo-right {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .sheet-logo-left {
            justify-content: flex-start !important;
        }

        .sheet-logo-right {
            justify-content: flex-end !important;
            flex-direction: column !important;
        }

        .sheet-logo-left img,
        .sheet-logo-right img {
            height: 40px !important;
            width: auto !important;
            max-width: 100% !important;
            object-fit: contain !important;
        }

        .sheet-title-area {
            text-align: center !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .sheet-title-area h2 {
            font-size: 12pt !important;
            font-weight: 800 !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
            display: block !important;
            white-space: normal !important;
            word-wrap: break-word !important;
        }
        .sheet-title-area h3 {
            font-size: 8pt !important;
            font-weight: 600 !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
            display: block !important;
            white-space: normal !important;
            word-wrap: break-word !important;
        }

        .sheet-meta-row {
            font-size: 7.5pt !important;
            margin-bottom: 0.3rem !important;
        }
    }
</style>
@endsection

@section('content')
<div class="content-header no-print">
    <div>
        <h1 class="page-title">Detail Laporan Harian</h1>
        <p class="page-subtitle">Site {{ $report->site->name }} | Tanggal: {{ $report->date->format('d-m-Y') }}</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">Kembali</a>
        @if(!Auth::user()->isFuelman())
            <button onclick="window.print()" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Cetak Laporan
            </button>
        @endif
        @if(Auth::user()->isFuelman() && in_array($report->status, ['draft', 'rejected']))
            <a href="{{ route('reports.edit', $report->id) }}" class="btn btn-primary">Ubah Laporan</a>
            <form action="{{ route('reports.submit', $report->id) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success">Kirim ke GL</button>
            </form>
        @endif
        @if((Auth::user()->isFuelman() && $report->fuelman_id === Auth::id() && in_array($report->status, ['draft', 'rejected'])) || Auth::user()->isSpv())
            <form action="{{ route('reports.destroy', $report->id) }}" method="POST" onsubmit="return confirmDelete(event, this);" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus Laporan</button>
            </form>
        @endif
    </div>
</div>

<!-- STATUS CARDS & META -->
<div class="sheet-meta-grid no-print" style="margin-bottom: 1.5rem;">
    <div class="detail-meta-item">
        <span class="detail-meta-label">Status Laporan</span>
        <span class="detail-meta-value">
            @if($report->status === 'draft')
                <span class="badge badge-draft">Draft</span>
            @elseif($report->status === 'submitted')
                <span class="badge badge-submitted">Menunggu Verifikasi GL</span>
            @elseif($report->status === 'verified')
                <span class="badge badge-verified">Menunggu Persetujuan SPV</span>
            @elseif($report->status === 'approved')
                <span class="badge badge-approved">Disetujui (Approved)</span>
            @elseif($report->status === 'rejected')
                <span class="badge badge-rejected">Ditolak / Perlu Revisi</span>
            @endif
        </span>
    </div>
    <div class="detail-meta-item">
        <span class="detail-meta-label">Dibuat Oleh</span>
        <span class="detail-meta-value">{{ $report->fuelman->name }}</span>
    </div>
    <div class="detail-meta-item">
        <span class="detail-meta-label">Diverifikasi Oleh (GL)</span>
        <span class="detail-meta-value">{{ $report->gl ? $report->gl->name : '-' }}</span>
    </div>
    <div class="detail-meta-item">
        <span class="detail-meta-label">Disetujui Oleh (SPV)</span>
        <span class="detail-meta-value">{{ $report->spv ? $report->spv->name : '-' }}</span>
    </div>
</div>

<!-- REJECTION FEEDBACK -->
@if(in_array($report->status, ['rejected']) && ($report->gl_feedback || $report->spv_feedback))
    <div class="feedback-box rejected no-print" style="margin-bottom: 1.5rem;">
        <h3 class="feedback-user">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--danger);">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            Catatan Penolakan / Revisi
            @if($report->gl_feedback && !$report->spv_feedback)
                <span style="font-size: 0.85rem; color: #dc2626; font-weight: 600;">(Ditolak oleh Group Leader)</span>
            @elseif($report->spv_feedback)
                <span style="font-size: 0.85rem; color: #dc2626; font-weight: 600;">(Ditolak oleh Supervisor)</span>
            @endif
        </h3>
        <div class="feedback-content">
            @if($report->gl_feedback)
                <p><strong>Dari Group Leader:</strong> "{{ $report->gl_feedback }}"</p>
            @endif
            @if($report->spv_feedback)
                <p style="margin-top: 0.5rem;"><strong>Dari Supervisor:</strong> "{{ $report->spv_feedback }}"</p>
            @endif
        </div>
    </div>
@endif

<!-- THE MAIN REPORT SHEET -->
<div class="report-sheet-container">
    <div class="sheet-header-container">
        <!-- Logo Left -->
        <div class="sheet-logo-left">
            <img src="{{ asset('logo-pertamina.png') }}" alt="Pertamina Logo" style="height: 40px; width: auto; object-fit: contain;">
        </div>
        
        <!-- Header Text -->
        <div class="sheet-title-area">
            <h2>LAPORAN HARIAN KEGIATAN FUELMAN</h2>
            <h3>WAREHOUSE & INVENTORY SITE {{ strtoupper($report->site->name) }}</h3>
        </div>
        
        <!-- Right text brand -->
        <div class="sheet-logo-right">
            <img src="{{ asset('logo-agm.png') }}" alt="AGM Logo" style="height: 40px; width: auto; object-fit: contain;">
        </div>
    </div>
    
    @php
        $days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $dayName = $days[$report->date->format('l')] ?? $report->date->format('l');
        
        // Group items by tank code to calculate rowspan
        $groupedItems = $report->items->sortBy('tank_id')->groupBy(function($item) {
            return $item->tank->code;
        });
        $noCounter = 1;
    @endphp

    <div class="sheet-meta-row">
        <div class="sheet-meta-item">
            <span>HARI</span>
            <span>:</span>
            <span>{{ $dayName }}</span>
        </div>
        <div class="sheet-meta-item">
            <span>TANGGAL</span>
            <span>:</span>
            <span>{{ $report->date->format('d/m/Y') }}</span>
        </div>
        <div class="sheet-meta-item">
            <span>TAHUN</span>
            <span>:</span>
            <span>{{ $report->date->format('Y') }}</span>
        </div>
    </div>

    <h3 style="margin-top: 1rem; margin-bottom: 0; font-size: 1rem; color: var(--text-primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 0.125rem;">
        A. LAPORAN HARIAN (MAIN TANK)
    </h3>
        <div class="table-responsive">
            <table class="sheet-table" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 40px;">NO</th>
                        <th rowspan="2" style="width: 90px;">KODE TANGKI</th>
                        <th rowspan="2" style="width: 100px;">MAIN HOLE</th>
                        <th colspan="4" class="section-pagi">SONDING PAGI</th>
                        <th colspan="4" class="section-sore">SONDING SORE</th>
                        <th colspan="3" class="section-fm">ANGKA FM KECIL</th>
                        <th rowspan="2" style="width: 180px;">KETERANGAN</th>
                    </tr>
                    <tr>
                        <th class="section-pagi" style="width: 75px;">SONDING (cm)</th>
                        <th class="section-pagi" style="width: 85px;">LITER</th>
                        <th class="section-pagi" style="width: 80px;">JAM</th>
                        <th class="section-pagi" style="width: 105px;">NAMA PETUGAS</th>
                        <th class="section-sore" style="width: 75px;">SONDING (cm)</th>
                        <th class="section-sore" style="width: 85px;">LITER</th>
                        <th class="section-sore" style="width: 80px;">JAM</th>
                        <th class="section-sore" style="width: 105px;">NAMA PETUGAS</th>
                        <th class="section-fm" style="width: 95px;">PAGI</th>
                        <th class="section-fm" style="width: 95px;">SORE</th>
                        <th class="section-fm" style="width: 95px;">JUMLAH PAKAI</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totFmPagi = 0;
                        $totFmSore = 0;
                        $totFmPakai = 0;
                        $summaryTanks = [];
                    @endphp
                    @foreach($groupedItems as $tankCode => $items)
                        @foreach($items as $subIndex => $item)
                            @php
                                $isAvgRow = ($item->tank->code === 'SPM3' && $item->tank->main_hole === '(D+B)/2');
                                if (!$isAvgRow) {
                                    $totFmPagi += $item->fm_pagi ?? 0;
                                    $totFmSore += $item->fm_sore ?? 0;
                                    $totFmPakai += $item->fm_pakai ?? 0;
                                }
                                
                                // Keep track of latest volumes for the side statistics widget
                                if (!isset($summaryTanks[$tankCode])) {
                                    $summaryTanks[$tankCode] = [
                                        'capacity' => $item->tank->capacity ?? 0,
                                        'soh'      => 0
                                    ];
                                }
                                if ($item->tank->main_hole !== 'BELAKANG' && $item->tank->main_hole === 'DEPAN' && $tankCode === 'SPM3') {
                                    // Average will be handled or updated later
                                } elseif ($item->tank->main_hole !== 'BELAKANG' && $item->tank->main_hole !== 'DEPAN') {
                                    $summaryTanks[$tankCode]['soh'] = $item->liter_sore ?? $item->liter_pagi ?? 0;
                                }
                            @endphp
                            <tr class="{{ $isAvgRow ? 'average-row' : '' }}">
                                @if($subIndex === 0)
                                    <td rowspan="{{ count($items) }}" style="text-align: center;">{{ $noCounter++ }}</td>
                                    <td rowspan="{{ count($items) }}" style="text-align: center; font-weight: bold;">{{ $tankCode }}</td>
                                @endif
                                <td style="text-align: center;">{{ $item->tank->main_hole }}</td>
                                
                                <!-- Sounding Pagi -->
                                <td class="val-sounding" style="text-align: center;">
                                    {{ $item->sounding_pagi !== null ? number_format($item->sounding_pagi, 1, ',', '.') : '' }}
                                </td>
                                <td class="val-liter" style="text-align: right; padding-right: 8px;">
                                    @if(Auth::user()->isFuelman())
                                        XXXX
                                    @elseif($item->liter_pagi !== null)
                                        {{ number_format($item->liter_pagi, 0, ',', '.') }}
                                    @else
                                        XXXX
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    {{ $item->jam_pagi ? \Carbon\Carbon::parse($item->jam_pagi)->format('H:i') : '' }}
                                </td>
                                <td style="text-align: center; text-transform: uppercase;">{{ $item->petugas_pagi }}</td>
                                
                                <!-- Sounding Sore -->
                                <td class="val-sounding" style="text-align: center;">
                                    {{ $item->sounding_sore !== null ? number_format($item->sounding_sore, 1, ',', '.') : '' }}
                                </td>
                                <td class="val-liter" style="text-align: right; padding-right: 8px;">
                                    @if(Auth::user()->isFuelman())
                                        XXXX
                                    @elseif($item->liter_sore !== null)
                                        {{ number_format($item->liter_sore, 0, ',', '.') }}
                                    @else
                                        XXXX
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    {{ $item->jam_sore ? \Carbon\Carbon::parse($item->jam_sore)->format('H:i') : '' }}
                                </td>
                                <td style="text-align: center; text-transform: uppercase;">{{ $item->petugas_sore }}</td>
                                
                                <!-- Angka FM Kecil -->
                                <td style="text-align: right; padding-right: 8px;">
                                    {{ $item->fm_pagi !== null ? number_format($item->fm_pagi, 0, ',', '.') : '' }}
                                </td>
                                <td style="text-align: right; padding-right: 8px;">
                                    {{ $item->fm_sore !== null ? number_format($item->fm_sore, 0, ',', '.') : '' }}
                                </td>
                                <td class="val-pakai" style="text-align: right; padding-right: 8px;">
                                        {{ $item->fm_pakai !== null ? number_format($item->fm_pakai, 0, ',', '.') : '0' }}
                                </td>
                                
                                <!-- Keterangan -->
                                <td style="text-align: left; padding-left: 8px;">{{ $item->keterangan }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    @if($report->items->isEmpty())
                        <tr>
                            <td colspan="15" style="text-align: center; color: var(--text-muted); font-style: italic; padding: 10px;">
                                Tidak ada data laporan harian (main tank).
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if(false)
        <!-- Right Capacity Statistics Widget -->
        @php
            // Real DB Capacities
            $spm1Cap = $report->items->where('tank.code', 'SPM1')->first()?->tank?->capacity ?? 0;
            $spm2Cap = $report->items->where('tank.code', 'SPM2')->first()?->tank?->capacity ?? 0;
            $spm3Cap = $report->items->where('tank.code', 'SPM3')->first()?->tank?->capacity ?? 0;
            $ft05Cap = $report->items->where('tank.code', 'FT05')->first()?->tank?->capacity ?? 0;

            // Saved SOH
            $sohSpm1 = $report->soh_spm1 ?? 0;
            $sohSpm2 = $report->soh_spm2 ?? 0;
            $sohSpm3 = $report->soh_spm3 ?? 0;
            $sohFt05 = $report->soh_ft05 ?? 0;
            $rataSpm1 = $report->rata_spm1 ?? 0;
            $rataSpm2 = $report->rata_spm2 ?? 0;
            $rataSpm3 = $report->rata_spm3 ?? 0;
            $rataFt05 = $report->rata_ft05 ?? 0;

            // Free Space (Bisa Masuk)
            $freeSpm1 = max(0, $spm1Cap - $sohSpm1);
            $freeSpm2 = max(0, $spm2Cap - $sohSpm2);
            $freeSpm3 = max(0, $spm3Cap - $sohSpm3);
            $freeFt05 = max(0, $ft05Cap - $sohFt05);

            // Group calculations
            $totSpmCap = $spm1Cap + $spm2Cap + $spm3Cap;
            $totSpmSoh = $sohSpm1 + $sohSpm2 + $sohSpm3;
            $totSpmFree = $freeSpm1 + $freeSpm2 + $freeSpm3;
            $totSpmUsed = $rataSpm1 + $rataSpm2 + $rataSpm3;
            $ftUsed = $rataFt05;

            $grandTotalCap = $totSpmCap + $ft05Cap;
            $grandTotalSoh = $totSpmSoh + $sohFt05;
            $grandTotalFree = $totSpmFree + $freeFt05;
            $grandTotalUsed = $totSpmUsed + $ftUsed;
        @endphp
        <!-- Kapasitas Tangki Widget (Below Section A) -->
        <h3 style="margin-top: 1rem; margin-bottom: 0; font-size: 1rem; color: var(--text-primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 0.125rem;">
            B. KAPASITAS TANGKI
        </h3>
       
        <div class="table-responsive">
            <table class="sheet-table" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th colspan="2" rowspan="2" style="vertical-align: middle;">KAPASITAS TANGKI</th>
                        <th rowspan="1">SOH</th>
                        <th rowspan="1">BISA MASUK</th>
                        <th rowspan="2" style="vertical-align: middle;">RATA RATA<br>PER DAY<br>USED</th>
                    </tr>
                    <tr>
                        <th>UPDATE</th>
                        <th>SOLAR QTY</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- SPM1 -->
                    <tr>
                        <td style="text-align: center; width: 15%;">SPM1</td>
                        <td style="text-align: right; padding-right: 8px; width: 15%;">{{ number_format($spm1Cap, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px; width: 18%;">{{ $sohSpm1 > 0 ? number_format($sohSpm1, 0, ',', '.') : '-' }}</td>
                        <td style="text-align: right; padding-right: 8px; width: 18%;">{{ number_format($freeSpm1, 0, ',', '.') }}</td>
                        <!-- Rata Rata SPM1 -->
                        <td style="text-align: right; padding-right: 8px; vertical-align: middle; font-weight: bold; width: 18%;">
                            {{ $rataSpm1 > 0 ? number_format($rataSpm1, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <!-- SPM2 -->
                    <tr>
                        <td style="text-align: center;">SPM2</td>
                        <td style="text-align: right; padding-right: 8px;">{{ number_format($spm2Cap, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ $sohSpm2 > 0 ? number_format($sohSpm2, 0, ',', '.') : '-' }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ number_format($freeSpm2, 0, ',', '.') }}</td>
                        <!-- Rata Rata SPM2 -->
                        <td style="text-align: right; padding-right: 8px; vertical-align: middle; font-weight: bold;">
                            {{ $rataSpm2 > 0 ? number_format($rataSpm2, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <!-- SPM3 -->
                    <tr>
                        <td style="text-align: center;">SPM3</td>
                        <td style="text-align: right; padding-right: 8px;">{{ number_format($spm3Cap, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ $sohSpm3 > 0 ? number_format($sohSpm3, 0, ',', '.') : '-' }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ number_format($freeSpm3, 0, ',', '.') }}</td>
                        <!-- Rata Rata SPM3 -->
                        <td style="text-align: right; padding-right: 8px; vertical-align: middle; font-weight: bold;">
                            {{ $rataSpm3 > 0 ? number_format($rataSpm3, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <!-- FT05 -->
                    <tr>
                        <td style="text-align: center;">FT05</td>
                        <td style="text-align: right; padding-right: 8px;">{{ number_format($ft05Cap, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ $sohFt05 > 0 ? number_format($sohFt05, 0, ',', '.') : '-' }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ number_format($freeFt05, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px; vertical-align: middle; font-weight: bold;">
                            {{ $rataFt05 > 0 ? number_format($rataFt05, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <!-- Total Summary Row -->
                    <tr style="font-weight: bold; background-color: #e0f2fe;">
                        <td style="text-align: center;">TOTAL</td>
                        <td style="text-align: right; padding-right: 8px;">{{ number_format($grandTotalCap, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ $grandTotalSoh > 0 ? number_format($grandTotalSoh, 0, ',', '.') : '-' }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ number_format($grandTotalFree, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ $grandTotalUsed > 0 ? number_format($grandTotalUsed, 0, ',', '.') : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

    <!-- SECTION B. TRANSFER SOLAR -->
    <h3 style="margin-top: 1rem; margin-bottom: 0; font-size: 1rem; color: var(--text-primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 0.125rem;">
        B. TRANSFER SOLAR
    </h3>
    <div class="table-responsive">
        <table class="sheet-table" style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 40px;">NO</th>
                    <th colspan="2">TRANSFER SOLAR</th>
                    <th colspan="3">SONDING TANGKI SPM</th>
                    <th rowspan="2" style="width: 85px;">LITER</th>
                    <th colspan="3">SONDING TANGKI FT</th>
                    <th rowspan="2" style="width: 85px;">LITER</th>
                    <th colspan="3">FLOW METER</th>
                    <th colspan="2">JAM TRANSFER</th>
                    <th rowspan="2" style="width: 90px;">LAMA TRANSFER</th>
                </tr>
                <tr>
                    <th>DARI</th>
                    <th>KE</th>
                    <th>AWAL</th>
                    <th>AKHIR</th>
                    <th>HASIL</th>
                    <th>AWAL</th>
                    <th>AKHIR</th>
                    <th>HASIL</th>
                    <th>AWAL</th>
                    <th>AKHIR</th>
                    <th>JUMLAH</th>
                    <th>MULAI</th>
                    <th>SELESAI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->transfers as $index => $transfer)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $transfer->dari_tangki }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $transfer->ke_tangki }}</td>
                        
                        <!-- SPM -->
                        <td style="text-align: center;">{{ $transfer->spm_awal !== null ? number_format($transfer->spm_awal, 1, ',', '.') : '' }}</td>
                        <td style="text-align: center;">{{ $transfer->spm_akhir !== null ? number_format($transfer->spm_akhir, 1, ',', '.') : '' }}</td>
                        <td style="text-align: center; font-weight: 500;">{{ $transfer->spm_hasil !== null ? number_format($transfer->spm_hasil, 1, ',', '.') : '' }}</td>
                        <td class="val-liter" style="text-align: right; padding-right: 8px;">
                            @if(Auth::user()->isFuelman())
                                XXXX
                            @elseif($transfer->spm_liter !== null)
                                {{ number_format($transfer->spm_liter, 0, ',', '.') }}
                            @else
                                XXXX
                            @endif
                        </td>
                        
                        <!-- FT -->
                        <td style="text-align: center;">{{ $transfer->ft_awal !== null ? number_format($transfer->ft_awal, 1, ',', '.') : '' }}</td>
                        <td style="text-align: center;">{{ $transfer->ft_akhir !== null ? number_format($transfer->ft_akhir, 1, ',', '.') : '' }}</td>
                        <td style="text-align: center; font-weight: 500;">{{ $transfer->ft_hasil !== null ? number_format($transfer->ft_hasil, 1, ',', '.') : '' }}</td>
                        <td class="val-liter" style="text-align: right; padding-right: 8px;">
                            @if(Auth::user()->isFuelman())
                                XXXX
                            @elseif($transfer->ft_liter !== null)
                                {{ number_format($transfer->ft_liter, 0, ',', '.') }}
                            @else
                                XXXX
                            @endif
                        </td>
                        
                        <!-- FM -->
                        <td style="text-align: right; padding-right: 8px;">{{ $transfer->fm_awal !== null ? number_format($transfer->fm_awal, 0, ',', '.') : '' }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ $transfer->fm_akhir !== null ? number_format($transfer->fm_akhir, 0, ',', '.') : '' }}</td>
                        <td class="val-pakai" style="text-align: right; padding-right: 8px; font-weight: bold;">
                            {{ $transfer->fm_jumlah !== null ? number_format($transfer->fm_jumlah, 0, ',', '.') : '' }}
                        </td>
                        
                        <!-- Time -->
                        <td style="text-align: center;">{{ $transfer->jam_mulai ? \Carbon\Carbon::parse($transfer->jam_mulai)->format('H:i') : '' }}</td>
                        <td style="text-align: center;">{{ $transfer->jam_selesai ? \Carbon\Carbon::parse($transfer->jam_selesai)->format('H:i') : '' }}</td>
                        <td style="text-align: center;">{{ $transfer->lama_transfer }}</td>
                    </tr>
                @endforeach
                @if($report->transfers->isEmpty())
                    <tr>
                        <td colspan="17" style="text-align: center; color: var(--text-muted); font-style: italic; padding: 10px;">
                            Tidak ada data transfer solar.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- SECTION C. PEMAKAIAN FLOWMETER -->
    <h3 style="margin-top: 1rem; margin-bottom: 0; font-size: 1rem; color: var(--text-primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 0.125rem;">
        C. PEMAKAIAN FLOWMETER
    </h3>
    <div class="table-responsive">
        <table class="sheet-table" style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr>
                    <th style="width: 40px;">NO</th>
                    <th>UNIT</th>
                    <th>JENIS<br>FLOWMETER</th>
                    <th>NOMOR<br>SERI</th>
                    <th>FLOWMETER<br>AWAL PAGI</th>
                    <th>FLOWMETER<br>AKHIR SORE</th>
                    <th>JUMLAH<br>PAKAI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->flowmeters as $index => $flowmeter)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $flowmeter->unit }}</td>
                        <td style="text-align: center;">{{ $flowmeter->jenis_flowmeter }}</td>
                        <td style="text-align: center;">{{ $flowmeter->nomor_seri }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ $flowmeter->awal_pagi !== null ? number_format($flowmeter->awal_pagi, 0, ',', '.') : '' }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ $flowmeter->akhir_sore !== null ? number_format($flowmeter->akhir_sore, 0, ',', '.') : '' }}</td>
                        <td class="val-pakai" style="text-align: right; padding-right: 8px; font-weight: bold;">
                            {{ $flowmeter->jumlah_pakai !== null ? number_format($flowmeter->jumlah_pakai, 0, ',', '.') : '' }}
                        </td>
                    </tr>
                @endforeach
                @if($report->flowmeters->count() === 0)
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); font-style: italic; padding: 10px;">
                            Tidak ada data pemakaian flowmeter.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if(false)
    <!-- BOTTOM STATISTICS SECTION -->
    @php
        // Calculate SELISIH SONDING and TOTAL PENGELUARAN for MT (SPM) and FT groups
        $soundingConsumptionMT = 0; // sounding-based: sum(liter_pagi - liter_sore) for SPM tanks
        $soundingConsumptionFT = 0;
        $totalPengeluaranMT = 0;    // FM-based: sum(fm_pakai) for SPM tanks  
        $totalPengeluaranFT = 0;
        
        foreach($report->items as $item) {
            if ($item->tank->main_hole === '(D+B)/2') continue;
            if ($item->tank->main_hole === 'DEPAN' || $item->tank->main_hole === 'BELAKANG') continue;
            
            $code = $item->tank->code;
            $soundingDiff = 0;
            if ($item->liter_pagi !== null && $item->liter_sore !== null) {
                $soundingDiff = abs($item->liter_pagi - $item->liter_sore);
            }
            $fmUsage = $item->fm_pakai ?? 0;
            
            if (str_starts_with($code, 'SPM')) {
                $soundingConsumptionMT += $soundingDiff;
                $totalPengeluaranMT += $fmUsage;
            } else {
                $soundingConsumptionFT += $soundingDiff;
                $totalPengeluaranFT += $fmUsage;
            }
        }
        
        $selisihMT = abs($soundingConsumptionMT - $totalPengeluaranMT);
        $selisihFT = abs($soundingConsumptionFT - $totalPengeluaranFT);
        
        $pctSelisihMT = $totalPengeluaranMT > 0 ? round(($selisihMT / $totalPengeluaranMT) * 100, 2) : 0;
        $pctActualMT = 100 - $pctSelisihMT;
        $pctSelisihFT = $totalPengeluaranFT > 0 ? round(($selisihFT / $totalPengeluaranFT) * 100, 2) : 0;
        $pctActualFT = 100 - $pctSelisihFT;
    @endphp
    <div class="report-summary" style="margin-top: 2rem; max-width: 700px;">
        <!-- Stats Cards Grid -->
        <div class="report-stats-grid" style="display: grid; gap: 12px; margin-bottom: 1.5rem;">
            <!-- Selisih Sonding MT -->
            <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; background: linear-gradient(135deg, #eff6ff, #f8fafc);">
                <div style="font-size: 7.5pt; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Selisih Sonding MT</div>
                <div style="font-size: 16pt; font-weight: 800; color: #1e40af; margin-top: 2px;">{{ $totalPengeluaranMT > 0 ? number_format($selisihMT, 0, ',', '.') : '-' }}</div>
            </div>
            <!-- Selisih Sonding FT -->
            <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; background: linear-gradient(135deg, #fefce8, #f8fafc);">
                <div style="font-size: 7.5pt; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Selisih Sonding FT</div>
                <div style="font-size: 16pt; font-weight: 800; color: #92400e; margin-top: 2px;">{{ $totalPengeluaranFT > 0 ? number_format($selisihFT, 0, ',', '.') : '-' }}</div>
            </div>
            <!-- Total Pengeluaran MT -->
            <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; background: linear-gradient(135deg, #f0fdf4, #f8fafc);">
                <div style="font-size: 7.5pt; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Pengeluaran MT</div>
                <div style="font-size: 16pt; font-weight: 800; color: #166534; margin-top: 2px;">{{ number_format($totalPengeluaranMT, 0, ',', '.') }}</div>
            </div>
            <!-- Total Pengeluaran FT -->
            <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; background: linear-gradient(135deg, #fdf2f8, #f8fafc);">
                <div style="font-size: 7.5pt; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Pengeluaran FT</div>
                <div style="font-size: 16pt; font-weight: 800; color: #9d174d; margin-top: 2px;">{{ number_format($totalPengeluaranFT, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Persentase Selisih Bars -->
        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
            <div style="padding: 10px 16px; background-color: #f8fafc; border-bottom: 1px solid #e5e7eb; font-weight: 700; font-size: 8.5pt; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">
                PERSENTASE SELISIH MANUAL & ACTUAL
            </div>
            <div style="padding: 14px 16px;">
                <!-- MT Bar -->
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="font-weight: 700; font-size: 8pt; color: #1e293b;">MT (Main Tank)</span>
                        <span style="font-weight: 600; font-size: 8pt; color: #64748b;">
                            Selisih: {{ number_format($pctSelisihMT, 2, ',', '.') }}% | Actual: {{ number_format($pctActualMT, 2, ',', '.') }}%
                        </span>
                    </div>
                    <div style="height: 28px; background-color: #f1f5f9; border-radius: 6px; overflow: hidden; display: flex;">
                        <div style="background: linear-gradient(90deg, #d97706, #f59e0b); color: white; font-weight: 700; font-size: 8pt; 
                                    display: flex; align-items: center; justify-content: center;
                                    min-width: 60px; height: 100%; padding: 0 10px; border-radius: 6px 0 0 6px;
                                    white-space: nowrap; overflow: hidden;
                                    width: {{ max($pctActualMT, 10) }}%;">
                            {{ number_format($pctActualMT, 2, ',', '.') }}% Actual
                        </div>
                    </div>
                </div>
                <!-- FT Bar -->
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="font-weight: 700; font-size: 8pt; color: #1e293b;">FT (Fuel Truck)</span>
                        <span style="font-weight: 600; font-size: 8pt; color: #64748b;">
                            Selisih: {{ number_format($pctSelisihFT, 2, ',', '.') }}% | Actual: {{ number_format($pctActualFT, 2, ',', '.') }}%
                        </span>
                    </div>
                    <div style="height: 28px; background-color: #f1f5f9; border-radius: 6px; overflow: hidden; display: flex;">
                        <div style="background: linear-gradient(90deg, #ca8a04, #eab308); color: white; font-weight: 700; font-size: 8pt;
                                    display: flex; align-items: center; justify-content: center;
                                    min-width: 60px; height: 100%; padding: 0 10px; border-radius: 6px 0 0 6px;
                                    white-space: nowrap; overflow: hidden;
                                    width: {{ max($pctActualFT, 10) }}%;">
                            {{ number_format($pctActualFT, 2, ',', '.') }}% Actual
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif
    <!-- TANDA TANGAN (Bottom, Right-Aligned) -->
    <div style="display: flex; justify-content: flex-end; margin-top: 2.5rem; padding-top: 1rem;" class="sheet-signers">
        <div style="display: flex; gap: 2rem; font-size: 9pt;">
            <div style="text-align: center; width: 180px;">
                <p style="font-weight: 600; color: #475569; margin-bottom: 0;">Dibuat Oleh</p>
                <div style="height: 60px;"></div>
                @if($report->fuelman)
                    <p style="text-decoration: underline; font-weight: 700; margin: 0;">( {{ $report->fuelman->name }} )</p>
                    <p style="font-size: 7.5pt; color: #94a3b8; margin-top: 2px;">{{ $report->fuelman->employee_id ?? '-' }}</p>
                @else
                    <p style="font-weight: 700; margin: 0;">(...........)</p>
                @endif
            </div>
            <div style="text-align: center; width: 180px;">
                <p style="font-weight: 600; color: #475569; margin-bottom: 0;">Diperiksa Oleh</p>
                <div style="height: 60px;"></div>
                @if($report->gl)
                    <p style="text-decoration: underline; font-weight: 700; margin: 0;">( {{ $report->gl->name }} )</p>
                    <p style="font-size: 7.5pt; color: #94a3b8; margin-top: 2px;">{{ $report->gl->employee_id ?? '-' }}</p>
                @else
                    <p style="font-weight: 700; margin: 0;">(...........)</p>
                @endif
            </div>
            <div style="text-align: center; width: 180px;">
                <p style="font-weight: 600; color: #475569; margin-bottom: 0;">Disetujui Oleh</p>
                <div style="height: 60px;"></div>
                @if($report->spv)
                    <p style="text-decoration: underline; font-weight: 700; margin: 0;">( {{ $report->spv->name }} )</p>
                    <p style="font-size: 7.5pt; color: #94a3b8; margin-top: 2px;">{{ $report->spv->employee_id ?? '-' }}</p>
                @else
                    <p style="font-weight: 700; margin: 0;">(...........)</p>
                @endif
            </div>
        </div>
    </div>

    @if($report->attachments->isNotEmpty())
        <section class="attachment-page">
            <h2 style="margin: 0 0 0.25rem; font-size: 14pt;">LAMPIRAN FOTO</h2>
            <p style="margin: 0 0 1rem; color: #64748b;">Dokumentasi Laporan Harian Fuelman</p>
            
            @php
                // Group by section and attachment_key to get photos for same context
                $groupedAttachments = $report->attachments
                    ->sortBy(fn ($attachment) => $attachment->section . ':' . $attachment->attachment_key)
                    ->groupBy(fn ($attachment) => $attachment->section . ':' . $attachment->attachment_key);
            @endphp
            
            @foreach($groupedAttachments as $groupKey => $photos)
                @php
                    $firstPhoto = $photos->first();
                    $photoCount = $photos->count();
                @endphp
                
                <div class="photo-card" style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 1rem; background: white; margin-bottom: 1rem;">
                    <!-- Header untuk group foto ini -->
                    <div style="margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                        <div style="font-size: 10pt; font-weight: 700; color: #1e293b;">
                            Bagian {{ $firstPhoto->section }} — {{ $firstPhoto->section === 'A' ? 'Laporan Harian Main Tank' : 'Transfer Solar' }}
                        </div>
                        <div style="font-size: 8.5pt; color: #475569; margin-top: 0.25rem;">
                            {{ $firstPhoto->context }}
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        @foreach($photos as $index => $attachment)
                            <div class="photo-item" style="flex: 1;">
                                <div style="font-size: 8.5pt; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">
                                    Foto {{ $index + 1 }}
                                </div>
                                @php
                                    $diskName = config('filesystems.report_attachment_disk', 'public');
                                    $imageUrl = Storage::disk($diskName)->url($attachment->path);
                                @endphp
                                <img src="{{ $imageUrl }}" alt="Foto {{ $index + 1 }}" style="display: block; width: 100%; height: 280px; object-fit: contain; border-radius: 6px; border: 1px solid #e2e8f0; background: #f8fafc;">
                            </div>
                        @endforeach
                        
                        @if($photoCount === 1)
                            <div style="flex: 1;"></div>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>
    @endif
</div>

<!-- WORKFLOW VERIFICATION / APPROVAL PANELS -->
@if(Auth::user()->isGl() && $report->status === 'submitted')
    <div class="card-table-container no-print" style="margin-top: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
            <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div>
                <h2 class="card-title" style="margin: 0;">Panel Verifikasi Group Leader</h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0 0;">Periksa dan verifikasi laporan harian yang diajukan oleh Fuelman</p>
            </div>
        </div>
        <form action="{{ route('reports.verify', $report->id) }}" method="POST" id="verifyForm">
            @csrf
            <div class="form-group">
                <label for="feedback">Catatan / Komentar (Wajib jika menolak/revisi)</label>
                <textarea name="feedback" id="gl_feedback" rows="4" class="form-control" placeholder="Masukkan catatan atau masukan revisi jika laporan perlu diperbaiki..."></textarea>
            </div>
            <div style="display: flex; gap: 0.75rem; padding-top: 0.5rem;">
                <button type="button" onclick="confirmVerifyAction('approve')" class="btn btn-success" style="flex: 1;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Verifikasi & Setujui
                </button>
                <button type="button" onclick="confirmVerifyAction('reject')" class="btn btn-danger" style="flex: 1;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Kembalikan untuk Revisi
                </button>
            </div>
            <input type="hidden" name="action" id="verifyAction" value="">
        </form>
    </div>
@endif

@if(Auth::user()->isSpv() && $report->status === 'verified')
    <div class="card-table-container no-print" style="margin-top: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
            <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
            </div>
            <div>
                <h2 class="card-title" style="margin: 0;">Panel Persetujuan Supervisor</h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0 0;">Berikan persetujuan final untuk laporan yang telah diverifikasi GL</p>
            </div>
        </div>
        <form action="{{ route('reports.approve', $report->id) }}" method="POST" id="approveForm">
            @csrf
            <div class="form-group">
                <label for="feedback">Catatan / Komentar (Wajib jika menolak/revisi)</label>
                <textarea name="feedback" id="spv_feedback" rows="4" class="form-control" placeholder="Masukkan catatan atau masukan revisi jika laporan perlu diperbaiki..."></textarea>
            </div>
            <div style="display: flex; gap: 0.75rem; padding-top: 0.5rem;">
                <button type="button" onclick="confirmApproveAction('approve')" class="btn btn-success" style="flex: 1;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    Approve Laporan (Final)
                </button>
                <button type="button" onclick="confirmApproveAction('reject')" class="btn btn-danger" style="flex: 1;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Kembalikan untuk Revisi
                </button>
            </div>
            <input type="hidden" name="action" id="approveAction" value="">
        </form>
    </div>
@endif

<!-- Verification Confirmation Modal -->
<div id="verifyConfirmModal" class="custom-modal-overlay no-print">
    <div class="custom-modal-content">
        <h3 class="custom-modal-title" id="verifyModalTitle">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span id="verifyModalTitleText">Konfirmasi Verifikasi</span>
        </h3>
        <p class="custom-modal-text" id="verifyModalMessage">Apakah Anda yakin ingin memverifikasi laporan ini?</p>
        <div class="custom-modal-actions">
            <button type="button" class="btn btn-secondary" style="padding: 0.5rem 1rem;" onclick="closeVerifyConfirm()">Batal</button>
            <button type="button" class="btn" id="btnConfirmVerify" style="padding: 0.5rem 1rem;">Lanjutkan</button>
        </div>
    </div>
</div>

<script>
function showSnackbar(message, type = 'error') {
    const snackbar = document.getElementById('snackbar');
    snackbar.textContent = message;
    snackbar.className = 'snackbar show ' + type;
    
    setTimeout(function() {
        snackbar.className = snackbar.className.replace('show', '');
    }, 3000);
}

let currentFormToSubmit = null;

function confirmVerifyAction(action) {
    const feedback = document.getElementById('gl_feedback');
    const feedbackValue = feedback ? feedback.value.trim() : '';
    
    // Validate feedback for reject action
    if (action === 'reject' && feedbackValue === '') {
        showSnackbar('Catatan / komentar wajib diisi saat menolak atau meminta revisi!', 'warning');
        feedback.focus();
        return false;
    }
    
    // Set form to submit
    currentFormToSubmit = 'verify';
    
    // Show confirmation modal
    const modal = document.getElementById('verifyConfirmModal');
    const modalTitle = document.getElementById('verifyModalTitleText');
    const modalMessage = document.getElementById('verifyModalMessage');
    const confirmBtn = document.getElementById('btnConfirmVerify');
    const titleIcon = modal.querySelector('.custom-modal-title svg');
    
    if (action === 'approve') {
        titleIcon.style.color = '#10b981';
        modalTitle.textContent = 'Konfirmasi Verifikasi';
        modalMessage.textContent = 'Apakah Anda yakin ingin memverifikasi dan menyetujui laporan ini? Laporan akan diteruskan ke Supervisor.';
        confirmBtn.className = 'btn btn-success';
        confirmBtn.style.padding = '0.5rem 1rem';
        confirmBtn.textContent = 'Ya, Verifikasi';
    } else {
        titleIcon.style.color = '#ef4444';
        modalTitle.textContent = 'Konfirmasi Penolakan';
        modalMessage.textContent = 'Apakah Anda yakin ingin menolak laporan ini? Laporan akan dikembalikan ke Fuelman untuk revisi.';
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.style.padding = '0.5rem 1rem';
        confirmBtn.textContent = 'Ya, Tolak';
    }
    
    document.getElementById('verifyAction').value = action;
    modal.classList.add('active');
}

function confirmApproveAction(action) {
    const feedback = document.getElementById('spv_feedback');
    const feedbackValue = feedback ? feedback.value.trim() : '';
    
    // Validate feedback for reject action
    if (action === 'reject' && feedbackValue === '') {
        showSnackbar('Catatan / komentar wajib diisi saat menolak atau meminta revisi!', 'warning');
        feedback.focus();
        return false;
    }
    
    // Set form to submit
    currentFormToSubmit = 'approve';
    
    // Show confirmation modal
    const modal = document.getElementById('verifyConfirmModal');
    const modalTitle = document.getElementById('verifyModalTitleText');
    const modalMessage = document.getElementById('verifyModalMessage');
    const confirmBtn = document.getElementById('btnConfirmVerify');
    const titleIcon = modal.querySelector('.custom-modal-title svg');
    
    if (action === 'approve') {
        titleIcon.style.color = '#10b981';
        modalTitle.textContent = 'Konfirmasi Approval';
        modalMessage.textContent = 'Apakah Anda yakin ingin menyetujui laporan ini? Ini adalah persetujuan final dan laporan akan berstatus Approved.';
        confirmBtn.className = 'btn btn-success';
        confirmBtn.style.padding = '0.5rem 1rem';
        confirmBtn.textContent = 'Ya, Approve';
    } else {
        titleIcon.style.color = '#ef4444';
        modalTitle.textContent = 'Konfirmasi Penolakan';
        modalMessage.textContent = 'Apakah Anda yakin ingin menolak laporan ini? Laporan akan dikembalikan ke Fuelman untuk revisi.';
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.style.padding = '0.5rem 1rem';
        confirmBtn.textContent = 'Ya, Tolak';
    }
    
    document.getElementById('approveAction').value = action;
    modal.classList.add('active');
}

function closeVerifyConfirm() {
    const modal = document.getElementById('verifyConfirmModal');
    modal.classList.remove('active');
    currentFormToSubmit = null;
}

function submitVerifyForm() {
    if (currentFormToSubmit === 'verify') {
        const form = document.getElementById('verifyForm');
        if (form) {
            form.submit();
        }
    } else if (currentFormToSubmit === 'approve') {
        const form = document.getElementById('approveForm');
        if (form) {
            form.submit();
        }
    }
    closeVerifyConfirm();
}

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Confirm button click handler
    const btnConfirm = document.getElementById('btnConfirmVerify');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', submitVerifyForm);
    }
    
    // Close modal on outside click
    const modal = document.getElementById('verifyConfirmModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === this) {
                closeVerifyConfirm();
            }
        });
    }
});
</script>
@endsection
