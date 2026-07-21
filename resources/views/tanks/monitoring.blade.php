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
            <label for="monitoring-date">Tanggal monitoring</label>
            <input id="monitoring-date" name="date" type="date" class="form-control" value="{{ $selectedDate }}">
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>
    <div class="monitoring-note">
        <strong>Info:</strong>
        <span>Monitoring hanya menampilkan laporan yang sudah disetujui (Approved) oleh Supervisor. Laporan dengan status Draft, Diajukan, Diverifikasi, atau Ditolak tidak akan ditampilkan.</span>
    </div>
</div>

<div class="grid-stats" style="margin-top:1.5rem;">
    <div class="stat-card primary"><span class="stat-title">Tanggal Monitoring</span><span class="stat-value" style="font-size:1.45rem;">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}</span></div>
    <div class="stat-card info"><span class="stat-title">Total Kapasitas Tangki</span><span class="stat-value">{{ number_format($totalCapacity, 0, ',', '.') }} <small>L</small></span></div>
    <div class="stat-card success"><span class="stat-title">Total Maks. Bisa Masuk</span><span class="stat-value">{{ number_format($totalCanEnter, 0, ',', '.') }} <small>L</small></span></div>
    <div class="stat-card warning"><span class="stat-title">Rata-rata Bisa Masuk / Tangki</span><span class="stat-value">{{ $averageCanEnter !== null ? number_format($averageCanEnter, 0, ',', '.') : '-' }} <small>{{ $averageCanEnter !== null ? 'L' : '' }}</small></span></div>
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
                                <div class="tank-bar capacity" style="height:{{ (($row->capacity ?? 0) / $chartMaximum) * 100 }}%" data-value="{{ number_format($row->capacity ?? 0, 0, ',', '.') }} L"></div>
                                <div class="tank-bar final" style="height:{{ (($row->final_liters ?? 0) / $chartMaximum) * 100 }}%" data-value="{{ number_format($row->final_liters ?? 0, 0, ',', '.') }} L"></div>
                                <div class="tank-bar available" style="height:{{ (($row->available_capacity ?? 0) / $chartMaximum) * 100 }}%" data-value="{{ number_format($row->available_capacity ?? 0, 0, ',', '.') }} L"></div>
                            </div>
                            <span class="tank-chart-label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="empty-monitoring"><strong>Data Monitoring Belum Tersedia.</strong><br>Belum ada laporan yang disetujui untuk tanggal ini. Pilih tanggal lain atau tunggu Supervisor menyetujui laporan.</div>
        @endif
    </section>

    <section class="chart-card">
        <h2 class="card-title">Ringkasan Perhitungan Harian</h2>
        <div class="metric-list">
            <div class="metric-item"><span class="metric-label">Status laporan</span><strong class="badge badge-{{ $selectedReport?->status ?? 'draft' }}">{{ $selectedReport ? ucfirst($selectedReport->status) : 'Belum ada laporan' }}</strong></div>
            <div class="metric-item"><span class="metric-label">Tangki dengan data akhir sore</span><span class="metric-value">{{ $calculatedRows->count() }} dari {{ $tanks->count() }} tangki</span></div>
            <div class="metric-item"><span class="metric-label">Total liter sore</span><span class="metric-value">{{ number_format($totalFinalLiters, 0, ',', '.') }} L</span></div>
            <div class="metric-item"><span class="metric-label">Rumus kapasitas tersedia</span><span class="metric-value">Kapasitas − liter sore</span></div>
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
                        <td>{{ $row->capacity !== null ? number_format($row->capacity, 0, ',', '.') . ' L' : '-' }}</td>
                        <td>{{ $row->item?->liter_pagi !== null ? number_format($row->item->liter_pagi, 0, ',', '.') . ' L' : '-' }}</td>
                        <td>{{ $row->final_liters !== null ? number_format($row->final_liters, 0, ',', '.') . ' L' : '-' }}</td>
                        <td>{{ $row->item?->fm_pakai !== null ? number_format($row->item->fm_pakai, 0, ',', '.') . ' L' : '-' }}</td>
                        <td><strong style="color:{{ $row->is_over_capacity ? 'var(--danger)' : 'var(--success)' }}">{{ $row->available_capacity !== null ? number_format($row->available_capacity, 0, ',', '.') . ' L' : '-' }}</strong></td>
                        <td>
                            @if($row->fill_percent !== null)<div style="display:flex;align-items:center;gap:.65rem;"><div class="capacity-track"><div class="capacity-fill" style="width:{{ $row->fill_percent }}%;background:{{ $row->is_over_capacity ? 'var(--danger)' : 'var(--primary)' }}"></div></div><span>{{ number_format($row->fill_percent, 1, ',', '.') }}%</span></div>@else - @endif
                        </td>
                        <td>@if($row->is_over_capacity)<span class="badge badge-rejected">Melebihi kapasitas</span>@elseif($row->final_liters !== null && $row->capacity !== null)<span class="badge badge-approved">Tercatat</span>@else<span class="badge badge-draft">Belum lengkap</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;color:var(--text-muted);">Belum ada tangki aktif untuk dimonitor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
