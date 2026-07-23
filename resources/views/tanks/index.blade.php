@extends('layouts.app')

@section('title', 'Daftar Tangki BBM')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Manajemen Tangki BBM</h1>
        <p class="page-subtitle">Daftar tangki BBM aktif di Warehouse & Inventory</p>
    </div>
    @if(Auth::user()->isSpv() || Auth::user()->role === 'admin')
        <a href="{{ route('tanks.create') }}" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Tambah Tangki
        </a>
    @endif
</div>

<div class="card-table-container">
    <h2 class="card-title">Daftar Tangki BBM</h2>
    <div class="table-responsive">
        <table class="table-list">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Site / Lokasi</th>
                    <th>Kode Tangki</th>
                    <th>Main Hole</th>
                    <th>Kapasitas (L)</th>
                    <th>Data Kalibrasi</th>
                    <th>Status</th>
                    @if(Auth::user()->isSpv() || Auth::user()->role === 'admin')
                        <th style="width: 180px;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($tanks as $index => $tank)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($tank->site)
                                <span style="font-weight: 500;">{{ $tank->site->code }}</span>
                                <span style="color: var(--text-muted); font-size: 0.85rem;">{{ $tank->site->name }}</span>
                            @else
                                <span class="badge badge-draft" style="background-color: #fef3c7; color: #92400e;">Belum Diset</span>
                            @endif
                        </td>
                        <td><strong>{{ $tank->code }}</strong></td>
                        <td>{{ $tank->main_hole }}</td>
                        <td>{{ $tank->capacity > 0 ? number_format($tank->capacity) . ' L' : '-' }}</td>
                        <td>
                            @if($tank->calibrations_count ?? $tank->calibrations()->count())
                                <span class="badge badge-submitted" style="background-color: var(--info-light); color: #1e3a8a;">
                                    {{ number_format($tank->calibrations_count ?? $tank->calibrations()->count()) }} Baris Kalibrasi
                                </span>
                            @else
                                <span class="badge badge-draft" style="color: var(--text-muted);">Belum Ada</span>
                            @endif
                        </td>
                        <td>
                            @if($tank->is_active)
                                <span class="badge badge-approved" style="background-color: var(--success-light); color: #065f46;">Aktif</span>
                            @else
                                <span class="badge badge-draft">Non-Aktif</span>
                            @endif
                        </td>
                        @if(Auth::user()->isSpv() || Auth::user()->role === 'admin')
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('tanks.edit', $tank->id) }}" class="btn-icon btn-icon-edit" title="Ubah">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('tanks.destroy', $tank->id) }}" method="POST" onsubmit="return confirmDelete(event, this);" style="margin: 0; display: inline-flex;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon btn-icon-delete" title="Hapus">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
