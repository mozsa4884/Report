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

<!-- Filter & Search Section -->
<div class="card-table-container" style="margin-bottom: 1.5rem;">
    <form method="GET" action="{{ route('reports.index') }}" class="form-row" style="display: grid; grid-template-columns: {{ $sites ? '2fr 1fr 1fr 1fr 1fr' : '2fr 1fr 1fr 1fr' }}; gap: 1rem; align-items: end;">
        <!-- Pencarian - Paling kiri dan lebih lebar -->
        <div class="form-group" style="margin-bottom: 0;">
            <label for="search">Pencarian</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Cari tanggal, nama..." value="{{ $search ?? '' }}">
        </div>
        
        <!-- Site (jika ada) -->
        @if($sites)
        <div class="form-group" style="margin-bottom: 0;">
            <label for="site_id">Site / Lokasi</label>
            <select name="site_id" id="site_id" class="form-control">
                <option value="">Semua Site</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ $siteId == $site->id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        
        <!-- Status -->
        <div class="form-group" style="margin-bottom: 0;">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="submitted" {{ $status == 'submitted' ? 'selected' : '' }}>Menunggu GL</option>
                <option value="verified" {{ $status == 'verified' ? 'selected' : '' }}>Menunggu SPV</option>
                <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Direvisi</option>
            </select>
        </div>
        
        <!-- Urutkan -->
        <div class="form-group" style="margin-bottom: 0;">
            <label for="sort">Urutkan</label>
            <select name="sort" id="sort" class="form-control">
                <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>Terbaru</option>
                <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>Terlama</option>
            </select>
        </div>
        
        <!-- Tombol Terapkan -->
        <div class="form-group" style="margin-bottom: 0;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                Terapkan
            </button>
        </div>
    </form>
    
    @if($search || $siteId || $status)
    <div style="margin-top: 1rem;">
        <a href="{{ route('reports.index') }}" class="btn btn-secondary" style="font-size: 0.85rem; padding: 6px 12px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            Reset Filter
        </a>
    </div>
    @endif
</div>

<div class="card-table-container">
    <!-- Header dengan Tampilkan per halaman -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h2 class="card-title" style="margin: 0;">Semua Laporan Harian</h2>
        <form method="GET" action="{{ route('reports.index') }}" style="display: flex; align-items: center; gap: 0.5rem;">
            <!-- Preserve all filter params -->
            <input type="hidden" name="site_id" value="{{ $siteId }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="sort" value="{{ $sortOrder }}">
            
            <label for="per_page_top" style="font-size: 0.9rem; color: var(--text-secondary); margin: 0;">Tampilkan:</label>
            <select name="per_page" id="per_page_top" class="form-control" onchange="this.form.submit()" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.9rem;">
                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
            </select>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table-list">
            <thead>
                <tr>
                    <th>Tanggal Laporan</th>
                    <th>Hari</th>
                    @if($sites)
                        <th>Site / Lokasi</th>
                    @endif
                    <th>Dibuat Oleh (Fuelman)</th>
                    <th>GL Pemverifikasi</th>
                    <th>SPV Penyetuju</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
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
                        @if($sites)
                            <td>{{ $report->site ? $report->site->name : '-' }}</td>
                        @endif
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
                            <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: center;">
                                <!-- Detail Button -->
                                <a href="{{ route('reports.show', $report->id) }}" class="icon-btn icon-btn-info" title="Detail">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                </a>
                                
                                <!-- Edit Button (Fuelman only for draft/rejected) -->
                                @if(Auth::user()->isFuelman() && in_array($report->status, ['draft', 'rejected']))
                                    <a href="{{ route('reports.edit', $report->id) }}" class="icon-btn icon-btn-primary" title="Ubah">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                @endif
                                
                                <!-- Delete Button -->
                                @if((Auth::user()->isFuelman() && $report->fuelman_id === Auth::id() && in_array($report->status, ['draft', 'rejected'])) || Auth::user()->isSpv())
                                    <form action="{{ route('reports.destroy', $report->id) }}" method="POST" onsubmit="return confirmDelete(event, this);" style="margin: 0; display: inline-flex;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn icon-btn-danger" title="Hapus">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $sites ? 8 : 7 }}" style="text-align: center; color: var(--text-muted);">
                            @if($search || $siteId || $status)
                                Tidak ada laporan yang sesuai dengan filter.
                            @else
                                Belum ada laporan harian.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Custom Pagination --}}
    <div style="margin-top: 1.5rem;">
        @if($reports->total() > 0)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; gap: 1rem; flex-wrap: wrap;">
                {{-- Info --}}
                <div style="color: var(--text-muted); font-size: 0.9rem;">
                    Menampilkan <strong style="color: var(--text-primary);">{{ $reports->firstItem() }}</strong> - <strong style="color: var(--text-primary);">{{ $reports->lastItem() }}</strong> dari <strong style="color: var(--text-primary);">{{ $reports->total() }}</strong> data
                </div>
                
                {{-- Pagination Controls --}}
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    {{-- Previous Button --}}
                    @if($reports->onFirstPage())
                        <span style="padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-secondary); color: var(--text-muted); font-size: 0.9rem; cursor: not-allowed; opacity: 0.5;">
                            ← Sebelumnya
                        </span>
                    @else
                        <a href="{{ $reports->appends(['site_id' => $siteId, 'status' => $status, 'search' => $search, 'sort' => $sortOrder, 'per_page' => $perPage])->previousPageUrl() }}" style="padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-secondary); color: var(--text-primary); font-size: 0.9rem; text-decoration: none; transition: all 0.2s ease; display: inline-block;">
                            ← Sebelumnya
                        </a>
                    @endif
                    
                    {{-- Page Info --}}
                    <span style="padding: 0.5rem 1rem; border: 1px solid var(--primary); border-radius: 8px; background: var(--primary); color: white; font-size: 0.9rem; font-weight: 600;">
                        Halaman {{ $reports->currentPage() }} dari {{ $reports->lastPage() }}
                    </span>
                    
                    {{-- Next Button --}}
                    @if($reports->hasMorePages())
                        <a href="{{ $reports->appends(['site_id' => $siteId, 'status' => $status, 'search' => $search, 'sort' => $sortOrder, 'per_page' => $perPage])->nextPageUrl() }}" style="padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-secondary); color: var(--text-primary); font-size: 0.9rem; text-decoration: none; transition: all 0.2s ease; display: inline-block;">
                            Selanjutnya →
                        </a>
                    @else
                        <span style="padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-secondary); color: var(--text-muted); font-size: 0.9rem; cursor: not-allowed; opacity: 0.5;">
                            Selanjutnya →
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    /* Hover effect for pagination buttons */
    a[href*="page="]:hover {
        background: var(--primary) !important;
        color: white !important;
        border-color: var(--primary) !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(13, 148, 136, 0.3);
    }
</style>
@endsection
