@extends('layouts.app')

@section('title', 'Rekap & Analisis Penggunaan')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary);
    }

    .stat-card.stat-success::before {
        background: var(--success);
    }

    .stat-card.stat-info::before {
        background: var(--info);
    }

    .stat-card.stat-warning::before {
        background: var(--warning);
    }

    .stat-card.stat-danger::before {
        background: var(--danger);
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        line-height: 1.2;
    }

    .stat-unit {
        font-size: 0.9rem;
        color: var(--text-muted);
        font-weight: normal;
    }

    .stat-comparison {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }

    .stat-comparison.trend-up {
        color: var(--danger);
    }

    .stat-comparison.trend-down {
        color: var(--success);
    }

    .stat-comparison.trend-stable {
        color: var(--text-muted);
    }

    .usage-chart {
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .usage-chart-title {
        margin: 0 0 1.25rem;
        font-size: 1.1rem;
        color: var(--text-primary);
    }

    .usage-chart-subtitle {
        margin: -0.85rem 0 1.25rem;
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    .usage-chart-bars {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .usage-chart-item {
        min-width: 0;
    }

    .usage-chart-value {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        text-align: center;
    }

    .usage-chart-plot {
        position: relative;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        height: 180px;
        padding: 0 0.75rem;
        padding-top: 2rem;
        border-bottom: 2px solid var(--border-color);
        background: repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent 44px,
            var(--border-color) 45px
        );
    }

    .usage-chart-bar {
        width: min(72%, 68px);
        min-height: 4px;
        border-radius: 8px 8px 2px 2px;
        background: linear-gradient(180deg, var(--primary), var(--info));
        transition: height 180ms ease;
        position: relative;
    }

    .usage-chart-bar::after {
        content: attr(data-percentage);
        position: absolute;
        top: -2rem;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.8rem;
        color: var(--text-primary);
        font-weight: 700;
        background: var(--bg-secondary);
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        white-space: nowrap;
        z-index: 10;
    }

    .usage-chart-zero {
        width: 12px;
        height: 12px;
        margin-bottom: -7px;
        border: 3px solid var(--info);
        border-radius: 50%;
        background: var(--bg-secondary);
    }

    .usage-chart-label {
        display: block;
        margin-top: 0.75rem;
        text-align: right;
        font-weight: 600;
        color: var(--text-primary);
        text-align: center;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .detail-table th {
        background: linear-gradient(180deg, var(--bg-primary), var(--bg-secondary));
        font-weight: 700;
        text-align: left;
        padding: 1rem;
        border-bottom: 2px solid var(--border-color);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-secondary);
    }

    .detail-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.95rem;
    }
    
    .detail-table tbody tr:nth-child(even) {
        background: var(--bg-primary);
    }

    .detail-table tbody tr:hover {
        background: rgba(13, 148, 136, 0.05);
        transition: background 0.15s ease;
    }
    
    .detail-table tbody tr:last-child {
        background: var(--bg-primary);
        font-weight: 700;
        border-top: 2px solid var(--border-color);
    }
    
    .detail-table tbody tr:last-child td {
        border-bottom: none;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    .detail-table tbody tr:last-child:hover {
        background: var(--bg-primary);
    }

    .progress-bar-container {
        width: 100%;
        height: 8px;
        background: var(--bg-primary);
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.25rem;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--info), var(--primary));
        transition: width 0.3s ease;
        border-radius: 4px;
    }

    @media (max-width: 640px) {
        .usage-chart-bars {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Rekap & Analisis Penggunaan BBM</h1>
        <p class="page-subtitle">Ringkasan total pemakaian BBM berdasarkan data laporan yang disetujui (Approved).</p>
    </div>
</div>

<div class="card-table-container" style="margin-bottom: 1.5rem;">
    <form method="GET" action="{{ route('reports.analytics') }}" class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div class="form-group" style="margin-bottom: 0;">
            <label for="site_id">Site / Lokasi <span style="color: var(--danger);">*</span></label>
            <select name="site_id" id="site_id" class="form-control" required>
                <option value="">Pilih Site</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ $siteId == $site->id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label for="month">Bulan <span style="color: var(--danger);">*</span></label>
            <select name="month" id="month" class="form-control" required>
                <option value="">Pilih Bulan</option>
                <option value="1" {{ $month == '1' ? 'selected' : '' }}>Januari</option>
                <option value="2" {{ $month == '2' ? 'selected' : '' }}>Februari</option>
                <option value="3" {{ $month == '3' ? 'selected' : '' }}>Maret</option>
                <option value="4" {{ $month == '4' ? 'selected' : '' }}>April</option>
                <option value="5" {{ $month == '5' ? 'selected' : '' }}>Mei</option>
                <option value="6" {{ $month == '6' ? 'selected' : '' }}>Juni</option>
                <option value="7" {{ $month == '7' ? 'selected' : '' }}>Juli</option>
                <option value="8" {{ $month == '8' ? 'selected' : '' }}>Agustus</option>
                <option value="9" {{ $month == '9' ? 'selected' : '' }}>September</option>
                <option value="10" {{ $month == '10' ? 'selected' : '' }}>Oktober</option>
                <option value="11" {{ $month == '11' ? 'selected' : '' }}>November</option>
                <option value="12" {{ $month == '12' ? 'selected' : '' }}>Desember</option>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label for="year">Tahun <span style="color: var(--danger);">*</span></label>
            <select name="year" id="year" class="form-control" required>
                <option value="">Pilih Tahun</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0; display: flex; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                Terapkan
            </button>
        </div>
    </form>
</div>

@php
    $maxUsage = max(1, (float) $usageData->max('total_pakai'));
    $hasFilters = $siteId && $month && $year;
    
    // Month names
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
@endphp

@if($hasFilters && $summaryStats)
<!-- Summary Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card stat-info">
        <div class="stat-label">Total Pemakaian BBM</div>
        <div class="stat-value">
            {{ number_format($summaryStats['total_usage'], 0, ',', '.') }}
            <span class="stat-unit">Liter</span>
        </div>
        @if($previousMonthComparison)
            <div class="stat-comparison trend-{{ $previousMonthComparison['trend'] }}">
                @if($previousMonthComparison['trend'] == 'up')
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                    {{ number_format(abs($previousMonthComparison['percentage']), 1) }}% lebih tinggi
                @elseif($previousMonthComparison['trend'] == 'down')
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    {{ number_format(abs($previousMonthComparison['percentage']), 1) }}% lebih rendah
                @else
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Stabil
                @endif
                dari bulan lalu
            </div>
        @endif
    </div>

    <div class="stat-card stat-success">
        <div class="stat-label">Rata-rata per Hari</div>
        <div class="stat-value">
            {{ number_format($summaryStats['avg_daily_usage'], 1, ',', '.') }}
            <span class="stat-unit">Liter</span>
        </div>
        <div class="stat-comparison" style="color: var(--text-muted); border-top: none; padding-top: 0;">
            Dari {{ $summaryStats['total_reports'] }} laporan disetujui
        </div>
    </div>

    <div class="stat-card stat-warning">
        <div class="stat-label">Jumlah Tangki</div>
        <div class="stat-value">
            {{ $summaryStats['tank_count'] }}
            <span class="stat-unit">Tangki</span>
        </div>
        <div class="stat-comparison" style="color: var(--text-muted); border-top: none; padding-top: 0;">
            Aktif digunakan bulan ini
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Periode Laporan</div>
        <div class="stat-value" style="font-size: 1.5rem;">
            {{ $monthNames[$month] }} {{ $year }}
        </div>
        <div class="stat-comparison" style="color: var(--text-muted); border-top: none; padding-top: 0;">
            {{ $summaryStats['total_reports'] }} dari {{ $summaryStats['days_in_month'] }} hari
        </div>
    </div>
</div>
@endif
<div class="card-table-container usage-chart">
    <h2 class="usage-chart-title">Grafik Pemakaian BBM per Tangki</h2>
    <p class="usage-chart-subtitle">Total pemakaian dari laporan yang telah disetujui.</p>
    @if(!$hasFilters)
        <div style="padding: 3rem 1rem; text-align: center; color: var(--text-muted);">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 1rem; opacity: 0.3;">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <p style="margin: 0; font-size: 1.1rem;">Silakan pilih Site, Bulan, dan Tahun untuk melihat grafik</p>
        </div>
    @elseif($usageData->isNotEmpty())
        <div class="usage-chart-bars">
            @foreach($usageData as $data)
                @php
                    $usage = (float) $data->total_pakai;
                    $percentage = min(100, max(0, ($usage / $maxUsage) * 100));
                    $sharePercentage = $summaryStats['total_usage'] > 0 ? ($usage / $summaryStats['total_usage']) * 100 : 0;
                @endphp
                <div class="usage-chart-item" role="img" aria-label="Pemakaian {{ $data->tank->code }}: {{ number_format($usage, 0, ',', '.') }} liter">
                    <span class="usage-chart-value">{{ number_format($usage, 0, ',', '.') }} L</span>
                    <div class="usage-chart-plot">
                        @if($usage > 0)
                            <div class="usage-chart-bar" style="height: {{ $percentage }}%;" data-percentage="{{ number_format($sharePercentage, 1) }}%"></div>
                        @else
                            <span class="usage-chart-zero"></span>
                        @endif
                    </div>
                    <span class="usage-chart-label">{{ $data->tank->code }}<br><small style="font-weight: 400; color: var(--text-muted);">{{ $data->tank->main_hole }}</small></span>
                </div>
            @endforeach
        </div>
    @else
        <p style="margin: 0; color: var(--text-muted);">Belum ada data pemakaian dari laporan yang disetujui.</p>
    @endif
</div>

@if($hasFilters && $usageData->isNotEmpty())
<div class="card-table-container">
    <h2 class="card-title">Detail Pemakaian per Tangki</h2>
    <div class="table-responsive">
        <table class="table-list detail-table">
            <thead>
                <tr>
                    <th>Kode Tangki</th>
                    <th>Lubang Utama</th>
                    <th style="text-align: right;">Total Pakai (L)</th>
                    <th style="text-align: right;">Rata-rata (L)</th>
                    <th style="text-align: right;">Maksimal (L)</th>
                    <th style="text-align: right;">Jumlah Laporan</th>
                    <th>Kontribusi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usageData->sortByDesc('total_pakai') as $data)
                    @php
                        $sharePercentage = $summaryStats['total_usage'] > 0 ? ($data->total_pakai / $summaryStats['total_usage']) * 100 : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $data->tank->code }}</strong></td>
                        <td>{{ $data->tank->main_hole }}</td>
                        <td style="text-align: right;"><strong>{{ number_format($data->total_pakai, 0, ',', '.') }}</strong></td>
                        <td style="text-align: right;">{{ number_format($data->avg_pakai, 1, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($data->max_pakai, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ $data->report_count }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div class="progress-bar-container" style="flex: 1;">
                                    <div class="progress-bar" style="width: {{ $sharePercentage }}%;"></div>
                                </div>
                                <span style="font-size: 0.85rem; color: var(--text-muted); min-width: 45px; text-align: right;">{{ number_format($sharePercentage, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2"><strong>TOTAL</strong></td>
                    <td style="text-align: right;"><strong>{{ number_format($summaryStats['total_usage'], 0, ',', '.') }}</strong></td>
                    <td colspan="3" style="text-align: right;">-</td>
                    <td><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card-table-container">
    <h2 class="card-title">Riwayat Laporan Disetujui (Approved)</h2>
    <div class="table-responsive">
        <table class="table-list">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Fuelman</th>
                    <th>GL Pemverifikasi</th>
                    <th>SPV Penyetuju</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if(!$hasFilters)
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem 1rem;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 1rem; opacity: 0.3; display: block;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <p style="margin: 0; color: var(--text-muted);">Silakan pilih Site, Bulan, dan Tahun untuk melihat riwayat laporan</p>
                        </td>
                    </tr>
                @else
                    @forelse($approvedReports as $report)
                        <tr>
                            <td><strong>{{ $report->date->format('d-m-Y') }}</strong></td>
                            <td>{{ $report->fuelman->name }}</td>
                            <td>{{ $report->gl ? $report->gl->name : '-' }}</td>
                            <td>{{ $report->spv ? $report->spv->name : '-' }}</td>
                            <td><span class="badge badge-approved">Approved</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted);">Belum ada laporan disetujui untuk periode yang dipilih.</td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
