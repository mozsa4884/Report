@php
    $savedItems = isset($report) ? $report->items->values() : collect();
    $oldItems = old('items', []);
    $itemRowCount = max(1, $savedItems->count(), count($oldItems));
@endphp
<tbody id="reportItemRows">
    @for($i = 0; $i < $itemRowCount; $i++)
        @php
            $savedItem = $savedItems->get($i);
            $selectedTankId = old("items.{$i}.tank_id", $savedItem?->tank_id);
            $selectedTank = $tanks->firstWhere('id', $selectedTankId);
        @endphp
        <tr>
            <td class="row-number" style="text-align: center;">{{ $i + 1 }}</td>
            <td class="tank-code-cell">
                @if($savedItem)
                    <input type="hidden" name="items[{{ $i }}][attachment_key]" value="item-{{ $savedItem->id }}">
                @endif
                <select name="items[{{ $i }}][tank_id]" class="sheet-input tank-select" data-item-type="tank_id">
                    <option value="">Pilih tangki</option>
                    @foreach($tanks as $tank)
                        <option value="{{ $tank->id }}" 
                                data-main-hole="{{ $tank->main_hole }}" 
                                data-site-id="{{ $tank->site_id }}"
                                @selected((string) $selectedTankId === (string) $tank->id)>
                            {{ $tank->code }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="item-main-hole" style="text-align: center;">{{ $selectedTank?->main_hole ?? '-' }}</td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][sounding_pagi]" class="sheet-input" data-item-type="sounding_pagi" value="{{ old("items.{$i}.sounding_pagi", $savedItem?->sounding_pagi) }}"></td>
            <td><input type="text" name="items[{{ $i }}][liter_pagi]" class="sheet-input read-only" data-item-type="liter_pagi" value="{{ old("items.{$i}.liter_pagi", $savedItem && $savedItem->sounding_pagi !== null && Auth::user()->isFuelman() ? 'XXXX' : ($savedItem?->liter_pagi ?? '')) }}" readonly></td>
            <td><input type="time" name="items[{{ $i }}][jam_pagi]" class="sheet-input" value="{{ old("items.{$i}.jam_pagi", $savedItem?->jam_pagi ? \Carbon\Carbon::parse($savedItem->jam_pagi)->format('H:i') : '') }}"></td>
            <td><input type="text" name="items[{{ $i }}][petugas_pagi]" class="sheet-input" value="{{ old("items.{$i}.petugas_pagi", $savedItem?->petugas_pagi) }}"></td>
            <td><input type="number" step="0.01" name="items[{{ $i }}][sounding_sore]" class="sheet-input" data-item-type="sounding_sore" value="{{ old("items.{$i}.sounding_sore", $savedItem?->sounding_sore) }}"></td>
            <td><input type="text" name="items[{{ $i }}][liter_sore]" class="sheet-input read-only" data-item-type="liter_sore" value="{{ old("items.{$i}.liter_sore", $savedItem && $savedItem->sounding_sore !== null && Auth::user()->isFuelman() ? 'XXXX' : ($savedItem?->liter_sore ?? '')) }}" readonly></td>
            <td><input type="time" name="items[{{ $i }}][jam_sore]" class="sheet-input" value="{{ old("items.{$i}.jam_sore", $savedItem?->jam_sore ? \Carbon\Carbon::parse($savedItem->jam_sore)->format('H:i') : '') }}"></td>
            <td><input type="text" name="items[{{ $i }}][petugas_sore]" class="sheet-input" value="{{ old("items.{$i}.petugas_sore", $savedItem?->petugas_sore) }}"></td>
            <td><input type="number" name="items[{{ $i }}][fm_pagi]" class="sheet-input" data-item-type="fm_pagi" value="{{ old("items.{$i}.fm_pagi", $savedItem?->fm_pagi) }}"></td>
            <td><input type="number" name="items[{{ $i }}][fm_sore]" class="sheet-input" data-item-type="fm_sore" value="{{ old("items.{$i}.fm_sore", $savedItem?->fm_sore) }}"></td>
            <td><input type="number" name="items[{{ $i }}][fm_pakai]" class="sheet-input read-only" data-item-type="fm_pakai" value="{{ old("items.{$i}.fm_pakai", $savedItem && $savedItem->fm_pakai !== null && $savedItem->fm_pakai != 0 ? $savedItem->fm_pakai : '') }}" readonly></td>
            <td><input type="text" name="items[{{ $i }}][keterangan]" class="sheet-input" value="{{ old("items.{$i}.keterangan", $savedItem?->keterangan) }}"></td>
            <td class="photo-upload-cell">
                @php
                    $existingAttachments = $savedItem && isset($report)
                        ? $report->attachments->where('section', 'A')->where('attachment_key', "item-{$savedItem->id}")
                        : collect();
                @endphp
                @if($existingAttachments->isNotEmpty())
                    <div class="saved-photo-count">{{ $existingAttachments->count() }} foto tersimpan</div>
                    <div class="saved-photo-list">
                        @foreach($existingAttachments as $attachment)
                            <div class="saved-photo-card" data-attachment-id="{{ $attachment->id }}">
                                <img src="{{ Storage::disk(config('filesystems.report_attachment_disk', 'public') === 'local' ? 'public' : config('filesystems.report_attachment_disk', 'public'))->url($attachment->path) }}" alt="Foto tangki">
                                <button type="button" class="photo-remove-button" data-delete-attachment="{{ $attachment->id }}" title="Hapus foto" aria-label="Hapus foto">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m-6 5v6m4-6v6"></path></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="photo-selected-list" data-photo-selected></div>
                <label class="photo-upload-button">
                    Pilih foto
                    <input type="file" name="items[{{ $i }}][photos][]" accept="image/jpeg,image/png,image/webp" multiple data-photo-input>
                </label>
            </td>
            <td class="row-action" style="text-align: center;"></td>
        </tr>
    @endfor
</tbody>
