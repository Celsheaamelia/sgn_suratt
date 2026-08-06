@extends('layouts.app')

@section('content')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
@endpush

<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    :root {
        --ink:        #1c2b23;
        --ink-soft:   #3d4f45;
        --ledger:     #eef3ea;
        --ledger-line:#cdd9c8;
        --paper:      #fbfcf9;
        --brass:      #a9812f;
        --brass-dark: #8a6a24;
        --brass-tint: #f4ecd8;
        --line:       #dfe6da;
        --danger:     #b3432f;
        --danger-bg:  #fdf1ee;
        --success:    #3f6b4a;
        --success-bg: #eef5ef;
        --warn:       #b8903f;
        --warn-bg:    #fbf3e2;

        --font-display: 'Fraunces', Georgia, serif;
        --font-body: 'Inter', -apple-system, sans-serif;
        --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
    }

    .ledger-page { background: transparent; font-family: var(--font-body); color: var(--ink); min-height: 100vh; position: relative; isolation: isolate; }

    .ledger-alert-success { background: var(--success-bg); border: 1px solid #cfe2d4; color: var(--success); border-radius: 0.75rem; font-size: 0.9rem; }
    .ledger-alert-danger { background: var(--danger-bg); border: 1px solid #f2d3cc; color: var(--danger); border-radius: 0.75rem; font-size: 0.9rem; }

    .ledger-card, .ledger-stamp, .ledger-status {
        border-radius: 0.9rem; border: 1px solid var(--line);
        box-shadow: 0 1px 2px rgba(28,43,35,0.05), 0 1px 10px rgba(28,43,35,0.04);
    }
    .ledger-card { background: var(--paper); }
    .ledger-card-header { background: transparent; border-bottom: 1px solid var(--line); padding: 1.5rem 1.75rem 1.1rem; }
    .ledger-title { font-family: var(--font-display); font-weight: 600; font-size: 1.55rem; color: var(--ink); letter-spacing: -0.01em; margin-bottom: 0.25rem; }
    .ledger-subtitle { color: var(--ink-soft); font-size: 0.85rem; }
    .ledger-card .card-body { padding: 1.75rem; }

    .ledger-form label { color: var(--ink); font-weight: 600; font-size: 0.82rem; letter-spacing: 0.01em; }
    .ledger-required { color: var(--brass-dark); }

    .ledger-form .form-control, .ledger-form .form-select {
        border-radius: 0.55rem; border: 1px solid var(--line); padding: 0.65rem 0.95rem;
        font-size: 0.92rem; font-family: var(--font-body); color: var(--ink);
        background-color: var(--paper); transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .ledger-form .form-control:focus, .ledger-form .form-select:focus {
        outline: none; border-color: var(--brass); box-shadow: 0 0 0 3px rgba(169,129,47,0.16);
    }
    .ledger-form .form-control:focus-visible, .ledger-form .form-select:focus-visible,
    .ledger-btn-brass:focus-visible, .ledger-btn-ghost:focus-visible, .ledger-btn-cancel:focus-visible {
        outline: 2px solid var(--brass-dark); outline-offset: 2px;
    }
    .ledger-form .form-control::placeholder { color: #a3ada2; }

    .ledger-form .choices { margin-bottom: 0; font-size: 0.92rem; font-family: var(--font-body); width: 100% !important; max-width: 100%; }
    .ledger-form .choices__inner {
        background-color: var(--paper) !important; border: 1px solid var(--line) !important;
        border-radius: 0.55rem !important; padding: 0.5rem 0.75rem !important; min-height: 48px; width: 100%; box-sizing: border-box;
    }
    .ledger-form .choices.is-focused .choices__inner, .ledger-form .choices.is-open .choices__inner {
        border-color: var(--brass) !important; box-shadow: 0 0 0 3px rgba(169,129,47,0.16);
    }
    .ledger-form .choices__list--single .choices__item { color: var(--ink); }
    .ledger-form .choices__list--dropdown, .ledger-form .choices__list[aria-expanded] {
        border: 1px solid var(--line) !important; border-radius: 0.55rem !important;
        box-shadow: 0 8px 24px rgba(28,43,35,0.12); overflow: hidden; width: 100%; z-index: 20;
    }
    .ledger-form .choices__list--dropdown .choices__input { border-bottom: 1px solid var(--line) !important; background: var(--paper); color: var(--ink); font-family: var(--font-body); }
    .ledger-form .choices__list--dropdown .choices__item--selectable.is-highlighted { background-color: var(--brass-tint) !important; color: var(--brass-dark); }
    .ledger-form .choices__list--dropdown .choices__item { font-size: 0.9rem; padding: 0.6rem 0.85rem; }
    .ledger-form .choices__placeholder { opacity: 1; color: #a3ada2; }

    .ledger-help { color: #8a9587; font-size: 0.75rem; margin-top: 0.35rem; }

    .ledger-btn-ghost { background: transparent; color: var(--ink-soft); font-weight: 500; border: none; padding: 0.6rem 1rem; transition: color 0.15s ease; }
    .ledger-btn-ghost:hover { color: var(--ink); }

    .ledger-btn-brass {
        background: linear-gradient(180deg, #b8903f, var(--brass-dark)); box-shadow: 0 4px 14px rgba(138,106,36,0.35);
        border: none; color: #fff; font-weight: 600; letter-spacing: 0.01em; padding: 0.65rem 1.4rem; border-radius: 0.55rem;
    }
    .ledger-btn-brass:hover { background: linear-gradient(180deg, #c39a4c, #7a5c1f); box-shadow: 0 6px 18px rgba(138,106,36,0.42); color: #fff; }
    .ledger-btn-brass:disabled { opacity: 0.5; cursor: not-allowed; }

    .ledger-stamp {
        background: linear-gradient(160deg, #24382e 0%, var(--ink) 70%); border: 1px solid rgba(255,255,255,0.06);
        position: relative; overflow: hidden; color: var(--brass-tint);
    }
    .ledger-stamp::before {
        content: "TERDAFTAR"; position: absolute; top: 14px; right: -34px; font-family: var(--font-mono);
        font-size: 0.62rem; letter-spacing: 0.28em; color: rgba(244,236,216,0.14); transform: rotate(8deg); pointer-events: none;
    }
    .ledger-stamp-title { font-family: var(--font-display); font-weight: 600; font-size: 1.05rem; color: var(--brass-tint); }
    .ledger-stamp .fa-eye { color: var(--brass); }
    .ledger-stamp-box { background: rgba(169,129,47,0.08); border: 1.5px dashed rgba(244,236,216,0.35); border-radius: 0.85rem; position: relative; padding: 1.1rem 1rem; }
    .ledger-stamp-label { color: rgba(244,236,216,0.65); font-family: var(--font-mono); font-size: 0.65rem; letter-spacing: 0.22em; text-transform: uppercase; }

    .ledger-preview-list { max-height: 260px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.35rem; }
    .ledger-preview-list::-webkit-scrollbar { width: 6px; }
    .ledger-preview-list::-webkit-scrollbar-thumb { background: rgba(244,236,216,0.25); border-radius: 3px; }
    .ledger-preview-row { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; }
    .ledger-preview-item { color: var(--brass-tint); font-family: var(--font-mono); font-weight: 600; font-size: 0.86rem; letter-spacing: 0.02em; margin: 0; word-break: break-all; }
    .ledger-preview-more { color: rgba(244,236,216,0.55); font-family: var(--font-mono); font-size: 0.78rem; font-style: italic; margin: 0.2rem 0 0; }
    .ledger-preview-empty { color: rgba(244,236,216,0.55); font-family: var(--font-body); font-size: 0.85rem; margin: 0; }

    .ledger-preview-badge {
        font-family: var(--font-mono); font-size: 0.64rem; letter-spacing: 0.05em; text-transform: uppercase;
        white-space: nowrap; flex-shrink: 0; font-weight: 600;
    }
    .ledger-preview-badge.is-available { color: #8fd4a8; }
    .ledger-preview-badge.is-terpakai { color: #e08a7a; }
    .ledger-preview-badge.is-direservasi { color: #e0c27a; }

    .ledger-stamp-key { color: rgba(244,236,216,0.55); font-size: 0.83rem; }
    .ledger-stamp-value { color: var(--brass-tint); font-family: var(--font-mono); font-weight: 600; font-size: 0.86rem; letter-spacing: 0.02em; }

    .ledger-status { background: var(--paper); }
    .ledger-status-title { font-family: var(--font-display); font-weight: 600; font-size: 1rem; color: var(--ink); }
    .ledger-status-dot { width: 0.5rem; height: 0.5rem; border-radius: 50%; background: var(--brass); box-shadow: 0 0 0 3px rgba(169,129,47,0.18); display: inline-block; flex-shrink: 0; }
    .ledger-status-line { color: var(--ink-soft); font-family: var(--font-mono); font-size: 0.82rem; }

    .ledger-table-title { font-family: var(--font-display); font-weight: 600; font-size: 1.1rem; color: var(--ink); }
    .ledger-table thead { background: var(--ledger); }
    .ledger-table thead th { color: var(--ink-soft); font-family: var(--font-mono); font-size: 0.7rem; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 600; border-bottom: none; padding: 0.9rem 1.75rem; }
    .ledger-table tbody tr { border-top: 1px solid var(--ledger-line); }
    .ledger-table tbody tr:hover { background: var(--brass-tint); }
    .ledger-table tbody tr td { padding: 0.8rem 1.75rem; vertical-align: middle; }
    .ledger-table .ledger-nomor { color: var(--brass-dark); font-family: var(--font-mono); font-weight: 600; letter-spacing: 0.02em; word-break: break-all; }
    .ledger-table .ledger-perihal { color: var(--ink); }
    .ledger-table .ledger-tanggal { color: var(--ink-soft); font-family: var(--font-mono); font-size: 0.82rem; }

    .ledger-btn-cancel {
        background: transparent; color: var(--danger); font-weight: 600; font-size: 0.78rem;
        border: 1px solid var(--danger); padding: 0.4rem 0.9rem; border-radius: 0.5rem;
        transition: background-color 0.15s ease, color 0.15s ease; display: inline-flex; align-items: center; gap: 0.3rem;
        white-space: nowrap; cursor: pointer;
    }
    .ledger-btn-cancel:hover { background: var(--danger); color: #fff; }

    @media (max-width: 640px) {
        .ledger-stamp::before { display: none; }
        .ledger-table thead th, .ledger-table tbody tr td { padding: 0.75rem 1rem; }
    }
    @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>

<div class="ledger-page">
    <div class="container-fluid py-1 py-md-2">

        @if (session('success'))
            <div class="alert ledger-alert-success d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert ledger-alert-danger d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert ledger-alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="row g-4">

            {{-- FORM --}}
            <div class="col-lg-8">
                <div class="card ledger-card h-100">
                    <div class="card-header ledger-card-header">
                        <h2 class="ledger-title">Pencadangan Nomor Surat</h2>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('keepnomorsurat.store') }}" class="ledger-form" id="keepForm">
                            @csrf

                            <div class="mb-3">
                                <label for="perihal" class="form-label">
                                    Perihal <span class="ledger-required">*</span>
                                </label>
                                <input type="text" name="perihal" id="perihal" required
                                       placeholder="Keterangan"
                                       value="{{ old('perihal') }}" class="form-control">
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="signatory" class="form-label">
                                        Penandatangan <span class="ledger-required">*</span>
                                    </label>
                                    <select name="signatory" id="signatory" required class="form-select">
                                        <option value="">Pilih Penandatangan</option>
                                        @foreach ($penandatanganList as $penandatangan)
                                            <option value="{{ $penandatangan->id }}"
                                                    data-kode="{{ $penandatangan->kode }}"
                                                    @selected(old('signatory') == $penandatangan->id)>
                                                {{ $penandatangan->jabatan }} ({{ $penandatangan->kode }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="kode_tujuan" class="form-label">
                                        Kode Tujuan <span class="ledger-required">*</span>
                                    </label>
                                    <select name="kode_tujuan" id="kode_tujuan" required class="form-select">
                                        <option value="">Pilih Kode Tujuan</option>
                                        @foreach ($tujuanList as $tujuan)
                                            <option value="{{ $tujuan->id }}"
                                                    data-kode="{{ $tujuan->kode }}"
                                                    @selected(old('kode_tujuan') == $tujuan->id)>
                                                {{ $tujuan->kode }} — {{ $tujuan->nama_tujuan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="klasifikasi" class="form-label">
                                        Klasifikasi Surat <span class="ledger-required">*</span>
                                    </label>
                                    <select name="klasifikasi" id="klasifikasi" required class="form-select">
                                        <option value="">Pilih Klasifikasi Surat</option>
                                        @foreach ($klasifikasiList as $klasifikasi)
                                            <option value="{{ $klasifikasi->id }}"
                                                    data-kode="{{ $klasifikasi->kode }}"
                                                    @selected(old('klasifikasi') == $klasifikasi->id)>
                                                {{ $klasifikasi->kode }} — {{ $klasifikasi->jenis_surat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="tanggal" class="form-label">
                                        Tanggal <span class="ledger-required">*</span>
                                    </label>
                                    <input type="date" name="tanggal" id="tanggal" required
                                           value="{{ old('tanggal', date('Y-m-d')) }}" class="form-control">
                                </div>
                            </div>

                            {{-- Rentang Nomor --}}
                            <div class="mb-4">
                                <label class="form-label">
                                    Rentang Nomor <span class="ledger-required">*</span>
                                </label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input
                                            type="number"
                                            name="nomor_awal"
                                            id="nomor_awal"
                                            min="1"
                                            required
                                            placeholder="Nomor awal"
                                            value="{{ old('nomor_awal') }}"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <input
                                            type="number"
                                            name="nomor_akhir"
                                            id="nomor_akhir"
                                            min="1"
                                            required
                                            placeholder="Nomor akhir"
                                            value="{{ old('nomor_akhir') }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="ledger-help">
                                    Nomor urut berikutnya yang tersedia: <strong>#{{ str_pad($nextSequence, 3, '0', STR_PAD_LEFT) }}</strong>.
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-end gap-3">
                                <button type="reset" class="btn ledger-btn-ghost">Reset</button>
                                <button type="submit" class="btn ledger-btn-brass" id="keepSubmitBtn">
                                    <i class="fa-solid fa-lock me-1"></i>
                                    Cadangkan Nomor
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
                            <i class="fa-solid fa-eye"></i>
                            <h3 class="ledger-stamp-title mb-0">Live Preview</h3>
                        </div>

                        <div class="ledger-stamp-box mb-4">
                            <p class="ledger-stamp-label mb-2">Nomor yang Akan diCadangkan</p>
                            <div class="ledger-preview-list" id="previewList">
                                <p class="ledger-preview-empty">Isi rentang nomor yang valid.</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Penandatangan</span>
                            <span class="ledger-stamp-value" id="previewSign">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Tujuan</span>
                            <span class="ledger-stamp-value" id="previewTujuan">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="ledger-stamp-key">Klasifikasi</span>
                            <span class="ledger-stamp-value" id="previewKlasifikasi">-</span>
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
                            <span id="statusLine">Menunggu rentang nomor...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar keep aktif --}}
        <div class="card ledger-card mt-4">
            <div class="card-header ledger-card-header">
                <h3 class="ledger-table-title mb-0">Daftar Cadangan Nomor</h3>
            </div>
            <div class="card-body p-0">
                <table class="table ledger-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th>Tanggal</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($keepList ?? [] as $keep)
                            <tr>
                                <td class="ledger-nomor">{{ $keep->nomor_surat }}</td>
                                <td class="ledger-perihal">{{ $keep->perihal }}</td>
                                <td class="ledger-tanggal">{{ \Illuminate\Support\Carbon::parse($keep->tanggal)->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <form method="POST"
                                          action="{{ route('keepnomorsurat.cancel', $keep->id) }}"
                                          onsubmit="return confirm('Batalkan cadangan nomor {{ $keep->nomor_surat }}? Nomor ini akan tersedia lagi untuk yang lain.');"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ledger-btn-cancel">
                                            <i class="fa-solid fa-xmark"></i>
                                            Cancel
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 ledger-subtitle">
                                    Belum ada nomor yang di-cadangkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    new Choices('#kode_tujuan', { searchEnabled: true, itemSelectText: '', shouldSort: false });
    new Choices('#klasifikasi', { searchEnabled: true, itemSelectText: '', shouldSort: false });

    const signEl = document.getElementById('signatory');
    const tujuanEl = document.getElementById('kode_tujuan');
    const klasifikasiEl = document.getElementById('klasifikasi');
    const tanggalEl = document.getElementById('tanggal');
    const awalEl = document.getElementById('nomor_awal');
    const akhirEl = document.getElementById('nomor_akhir');

    const previewList = document.getElementById('previewList');
    const statusLine = document.getElementById('statusLine');
    const submitBtn = document.getElementById('keepSubmitBtn');

    const MAX_SHOW = 30; // batas tampil di preview biar nggak kepanjangan kalau rentangnya gede

    // Nomor yang statusnya sudah terpakai jadi surat definitif (Terupload / Belum Terupload)
    let terpakaiNumbers = [];
    // Nomor yang masih di-keep (Direservasi) oleh siapa pun, di tanggal terpilih
    let direservasiNumbers = [];

    function selectedKode(select) {
        if (select.selectedIndex == -1 || !select.value) return "-----";
        return select.options[select.selectedIndex].dataset.kode ?? "-----";
    }

    function formatTanggal(tanggal) {
        if (!tanggal) return "--------";
        return tanggal.replaceAll("-", "");
    }

    function pad(n) {
        return String(n).padStart(3, '0');
    }

    async function loadUsedNumbers() {
        if (!tanggalEl.value) {
            terpakaiNumbers = [];
            direservasiNumbers = [];
            return;
        }
        try {
            let response = await fetch(`{{ route('keepnomorsurat.cek-nomor') }}?tanggal=${tanggalEl.value}`);
            if (!response.ok) throw new Error('Gagal cek nomor terpakai');
            let data = await response.json();
            terpakaiNumbers = data.terpakai ?? [];
            direservasiNumbers = data.direservasi ?? [];
        } catch (err) {
            console.log(err);
            terpakaiNumbers = [];
            direservasiNumbers = [];
        }
    }

    function updatePreview() {
        let sign = selectedKode(signEl);
        let tujuan = selectedKode(tujuanEl);
        let klasifikasi = selectedKode(klasifikasiEl);
        let tanggalFormatted = formatTanggal(tanggalEl.value);

        document.getElementById("previewSign").textContent = sign;
        document.getElementById("previewTujuan").textContent = tujuan;
        document.getElementById("previewKlasifikasi").textContent = klasifikasi;
        document.getElementById("previewTanggal").textContent = tanggalEl.value || "-";

        previewList.innerHTML = "";

        let awal = parseInt(awalEl.value);
        let akhir = parseInt(akhirEl.value);

        if (!awal || !akhir || akhir < awal) {
            previewList.innerHTML = '<p class="ledger-preview-empty">Isi rentang nomor yang valid.</p>';
            statusLine.textContent = "Menunggu rentang nomor...";
            if (submitBtn) submitBtn.disabled = false;
            return;
        }

        let total = akhir - awal + 1;
        let shown = Math.min(total, MAX_SHOW);

        for (let i = 0; i < shown; i++) {
            let seq = awal + i;
            let isTerpakai = terpakaiNumbers.includes(seq);
            let isDireservasi = direservasiNumbers.includes(seq);

            let row = document.createElement('div');
            row.className = 'ledger-preview-row';

            let line = document.createElement('p');
            line.className = 'ledger-preview-item mb-0';
            line.textContent = `${sign}-${tujuan}-${klasifikasi}/${tanggalFormatted}.${pad(seq)}`;
            if (isTerpakai || isDireservasi) line.style.opacity = '0.55';

            let badge = document.createElement('span');
            badge.className = 'ledger-preview-badge';

            if (isTerpakai) {
                badge.textContent = 'SUDAH DIPAKAI';
                badge.classList.add('is-terpakai');
            } else if (isDireservasi) {
                badge.textContent = 'SUDAH DICADANGKAN';
                badge.classList.add('is-direservasi');
            } else {
                badge.textContent = 'TERSEDIA';
                badge.classList.add('is-available');
            }

            row.appendChild(line);
            row.appendChild(badge);
            previewList.appendChild(row);
        }

        if (total > MAX_SHOW) {
            let more = document.createElement('p');
            more.className = 'ledger-preview-more';
            more.textContent = `+${total - MAX_SHOW} nomor lagi...`;
            previewList.appendChild(more);
        }

        // Cek konflik di seluruh rentang (bukan cuma yang ditampilkan)
        let fullRange = [];
        for (let n = awal; n <= akhir; n++) fullRange.push(n);

        let conflictTerpakai = fullRange.filter(n => terpakaiNumbers.includes(n));
        let conflictDireservasi = fullRange.filter(n => direservasiNumbers.includes(n));

        if (conflictTerpakai.length > 0 || conflictDireservasi.length > 0) {
            let messages = [];

            if (conflictTerpakai.length > 0) {
                messages.push(`Sudah dipakai: #${conflictTerpakai.map(pad).join(', #')}`);
            }
            if (conflictDireservasi.length > 0) {
                messages.push(`Sudah dicadangkan: #${conflictDireservasi.map(pad).join(', #')}`);
            }

            statusLine.textContent = messages.join(' · ') + '. Ubah rentang.';
            statusLine.parentElement.querySelector('.ledger-status-dot').style.background = '#b3432f';
            if (submitBtn) submitBtn.disabled = true;
        } else {
            statusLine.textContent = `Siap cadangkan ${total} nomor (#${pad(awal)} – #${pad(akhir)})`;
            statusLine.parentElement.querySelector('.ledger-status-dot').style.background = 'var(--brass)';
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    async function refreshAndPreview() {
        await loadUsedNumbers();
        updatePreview();
    }

    signEl.addEventListener("change", updatePreview);
    tujuanEl.addEventListener("change", updatePreview);
    klasifikasiEl.addEventListener("change", updatePreview);
    tanggalEl.addEventListener("change", refreshAndPreview);
    awalEl.addEventListener("input", updatePreview);
    akhirEl.addEventListener("input", updatePreview);

    refreshAndPreview();
});
</script>
@endpush

@if (session('created_numbers'))
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: 1px solid var(--line); border-radius: 0.9rem;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4 px-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-circle-check" style="font-size: 2.5rem; color: var(--success);"></i>
                    </div>
                    <h5 class="ledger-title mb-2" style="font-size: 1.2rem;">Nomor Berhasil Dicadangkan</h5>
                    <p class="ledger-subtitle mb-1">{{ session('success') }}</p>
                    <div class="mb-0" style="max-height: 220px; overflow-y: auto;">
                        @foreach (session('created_numbers') as $nomorSurat)
                            <p class="mb-1" style="font-family: var(--font-mono); font-weight: 600; color: var(--brass-dark); word-break: break-all;">
                                {{ $nomorSurat }}
                            </p>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                    <a href="{{ route('riwayatsurat') }}" class="btn ledger-btn-brass">
                        Lihat Riwayat Surat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successModalEl = document.getElementById('successModal');
            if (successModalEl) {
                new bootstrap.Modal(successModalEl).show();
            }
        });
    </script>
@endif

@endsection
