@php $t = $tamu ?? null; @endphp

<div class="wisma-form-section">
    <div class="wisma-form-section-title">
        <i class="bi bi-door-open"></i>
        <span>Detail Kamar &amp; Tanggal Menginap</span>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <label class="form-label">Nomor Kamar <span class="wisma-required">*</span></label>
            <select name="nomor_kamar" class="form-select" required>
                <option value="">Pilih kamar</option>
                @for ($nomor = 1; $nomor <= \App\Models\WismaTamu::TOTAL_KAMAR; $nomor++)
                    @php
                        $selected = old('nomor_kamar', $t->nomor_kamar ?? null) == $nomor;
                        $kosong = in_array($nomor, $kamarKosong) || $selected;
                    @endphp
                    <option value="{{ $nomor }}" {{ $selected ? 'selected' : '' }} {{ !$kosong ? 'disabled' : '' }}>
                        Kamar {{ $nomor }} {{ !$kosong ? '(sedang terisi)' : '' }}
                    </option>
                @endfor
            </select>
            <div class="form-text text-muted">Hanya kamar kosong yang bisa dipilih.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Check-in <span class="wisma-required">*</span></label>
            <input type="date" name="tanggal_checkin" class="form-control"
                   value="{{ old('tanggal_checkin', optional($t->tanggal_checkin ?? null)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Rencana Check-out <span class="wisma-required">*</span></label>
            <input type="date" name="tanggal_checkout" class="form-control"
                   value="{{ old('tanggal_checkout', optional($t->tanggal_checkout ?? null)->format('Y-m-d')) }}" required>
            <div class="form-text text-muted">Kamar otomatis kosong lagi setelah tanggal ini lewat.</div>
        </div>
    </div>
</div>

<div class="wisma-form-section">
    <div class="wisma-form-section-title">
        <i class="bi bi-person-lines-fill"></i>
        <span>Data Tamu</span>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <label class="form-label">Nama Tamu <span class="wisma-required">*</span></label>
            <input type="text" name="nama_tamu" class="form-control"
                   value="{{ old('nama_tamu', $t->nama_tamu ?? '') }}" required placeholder="Nama tamu yang menginap">
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <label class="form-label">No. HP</label>
            <input type="text" name="no_hp" class="form-control"
                   value="{{ old('no_hp', $t->no_hp ?? '') }}" placeholder="08xxxxxxxxxx">
        </div>
        <div class="col-md-4">
            <label class="form-label">Asal Instansi</label>
            <input type="text" name="asal_instansi" class="form-control"
                   value="{{ old('asal_instansi', $t->asal_instansi ?? '') }}" placeholder="cth: PTPN, Dinas Perkebunan">
        </div>
        <div class="col-md-4">
            <label class="form-label">Keperluan</label>
            <input type="text" name="keperluan" class="form-control"
                   value="{{ old('keperluan', $t->keperluan ?? '') }}" placeholder="cth: Dinas, Kunjungan Kerja">
        </div>
    </div>
</div>

<div class="wisma-form-section">
    <div class="wisma-form-section-title">
        <i class="bi bi-journal-text"></i>
        <span>Catatan Tambahan</span>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <label class="form-label">Catatan</label>
            <textarea name="catatan" rows="3" class="form-control" placeholder="Opsional, cth: permintaan khusus, kondisi kamar, dll.">{{ old('catatan', $t->catatan ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-end gap-3">
    <a href="{{ route('wisma-tamu.index') }}" class="btn wisma-btn-ghost">Batal</a>
    <button type="submit" class="btn wisma-btn-brass">
        <i class="bi bi-save me-1"></i> Simpan
    </button>
</div>