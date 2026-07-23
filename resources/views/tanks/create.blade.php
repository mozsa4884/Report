@extends('layouts.app')

@section('title', 'Tambah Tangki BBM')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Tambah Tangki BBM</h1>
        <p class="page-subtitle">Buat entri tangki BBM baru di dalam sistem.</p>
    </div>
    <a href="{{ route('tanks.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<div class="card-table-container" style="max-width: 600px;">
    <h2 class="card-title">Form Data Tangki</h2>
    
    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 1.5rem; flex-direction: column; align-items: flex-start; gap: 0.25rem;">
            @foreach ($errors->all() as $error)
                <span>• {{ $error }}</span>
            @endforeach
        </div>
    @endif

    <form action="{{ route('tanks.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label for="site_id">Site / Lokasi <span style="color: #dc2626;">*</span></label>
            <select name="site_id" id="site_id" class="form-control" required>
                <option value="">Pilih Site</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" @selected(old('site_id') == $site->id)>{{ $site->code }} - {{ $site->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group">
            <label for="code">Kode Tangki</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="Contoh: SPM1, FT05" required>
        </div>
        
        <div class="form-group">
            <label for="main_hole">Main Hole</label>
            <select name="main_hole" id="main_hole" class="form-control" required>
                <option value="">Pilih Main Hole</option>
                <option value="TENGAH" @selected(old('main_hole') === 'TENGAH')>TENGAH</option>
                <option value="(DEPAN + BELAKANG) / 2" @selected(old('main_hole') === '(DEPAN + BELAKANG) / 2')>(DEPAN + BELAKANG) / 2</option>
            </select>
        </div>

        <div class="form-group">
            <label for="capacity">Kapasitas Maksimal (Liter)</label>
            <input type="number" step="0.01" name="capacity" id="capacity" class="form-control" value="{{ old('capacity') }}" placeholder="Masukkan total kapasitas tangki (contoh: 20000)">
        </div>

        <div class="form-group">
            <label for="calibration_file">File Excel Kalibrasi (.xlsx / .xls)</label>
            <input type="file" name="calibration_file" id="calibration_file" class="form-control" accept=".xlsx, .xls">
            <span style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem; display: block;">
                Excel wajib memiliki header pada baris pertama dengan format kolom lengkap: <br>
                <strong>NO</strong> | <strong>DIPP (CM)</strong> | <strong>DIPP (MM)</strong> | <strong>VOLUME (L)</strong> | <strong>VOLUME (M3)</strong> | <strong>DIFF (M3)</strong>
            </span>
        </div>
        
        <div class="form-group">
            <label for="is_active">Status Keaktifan</label>
            <select name="is_active" id="is_active" class="form-control" required>
                <option value="1" {{ old('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Non-Aktif</option>
            </select>
        </div>
        
        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Tangki</button>
        </div>
    </form>
</div>
@endsection
