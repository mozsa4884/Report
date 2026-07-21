@extends('layouts.app')

@section('title', 'Daftar Laporan Harian')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Laporan Harian Kegiatan Fuelman</h1>
        <p class="page-subtitle">Warehouse & Inventory</p>
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

<div class="card-table-container">
    <h2 class="card-title">Semua Laporan Harian</h2>
    <div class="table-responsive">
        <table class="table-list">
            <thead>
                <tr>
                    <th>Tanggal Laporan</th>
                    <th>Hari</th>
                    <th>Dibuat Oleh (Fuelman)</th>
                    <th>GL Pemverifikasi</th>
                    <th>SPV Penyetuju</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        // Get Indonesian day name
                        $days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                        $dayName = $days[$report->date->format('l')] ?? $report->date->format('l');
                    @endphp
                    <tr>
                        <td><strong>{{ $report->date->format('d-m-Y') }}</strong></td>
                        <td>{{ $dayName }}</td>
                        <td>{{ $report->fuelman->name }}</td>
                        <td>{{ $report->gl ? $report->gl->name : '-' }}</td>
                        <td>{{ $report->spv ? $report->spv->name : '-' }}</td>
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
                            <div style="display: flex; gap: 0.25rem;">
                                <a href="{{ route('reports.show', $report->id) }}" class="btn btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;">
                                    Detail
                                </a>
                                @if(Auth::user()->isFuelman() && in_array($report->status, ['draft', 'rejected']))
                                    <a href="{{ route('reports.edit', $report->id) }}" class="btn btn-primary" style="font-size: 0.8rem; padding: 6px 12px;">
                                        Ubah
                                    </a>
                                @endif
                                @if((Auth::user()->isFuelman() && $report->fuelman_id === Auth::id() && in_array($report->status, ['draft', 'rejected'])) || Auth::user()->isSpv())
                                    <form action="{{ route('reports.destroy', $report->id) }}" method="POST" onsubmit="return confirmDelete(event, this);" style="margin: 0; display: inline-flex;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="font-size: 0.8rem; padding: 6px 12px; margin: 0;">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted);">Belum ada laporan harian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 1.5rem;">
        {{ $reports->links() }}
    </div>
</div>
@endsection
