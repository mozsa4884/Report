@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Selamat datang kembali, {{ Auth::user()->name }}!</p>
    </div>
    @if(Auth::user()->isFuelman())
        <a href="{{ route('reports.create') }}" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Buat Laporan Baru
        </a>
    @endif
</div>

<!-- STATS GRID -->
<div class="grid-stats">
    @if(Auth::user()->isFuelman())
        <div class="stat-card info">
            <span class="stat-title">Draft Laporan</span>
            <span class="stat-value">{{ $stats['drafts'] }}</span>
        </div>
        <div class="stat-card warning">
            <span class="stat-title">Menunggu Verifikasi (GL)</span>
            <span class="stat-value">{{ $stats['submitted'] }}</span>
        </div>
        <div class="stat-card primary">
            <span class="stat-title">Terverifikasi (GL)</span>
            <span class="stat-value">{{ $stats['verified'] }}</span>
        </div>
        <div class="stat-card success">
            <span class="stat-title">Disetujui (SPV)</span>
            <span class="stat-value">{{ $stats['approved'] }}</span>
        </div>
        <div class="stat-card danger">
            <span class="stat-title">Perlu Revisi</span>
            <span class="stat-value">{{ $stats['rejected'] }}</span>
        </div>
    @elseif(Auth::user()->isGl())
        <div class="stat-card warning">
            <span class="stat-title">Perlu Verifikasi Anda</span>
            <span class="stat-value">{{ $stats['pending_verification'] }}</span>
        </div>
        <div class="stat-card success">
            <span class="stat-title">Telah Anda Verifikasi</span>
            <span class="stat-value">{{ $stats['verified_by_me'] }}</span>
        </div>
        <div class="stat-card primary">
            <span class="stat-title">Total Disetujui SPV</span>
            <span class="stat-value">{{ $stats['total_approved'] }}</span>
        </div>
        <div class="stat-card danger">
            <span class="stat-title">Telah Anda Tolak</span>
            <span class="stat-value">{{ $stats['total_rejected_by_me'] }}</span>
        </div>
    @elseif(Auth::user()->isSpv())
        <div class="stat-card warning">
            <span class="stat-title">Perlu Persetujuan Anda</span>
            <span class="stat-value">{{ $stats['pending_approval'] }}</span>
        </div>
        <div class="stat-card success">
            <span class="stat-title">Telah Anda Setujui</span>
            <span class="stat-value">{{ $stats['approved_by_me'] }}</span>
        </div>
        <div class="stat-card primary">
            <span class="stat-title">Total Laporan</span>
            <span class="stat-value">{{ $stats['total_reports'] }}</span>
        </div>
        <div class="stat-card info">
            <span class="stat-title">Volume Keluar (FM Pakai)</span>
            <span class="stat-value">{{ number_format($totalUsage, 0, ',', '.') }} L</span>
        </div>
    @endif
</div>

<!-- WORKFLOW ACTIONS -->
@if(Auth::user()->isFuelman())
    @if($latestRejected)
        <div class="alert alert-danger" style="margin-top: 1rem; align-items: flex-start; flex-direction: column; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>Ada Laporan yang Dikembalikan (Ditolak) untuk Direvisi!</span>
            </div>
            <p style="font-size: 0.85rem; margin-left: 1.7rem;">
                Laporan tanggal <strong>{{ $latestRejected->date->format('d-m-Y') }}</strong> ditolak. 
                Catatan: <em>"{{ $latestRejected->gl_feedback ?: ($latestRejected->spv_feedback ?: 'Tidak ada catatan khusus') }}"</em>.
            </p>
            <a href="{{ route('reports.edit', $latestRejected->id) }}" class="btn btn-danger" style="font-size: 0.8rem; padding: 4px 12px; margin-left: 1.7rem; margin-top: 4px;">
                Revisi Sekarang
            </a>
        </div>
    @endif

    @if($latestDraft)
        <div class="alert alert-warning" style="margin-top: 1rem; align-items: flex-start; flex-direction: column; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>Anda memiliki draft laporan yang belum dikirim.</span>
            </div>
            <p style="font-size: 0.85rem; margin-left: 1.7rem;">
                Laporan tanggal <strong>{{ $latestDraft->date->format('d-m-Y') }}</strong> masih berstatus Draft.
            </p>
            <div style="margin-left: 1.7rem; display: flex; gap: 0.5rem; margin-top: 4px;">
                <a href="{{ route('reports.show', $latestDraft->id) }}" class="btn btn-primary" style="font-size: 0.8rem; padding: 4px 12px;">
                    Lihat Draft
                </a>
                <a href="{{ route('reports.edit', $latestDraft->id) }}" class="btn btn-secondary" style="font-size: 0.8rem; padding: 4px 12px;">
                    Ubah Laporan
                </a>
            </div>
        </div>
    @endif
@endif

@if((Auth::user()->isGl() || Auth::user()->isSpv()) && count($pendingReports) > 0)
    <div class="card-table-container">
        <h2 class="card-title">Tugas Perlu Tindakan</h2>
        <div class="table-responsive">
            <table class="table-list">
                <thead>
                    <tr>
                        <th>Tanggal Laporan</th>
                        <th>Fuelman</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingReports as $report)
                        <tr>
                            <td><strong>{{ $report->date->format('d-m-Y') }}</strong></td>
                            <td>{{ $report->fuelman->name }}</td>
                            <td>
                                @if($report->status === 'submitted')
                                    <span class="badge badge-submitted">Menunggu Verifikasi GL</span>
                                @elseif($report->status === 'verified')
                                    <span class="badge badge-verified">Menunggu Persetujuan SPV</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('reports.show', $report->id) }}" class="btn btn-primary" style="font-size: 0.8rem; padding: 6px 12px;">
                                    Tinjau Laporan
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- SPV FUEL STATUS WIDGET -->
@if(Auth::user()->isSpv())
    <div class="card-table-container">
        @if($todayReport)
            <h2 class="card-title">Kondisi Stok & Sounding Terkini (Laporan Hari Ini: {{ $todayReport->date->format('d-m-Y') }})</h2>
            <div class="table-responsive">
                <table class="table-list">
                    <thead>
                        <tr>
                            <th>Tangki</th>
                            <th>Main Hole</th>
                            <th>Sounding Pagi</th>
                            <th>Liter Pagi</th>
                            <th>Sounding Sore</th>
                            <th>Liter Sore</th>
                            <th>Flow Meter Pakai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tankStatus as $item)
                            <tr>
                                <td><strong>{{ $item->tank->code }}</strong></td>
                                <td>{{ $item->tank->main_hole }}</td>
                                <td>{{ $item->sounding_pagi !== null ? number_format($item->sounding_pagi, 1, ',', '.') . ' cm' : '-' }}</td>
                                <td>{{ $item->liter_pagi !== null ? number_format($item->liter_pagi, 0, ',', '.') . ' L' : '-' }}</td>
                                <td>{{ $item->sounding_sore !== null ? number_format($item->sounding_sore, 1, ',', '.') . ' cm' : '-' }}</td>
                                <td>{{ $item->liter_sore !== null ? number_format($item->liter_sore, 0, ',', '.') . ' L' : '-' }}</td>
                                <td>
                                    @if($item->fm_pakai !== null)
                                        <span style="color: {{ $item->fm_pakai > 0 ? 'var(--success)' : ($item->fm_pakai < 0 ? 'var(--danger)' : 'inherit') }}">
                                            {{ number_format($item->fm_pakai, 0, ',', '.') }} L
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <h2 class="card-title">Kondisi Stok & Sounding Terkini</h2>
            <div style="padding: 3rem 1rem; text-align: center; color: var(--text-muted);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 1rem; opacity: 0.3;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <p style="margin: 0; font-size: 1.1rem;">Belum ada laporan untuk hari ini ({{ date('d-m-Y') }})</p>
            </div>
        @endif
    </div>
@endif

<!-- RECENT REPORTS -->
<div class="card-table-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 class="card-title" style="margin: 0;">Laporan Terbaru</h2>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary" style="font-size: 0.85rem; padding: 6px 14px;">
            Lihat Semua Laporan
        </a>
    </div>
    <div class="table-responsive">
        <table class="table-list">
            <thead>
                <tr>
                    <th>Tanggal Laporan</th>
                    <th>Pembuat (Fuelman)</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentReports as $report)
                    <tr>
                        <td><strong>{{ $report->date->format('d-m-Y') }}</strong></td>
                        <td>{{ $report->fuelman->name }}</td>
                        <td>
                            @if($report->status === 'draft')
                                <span class="badge badge-draft">Draft</span>
                            @elseif($report->status === 'submitted')
                                <span class="badge badge-submitted">Menunggu GL</span>
                            @elseif($report->status === 'verified')
                                <span class="badge badge-verified">Menunggu SPV</span>
                            @elseif($report->status === 'approved')
                                <span class="badge badge-approved">Disetujui</span>
                            @elseif($report->status === 'rejected')
                                <span class="badge badge-rejected">Direvisi</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: center;">
                                <!-- Detail Button - Untuk semua role -->
                                <a href="{{ route('reports.show', $report->id) }}" class="icon-btn icon-btn-info" title="Detail">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                
                                <!-- Edit Button - Hanya untuk Fuelman dengan status draft/rejected -->
                                @if(Auth::user()->isFuelman() && in_array($report->status, ['draft', 'rejected']))
                                    <a href="{{ route('reports.edit', $report->id) }}" class="icon-btn icon-btn-primary" title="Ubah">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                    </a>
                                @endif
                                
                                <!-- Delete Button - Untuk Fuelman (draft/rejected) atau SPV -->
                                @if((Auth::user()->isFuelman() && $report->fuelman_id === Auth::id() && in_array($report->status, ['draft', 'rejected'])) || Auth::user()->isSpv())
                                    <form action="{{ route('reports.destroy', $report->id) }}" method="POST" onsubmit="return confirmDelete(event, this);" style="margin: 0; display: inline-flex;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn icon-btn-danger" title="Hapus">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"></path>
                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted);">Belum ada laporan harian yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
