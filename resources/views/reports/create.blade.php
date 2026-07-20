@extends('layouts.app')

@section('title', 'Buat Laporan Baru')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Buat Laporan Harian</h1>
        <p class="page-subtitle">Input sounding tangki dan angka flow meter harian.</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<form action="{{ route('reports.store') }}" method="POST" id="reportForm">
    @csrf

    <div class="report-sheet-container">
        <div class="sheet-header-container" style="display: grid; grid-template-columns: 120px 1fr 120px; align-items: center; width: 100%; margin-bottom: 1.5rem;">
            <div class="sheet-logo-left">
                <img src="{{ asset('logo-pertamina.png') }}" alt="Pertamina Logo" style="height: 40px; width: auto; object-fit: contain;">
            </div>
            <div class="sheet-title-area" style="text-align: center;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: 0.5px;">LAPORAN HARIAN KEGIATAN FUELMAN</h2>
                <h3 style="font-size: 0.95rem; color: var(--text-secondary); font-weight: 600; margin-top: 0.25rem;">WAREHOUSE & INVENTORY SITE SUNGAI PUTTING</h3>
            </div>
            <div class="sheet-logo-right" style="display: flex; justify-content: flex-end;">
                <img src="{{ asset('logo-agm.png') }}" alt="AGM Logo" style="height: 40px; width: auto; object-fit: contain;">
            </div>
        </div>

        <div class="sheet-meta-grid">
            <div class="form-group" style="margin: 0;">
                <label for="date" style="margin-bottom: 4px;">TANGGAL LAPORAN</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $defaultDate) }}" required>
            </div>
            <div class="form-group" style="margin: 0; display: flex; align-items: flex-end;">
                <p style="font-size: 0.85rem; color: var(--text-secondary);">
                    * Kolom abu-abu akan terisi otomatis berdasarkan perhitungan formula.
                </p>
            </div>
        </div>

        <h3 style="margin-top: 2rem; margin-bottom: 0.5rem; font-size: 1rem; color: var(--text-primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 0.25rem;">
            A. LAPORAN HARIAN
        </h3>
        <div>
        <div class="table-responsive">
            <table class="sheet-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 40px;">NO</th>
                        <th rowspan="2" style="width: 100px;">KODE TANGKI</th>
                        <th rowspan="2" style="width: 120px;">MAIN HOLE</th>
                        <th colspan="4" class="section-pagi">SONDING PAGI</th>
                        <th colspan="4" class="section-sore">SONDING SORE</th>
                        <th colspan="3" class="section-fm">ANGKA FM KECIL</th>
                        <th rowspan="2" style="width: 180px;">KETERANGAN</th>
                    </tr>
                    <tr>
                        <th class="section-pagi" style="width: 80px;">SONDING (cm)</th>
                        <th class="section-pagi" style="width: 90px;">LITER</th>
                        <th class="section-pagi" style="width: 85px;">JAM SONDING</th>
                        <th class="section-pagi" style="width: 120px;">NAMA PETUGAS</th>
                        <th class="section-sore" style="width: 80px;">SONDING (cm)</th>
                        <th class="section-sore" style="width: 90px;">LITER</th>
                        <th class="section-sore" style="width: 85px;">JAM SONDING</th>
                        <th class="section-sore" style="width: 120px;">NAMA PETUGAS</th>
                        <th class="section-fm" style="width: 100px;">PAGI</th>
                        <th class="section-fm" style="width: 100px;">SORE</th>
                        <th class="section-fm" style="width: 100px;">JUMLAH PAKAI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tanks as $index => $tank)
                        @php
                            $isAvgRow = false;
                        @endphp
                        <tr class="{{ $isAvgRow ? 'average-row' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $tank->code }}</strong>
                                <input type="hidden" name="items[{{ $index }}][tank_id]" value="{{ $tank->id }}">
                            </td>
                            <td>{{ $tank->main_hole }}</td>
                            
                            <!-- SONDING PAGI -->
                            <td>
                                <input type="number" step="0.01" 
                                       name="items[{{ $index }}][sounding_pagi]" 
                                       class="sheet-input {{ $isAvgRow ? 'read-only' : '' }}" 
                                       data-tank-code="{{ $tank->code }}" 
                                       data-main-hole="{{ $tank->main_hole }}" 
                                       data-type="sounding_pagi" 
                                       value="{{ old("items.{$index}.sounding_pagi") }}"
                                       {{ $isAvgRow ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="text" 
                                       name="items[{{ $index }}][liter_pagi]" 
                                       class="sheet-input read-only" 
                                       data-tank-code="{{ $tank->code }}" 
                                       data-main-hole="{{ $tank->main_hole }}" 
                                       data-type="liter_pagi" 
                                       value="{{ old("items.{$index}.liter_pagi", 'XXXX') }}"
                                       readonly>
                            </td>
                            <td>
                                <input type="time" 
                                       name="items[{{ $index }}][jam_pagi]" 
                                       class="sheet-input {{ $isAvgRow ? 'read-only' : '' }}" 
                                       data-tank-code="{{ $tank->code }}" 
                                       data-main-hole="{{ $tank->main_hole }}" 
                                       data-type="jam_pagi" 
                                       value="{{ old("items.{$index}.jam_pagi") }}"
                                       {{ $isAvgRow ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="text" 
                                       name="items[{{ $index }}][petugas_pagi]" 
                                       class="sheet-input {{ $isAvgRow ? 'read-only' : '' }}" 
                                       data-tank-code="{{ $tank->code }}" 
                                       data-main-hole="{{ $tank->main_hole }}" 
                                       data-type="petugas_pagi" 
                                       value="{{ old("items.{$index}.petugas_pagi", $isAvgRow ? '' : Auth::user()->name) }}"
                                       {{ $isAvgRow ? 'readonly' : '' }}>
                            </td>
                            
                            <!-- SONDING SORE -->
                            <td>
                                <input type="number" step="0.01" 
                                       name="items[{{ $index }}][sounding_sore]" 
                                       class="sheet-input {{ $isAvgRow ? 'read-only' : '' }}" 
                                       data-tank-code="{{ $tank->code }}" 
                                       data-main-hole="{{ $tank->main_hole }}" 
                                       data-type="sounding_sore" 
                                       value="{{ old("items.{$index}.sounding_sore") }}"
                                       {{ $isAvgRow ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="text" 
                                       name="items[{{ $index }}][liter_sore]" 
                                       class="sheet-input read-only" 
                                       data-tank-code="{{ $tank->code }}" 
                                       data-main-hole="{{ $tank->main_hole }}" 
                                       data-type="liter_sore" 
                                       value="{{ old("items.{$index}.liter_sore", 'XXXX') }}"
                                       readonly>
                            </td>
                            <td>
                                <input type="time" 
                                       name="items[{{ $index }}][jam_sore]" 
                                       class="sheet-input {{ $isAvgRow ? 'read-only' : '' }}" 
                                       data-tank-code="{{ $tank->code }}" 
                                       data-main-hole="{{ $tank->main_hole }}" 
                                       data-type="jam_sore" 
                                       value="{{ old("items.{$index}.jam_sore") }}"
                                       {{ $isAvgRow ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="text" 
                                       name="items[{{ $index }}][petugas_sore]" 
                                       class="sheet-input {{ $isAvgRow ? 'read-only' : '' }}" 
                                       data-tank-code="{{ $tank->code }}" 
                                       data-main-hole="{{ $tank->main_hole }}" 
                                       data-type="petugas_sore" 
                                       value="{{ old("items.{$index}.petugas_sore", $isAvgRow ? '' : Auth::user()->name) }}"
                                       {{ $isAvgRow ? 'readonly' : '' }}>
                            </td>
                            
                            <!-- ANGKA FM KECIL -->
                            <td>
                                <input type="number" 
                                       name="items[{{ $index }}][fm_pagi]" 
                                       class="sheet-input" 
                                       data-index="{{ $index }}" 
                                       data-tank-code="{{ $tank->code }}"
                                       data-main-hole="{{ $tank->main_hole }}"
                                       data-type="fm_pagi" 
                                       value="{{ old("items.{$index}.fm_pagi") }}">
                            </td>
                            <td>
                                <input type="number" 
                                       name="items[{{ $index }}][fm_sore]" 
                                       class="sheet-input" 
                                       data-index="{{ $index }}" 
                                       data-tank-code="{{ $tank->code }}"
                                       data-main-hole="{{ $tank->main_hole }}"
                                       data-type="fm_sore" 
                                       value="{{ old("items.{$index}.fm_sore") }}">
                            </td>
                            <td>
                                <input type="number" 
                                       name="items[{{ $index }}][fm_pakai]" 
                                       class="sheet-input read-only" 
                                       data-index="{{ $index }}" 
                                       data-tank-code="{{ $tank->code }}"
                                       data-main-hole="{{ $tank->main_hole }}"
                                       data-type="fm_pakai" 
                                       value="{{ old("items.{$index}.fm_pakai", 0) }}" 
                                       readonly>
                            </td>
                            
                            <!-- KETERANGAN -->
                            <td>
                                <input type="text" 
                                       name="items[{{ $index }}][keterangan]" 
                                       class="sheet-input" 
                                       data-tank-code="{{ $tank->code }}" 
                                       data-main-hole="{{ $tank->main_hole }}" 
                                       data-type="keterangan" 
                                       value="{{ old("items.{$index}.keterangan") }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>

        <!-- B. KAPASITAS TANGKI Summary Widget -->
         <h3 style="margin-top: 2rem; margin-bottom: 0.5rem; font-size: 1rem; color: var(--text-primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 0.25rem;">
                B. KAPASITAS TANGKI
            </h3>

       
           
            @php
                $spm1Cap = $tanks->where('code', 'SPM1')->first()->capacity ?? 0;
                $spm2Cap = $tanks->where('code', 'SPM2')->first()->capacity ?? 0;
                $spm3Cap = $tanks->where('code', 'SPM3')->first()->capacity ?? 0;
                $ft05Cap = $tanks->where('code', 'FT05')->first()->capacity ?? 0;
                $totCapVal = $spm1Cap + $spm2Cap + $spm3Cap + $ft05Cap;
            @endphp
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
                        <td style="text-align: right; padding-right: 8px; width: 15%;" data-cap="{{ $spm1Cap }}">{{ number_format($spm1Cap, 0, ',', '.') }}</td>
                        <td style="width: 18%; padding: 2px 4px;">
                            <input type="number" class="sheet-input soh-input" id="soh_input_SPM1" name="kapasitas[SPM1][soh]" placeholder="" style="text-align: right; width: 100%;">
                        </td>
                        <td id="free_SPM1" style="text-align: right; padding-right: 8px; width: 18%;">{{ number_format($spm1Cap, 0, ',', '.') }}</td>
                        <!-- Rata Rata SPM1 (Input) -->
                        <td style="width: 18%; padding: 2px 4px;">
                            <input type="number" min="0" step="any" class="sheet-input rata-input" id="rata_input_SPM1" name="kapasitas[SPM1][rata]" placeholder="" style="text-align: right; width: 100%;">
                        </td>
                    </tr>
                    <!-- SPM2 -->
                    <tr>
                        <td style="text-align: center;">SPM2</td>
                        <td style="text-align: right; padding-right: 8px;" data-cap="{{ $spm2Cap }}">{{ number_format($spm2Cap, 0, ',', '.') }}</td>
                        <td style="padding: 2px 4px;">
                            <input type="number" class="sheet-input soh-input" id="soh_input_SPM2" name="kapasitas[SPM2][soh]" placeholder="" style="text-align: right; width: 100%;">
                        </td>
                        <td id="free_SPM2" style="text-align: right; padding-right: 8px;">{{ number_format($spm2Cap, 0, ',', '.') }}</td>
                        <!-- Rata Rata SPM2 (Input) -->
                        <td style="padding: 2px 4px;">
                            <input type="number" min="0" step="any" class="sheet-input rata-input" id="rata_input_SPM2" name="kapasitas[SPM2][rata]" placeholder="" style="text-align: right; width: 100%;">
                        </td>
                    </tr>
                    <!-- SPM3 -->
                    <tr>
                        <td style="text-align: center;">SPM3</td>
                        <td style="text-align: right; padding-right: 8px;" data-cap="{{ $spm3Cap }}">{{ number_format($spm3Cap, 0, ',', '.') }}</td>
                        <td style="padding: 2px 4px;">
                            <input type="number" class="sheet-input soh-input" id="soh_input_SPM3" name="kapasitas[SPM3][soh]" placeholder="" style="text-align: right; width: 100%;">
                        </td>
                        <td id="free_SPM3" style="text-align: right; padding-right: 8px;">{{ number_format($spm3Cap, 0, ',', '.') }}</td>
                        <!-- Rata Rata SPM3 (Input) -->
                        <td style="padding: 2px 4px;">
                            <input type="number" min="0" step="any" class="sheet-input rata-input" id="rata_input_SPM3" name="kapasitas[SPM3][rata]" placeholder="" style="text-align: right; width: 100%;">
                        </td>
                    </tr>
                    <!-- FT05 -->
                    <tr>
                        <td style="text-align: center;">FT05</td>
                        <td style="text-align: right; padding-right: 8px;" data-cap="{{ $ft05Cap }}">{{ number_format($ft05Cap, 0, ',', '.') }}</td>
                        <td style="padding: 2px 4px;">
                            <input type="number" class="sheet-input soh-input" id="soh_input_FT05" name="kapasitas[FT05][soh]" placeholder="" style="text-align: right; width: 100%;">
                        </td>
                        <td id="free_FT05" style="text-align: right; padding-right: 8px;">{{ number_format($ft05Cap, 0, ',', '.') }}</td>
                        <td style="padding: 2px 4px;">
                            <input type="number" min="0" step="any" class="sheet-input rata-input" id="rata_input_FT05" name="kapasitas[FT05][rata]" placeholder="" style="text-align: right; width: 100%;">
                        </td>
                    </tr>
                    <!-- Total Row -->
                    <tr style="font-weight: bold; background-color: #e0f2fe;">
                        <td style="text-align: center;">TOTAL</td>
                        <td style="text-align: right; padding-right: 8px;" id="totCap">{{ number_format($totCapVal, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px;" id="totSoh"></td>
                        <td style="text-align: right; padding-right: 8px;" id="totFree">{{ number_format($totCapVal, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px;" id="totRata"></td>
                    </tr>
                </tbody>
            </table>
            </div>


        <!-- SECTION C. TRANSFER SOLAR -->
        <h3 style="margin-top: 2rem; margin-bottom: 0.5rem; font-size: 1rem; color: var(--text-primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 0.25rem;">
            C. TRANSFER SOLAR
        </h3>
        <div class="table-responsive">
            <table class="sheet-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 40px;">NO</th>
                        <th colspan="2">TRANSFER SOLAR</th>
                        <th colspan="3">SONDING TANGKI SPM</th>
                        <th rowspan="2" style="width: 90px;">LITER</th>
                        <th colspan="3">SONDING TANGKI FT</th>
                        <th rowspan="2" style="width: 90px;">LITER</th>
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
                @php
                    $transferTankCodes = $tanks->pluck('code')->unique()->values();
                    $transferRowCount = max($transferTankCodes->count(), count(old('transfers', [])));
                @endphp
                <tbody>
                    @for($i = 0; $i < $transferRowCount; $i++)
                        <tr>
                            <td style="text-align: center;">{{ $i + 1 }}</td>
                            <td>
                                <select name="transfers[{{ $i }}][dari_tangki]" class="sheet-input">
                                    <option value="">Pilih</option>
                                    @foreach($transferTankCodes as $tankCode)
                                        <option value="{{ $tankCode }}" @selected(old("transfers.{$i}.dari_tangki", $transferTankCodes[$i] ?? '') === $tankCode)>{{ $tankCode }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="transfers[{{ $i }}][ke_tangki]" class="sheet-input">
                                    <option value="">Pilih</option>
                                    @foreach($transferTankCodes as $tankCode)
                                        <option value="{{ $tankCode }}" @selected(old("transfers.{$i}.ke_tangki") === $tankCode)>{{ $tankCode }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <!-- SPM Sounding -->
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][spm_awal]" class="sheet-input" data-index="{{ $i }}" data-trans-type="spm_awal">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][spm_akhir]" class="sheet-input" data-index="{{ $i }}" data-trans-type="spm_akhir">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][spm_hasil]" class="sheet-input read-only" data-index="{{ $i }}" data-trans-type="spm_hasil" readonly>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][spm_liter]" class="sheet-input" data-index="{{ $i }}" data-trans-type="spm_liter">
                            </td>
                            <!-- FT Sounding -->
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][ft_awal]" class="sheet-input" data-index="{{ $i }}" data-trans-type="ft_awal">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][ft_akhir]" class="sheet-input" data-index="{{ $i }}" data-trans-type="ft_akhir">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][ft_hasil]" class="sheet-input read-only" data-index="{{ $i }}" data-trans-type="ft_hasil" readonly>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][ft_liter]" class="sheet-input" data-index="{{ $i }}" data-trans-type="ft_liter">
                            </td>
                            <!-- Flow Meter -->
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][fm_awal]" class="sheet-input" data-index="{{ $i }}" data-trans-type="fm_awal">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][fm_akhir]" class="sheet-input" data-index="{{ $i }}" data-trans-type="fm_akhir">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="transfers[{{ $i }}][fm_jumlah]" class="sheet-input read-only" data-index="{{ $i }}" data-trans-type="fm_jumlah" readonly>
                            </td>
                            <!-- Time -->
                            <td>
                                <input type="time" name="transfers[{{ $i }}][jam_mulai]" class="sheet-input" data-index="{{ $i }}" data-trans-type="jam_mulai">
                            </td>
                            <td>
                                <input type="time" name="transfers[{{ $i }}][jam_selesai]" class="sheet-input" data-index="{{ $i }}" data-trans-type="jam_selesai">
                            </td>
                            <td>
                                <input type="text" name="transfers[{{ $i }}][lama_transfer]" class="sheet-input read-only" placeholder="" data-index="{{ $i }}" data-trans-type="lama_transfer" readonly>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- SECTION D. PEMAKAIAN FLOWMETER -->
        <h3 style="margin-top: 2rem; margin-bottom: 0.5rem; font-size: 1rem; color: var(--text-primary); border-bottom: 2px solid #e2e8f0; padding-bottom: 0.25rem;">
            D. PEMAKAIAN FLOWMETER
        </h3>
        <div class="table-responsive">
            <table class="sheet-table" style="max-width: 800px;">
                <thead>
                    <tr>
                        <th style="width: 40px;">NO</th>
                        <th>UNIT</th>
                        <th>JENIS FLOWMETER</th>
                        <th>NOMOR SERI</th>
                        <th>AWAL PAGI</th>
                        <th>AKHIR SORE</th>
                        <th>JUMLAH PAKAI</th>
                    </tr>
                </thead>
                <tbody id="flowmeterRows">
                    @for($i = 0; $i < max(1, count(old('flowmeters', []))); $i++)
                        <tr>
                            <td style="text-align: center;">{{ $i + 1 }}</td>
                            <td>
                                <input type="text" name="flowmeters[{{ $i }}][unit]" class="sheet-input" value="{{ old("flowmeters.{$i}.unit") }}">
                            </td>
                            <td>
                                <input type="text" name="flowmeters[{{ $i }}][jenis_flowmeter]" class="sheet-input" value="{{ old("flowmeters.{$i}.jenis_flowmeter") }}">
                            </td>
                            <td>
                                <input type="text" name="flowmeters[{{ $i }}][nomor_seri]" class="sheet-input" value="{{ old("flowmeters.{$i}.nomor_seri") }}">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="flowmeters[{{ $i }}][awal_pagi]" class="sheet-input" data-index="{{ $i }}" data-flow-type="awal_pagi">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="flowmeters[{{ $i }}][akhir_sore]" class="sheet-input" data-index="{{ $i }}" data-flow-type="akhir_sore">
                            </td>
                            <td>
                                <input type="number" step="1" name="flowmeters[{{ $i }}][jumlah_pakai]" class="sheet-input read-only" data-index="{{ $i }}" data-flow-type="jumlah_pakai" readonly>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <button type="button" id="addFlowmeterRow" class="btn btn-secondary" style="margin-top: 0.75rem;">+ Tambah Flowmeter</button>

        <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">Simpan Laporan Harian</button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reportForm = document.getElementById('reportForm');
    
    // Select SPM3 elements
    const depanSoundingPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="DEPAN"][data-type="sounding_pagi"]');
    const depanLiterPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="DEPAN"][data-type="liter_pagi"]');
    const depanSoundingSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="DEPAN"][data-type="sounding_sore"]');
    const depanLiterSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="DEPAN"][data-type="liter_sore"]');
    const depanJamPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="DEPAN"][data-type="jam_pagi"]');
    const depanPetugasPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="DEPAN"][data-type="petugas_pagi"]');
    const depanJamSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="DEPAN"][data-type="jam_sore"]');
    const depanPetugasSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="DEPAN"][data-type="petugas_sore"]');

    const belakangSoundingPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="BELAKANG"][data-type="sounding_pagi"]');
    const belakangLiterPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="BELAKANG"][data-type="liter_pagi"]');
    const belakangSoundingSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="BELAKANG"][data-type="sounding_sore"]');
    const belakangLiterSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="BELAKANG"][data-type="liter_sore"]');
    const belakangJamPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="BELAKANG"][data-type="jam_pagi"]');
    const belakangPetugasPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="BELAKANG"][data-type="petugas_pagi"]');
    const belakangJamSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="BELAKANG"][data-type="jam_sore"]');
    const belakangPetugasSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="BELAKANG"][data-type="petugas_sore"]');

    const avgSoundingPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="(D+B)/2"][data-type="sounding_pagi"]');
    const avgLiterPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="(D+B)/2"][data-type="liter_pagi"]');
    const avgSoundingSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="(D+B)/2"][data-type="sounding_sore"]');
    const avgLiterSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="(D+B)/2"][data-type="liter_sore"]');
    const avgJamPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="(D+B)/2"][data-type="jam_pagi"]');
    const avgPetugasPagi = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="(D+B)/2"][data-type="petugas_pagi"]');
    const avgJamSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="(D+B)/2"][data-type="jam_sore"]');
    const avgPetugasSore = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="(D+B)/2"][data-type="petugas_sore"]');
    const avgKeterangan = document.querySelector('input[data-tank-code="SPM3"][data-main-hole="(D+B)/2"][data-type="keterangan"]');

    const autoCalculateSPM3Average = false;

    // (D+B)/2 is maintained as a manual tank row.
    function calculateSPM3Averages() {
        if (!autoCalculateSPM3Average) return;
        if (!depanSoundingPagi || !belakangSoundingPagi || !depanLiterPagi || !belakangLiterPagi ||
            !depanSoundingSore || !belakangSoundingSore || !depanLiterSore || !belakangLiterSore ||
            !avgSoundingPagi || !avgLiterPagi || !avgSoundingSore || !avgLiterSore) {
            return;
        }

        // 1. Sounding Pagi Average
        const depSP = parseFloat(depanSoundingPagi.value);
        const belSP = parseFloat(belakangSoundingPagi.value);
        if (!isNaN(depSP) || !isNaN(belSP)) {
            const count = (!isNaN(depSP) ? 1 : 0) + (!isNaN(belSP) ? 1 : 0);
            const sum = (isNaN(depSP) ? 0 : depSP) + (isNaN(belSP) ? 0 : belSP);
            avgSoundingPagi.value = (sum / count).toFixed(1);
        } else {
            avgSoundingPagi.value = '';
        }

        // 2. Liter Pagi Average
        const depLP = parseFloat(depanLiterPagi.value);
        const belLP = parseFloat(belakangLiterPagi.value);
        if (!isNaN(depLP) || !isNaN(belLP)) {
            const count = (!isNaN(depLP) ? 1 : 0) + (!isNaN(belLP) ? 1 : 0);
            const sum = (isNaN(depLP) ? 0 : depLP) + (isNaN(belLP) ? 0 : belLP);
            const avg = sum / count;
            avgLiterPagi.value = avg.toFixed(1);
            if (avgKeterangan) avgKeterangan.value = avg.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        } else {
            avgLiterPagi.value = '';
            if (avgKeterangan) avgKeterangan.value = '';
        }

        // 3. Sounding Sore Average
        const depSS = parseFloat(depanSoundingSore.value);
        const belSS = parseFloat(belakangSoundingSore.value);
        if (!isNaN(depSS) || !isNaN(belSS)) {
            const count = (!isNaN(depSS) ? 1 : 0) + (!isNaN(belSS) ? 1 : 0);
            const sum = (isNaN(depSS) ? 0 : depSS) + (isNaN(belSS) ? 0 : belSS);
            avgSoundingSore.value = (sum / count).toFixed(1);
        } else {
            avgSoundingSore.value = '';
        }

        // 4. Liter Sore Average
        const depLS = parseFloat(depanLiterSore.value);
        const belLS = parseFloat(belakangLiterSore.value);
        if (!isNaN(depLS) || !isNaN(belLS)) {
            const count = (!isNaN(depLS) ? 1 : 0) + (!isNaN(belLS) ? 1 : 0);
            const sum = (isNaN(depLS) ? 0 : depLS) + (isNaN(belLS) ? 0 : belLS);
            avgLiterSore.value = (sum / count).toFixed(1);
        } else {
            avgLiterSore.value = '';
        }

        // 5. Sync metadata
        if (avgJamPagi) avgJamPagi.value = depanJamPagi.value || belakangJamPagi.value || '';
        if (avgPetugasPagi) avgPetugasPagi.value = depanPetugasPagi.value || belakangPetugasPagi.value || '';
        if (avgJamSore) avgJamSore.value = depanJamSore.value || belakangJamSore.value || '';
        if (avgPetugasSore) avgPetugasSore.value = depanPetugasSore.value || belakangPetugasSore.value || '';
    }

    if (autoCalculateSPM3Average) {
        [depanSoundingPagi, belakangSoundingPagi, depanLiterPagi, belakangLiterPagi,
         depanSoundingSore, belakangSoundingSore, depanLiterSore, belakangLiterSore,
         depanJamPagi, belakangJamPagi, depanPetugasPagi, belakangPetugasPagi,
         depanJamSore, belakangJamSore, depanPetugasSore, belakangPetugasSore].forEach(el => {
            if (el) {
                el.addEventListener('input', calculateSPM3Averages);
                el.addEventListener('change', calculateSPM3Averages);
            }
        });
        calculateSPM3Averages();
    }

    // Auto-calculate liters from sounding (AJAX Calibration)
    const allSoundingInputs = document.querySelectorAll('input[data-type="sounding_pagi"], input[data-type="sounding_sore"]');
    
    allSoundingInputs.forEach(input => {
        if (input.classList.contains('read-only')) return; // Skip average rows

        input.addEventListener('change', function() {
            const soundingVal = parseFloat(this.value);
            if (isNaN(soundingVal)) return;

            // Find matching row elements
            const parentRow = this.closest('tr');
            const tankIdInput = parentRow.querySelector('input[name$="[tank_id]"]');
            if (!tankIdInput) return;

            const tankId = tankIdInput.value;
            const isPagi = this.dataset.type === 'sounding_pagi';
            const targetLiterInput = isPagi 
                ? parentRow.querySelector('input[data-type="liter_pagi"]') 
                : parentRow.querySelector('input[data-type="liter_sore"]');

            if (!targetLiterInput) return;

            // Set loading styling/opacity momentarily
            targetLiterInput.style.opacity = '0.5';

            fetch(`/api/tanks/${tankId}/volume?sounding=${soundingVal}`)
                .then(response => response.json())
                .then(data => {
                    targetLiterInput.style.opacity = '1';
                    if (data.volume !== null && data.volume !== undefined) {
                        targetLiterInput.value = data.volume;
                    } else {
                        targetLiterInput.value = 'XXXX';
                    }
                    
                    // If it's SPM3, trigger average recalculation
                    if (input.dataset.tankCode === 'SPM3') {
                        calculateSPM3Averages();
                    }
                })
                .catch(err => {
                    targetLiterInput.style.opacity = '1';
                    targetLiterInput.value = 'XXXX';
                    console.error('Error fetching volume calibration:', err);
                });
        });
    });

    // Flow Meter usage calculation
    const fmPagiInputs = document.querySelectorAll('input[data-type="fm_pagi"]');
    const fmSoreInputs = document.querySelectorAll('input[data-type="fm_sore"]');

    function calculateFMPakai(index) {
        const fmPagiInput = document.querySelector(`input[data-index="${index}"][data-type="fm_pagi"]`);
        const fmSoreInput = document.querySelector(`input[data-index="${index}"][data-type="fm_sore"]`);
        const fmPakaiInput = document.querySelector(`input[data-index="${index}"][data-type="fm_pakai"]`);

        if (fmPagiInput && fmSoreInput && fmPakaiInput) {
            const pagi = parseFloat(fmPagiInput.value);
            const sore = parseFloat(fmSoreInput.value);

            if (!isNaN(pagi) && !isNaN(sore)) {
                fmPakaiInput.value = sore - pagi;
            } else {
                fmPakaiInput.value = 0;
            }
        }
    }

    fmPagiInputs.forEach(input => {
        input.addEventListener('input', function() {
            calculateFMPakai(this.dataset.index);
        });
    });

    fmSoreInputs.forEach(input => {
        input.addEventListener('input', function() {
            calculateFMPakai(this.dataset.index);
        });
    });

    // --- Section B: Transfer Solar Autocalculations ---
    function calculateLamaTransfer(index) {
        const mulaiEl = document.querySelector(`input[data-index="${index}"][data-trans-type="jam_mulai"]`);
        const selesaiEl = document.querySelector(`input[data-index="${index}"][data-trans-type="jam_selesai"]`);
        const lamaEl = document.querySelector(`input[data-index="${index}"][data-trans-type="lama_transfer"]`);

        if (!mulaiEl || !selesaiEl || !lamaEl || !mulaiEl.value || !selesaiEl.value) {
            if (lamaEl) lamaEl.value = '';
            return;
        }

        const [mulaiJam, mulaiMenit] = mulaiEl.value.split(':').map(Number);
        const [selesaiJam, selesaiMenit] = selesaiEl.value.split(':').map(Number);
        let durasiMenit = (selesaiJam * 60 + selesaiMenit) - (mulaiJam * 60 + mulaiMenit);

        if (durasiMenit < 0) durasiMenit += 24 * 60;

        const jam = Math.floor(durasiMenit / 60);
        const menit = durasiMenit % 60;
        lamaEl.value = jam > 0 && menit > 0 ? `${jam} jam ${menit} menit` : jam > 0 ? `${jam} jam` : `${menit} menit`;
    }

    const transInputs = document.querySelectorAll('input[data-trans-type]');
    transInputs.forEach(input => {
        input.addEventListener('input', function() {
            const index = this.dataset.index;
            
            // 1. SPM Hasil: Awal - Akhir
            const spmAwalEl = document.querySelector(`input[data-index="${index}"][data-trans-type="spm_awal"]`);
            const spmAkhirEl = document.querySelector(`input[data-index="${index}"][data-trans-type="spm_akhir"]`);
            const spmHasilEl = document.querySelector(`input[data-index="${index}"][data-trans-type="spm_hasil"]`);
            if (spmAwalEl && spmAkhirEl && spmHasilEl) {
                const awal = parseFloat(spmAwalEl.value);
                const akhir = parseFloat(spmAkhirEl.value);
                if (!isNaN(awal) && !isNaN(akhir)) {
                    spmHasilEl.value = (awal - akhir).toFixed(1);
                } else {
                    spmHasilEl.value = '';
                }
            }

            // 2. FT Hasil: Akhir - Awal
            const ftAwalEl = document.querySelector(`input[data-index="${index}"][data-trans-type="ft_awal"]`);
            const ftAkhirEl = document.querySelector(`input[data-index="${index}"][data-trans-type="ft_akhir"]`);
            const ftHasilEl = document.querySelector(`input[data-index="${index}"][data-trans-type="ft_hasil"]`);
            if (ftAwalEl && ftAkhirEl && ftHasilEl) {
                const awal = parseFloat(ftAwalEl.value);
                const akhir = parseFloat(ftAkhirEl.value);
                if (!isNaN(awal) && !isNaN(akhir)) {
                    ftHasilEl.value = (akhir - awal).toFixed(1);
                } else {
                    ftHasilEl.value = '';
                }
            }

            // 3. FM Jumlah: Akhir - Awal
            const fmAwalEl = document.querySelector(`input[data-index="${index}"][data-trans-type="fm_awal"]`);
            const fmAkhirEl = document.querySelector(`input[data-index="${index}"][data-trans-type="fm_akhir"]`);
            const fmJumlahEl = document.querySelector(`input[data-index="${index}"][data-trans-type="fm_jumlah"]`);
            if (fmAwalEl && fmAkhirEl && fmJumlahEl) {
                const awal = parseFloat(fmAwalEl.value);
                const akhir = parseFloat(fmAkhirEl.value);
                if (!isNaN(awal) && !isNaN(akhir)) {
                    fmJumlahEl.value = (akhir - awal).toFixed(1);
                } else {
                    fmJumlahEl.value = '';
                }
            }

            calculateLamaTransfer(index);
        });
    });
    document.querySelectorAll('input[data-trans-type="jam_mulai"]').forEach(input => calculateLamaTransfer(input.dataset.index));

    // --- Section C: Pemakaian Flowmeter ---
    function calculateFlowmeterUsage(index) {
        const awalEl = document.querySelector(`input[data-index="${index}"][data-flow-type="awal_pagi"]`);
        const akhirEl = document.querySelector(`input[data-index="${index}"][data-flow-type="akhir_sore"]`);
        const jumlahEl = document.querySelector(`input[data-index="${index}"][data-flow-type="jumlah_pakai"]`);
        if (!awalEl || !akhirEl || !jumlahEl) return;

        const awal = parseFloat(awalEl.value);
        const akhir = parseFloat(akhirEl.value);
        jumlahEl.value = !isNaN(awal) && !isNaN(akhir) ? Math.round(akhir - awal) : '';
    }

    const flowmeterRows = document.getElementById('flowmeterRows');
    flowmeterRows.addEventListener('input', event => {
        if (event.target.matches('input[data-flow-type="awal_pagi"], input[data-flow-type="akhir_sore"]')) {
            calculateFlowmeterUsage(event.target.dataset.index);
        }
    });
    document.querySelectorAll('input[data-flow-type="awal_pagi"]').forEach(input => calculateFlowmeterUsage(input.dataset.index));

    document.getElementById('addFlowmeterRow').addEventListener('click', () => {
        const index = flowmeterRows.querySelectorAll('tr').length;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="text-align: center;">${index + 1}</td>
            <td><input type="text" name="flowmeters[${index}][unit]" class="sheet-input"></td>
            <td><input type="text" name="flowmeters[${index}][jenis_flowmeter]" class="sheet-input"></td>
            <td><input type="text" name="flowmeters[${index}][nomor_seri]" class="sheet-input"></td>
            <td><input type="number" step="0.01" name="flowmeters[${index}][awal_pagi]" class="sheet-input" data-index="${index}" data-flow-type="awal_pagi"></td>
            <td><input type="number" step="0.01" name="flowmeters[${index}][akhir_sore]" class="sheet-input" data-index="${index}" data-flow-type="akhir_sore"></td>
            <td><input type="number" step="1" name="flowmeters[${index}][jumlah_pakai]" class="sheet-input read-only" data-index="${index}" data-flow-type="jumlah_pakai" readonly></td>`;
        flowmeterRows.appendChild(row);
    });

    // ===== Kapasitas Tangki Widget - SOH Input Auto-Calculate =====
    const tankCapacities = @json($tanks->groupBy('code')->map(fn($g) => $g->first()->capacity ?? 0));

    function updateKapasitasWidget() {
        let totSoh = 0;
        let totFree = 0;
        let totRata = 0;
        let spmRataSum = 0;

        ['SPM1', 'SPM2', 'SPM3', 'FT05'].forEach(tCode => {
            const capacity = tankCapacities[tCode] || 0;

            // Read SOH directly from input
            const sohInput = document.getElementById('soh_input_' + tCode);
            const soh = sohInput ? (parseFloat(sohInput.value) || 0) : 0;

            const freeSpace = Math.max(0, capacity - soh);

            // Update Bisa Masuk display
            const freeEl = document.getElementById('free_' + tCode);
            if (freeEl) freeEl.textContent = new Intl.NumberFormat('id-ID').format(freeSpace);

            const rataInput = document.getElementById('rata_input_' + tCode);
            const rata = rataInput ? (parseFloat(rataInput.value) || 0) : 0;

            totSoh += soh;
            totFree += freeSpace;
            totRata += rata;
        });

        // Update totals
        const fmt = v => v > 0 ? new Intl.NumberFormat('id-ID').format(v) : '';
        const totSohEl  = document.getElementById('totSoh');
        const totFreeEl = document.getElementById('totFree');
        const totRataEl = document.getElementById('totRata');

        if (totSohEl)  totSohEl.textContent  = fmt(totSoh);
        if (totFreeEl) totFreeEl.textContent = new Intl.NumberFormat('id-ID').format(totFree);
        if (totRataEl) totRataEl.textContent  = fmt(totRata);
    }

    // Hook SOH inputs → update Bisa Masuk immediately on typing
    document.querySelectorAll('.soh-input').forEach(input => {
        input.addEventListener('input', updateKapasitasWidget);
    });

    document.querySelectorAll('.rata-input').forEach(input => input.addEventListener('input', updateKapasitasWidget));

    updateKapasitasWidget();
});
</script>
@endsection
