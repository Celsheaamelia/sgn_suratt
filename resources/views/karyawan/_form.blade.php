@php $k = $karyawan ?? null; @endphp

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label">NIK <span class="ledger-required">*</span></label>
        <input type="text" name="nik" class="form-control" required value="{{ old('nik', $k->nik ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">No. KTP</label>
        <input type="text" name="no_ktp" class="form-control" value="{{ old('no_ktp', $k->no_ktp ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Nama Lengkap <span class="ledger-required">*</span></label>
        <input type="text" name="nama" class="form-control" required value="{{ old('nama', $k->nama ?? '') }}">
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <label class="form-label">Tempat, Tanggal Lahir</label>
        <input type="text" name="tempat_tanggal_lahir" class="form-control"
               value="{{ old('tempat_tanggal_lahir', $k->tempat_tanggal_lahir ?? '') }}"
               placeholder="cth: Lumajang, 29 November 1971">
    </div>
    <div class="col-md-4">
        <label class="form-label">Jenis Kelamin</label>
        <select name="jenis_kelamin" class="form-select">
            <option value="">-</option>
            <option value="Laki-laki" @selected(old('jenis_kelamin', $k->jenis_kelamin ?? '') === 'Laki-laki')>Laki-laki</option>
            <option value="Perempuan" @selected(old('jenis_kelamin', $k->jenis_kelamin ?? '') === 'Perempuan')>Perempuan</option>
        </select>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Agama</label>
        <input type="text" name="agama" class="form-control" value="{{ old('agama', $k->agama ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Status Perkawinan</label>
        <input type="text" name="status_perkawinan" class="form-control" value="{{ old('status_perkawinan', $k->status_perkawinan ?? '') }}">
    </div>
</div>

<div class="mb-4">
    <label class="form-label">Alamat Lengkap</label>
    <textarea name="alamat" rows="2" class="form-control">{{ old('alamat', $k->alamat ?? '') }}</textarea>
</div>

<div class="d-flex align-items-center justify-content-end gap-3">
    <a href="{{ route('karyawan.index') }}" class="btn ledger-btn-ghost">Batal</a>
    <button type="submit" class="btn ledger-btn-brass">
        <i class="bi bi-save me-1"></i> Simpan
    </button>
</div>
