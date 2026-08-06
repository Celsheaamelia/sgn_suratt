@extends('layouts.app')

@section('content')

@include('partials.ledger-styles')

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        @if (session('success'))
            <div class="alert ledger-alert-success d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert ledger-alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert ledger-alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="row g-4">

            {{-- FORM --}}
            <div class="col-lg-8">
                <div class="card ledger-card h-100">
                    <div class="card-header ledger-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h2 class="ledger-title mb-0">Buat Kontrak Karyawan</h2>
                            {{-- <p class="ledger-subtitle mb-0">Cari karyawan dari database, data diri otomatis terisi ke dokumen.</p> --}}
                        </div>
                        {{-- Tombol Kelola Template --}}
                        <a href="{{ Route::has('kontrak-template.index') ? route('kontrak-template.index') : url('/kontrak-template') }}"
                           class="btn btn-sm ledger-btn-ghost">
                            <i class="bi bi-file-earmark-text me-1"></i>
                            Kelola Format
                        </a>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('kontrak.store') }}" class="ledger-form" id="kontrakForm">
                            @csrf

                            {{-- Cari Karyawan --}}
                            <div class="mb-3 karyawan-search-wrap">
                                <label class="form-label">Cari Karyawan <span class="ledger-required">*</span></label>
                                <input type="text" id="karyawanSearch" class="form-control"
                                       placeholder="Ketik NIK atau nama karyawan..." autocomplete="off">
                                <div class="karyawan-search-results" id="karyawanResults"></div>
                                <input type="hidden" name="karyawan_id" id="karyawan_id" value="{{ old('karyawan_id') }}" required>
                                {{-- <div class="ledger-help">
                                    Belum ada di database? <a href="{{ route('karyawan.create') }}" target="_blank">Tambah data karyawan baru</a>.
                                </div> --}}
                            </div>

                            {{-- Kartu data karyawan terpilih (autofill) --}}
                            <div class="karyawan-selected-card mb-4 d-none" id="karyawanCard">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-bold" id="karyawanNama" style="color: var(--ink);">-</div>
                                        <div class="ledger-help mb-0" id="karyawanDetail">-</div>
                                    </div>
                                    <button type="button" class="btn btn-sm ledger-btn-ghost" id="clearKaryawan">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 mb-1">
                                <div class="col-md-6">
                                    <label for="jenis_kontrak_id" class="form-label">Jenis Kontrak <span class="ledger-required">*</span></label>
                                    <div class="d-flex gap-2 align-items-start">
                                        <select name="jenis_kontrak_id" id="jenis_kontrak_id" required class="form-select">
                                            <option value="">Pilih Jenis Kontrak</option>
                                            @foreach ($jenisList as $jenis)
                                                <option value="{{ $jenis->id }}"
                                                        data-kode="{{ $jenis->kode }}"
                                                        data-kode-nomor="{{ $jenis->kode_nomor ?? $jenis->kode }}"
                                                        data-label="{{ $jenis->nama_singkat ?: $jenis->nama_jenis }}"
                                                        data-masa="{{ $jenis->masa_berlaku_bulan }}"
                                                        data-masa-giling="{{ $jenis->masa_giling ? '1' : '0' }}"
                                                        @selected(old('jenis_kontrak_id') == $jenis->id)>
                                                    {{ $jenis->kode_nomor ?? $jenis->kode }} — {{ $jenis->nama_singkat ?: $jenis->nama_jenis }}
                                                </option>
                                            @endforeach
                                        </select>
                                        {{-- Tombol Preview Template --}}
                                        <button type="button" id="previewTemplateBtn" class="btn ledger-btn-ghost flex-shrink-0" disabled title="Pilih jenis kontrak dulu">
                                            {{-- <i class="bi bi-eye me-1"></i> --}}
                                            {{-- Pratinjau --}}
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="nomorUrut" class="form-label">Nomor Urut <span class="ledger-required">*</span></label>
                                    <input id="nomorUrut" name="nomor_urut" type="number" min="1"
                                           value="{{ old('nomor_urut', $nextSequence) }}" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="ledger-help" id="nomorUrutHelp">
                                    Nomor #{{ str_pad($nextSequence, 3, '0', STR_PAD_LEFT) }} tersedia paling awal untuk tanggal ini.
                                    Ditandatangani oleh <strong>{{ $penandatangan->jabatan ?? 'Manajemen SG26' }}</strong> (otomatis).
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="tanggal" class="form-label">Tanggal Kontrak <span class="ledger-required">*</span></label>
                                    <input type="date" name="tanggal" id="tanggal" required
                                           value="{{ old('tanggal', date('Y-m-d')) }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="tanggal_mulai" class="form-label">Mulai Berlaku <span class="ledger-required">*</span></label>
                                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" required
                                           value="{{ old('tanggal_mulai', date('Y-m-d')) }}" class="form-control">
                                </div>
                            </div>

                            {{-- Tanggal Selesai: cuma muncul kalau jenis kontraknya BUKAN masa giling.
                                 Untuk masa giling (PJJ/DMG), selesainya otomatis "sampai berakhirnya
                                 Masa Giling" - tidak ada tanggal tetap yang bisa diisi manual. --}}
                            <div class="mb-3" id="tanggalSelesaiWrap">
                                <label for="tanggal_selesai" class="form-label" id="tanggalSelesaiLabel">
                                    Tanggal Selesai <span class="ledger-required">*</span>
                                </label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                       value="{{ old('tanggal_selesai') }}" class="form-control">
                                <div class="ledger-help d-none" id="masaGilingNote">
                                    Otomatis: <strong>sampai dengan ditetapkan tanggal berakhirnya Masa Giling</strong>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="jabatan_kontrak" class="form-label">Jabatan (di kontrak ini)</label>
                                    <input type="text" name="jabatan_kontrak" id="jabatan_kontrak"
                                           value="{{ old('jabatan_kontrak') }}" class="form-control" placeholder="cth: Operator Mesin">
                                </div>
                                <div class="col-md-6">
                                    <label for="bagian_kontrak" class="form-label">Bagian / Departemen (di kontrak ini)</label>
                                    <select name="bagian_kontrak" id="bagian_kontrak" class="form-select">
                                        <option value="">-- Pilih Bagian --</option>
                                        @foreach (\App\Support\BagianKontrak::OPTIONS as $bagianOpt)
                                            <option value="{{ $bagianOpt }}" @selected(old('bagian_kontrak') == $bagianOpt)>{{ $bagianOpt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rincian Pekerjaan</label>
                                <input type="text" name="rincian_pekerjaan_1" value="{{ old('rincian_pekerjaan_1') }}" class="form-control mb-2" placeholder="a. ...">
                                <input type="text" name="rincian_pekerjaan_2" value="{{ old('rincian_pekerjaan_2') }}" class="form-control mb-2" placeholder="b. ...">
                                <input type="text" name="rincian_pekerjaan_3" value="{{ old('rincian_pekerjaan_3') }}" class="form-control" placeholder="c. ...">
                            </div>

                            <div class="d-flex align-items-center justify-content-end gap-3">
                                <button type="reset" class="btn ledger-btn-ghost" id="resetBtn">Reset</button>
                                <button type="submit" class="btn ledger-btn-brass">
                                    <i class="bi bi-file-earmark-word me-1"></i>
                                    Simpan &amp; Buat Dokumen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- PREVIEW --}}
            <div class="col-lg-4">
                <div class="card ledger-stamp mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <i class="bi bi-eye"></i>
                            <h3 class="ledger-stamp-title mb-0">Pratinjau</h3>
                        </div>

                        <div class="ledger-stamp-box mb-4">
                            <p class="ledger-stamp-label mb-1">Nomor Kontrak</p>
                            <p class="mb-0" id="previewNumber">{{ config('kontrak.prefix', 'SG26-PERSE') }}-.--------.{{ str_pad($nextSequence, 3, '0', STR_PAD_LEFT) }}</p>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Karyawan</span>
                            <span class="ledger-stamp-value" id="previewKaryawan">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Jenis</span>
                            <span class="ledger-stamp-value" id="previewJenis">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Penandatangan</span>
                            <span class="ledger-stamp-value">{{ $penandatangan->jabatan ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="ledger-stamp-key">Tanggal</span>
                            <span class="ledger-stamp-value" id="previewTanggal">-</span>
                        </div>
                    </div>
                </div>

                <div class="card ledger-status">
                    <div class="card-body">
                        <h3 class="ledger-status-title mb-3">Status Sistem</h3>
                        <div class="d-flex align-items-center gap-2 ledger-status-line">
                            <span class="ledger-status-dot"></span>
                            Nomor #{{ str_pad($nextSequence, 3, '0', STR_PAD_LEFT) }} siap dipakai
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ------- Cari & autofill karyawan -------
    const searchInput = document.getElementById('karyawanSearch');
    const resultsBox  = document.getElementById('karyawanResults');
    const karyawanIdInput = document.getElementById('karyawan_id');
    const karyawanCard = document.getElementById('karyawanCard');
    const karyawanNama = document.getElementById('karyawanNama');
    const karyawanDetail = document.getElementById('karyawanDetail');
    const previewKaryawan = document.getElementById('previewKaryawan');
    let searchTimer = null;

    async function doSearch(q) {
        try {
            const res = await fetch(`{{ route('karyawan.search') }}?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            renderResults(data);
        } catch (e) {
            console.error(e);
        }
    }

    function renderResults(list) {
        if (!list.length) {
            resultsBox.innerHTML = '<div class="karyawan-search-item text-muted">Tidak ditemukan.</div>';
            resultsBox.classList.add('show');
            return;
        }
        resultsBox.innerHTML = list.map(k => `
            <div class="karyawan-search-item" data-item='${JSON.stringify(k).replace(/'/g, "&apos;")}'>
                <div>${k.nama}</div>
                <div class="nik">${k.nik} ${k.no_ktp ? '· KTP ' + k.no_ktp : ''}</div>
            </div>
        `).join('');
        resultsBox.classList.add('show');

        resultsBox.querySelectorAll('.karyawan-search-item').forEach(el => {
            el.addEventListener('click', function () {
                const k = JSON.parse(this.dataset.item.replace(/&apos;/g, "'"));
                selectKaryawan(k);
            });
        });
    }

    function selectKaryawan(k) {
    karyawanIdInput.value = k.id;
    karyawanNama.textContent = k.nama;
    karyawanDetail.textContent = `${k.nik} · ${k.tempat_tanggal_lahir ?? '-'}`;
    karyawanCard.classList.remove('d-none');
    searchInput.value = k.nama;
    previewKaryawan.textContent = k.nama;
    resultsBox.classList.remove('show');

    document.getElementById('jabatan_kontrak').value = k.jabatan_terakhir ?? '';
    document.getElementById('bagian_kontrak').value = k.bagian_terakhir ?? '';
    document.querySelector('[name="rincian_pekerjaan_1"]').value = k.rincian_1_terakhir ?? '';
    document.querySelector('[name="rincian_pekerjaan_2"]').value = k.rincian_2_terakhir ?? '';
    document.querySelector('[name="rincian_pekerjaan_3"]').value = k.rincian_3_terakhir ?? '';

    if (k.jenis_kontrak_id_terakhir) {
        const jenisSelect = document.getElementById('jenis_kontrak_id');
        const optionExists = [...jenisSelect.options].some(opt => opt.value == k.jenis_kontrak_id_terakhir);
        if (optionExists) {
            jenisSelect.value = k.jenis_kontrak_id_terakhir;
            jenisSelect.dispatchEvent(new Event('change'));
        }
    }
}

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        karyawanIdInput.value = '';
        karyawanCard.classList.add('d-none');
        previewKaryawan.textContent = '-';
        if (q.length < 1) { resultsBox.classList.remove('show'); return; }
        searchTimer = setTimeout(() => doSearch(q), 200);
    });

    document.addEventListener('click', function (e) {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.classList.remove('show');
        }
    });

    document.getElementById('clearKaryawan').addEventListener('click', function () {
        karyawanIdInput.value = '';
        searchInput.value = '';
        karyawanCard.classList.add('d-none');
        previewKaryawan.textContent = '-';
    });

    // ------- Live preview + toggle Tanggal Selesai per jenis kontrak -------
    const jenisEl = document.getElementById('jenis_kontrak_id');
    const tanggalEl = document.getElementById('tanggal');
    const tanggalSelesaiEl = document.getElementById('tanggal_selesai');
    const tanggalSelesaiLabel = document.getElementById('tanggalSelesaiLabel');
    const masaGilingNote = document.getElementById('masaGilingNote');

    const nomorUrut = document.getElementById('nomorUrut');
    const nomorUrutHelp = document.getElementById('nomorUrutHelp');
    const previewNumber = document.getElementById('previewNumber');
    const previewJenis = document.getElementById('previewJenis');
    const previewTanggal = document.getElementById('previewTanggal');
    const submitBtn = document.querySelector('#kontrakForm button[type="submit"]');

    // ------- Tombol Preview Template -------
    const previewTemplateBtn = document.getElementById('previewTemplateBtn');
    // NOTE: sesuaikan URL ini kalau route preview template kamu berbeda.
    // Asumsi endpoint: GET /kontrak-template/preview/{jenis_kontrak_id}
    function updatePreviewTemplateBtn() {
        if (jenisEl.value) {
            previewTemplateBtn.disabled = false;
            previewTemplateBtn.title = 'Lihat preview template untuk jenis kontrak ini';
        } else {
            previewTemplateBtn.disabled = true;
            previewTemplateBtn.title = 'Pilih jenis kontrak dulu';
        }
    }
    previewTemplateBtn.addEventListener('click', function () {
        if (!jenisEl.value) return;
        const url = `{{ url('/kontrak-template/preview') }}/${jenisEl.value}`;
        window.open(url, '_blank');
    });

    let seqText = "{{ str_pad($nextSequence,3,'0',STR_PAD_LEFT) }}";
    let terpakaiNumbers = [];
    let direservasiNumbers = [];
    const kontrakPrefix = "{{ config('kontrak.prefix', 'SG26-PERSE') }}";

    function pad(n){ return String(n).padStart(3, '0'); }

    function selectedKodeNomor(select){
        if(select.selectedIndex==-1) return "---";
        return select.options[select.selectedIndex].dataset.kodeNomor ?? "---";
    }

    function formatTanggal(tanggal){
        if(!tanggal) return "--------";
        return tanggal.replaceAll("-", "");
    }

    function updatePreview(){
        let kodeNomor = selectedKodeNomor(jenisEl);
        let label = jenisEl.selectedIndex > -1 ? (jenisEl.options[jenisEl.selectedIndex].dataset.label || '-') : '-';

        previewNumber.textContent = `${kontrakPrefix}-${kodeNomor}/${formatTanggal(tanggalEl.value)}.${seqText}`;
        previewJenis.textContent = label;
        previewTanggal.textContent = tanggalEl.value || '-';
    }

    // Toggle input Tanggal Selesai: masa giling (PJJ/DMG) -> sembunyikan input,
    // tampil catatan otomatis. Bukan masa giling (KTR) -> input wajib.
    function toggleTanggalSelesai(){
        const opt = jenisEl.options[jenisEl.selectedIndex];
        const masaGiling = opt && opt.dataset.masaGiling === '1';

        if (masaGiling) {
            tanggalSelesaiEl.classList.add('d-none');
            tanggalSelesaiEl.required = false;
            tanggalSelesaiEl.value = '';
            tanggalSelesaiLabel.querySelector('.ledger-required')?.classList.add('d-none');
            masaGilingNote.classList.remove('d-none');
        } else {
            tanggalSelesaiEl.classList.remove('d-none');
            tanggalSelesaiEl.required = true;
            tanggalSelesaiLabel.querySelector('.ledger-required')?.classList.remove('d-none');
            masaGilingNote.classList.add('d-none');
        }
    }

    jenisEl.addEventListener('change', function () {
        toggleTanggalSelesai();
        updatePreview();
        updatePreviewTemplateBtn();
    });

    async function loadUsedNumbers(){
        if(!tanggalEl.value || !jenisEl.value){ terpakaiNumbers = []; direservasiNumbers = []; return; }
        try{
            let response = await fetch(`{{ route('kontrak.cek-status-nomor') }}?tanggal=${tanggalEl.value}&jenis_kontrak_id=${jenisEl.value}`);
            if (!response.ok) throw new Error('HTTP ' + response.status);
            let data = await response.json();
            terpakaiNumbers = data.terpakai ?? [];
            direservasiNumbers = data.direservasi ?? [];
        }catch(err){
            console.log(err);
            terpakaiNumbers = [];
            direservasiNumbers = [];
        }
    }

    // SESUDAH — hapus ketergantungan ke jenisEl.value, karena nomor urut
// sekarang satu rangkaian gabungan (surat + KTR + PJJ), gak lagi per jenis
    async function loadUsedNumbers(){
        if(!tanggalEl.value){ terpakaiNumbers = []; direservasiNumbers = []; return; }
        try{
            let response = await fetch(`{{ route('kontrak.cek-status-nomor') }}?tanggal=${tanggalEl.value}`);
            if (!response.ok) throw new Error('HTTP ' + response.status);
            let data = await response.json();
            terpakaiNumbers = data.terpakai ?? [];
            direservasiNumbers = data.direservasi ?? [];
        }catch(err){
            console.log(err);
            terpakaiNumbers = [];
            direservasiNumbers = [];
        }
    }

    async function refreshNextSequence(){
        if(!tanggalEl.value) return;
        try{
            const res = await fetch(`{{ route('kontrak.next-sequence') }}?tanggal=${tanggalEl.value}`);
            if(!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            nomorUrut.value = parseInt(data.sequence, 10);
        }catch(err){
            console.log(err);
        }
    }

    function checkNomorStatus(){
        let nomor = parseInt(nomorUrut.value);
        seqText = pad(nomor || 0);

        if(!nomor){
            nomorUrutHelp.textContent = 'Isi nomor urut.';
            nomorUrutHelp.style.color = '';
            if (submitBtn) submitBtn.disabled = false;
            updatePreview();
            return;
        }

        if (terpakaiNumbers.includes(nomor)) {
            nomorUrutHelp.textContent = `Nomor #${pad(nomor)} sudah dipakai.`;
            nomorUrutHelp.style.color = 'var(--danger)';
            if (submitBtn) submitBtn.disabled = true;
        } else if (direservasiNumbers.includes(nomor)) {
            nomorUrutHelp.textContent = `Nomor #${pad(nomor)} sedang direservasi.`;
            nomorUrutHelp.style.color = 'var(--danger)';
            if (submitBtn) submitBtn.disabled = true;
        } else {
            nomorUrutHelp.textContent = `Nomor #${pad(nomor)} tersedia.`;
            nomorUrutHelp.style.color = 'var(--success)';
            if (submitBtn) submitBtn.disabled = false;
        }
        updatePreview();
    }

    jenisEl.addEventListener('change', async function () {
        await loadUsedNumbers();
        await refreshNextSequence();
        checkNomorStatus();
    });

    tanggalEl.addEventListener('change', async function(){
        await loadUsedNumbers();
        await refreshNextSequence();
        checkNomorStatus();
    });

    nomorUrut.addEventListener('input', checkNomorStatus);

    (async function init(){
        toggleTanggalSelesai();
        await loadUsedNumbers();
        checkNomorStatus();
        updatePreviewTemplateBtn();
    })();
});
</script>
@endpush

@if (session('success') && session('created_nomor'))
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: 1px solid var(--line); border-radius: 0.9rem;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4 px-4">
                    <div class="mb-3"><i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: var(--success);"></i></div>
                    <h5 class="ledger-title mb-2" style="font-size: 1.2rem;">Kontrak Berhasil Dibuat</h5>
                    <p class="ledger-subtitle mb-1">{{ session('success') }}</p>
                    <p class="mb-0" style="font-family: var(--font-mono); font-weight: 600; color: var(--brass-dark); word-break: break-all;">
                        {{ session('created_nomor') }}
                    </p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                    <a href="{{ route('kontrak.index') }}" class="btn ledger-btn-brass">Lihat Daftar Kontrak</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('successModal');
            if (el) new bootstrap.Modal(el).show();
        });
    </script>
@endif

@endsection
