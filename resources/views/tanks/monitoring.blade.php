@extends('layouts.app')

@section('title', 'Monitoring Tangki BBM')

@section('styles')
<style>
    /* Monitoring filter section */
    .monitoring-filter { display:flex; align-items:end; gap:.75rem; flex-wrap:wrap; }
    .monitoring-filter .form-group { margin:0; min-width:200px; }
    .monitoring-filter .form-control { min-height:42px; }
    .monitoring-note { display:flex; align-items:flex-start; gap:.65rem; margin:1.25rem 0; padding:.9rem 1rem; border:1px solid #bae6fd; border-radius:var(--radius-sm); background:#f0f9ff; color:#075985; font-size:.9rem; }
    
    /* Monitoring grid - removed height restrictions */
    .monitoring-grid { 
        display:grid; 
        grid-template-columns:minmax(0, 1.15fr) minmax(320px, .85fr); 
        gap:1.5rem; 
        margin-top:1.5rem;
        width: 100%;
    }
    
    /* Chart card with proper overflow handling */
    .chart-card { 
        background:var(--bg-secondary); 
        border-radius:var(--radius-md); 
        padding:1.5rem; 
        box-shadow:var(--card-shadow); 
        min-width:0;
        overflow: visible;
    }
    
    .chart-legend { display:flex; flex-wrap:wrap; gap:1rem; margin:-.5rem 0 1.25rem; color:var(--text-secondary); font-size:.8rem; font-weight:600; }
    .chart-legend i { display:inline-block; width:10px; height:10px; margin-right:.35rem; border-radius:2px; }
    
    /* Tank chart with proper scrolling */
    .tank-chart-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        margin: 0;
        padding-bottom: 0.5rem;
    }
    
    .tank-chart { 
        display:flex; 
        align-items:flex-end; 
        gap:1rem; 
        min-height:270px; 
        min-width: max-content;
        padding:1rem .5rem 0; 
        border-bottom:1px solid var(--border-color);
    }
    
    .tank-chart-group { 
        flex:1 0 80px; 
        min-width:80px; 
        height:245px; 
        display:flex; 
        flex-direction:column; 
        justify-content:flex-end; 
        align-items:center; 
        gap:.4rem; 
    }
    
    .tank-bars { height:205px; display:flex; align-items:flex-end; justify-content:center; gap:4px; }
    .tank-bar { width:16px; min-height:2px; border-radius:4px 4px 0 0; position:relative; transition:opacity .2s; }
    .tank-bar:hover { opacity:.75; }
    .tank-bar.capacity { background:#94a3b8; }
    .tank-bar.final { background:#0d9488; }
    .tank-bar.available { background:#38bdf8; }
    .tank-bar::after { content:attr(data-value); display:none; position:absolute; bottom:calc(100% + 5px); left:50%; transform:translateX(-50%); padding:.25rem .4rem; border-radius:4px; color:#fff; background:#0f172a; white-space:nowrap; font-size:.68rem; z-index:10; }
    .tank-bar:hover::after { display:block; }
    .tank-chart-label { width:100%; color:var(--text-secondary); text-align:center; font-size:.72rem; font-weight:700; line-height:1.2; word-break:break-word; }
    
    /* Metrics */
    .metric-list { display:grid; gap:.85rem; }
    .metric-item { display:flex; justify-content:space-between; align-items:center; padding:.85rem 0; border-bottom:1px solid var(--border-color); gap:1rem; }
    .metric-item:last-child { border-bottom:0; }
    .metric-label { color:var(--text-secondary); font-size:.87rem; }
    .metric-value { color:var(--text-primary); font-weight:800; text-align:right; }
    .capacity-track { min-width:130px; height:7px; overflow:hidden; border-radius:999px; background:var(--bg-primary); }
    .capacity-fill { height:100%; border-radius:inherit; background:var(--primary); }
    .empty-monitoring { text-align:center; color:var(--text-muted); padding:3rem 1rem; }
    
    /* Responsive design */
    @media (max-width:900px) { 
        .monitoring-grid { 
            grid-template-columns:1fr; 
        } 
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Monitoring Tangki BBM</h1>
        <p class="page-subtitle">Pantau volume akhir sore dan kapasitas penerimaan solar per tanggal.</p>
    </div>
    @if($selectedReport)
        <a href="{{ route('reports.show', $selectedReport->id) }}" class="btn btn-secondary">Lihat Laporan</a>
    @endif
</div>

<div class="card-table-container" style="margin-top:1.5rem;">
    <form method="GET" action="{{ route('tanks.monitoring') }}" class="monitoring-filter">
        <div class="form-group">
            <label for="monitoring-site">Site <span style="color: var(--danger);">*</span></label>
            <select id="monitoring-site" name="site_id" class="form-control" required>
                <option value="">-- Pilih Site --</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ $selectedSiteId == $site->id ? 'selected' : '' }}>
                        {{ $site->code }} - {{ $site->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="monitoring-date">Tanggal Monitoring</label>
            <input id="monitoring-date" name="date" type="date" class="form-control" value="{{ $selectedDate }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan Data</button>
    </form>
    @if(!$selectedSiteId)
        <div class="monitoring-note" style="background: #fef3c7; border-color: #fbbf24; color: #92400e;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <span><strong>Pilih Site dan Tanggal:</strong> Silakan pilih site dan tanggal terlebih dahulu untuk menampilkan data monitoring tangki.</span>
        </div>
    @else
        <div class="monitoring-note">
            <strong>Info:</strong>
            <span>Monitoring menampilkan semua laporan termasuk yang berstatus Draft. Untuk data yang lebih akurat, pilih tanggal dengan laporan yang sudah Approved.</span>
        </div>
    @endif
</div>

@if($selectedSiteId)
<div class="grid-stats" style="margin-top:1.5rem;">
    <div class="stat-card primary" style="align-items: flex-start;">
        <span class="stat-title">Site Monitoring</span>
        @if($selectedSite)
            <span class="stat-value" style="font-size:1.2rem;">{{ $selectedSite->code }} - {{ $selectedSite->name }}</span>
        @else
            <span class="stat-value" style="font-size:1rem; color: var(--text-muted);">Pilih Site</span>
        @endif
    </div>
    <div class="stat-card info">
        <span class="stat-title">Tanggal Monitoring</span>
        <span class="stat-value" style="font-size:1.2rem;">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}</span>
        @if($selectedReport)
            <span class="badge badge-{{ $selectedReport->status }}" style="margin-top: 0.5rem; font-size: 0.7rem; padding: 0.3rem 0.7rem; font-weight: 700; letter-spacing: 0.3px;">
                @if($selectedReport->status === 'draft') DRAFT
                @elseif($selectedReport->status === 'submitted') SUBMITTED
                @elseif($selectedReport->status === 'verified') VERIFIED
                @elseif($selectedReport->status === 'approved') APPROVED
                @elseif($selectedReport->status === 'rejected') REJECTED
                @endif
            </span>
        @endif
    </div>
    <div class="stat-card success"><span class="stat-title">Total Kapasitas Tangki</span><span class="stat-value">{{ number_format($totalCapacity, 0, ',', '.') }} <small>L</small></span></div>
    <div class="stat-card warning"><span class="stat-title">Total Maks. Bisa Masuk</span><span class="stat-value">{{ number_format($totalCanEnter, 0, ',', '.') }} <small>L</small></span></div>
</div>

<div class="monitoring-grid">
    <section class="chart-card">
        <h2 class="card-title">Grafik Kapasitas & Stok Akhir Sore</h2>
        <div class="chart-legend"><span><i style="background:#94a3b8"></i>Kapasitas</span><span><i style="background:#0d9488"></i>Liter sore</span><span><i style="background:#38bdf8"></i>Bisa masuk</span></div>
        @if($selectedReport && $monitoringRows->isNotEmpty())
            @php($chartMaximum = max(1, $monitoringRows->max(fn($row) => max($row->capacity ?? 0, $row->final_liters ?? 0))))
            <div class="tank-chart-wrapper">
                <div class="tank-chart" aria-label="Grafik kapasitas tangki">
                    @foreach($monitoringRows as $row)
                        @php($label = trim($row->tank->code . ' ' . $row->tank->main_hole))
                        <div class="tank-chart-group">
                            <div class="tank-bars">
                                <div class="tank-bar capacity" style="height:{{ (($row->capacity ?? 0) / $chartMaximum) * 100 }}%" data-value="{{ $row->capacity !== null && $row->capacity > 0 ? number_format($row->capacity, 0, ',', '.') . ' L' : '-' }}"></div>
                                <div class="tank-bar final" style="height:{{ (($row->final_liters ?? 0) / $chartMaximum) * 100 }}%" data-value="{{ number_format($row->final_liters ?? 0, 0, ',', '.') }} L"></div>
                                <div class="tank-bar available" style="height:{{ (($row->available_capacity ?? 0) / $chartMaximum) * 100 }}%" data-value="{{ number_format($row->available_capacity ?? 0, 0, ',', '.') }} L"></div>
                            </div>
                            <span class="tank-chart-label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="empty-monitoring"><strong>Data Monitoring Belum Tersedia.</strong><br>Belum ada laporan untuk tanggal ini. Pilih tanggal lain atau buat laporan baru.</div>
        @endif
    </section>

    <section class="chart-card">
        <h2 class="card-title">Ringkasan Perhitungan Harian</h2>
        <div class="metric-list">
            <div class="metric-item">
                <span class="metric-label">Status Laporan</span>
                @if($selectedReport)
                    <span class="badge badge-{{ $selectedReport->status }}" style="font-size: 0.7rem; padding: 0.35rem 0.75rem; border-radius: 4px; font-weight: 700;">
                        @if($selectedReport->status === 'draft') DRAFT
                        @elseif($selectedReport->status === 'submitted') SUBMITTED
                        @elseif($selectedReport->status === 'verified') VERIFIED
                        @elseif($selectedReport->status === 'approved') APPROVED
                        @elseif($selectedReport->status === 'rejected') REJECTED
                        @endif
                    </span>
                @else
                    <span class="metric-value" style="color: var(--text-muted); font-size: 0.85rem;">Tidak Ada</span>
                @endif
            </div>
            <div class="metric-item">
                <span class="metric-label">Tangki Terdata</span>
                <span class="metric-value">{{ $calculatedRows->count() }}<span style="color: var(--text-muted); font-weight: 500;"> / {{ $tanks->count() }}</span></span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Total Liter Sore</span>
                <span class="metric-value">{{ number_format($totalFinalLiters, 0, ',', '.') }} L</span>
            </div>
            <div class="metric-item" style="border-bottom: 0; padding-bottom: 0;">
                <span class="metric-label">Perhitungan</span>
                <span class="metric-value" style="font-size: 0.8rem; color: var(--text-secondary);">Kapasitas − Liter Sore</span>
            </div>
        </div>
    </section>
</div>

<div class="card-table-container" style="margin-top:1.5rem;">
    <h2 class="card-title">Detail Monitoring per Tangki</h2>
    <div class="table-responsive">
        <table class="table-list">
            <thead><tr><th>Kode Tangki</th><th>Main Hole</th><th>Total Kapasitas</th><th>Liter Pagi</th><th>Liter Sore (Final)</th><th>FM Pakai</th><th>Sisa Bisa Masuk</th><th>Terisi</th><th>Status Data</th></tr></thead>
            <tbody>
                @forelse($monitoringRows as $row)
                    <tr>
                        <td><strong>{{ $row->tank->code }}</strong></td><td>{{ $row->tank->main_hole }}</td>
                        <td>{{ $row->capacity !== null && $row->capacity > 0 ? number_format($row->capacity, 0, ',', '.') . ' L' : '-' }}</td>
                        <td>{{ $row->item?->liter_pagi !== null ? number_format($row->item->liter_pagi, 0, ',', '.') . ' L' : '-' }}</td>
                        <td>{{ $row->final_liters !== null ? number_format($row->final_liters, 0, ',', '.') . ' L' : '-' }}</td>
                        <td>{{ $row->item?->fm_pakai !== null ? number_format($row->item->fm_pakai, 0, ',', '.') . ' L' : '-' }}</td>
                        <td><strong style="color:{{ $row->is_over_capacity ? 'var(--danger)' : 'var(--success)' }}">{{ $row->available_capacity !== null ? number_format($row->available_capacity, 0, ',', '.') . ' L' : '-' }}</strong></td>
                        <td>
                            @if($row->fill_percent !== null)<div style="display:flex;align-items:center;gap:.65rem;"><div class="capacity-track"><div class="capacity-fill" style="width:{{ $row->fill_percent }}%;background:{{ $row->is_over_capacity ? 'var(--danger)' : 'var(--primary)' }}"></div></div><span>{{ number_format($row->fill_percent, 1, ',', '.') }}%</span></div>@else - @endif
                        </td>
                        <td>
                            @if($row->is_over_capacity)
                                <span class="badge badge-rejected" style="font-size: 0.7rem; padding: 0.35rem 0.65rem; border-radius: 4px;">OVER</span>
                            @elseif($row->final_liters !== null && $row->capacity !== null)
                                <span class="badge badge-approved" style="font-size: 0.7rem; padding: 0.35rem 0.65rem; border-radius: 4px;">LENGKAP</span>
                            @else
                                <span class="badge badge-draft" style="font-size: 0.7rem; padding: 0.35rem 0.65rem; border-radius: 4px;">KOSONG</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;color:var(--text-muted);">Belum ada tangki aktif untuk dimonitor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<!-- Placeholder when no site selected -->
<div style="margin-top: 3rem; text-align: center; padding: 4rem 2rem; background: var(--bg-secondary); border-radius: var(--radius-md); border: 2px dashed var(--border-color);">
    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted); margin: 0 auto 1.5rem;">
        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
        <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path>
    </svg>
    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.5rem;">Monitoring Tangki BBM</h3>
    <p style="font-size: 0.95rem; color: var(--text-secondary); margin: 0 0 2rem; max-width: 500px; margin-left: auto; margin-right: auto;">
        Silakan pilih <strong>Site</strong> dan <strong>Tanggal</strong> pada filter di atas, kemudian klik <strong>"Tampilkan Data"</strong> untuk melihat monitoring kapasitas tangki.
    </p>
    <div style="display: inline-flex; gap: 1rem; align-items: center; padding: 1rem 1.5rem; background: #eff6ff; border-radius: var(--radius-sm); border: 1px solid #bae6fd;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <span style="font-size: 0.9rem; color: #075985; font-weight: 500;">Data akan tampil setelah Anda memilih site dan tanggal</span>
    </div>
</div>
@endif
@endsection
