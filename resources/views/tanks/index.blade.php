@extends('layouts.app')

@section('title', 'Daftar Tangki BBM')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Manajemen Tangki BBM</h1>
        <p class="page-subtitle">Daftar tangki BBM aktif di Warehouse & Inventory</p>
    </div>
    @if(Auth::user()->isSpv())
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
                    <th>Kode Tangki</th>
                    <th>Main Hole</th>
                    <th>Kapasitas (L)</th>
                    <th>Data Kalibrasi</th>
                    <th>Status</th>
                    @if(Auth::user()->isSpv())
                        <th style="width: 180px;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($tanks as $index => $tank)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $tank->code }}</strong></td>
                        <td>{{ $tank->main_hole }}</td>
                        <td>{{ $tank->capacity ? number_format($tank->capacity) . ' L' : '-' }}</td>
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
                        @if(Auth::user()->isSpv())
                            <td>
                                <div style="display: flex; gap: 0.25rem; align-items: center;">
                                    <a href="{{ route('tanks.edit', $tank->id) }}" class="btn btn-secondary" style="font-size: 0.8rem; padding: 6px 12px; margin: 0;">
                                        Ubah
                                    </a>
                                    <form action="{{ route('tanks.destroy', $tank->id) }}" method="POST" onsubmit="return confirmDelete(event, this);" style="margin: 0; display: inline-flex;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="font-size: 0.8rem; padding: 6px 12px; margin: 0;">Hapus</button>
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
